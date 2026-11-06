<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Frame;
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

        Frame::create([
            'name' => 'K-pop Frame', 'slug' => 'kpop', 'category' => 'K-pop', 'photo_count' => 3, 'price' => 30000,
            'slots' => [
                ['x' => 0.1, 'y' => 0.1, 'w' => 0.8, 'h' => 0.2],
                ['x' => 0.1, 'y' => 0.4, 'w' => 0.8, 'h' => 0.2],
                ['x' => 0.1, 'y' => 0.7, 'w' => 0.8, 'h' => 0.2],
            ],
        ]);

        Frame::create([
            'name' => 'Neon Frame', 'slug' => 'neon', 'category' => 'Neon', 'photo_count' => 4, 'price' => 30000,
            'slots' => [
                ['x' => 0.1, 'y' => 0.1, 'w' => 0.8, 'h' => 0.1],
                ['x' => 0.1, 'y' => 0.3, 'w' => 0.8, 'h' => 0.1],
                ['x' => 0.1, 'y' => 0.5, 'w' => 0.8, 'h' => 0.1],
                ['x' => 0.1, 'y' => 0.7, 'w' => 0.8, 'h' => 0.1],
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
        return Voucher::create(array_merge([
            'code' => 'PB-TEST',
            'name' => 'Voucher Test',
            'items' => ['kpop' => 2],
            'max_uses' => 1,
            'used_count' => 0,
            'is_active' => true,
        ], $overrides));
    }

    public function test_voucher_skips_frame_and_payment_and_reaches_review(): void
    {
        $voucher = $this->voucher();

        $this->post('/mulai');
        $this->post('/metode', ['metode' => 'voucher', 'voucher_code' => 'pb-test'])
            ->assertRedirect('/foto');

        $this->assertSame('voucher', session('metode'));
        $this->assertTrue(session()->has('paid'));
        $this->assertSame(['kpop' => 2], session('cart'));
        $this->assertSame('PB-TEST', session('voucher.code'));
        $this->assertSame(1, $voucher->fresh()->used_count);

        // Frame dan konfirmasi QRIS dilewati.
        $this->get('/frame')->assertRedirect('/foto');
        $this->get('/pembayaran')->assertRedirect('/foto');

        $foto = $this->get('/foto')->assertOk();
        $foto->assertSee('PB-TEST');
        $foto->assertSee('<strong>3 foto</strong>', false);

        $photos = array_map(fn () => $this->photo(), range(1, 3));
        $this->post('/foto', ['photos' => $photos])
            ->assertJsonPath('redirect', 'http://localhost/review');

        $this->get('/review')
            ->assertOk()
            ->assertSee('Voucher terverifikasi')
            ->assertSee('PB-TEST');

        $log = ActivityLog::first();
        $this->assertSame('voucher', $log->payment_method);
        $this->assertSame(0, $log->payment_total);
        $this->assertSame('PB-TEST', $log->metadata['voucher_code'] ?? null);
    }

    public function test_repeating_the_same_voucher_does_not_use_quota_twice(): void
    {
        $voucher = $this->voucher(['max_uses' => 2]);

        $this->post('/mulai');
        $this->post('/metode', ['metode' => 'voucher', 'voucher_code' => 'PB-TEST']);
        $this->post('/metode', ['metode' => 'voucher', 'voucher_code' => 'PB-TEST'])
            ->assertRedirect('/foto');

        $this->assertSame(1, $voucher->fresh()->used_count);
        $this->assertSame(1, ActivityLog::count());
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
        $this->assertSame(0, ActivityLog::count());
    }

    public function test_admin_voucher_and_appearance_pages_render(): void
    {
        $admin = User::factory()->create();
        $voucher = $this->voucher();

        $this->actingAs($admin, 'auth');

        $this->get('/admin/vouchers')->assertOk()->assertSee('PB-TEST');
        $this->get('/admin/vouchers/create')->assertOk()->assertSee('K-pop Frame');
        $this->get('/admin/vouchers/'.$voucher->id.'/edit')->assertOk()->assertSee('PB-TEST');
        $this->get('/admin/appearance')->assertOk()->assertSee('Identitas Brand');
    }

    public function test_admin_can_create_voucher_with_frame_quantities(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin, 'auth')
            ->post('/admin/vouchers', [
                'name' => 'Voucher Event',
                'code' => 'pb-event',
                'description' => 'Event kantor',
                'frames' => ['kpop' => 2, 'neon' => 0],
                'max_uses' => 5,
                'expires_at' => now()->toDateString(),
                'is_active' => 1,
            ])
            ->assertRedirect('/admin/vouchers');

        $voucher = Voucher::firstOrFail();
        $this->assertSame('PB-EVENT', $voucher->code);
        $this->assertSame(['kpop' => 2], $voucher->items);
        $this->assertSame(5, $voucher->max_uses);
        $this->assertTrue($voucher->expires_at->isEndOfDay());
        $this->assertTrue($voucher->isUsable());
        $this->assertTrue($voucher->is_active);
    }
}
