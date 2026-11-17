<?php

namespace Tests\Feature;

use App\Models\Frame;
use App\Models\Size;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SizePricingTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create();
    }

    private function sizeNamed(string $name): Size
    {
        return Size::where('name', $name)->firstOrFail();
    }

    /** PNG dengan latar opaque dan dua lubang transparan (area foto). */
    private function framePng(int $width, int $height): string
    {
        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        imagefill($image, 0, 0, imagecolorallocatealpha($image, 245, 245, 245, 0));

        $hole = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefilledrectangle(
            $image,
            (int) ($width * 0.1), (int) ($height * 0.08),
            (int) ($width * 0.9), (int) ($height * 0.38),
            $hole
        );
        imagefilledrectangle(
            $image,
            (int) ($width * 0.1), (int) ($height * 0.55),
            (int) ($width * 0.9), (int) ($height * 0.85),
            $hole
        );

        $path = tempnam(sys_get_temp_dir(), 'frame_');
        imagepng($image, $path);
        imagedestroy($image);

        return $path;
    }

    private function uploadPng(int $width, int $height, string $name = 'frame.png'): UploadedFile
    {
        return new UploadedFile($this->framePng($width, $height), $name, 'image/png', null, true);
    }

    private function photo(): string
    {
        $bytes = base64_decode('/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/wAALCAABAAEBAREA/8QAFAABAAAAAAAAAAAAAAAAAAAACf/EABQQAQAAAAAAAAAAAAAAAAAAAAD/2gAIAQEAAD8AKp//2Q==');

        return 'data:image/jpeg;base64,'.base64_encode($bytes);
    }

    private function slot(): array
    {
        return [['x' => 0.1, 'y' => 0.1, 'w' => 0.8, 'h' => 0.3]];
    }

    public function test_admin_can_create_update_and_delete_size(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'auth')->post('/admin/sizes', [
            'name' => 'Polaroid',
            'width_cm' => 8.8,
            'height_cm' => 10.7,
            'price' => 40000,
            'is_active' => 1,
            'sort_order' => 4,
        ])->assertRedirect(route('admin.sizes.index'));

        $size = Size::where('name', 'Polaroid')->firstOrFail();
        $this->assertSame(40000, $size->price);

        $this->actingAs($admin, 'auth')
            ->get('/admin/sizes')
            ->assertOk()
            ->assertSee('Polaroid')
            ->assertSee('8.8 × 10.7 cm')
            ->assertSee('40.000');

        $this->actingAs($admin, 'auth')->put('/admin/sizes/'.$size->id, [
            'name' => 'Polaroid',
            'width_cm' => 8.8,
            'height_cm' => 10.7,
            'price' => 45000,
            'is_active' => 1,
            'sort_order' => 4,
        ])->assertRedirect(route('admin.sizes.index'));

        $this->assertSame(45000, $size->fresh()->price);

        $this->actingAs($admin, 'auth')
            ->delete('/admin/sizes/'.$size->id)
            ->assertRedirect(route('admin.sizes.index'));

        $this->assertDatabaseMissing('sizes', ['id' => $size->id]);
    }

    public function test_size_template_is_png_with_matching_ratio(): void
    {
        $admin = $this->admin();
        $size = $this->sizeNamed('4R');

        $response = $this->actingAs($admin, 'auth')
            ->get('/admin/sizes/'.$size->id.'/template')
            ->assertOk();

        $this->assertSame('image/png', $response->headers->get('Content-Type'));
        $this->assertStringContainsString(
            'template-4r-10.2x15.2cm.png',
            (string) $response->headers->get('Content-Disposition')
        );

        $image = imagecreatefromstring($response->getContent());
        $this->assertNotFalse($image);

        $ratio = imagesx($image) / imagesy($image);
        $this->assertEqualsWithDelta(10.2 / 15.2, $ratio, 0.01);

        imagedestroy($image);
    }

    public function test_frame_upload_rejects_png_with_wrong_ratio(): void
    {
        Storage::fake('public');
        $size = $this->sizeNamed('4R');

        $this->actingAs($this->admin(), 'auth')->post('/admin/frames', [
            'name' => 'Frame Persegi',
            'size_id' => $size->id,
            'is_active' => 1,
            'frame_image' => $this->uploadPng(400, 400),
        ])->assertSessionHasErrors('frame_image');

        $this->assertDatabaseMissing('frames', ['name' => 'Frame Persegi']);
        Storage::disk('public')->assertDirectoryEmpty('frames');
    }

    public function test_frame_upload_accepts_matching_ratio_and_links_size(): void
    {
        Storage::fake('public');
        $size = $this->sizeNamed('4R');

        $this->actingAs($this->admin(), 'auth')->post('/admin/frames', [
            'name' => 'Frame Sesuai',
            'size_id' => $size->id,
            'is_active' => 1,
            'frame_image' => $this->uploadPng(408, 608),
        ])->assertRedirect(route('admin.frames.index'))->assertSessionHasNoErrors();

        $frame = Frame::where('slug', 'frame-sesuai')->firstOrFail();

        $this->assertSame($size->id, $frame->size_id);
        $this->assertSame('4R', $frame->category);
        $this->assertNull($frame->price);
        $this->assertSame($size->price, $frame->effectivePrice());
        $this->assertGreaterThan(0, $frame->photo_count);
        Storage::disk('public')->assertExists('frames/frame-sesuai.png');
    }

    public function test_changing_size_rejects_existing_frame_with_wrong_ratio(): void
    {
        Storage::fake('public');
        $fourR = $this->sizeNamed('4R');
        $a4 = $this->sizeNamed('A4');

        $source = $this->framePng(408, 608);
        Storage::disk('public')->put('frames/frame-lama.png', file_get_contents($source));
        @unlink($source);

        $frame = Frame::create([
            'name' => 'Frame Lama',
            'slug' => 'frame-lama',
            'category' => '4R',
            'size_id' => $fourR->id,
            'price' => null,
            'image_path' => 'frames/frame-lama.png',
            'photo_count' => 2,
            'slots' => $this->slot(),
            'is_active' => true,
        ]);

        $this->actingAs($this->admin(), 'auth')->put('/admin/frames/'.$frame->id, [
            'name' => 'Frame Lama',
            'size_id' => $a4->id,
            'is_active' => 1,
        ])->assertSessionHasErrors('size_id');

        $this->assertSame($fourR->id, $frame->fresh()->size_id);
    }

    public function test_frame_price_overrides_size_price_only_when_filled(): void
    {
        $size = $this->sizeNamed('4R');

        $inherited = Frame::create([
            'name' => 'Ikut Ukuran',
            'slug' => 'ikut-ukuran',
            'category' => '4R',
            'size_id' => $size->id,
            'price' => null,
            'photo_count' => 2,
            'slots' => $this->slot(),
            'is_active' => true,
        ]);

        $override = Frame::create([
            'name' => 'Harga Khusus',
            'slug' => 'harga-khusus',
            'category' => '4R',
            'size_id' => $size->id,
            'price' => 12000,
            'photo_count' => 2,
            'slots' => $this->slot(),
            'is_active' => true,
        ]);

        $this->assertSame(30000, $inherited->effectivePrice());
        $this->assertSame(12000, $override->effectivePrice());

        // Perubahan harga ukuran langsung berlaku untuk frame tanpa override.
        $size->update(['price' => 31000]);
        $this->assertSame(31000, $inherited->fresh()->effectivePrice());
        $this->assertSame(12000, $override->fresh()->effectivePrice());
    }

    public function test_delete_size_is_blocked_while_frames_use_it(): void
    {
        $size = $this->sizeNamed('4R');

        Frame::create([
            'name' => 'Frame Terkait',
            'slug' => 'frame-terkait',
            'category' => '4R',
            'size_id' => $size->id,
            'price' => null,
            'photo_count' => 2,
            'slots' => $this->slot(),
            'is_active' => true,
        ]);

        $this->actingAs($this->admin(), 'auth')
            ->delete('/admin/sizes/'.$size->id)
            ->assertSessionHas('error');

        $this->assertDatabaseHas('sizes', ['id' => $size->id]);
    }

    public function test_size_dimensions_are_locked_while_frames_use_them_and_name_change_syncs_category(): void
    {
        $size = $this->sizeNamed('4R');

        $frame = Frame::create([
            'name' => 'Frame Ukuran',
            'slug' => 'frame-ukuran',
            'category' => '4R',
            'size_id' => $size->id,
            'price' => null,
            'photo_count' => 2,
            'slots' => $this->slot(),
            'is_active' => true,
        ]);

        $admin = $this->admin();

        // Dimensi tidak boleh berubah selama frame masih memakai ukuran ini.
        $this->actingAs($admin, 'auth')->put('/admin/sizes/'.$size->id, [
            'name' => '4R',
            'width_cm' => 11,
            'height_cm' => 15.2,
            'price' => 30000,
            'is_active' => 1,
        ])->assertSessionHasErrors('width_cm');

        $this->assertEqualsWithDelta(10.2, $size->fresh()->width_cm, 0.001);

        // Nama/harga tetap boleh diubah, dan kategori frame ikut tersinkron.
        $this->actingAs($admin, 'auth')->put('/admin/sizes/'.$size->id, [
            'name' => '4R Besar',
            'width_cm' => 10.2,
            'height_cm' => 15.2,
            'price' => 32000,
            'is_active' => 1,
        ])->assertRedirect(route('admin.sizes.index'));

        $this->assertSame('4R Besar', $size->fresh()->name);
        $this->assertSame('4R Besar', $frame->fresh()->category);
        $this->assertSame(32000, $frame->fresh()->effectivePrice());
    }

    public function test_kiosk_uses_size_price_and_prints_with_size_dimensions(): void
    {
        $size = $this->sizeNamed('4R');

        Frame::create([
            'name' => 'Ikut Ukuran',
            'slug' => 'ikut-ukuran',
            'category' => '4R',
            'size_id' => $size->id,
            'price' => null,
            'photo_count' => 2,
            'slots' => $this->slot(),
            'is_active' => true,
        ]);

        Frame::create([
            'name' => 'Harga Khusus',
            'slug' => 'harga-khusus',
            'category' => '4R',
            'size_id' => $size->id,
            'price' => 20000,
            'photo_count' => 2,
            'slots' => $this->slot(),
            'is_active' => true,
        ]);

        $this->post('/mulai');
        $this->post('/metode', ['metode' => 'qris'])
            ->assertRedirect('/frame');

        $this->get('/frame')
            ->assertOk()
            ->assertSee('data-size-name="4R"', false)
            ->assertSee('Rp 30.000');

        $this->post('/frame', ['cart' => ['ikut-ukuran' => 1, 'harga-khusus' => 1]])
            ->assertRedirect('/pembayaran');

        $this->get('/pembayaran')
            ->assertOk()
            ->assertSee('50.000')
            ->assertSee('Rp 30.000')
            ->assertSee('Rp 20.000')
            ->assertSee('4R');

        $this->post('/pembayaran')->assertRedirect('/foto');

        $photos = array_map(fn () => $this->photo(), range(1, 4));
        $this->post('/foto', ['photos' => $photos])
            ->assertJsonPath('redirect', url('/filter'));

        $this->post('/filter')->assertRedirect('/review');

        $review = $this->get('/review')->assertOk();
        $review->assertSee('data-print-width="10.2"', false);
        $review->assertSee('data-print-height="15.2"', false);
        $review->assertSee('50.000');
        $review->assertSee('4R');
    }
}
