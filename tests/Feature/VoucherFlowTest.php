<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Frame;
use App\Models\Size;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoucherFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $fourR = Size::where('name', '4R')->firstOrFail();
        $a4 = Size::where('name', 'A4')->firstOrFail();

        Frame::create([
            'name' => 'K-pop Frame', 'slug' => 'kpop', 'category' => '4R', 'size_id' => $fourR->id,
            'photo_count' => 3, 'price' => null,
            'slots' => [
                ['x' => 0.1, 'y' => 0.1, 'w' => 0.8, 'h' => 0.2],
                ['x' => 0.1, 'y' => 0.4, 'w' => 0.8, 'h' => 0.2],
                ['x' => 0.1, 'y' => 0.7, 'w' => 0.8, 'h' => 0.2],
            ],
        ]);

        Frame::create([
            'name' => 'Neon Frame', 'slug' => 'neon', 'category' => '4R', 'size_id' => $fourR->id,
            'photo_count' => 4, 'price' => null,
            'slots' => [
                ['x' => 0.1, 'y' => 0.1, 'w' => 0.8, 'h' => 0.1],
                ['x' => 0.1, 'y' => 0.3, 'w' => 0.8, 'h' => 0.1],
                ['x' => 0.1, 'y' => 0.5, 'w' => 0.8, 'h' => 0.1],
                ['x' => 0.1, 'y' => 0.7, 'w' => 0.8, 'h' => 0.1],
            ],
        ]);

        Frame::create([
            'name' => 'A4 Frame', 'slug' => 'a4frame', 'category' => 'A4', 'size_id' => $a4->id,
            'photo_count' => 2, 'price' => null,
            'slots' => [
                ['x' => 0.1, 'y' => 0.1, 'w' => 0.8, 'h' => 0.4],
                ['x' => 0.1, 'y' => 0.6, 'w' => 0.8, 'h' => 0.3],
            ],
        ]);
    }

    private function photo(): string
    {
        $bytes = base64_decode('/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/wAALCAABAAEBAREA/8QAFAABAAAAAAAAAAAAAAAAAAAACf/EABQQAQAAAAAAAAAAAAAAAAAAAAD/2gAIAQEAAD8AKp//2Q==');

        return 'data:image/jpeg;base64,'.base64_encode($bytes);
    }

    private function voucher(array $overrides = []): Voucher
    {
        $fourR = Size::where('name', '4R')->firstOrFail();

        return Voucher::create(array_merge([
            'code' => 'PB-TEST',
            'name' => 'Voucher Test',
            'items' => [$fourR->id => 2],
            'max_uses' => 1,
            'used_count' => 0,
            'is_active' => true,
        ], $overrides));
    }

    public function test_voucher_requires_frame_selection_and_reaches_review(): void
    {
        $voucher = $this->voucher();

        $this->post('/mulai');
        $this->post('/metode', ['metode' => 'voucher', 'voucher_code' => 'pb-test'])
            ->assertRedirect('/frame');

        $this->assertSame('voucher', session('metode'));
        $this->assertTrue(session()->has('paid'));
        $this->assertNull(session('cart'));
        $this->assertSame('PB-TEST', session('voucher.code'));
        $this->assertSame(1, $voucher->fresh()->used_count);

        // Frame dan konfirmasi QRIS dilewati sampai frame dipilih.
        $this->get('/frame')->assertOk()->assertSee('data-voucher-frame-form')->assertSee('PB-TEST');
        $this->get('/pembayaran')->assertRedirect('/');

        $this->post('/frame', ['cart' => ['kpop' => 1, 'neon' => 1]])->assertRedirect('/foto');

        $this->assertSame(['kpop' => 1, 'neon' => 1], session('cart'));

        $foto = $this->get('/foto')->assertOk();
        $foto->assertSee('PB-TEST');
        $foto->assertSee('7 foto');

        $photos = array_map(fn () => $this->photo(), range(1, 7));
        $this->post('/foto', ['photos' => $photos])
            ->assertJsonPath('redirect', url('/filter'));

        $this->post('/filter')->assertRedirect('/review');

        $this->get('/review')
            ->assertOk()
            ->assertSee('Voucher terverifikasi')
            ->assertSee('PB-TEST');

        $log = ActivityLog::latest('id')->first();
        $this->assertSame('voucher', $log->payment_method);
        $this->assertSame(0, $log->payment_total);
        $this->assertSame('PB-TEST', $log->metadata['voucher_code'] ?? null);
        $this->assertSame('Voucher Test', $log->metadata['voucher_name'] ?? null);
    }

    public function test_voucher_frame_selection_must_match_size_quota(): void
    {
        $this->voucher();

        $this->post('/mulai');
        $this->post('/metode', ['metode' => 'voucher', 'voucher_code' => 'PB-TEST'])
            ->assertRedirect('/frame');

        // Kuota 4R x2, pilih 1 frame saja -> ditolak.
        $this->post('/frame', ['cart' => ['kpop' => 1]])
            ->assertSessionHasErrors('cart');
        $this->assertNull(session('cart'));

        // Pilih 2 frame ukuran 4R -> diterima.
        $this->post('/frame', ['cart' => ['kpop' => 1, 'neon' => 1]])
            ->assertRedirect('/foto');
        $this->assertSame(['kpop' => 1, 'neon' => 1], session('cart'));
    }

    public function test_repeating_the_same_voucher_does_not_use_quota_twice(): void
    {
        $voucher = $this->voucher(['max_uses' => 2]);

        $this->post('/mulai');
        $this->post('/metode', ['metode' => 'voucher', 'voucher_code' => 'PB-TEST']);
        $this->post('/metode', ['metode' => 'voucher', 'voucher_code' => 'PB-TEST'])
            ->assertRedirect('/frame');

        $this->assertSame(1, $voucher->fresh()->used_count);
        $this->assertSame(2, ActivityLog::count());
    }

    public function test_voucher_errors_do_not_consume_quota(): void
    {
        $this->post('/mulai');

        $this->post('/metode', ['metode' => 'voucher', 'voucher_code' => 'TIDAK-ADA'])
            ->assertSessionHasErrors('voucher_code');

        $expired = $this->voucher(['code' => 'PB-EXPIRED', 'expires_at' => now()->subDay()]);
        $this->post('/metode', ['metode' => 'voucher', 'voucher_code' => 'PB-EXPIRED'])
            ->assertSessionHasErrors('voucher_code');

        $inactive = $this->voucher(['code' => 'PB-OFF', 'is_active' => false]);
        $this->post('/metode', ['metode' => 'voucher', 'voucher_code' => 'PB-OFF'])
            ->assertSessionHasErrors('voucher_code');

        $exhausted = $this->voucher(['code' => 'PB-HABIS', 'max_uses' => 1, 'used_count' => 1]);
        $this->post('/metode', ['metode' => 'voucher', 'voucher_code' => 'PB-HABIS'])
            ->assertSessionHasErrors('voucher_code');

        $this->assertSame(0, $expired->fresh()->used_count);
        $this->assertSame(0, $inactive->fresh()->used_count);
        $this->assertSame(1, $exhausted->fresh()->used_count);
        $this->assertSame(3, ActivityLog::count());
    }

    public function test_admin_voucher_and_appearance_pages_render(): void
    {
        $admin = User::factory()->create();
        $voucher = $this->voucher();

        $this->actingAs($admin, 'auth');

        $this->get('/admin/vouchers')->assertOk()->assertSee('PB-TEST');
        $this->get('/admin/vouchers/create')->assertOk()->assertSee('4R');
        $this->get('/admin/vouchers/'.$voucher->id.'/edit')->assertOk()->assertSee('PB-TEST');
        $this->get('/admin/appearance')->assertOk()->assertSee('Identitas Brand');

        $this->get('/admin/vouchers/'.$voucher->id.'/print-invoice')
            ->assertOk()
            ->assertSee('INVOICE')
            ->assertSee('Voucher Test')
            ->assertSee('id="print-sheet"', false);
        $this->get('/admin/vouchers/'.$voucher->id.'/print-voucher')
            ->assertOk()
            ->assertSee('PB-TEST')
            ->assertSee('Cara Penggunaan')
            ->assertSee('id="print-sheet"', false);
    }

    public function test_admin_can_create_voucher_with_size_quantities(): void
    {
        $admin = User::factory()->create();
        $fourR = Size::where('name', '4R')->firstOrFail();

        $this->actingAs($admin, 'auth')
            ->post('/admin/vouchers', [
                'type' => 'cash',
                'name' => 'Voucher Event',
                'code' => 'pb-event',
                'description' => 'Event kantor',
                'sizes' => [$fourR->id => 2],
                'max_uses' => 5,
                'expires_at' => now()->toDateString(),
                'is_active' => 1,
            ])
            ->assertRedirect('/admin/vouchers');

        $voucher = Voucher::firstOrFail();
        $this->assertSame('PB-EVENT', $voucher->code);
        $this->assertSame([$fourR->id => 2], $voucher->items);
        $this->assertSame(5, $voucher->max_uses);
        $this->assertTrue($voucher->expires_at->isEndOfDay());
        $this->assertTrue($voucher->isUsable());
        $this->assertSame('cash', $voucher->type);
        $this->assertSame(2, $voucher->photos_per_voucher);
        $this->assertSame(60000, $voucher->cost_per_person);
    }

    public function test_admin_voucher_create_uses_step_form_and_filters(): void
    {
        $admin = User::factory()->create();
        $cash = $this->voucher(['code' => 'PB-CASH', 'name' => 'Cash Filter']);
        $cash->forceFill(['created_at' => now()->subDays(2)])->save();
        $this->voucher(['code' => 'PB-EVENT', 'name' => 'Event Filter', 'type' => 'event', 'event_name' => 'Acara Filter']);

        $this->actingAs($admin, 'auth');

        $this->get('/admin/vouchers/create?type=cash')
            ->assertOk()
            ->assertSee('data-voucher-form', false)
            ->assertSee('Alur pembuatan')
            ->assertDontSee('Pengaturan event')
            ->assertDontSee('name="event_name"', false);

        $this->get('/admin/vouchers/create?type=event')
            ->assertOk()
            ->assertSee('data-step-panel="2"', false)
            ->assertSee('Pengaturan event')
            ->assertSee('Kode event dibuat otomatis');

        $this->get('/admin/vouchers?q=Event+Filter&type=event&status=active')
            ->assertOk()
            ->assertSee('PB-EVENT')
            ->assertDontSee('PB-CASH')
            ->assertSee('data-date-picker', false)
            ->assertSee('Tanggal dibuat');

        $this->get('/admin/vouchers?from='.now()->toDateString().'&to='.now()->toDateString())
            ->assertOk()
            ->assertSee('PB-EVENT')
            ->assertDontSee('PB-CASH');
    }

    public function test_frame_mode_voucher_skips_frame_selection(): void
    {
        $voucher = Voucher::create([
            'code' => 'PB-FRAME',
            'name' => 'Voucher Frame',
            'items' => ['kpop' => 2],
            'max_uses' => 1,
            'used_count' => 0,
            'is_active' => true,
        ]);

        $this->post('/mulai');
        $this->post('/metode', ['metode' => 'voucher', 'voucher_code' => 'PB-FRAME'])
            ->assertRedirect('/foto');

        $this->assertSame('voucher', session('metode'));
        $this->assertTrue(session()->has('paid'));
        $this->assertSame(['kpop' => 2], session('cart'));
        $this->assertSame(1, $voucher->fresh()->used_count);

        $this->get('/frame')->assertRedirect('/foto');

        $foto = $this->get('/foto')->assertOk();
        $foto->assertSee('PB-FRAME');
        $foto->assertSee('3 foto');

        $photos = array_map(fn () => $this->photo(), range(1, 3));
        $this->post('/foto', ['photos' => $photos])
            ->assertJsonPath('redirect', url('/filter'));

        $this->post('/filter')->assertRedirect('/review');
        $this->get('/review')->assertOk()->assertSee('Voucher terverifikasi');
    }

    public function test_admin_can_create_frame_mode_voucher(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin, 'auth')
            ->post('/admin/vouchers', [
                'type' => 'cash',
                'name' => 'Voucher Frame Mode',
                'code' => 'pb-frame-mode',
                'frames' => ['kpop' => 2, 'neon' => 0],
                'max_uses' => 3,
                'is_active' => 1,
            ])
            ->assertRedirect('/admin/vouchers');

        $voucher = Voucher::firstOrFail();
        $this->assertSame('PB-FRAME-MODE', $voucher->code);
        $this->assertSame(['kpop' => 2], $voucher->items);
        $this->assertTrue($voucher->isFrameMode());
        $this->assertSame(6, $voucher->photos_per_voucher);
    }

    public function test_admin_can_create_event_vouchers_with_generated_codes(): void
    {
        $admin = User::factory()->create();
        $fourR = Size::where('name', '4R')->firstOrFail();

        $this->actingAs($admin, 'auth')
            ->post('/admin/vouchers', [
                'type' => 'event',
                'name' => 'Voucher Event',
                'event_code' => 'wedding',
                'event_name' => 'Pernikahan Budi',
                'event_date' => now()->addWeek()->toDateString(),
                'number_of_people' => 2,
                'sizes' => [$fourR->id => 2],
                'discount' => 10,
                'is_active' => 1,
            ])
            ->assertRedirect('/admin/vouchers');

        $voucher = Voucher::firstOrFail();
        $this->assertSame('WEDDING', $voucher->code);
        $this->assertSame(2, $voucher->max_uses);
        $this->assertTrue($voucher->event_date instanceof \Carbon\CarbonInterface);
        $this->assertSame(2, $voucher->photos_per_voucher);
        $this->assertSame(120000, $voucher->total_cost);
        $this->assertSame(10, $voucher->discount);
    }
}