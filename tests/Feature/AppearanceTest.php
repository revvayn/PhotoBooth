<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AppearanceTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'brand_name' => 'Momen Kita',
            'brand_tagline' => 'Foto seru tiap hari',
            'brand_primary' => '#112233',
            'brand_secondary' => '#445566',
            'brand_accent' => '#778899',
            'brand_bg_from' => '#fef2f2',
            'brand_bg_via' => '#f5f3ff',
            'brand_bg_to' => '#eff6ff',
            'brand_welcome' => 'Selamat datang di Momen Kita.',
            'brand_footer' => 'Momen Kita Studio',
        ], $overrides);
    }

    public function test_admin_can_update_brand_and_it_applies_to_kiosk(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin, 'auth')
            ->put('/admin/appearance', $this->payload())
            ->assertRedirect();

        $this->assertSame('Momen Kita', Setting::get('brand_name'));
        $this->assertSame('#112233', Setting::get('brand_primary'));

        $this->get('/')
            ->assertOk()
            ->assertSee('Momen Kita')
            ->assertSee('Selamat datang di Momen Kita.')
            ->assertSee('--brand-primary: #112233', false);
    }

    public function test_logo_upload_and_removal(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create();

        $this->actingAs($admin, 'auth')
            ->put('/admin/appearance', $this->payload([
                'brand_logo' => UploadedFile::fake()->image('logo.png', 64, 64),
            ]))
            ->assertRedirect();

        $path = Setting::get('brand_logo');
        $this->assertNotEmpty($path);
        Storage::disk('public')->assertExists($path);

        $this->put('/admin/appearance', $this->payload(['remove_brand_logo' => '1']))
            ->assertRedirect();

        $this->assertSame('', Setting::get('brand_logo'));
        Storage::disk('public')->assertMissing($path);
    }

    public function test_invalid_brand_color_is_rejected(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin, 'auth')
            ->put('/admin/appearance', $this->payload(['brand_primary' => 'merah']))
            ->assertSessionHasErrors('brand_primary');

        $this->assertNull(Setting::get('brand_primary'));
    }
}
