<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Frame;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PaymentLogTest extends TestCase
{
    use RefreshDatabase;

    private function photo(): string
    {
        $bytes = base64_decode('/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/wAALCAABAAEBAREA/8QAFAABAAAAAAAAAAAAAAAAAAAACf/EABQQAQAAAAAAAAAAAAAAAAAAAAD/2gAIAQEAAD8AKp//2Q==');

        return 'data:image/jpeg;base64,'.base64_encode($bytes);
    }

    /** Jalankan alur kiosk sampai tepat sebelum konfirmasi pembayaran. */
    private function flowUntilPayment(): void
    {
        Frame::create([
            'name' => 'K-pop Frame', 'slug' => 'kpop', 'category' => 'K-pop', 'photo_count' => 3, 'price' => 30000,
            'slots' => [
                ['x' => 0.1, 'y' => 0.1, 'w' => 0.8, 'h' => 0.2],
                ['x' => 0.1, 'y' => 0.4, 'w' => 0.8, 'h' => 0.2],
                ['x' => 0.1, 'y' => 0.7, 'w' => 0.8, 'h' => 0.2],
            ],
        ]);

        $this->post('/mulai');
        $this->post('/metode', ['metode' => 'qris']);
        $this->post('/frame', ['cart' => ['kpop' => 2]]);
    }

    public function test_payment_is_logged_once_with_details(): void
    {
        $this->flowUntilPayment();

        $this->post('/pembayaran')->assertRedirect('/foto');
        $this->post('/pembayaran')->assertRedirect('/foto'); // klik ganda

        $this->assertSame(1, ActivityLog::count());

        $log = ActivityLog::first();
        $this->assertSame(ActivityLog::PAYMENT, $log->action);
        $this->assertSame('qris', $log->payment_method);
        $this->assertSame(60000, $log->payment_total);
        $this->assertSame(3, $log->payment_photo_count);
        $this->assertStringContainsString('K-pop Frame x2', $log->payment_items_label);
    }

    public function test_nothing_else_is_logged_during_the_flow(): void
    {
        $this->flowUntilPayment();

        $this->assertSame(0, ActivityLog::count());

        $this->post('/pembayaran');
        $this->get('/selesai');

        $this->assertSame(1, ActivityLog::where('action', ActivityLog::PAYMENT)->count());
        $this->assertSame(1, ActivityLog::count());
    }

    public function test_cashier_pin_blocks_payment_until_correct(): void
    {
        Setting::set('cashier_pin_hash', Hash::make('1234'));
        $this->flowUntilPayment();

        $this->post('/pembayaran')->assertSessionHasErrors('pin');
        $this->post('/pembayaran', ['pin' => '0000'])->assertSessionHasErrors('pin');
        $this->assertFalse(session()->has('paid'));
        $this->assertSame(0, ActivityLog::count());

        $this->post('/pembayaran', ['pin' => '1234'])->assertRedirect('/foto');
        $this->assertTrue(session()->has('paid'));
        $this->assertSame(1, ActivityLog::count());
    }

    public function test_spa_locks_to_photo_stage_after_payment_and_review_after_photos(): void
    {
        $this->flowUntilPayment();
        $this->post('/pembayaran', ['from_spa' => 1]);

        // Pembayaran sudah masuk: frame tidak boleh diubah lagi.
        $html = $this->get('/spa?step=frame')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/data-spa-step-dot="foto" data-state="active"/', $html);

        $photos = array_map(fn () => $this->photo(), range(1, 3));
        $this->post('/foto', ['photos' => $photos, 'from_spa' => 1]);

        $html = $this->get('/spa?step=frame')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/data-spa-step-dot="review" data-state="active"/', $html);
    }

    public function test_admin_logs_require_login(): void
    {
        $this->get('/admin/logs')->assertRedirect(route('admin.login'));
        $this->get('/admin')->assertRedirect(route('admin.login'));
    }

    public function test_admin_sees_only_payments_with_sorting(): void
    {
        $admin = User::factory()->create();

        ActivityLog::create(['session_id' => 'a', 'queue_number' => '#1111', 'action' => ActivityLog::PAYMENT,
            'metadata' => ['metode' => 'qris', 'total' => 30000, 'photo_count' => 3, 'items' => [['name' => 'K-pop Frame', 'qty' => 1, 'price' => 30000]]]]);
        ActivityLog::create(['session_id' => 'b', 'queue_number' => '#2222', 'action' => ActivityLog::PAYMENT,
            'metadata' => ['metode' => 'cash', 'total' => 90000, 'photo_count' => 3, 'items' => []]]);
        ActivityLog::create(['session_id' => 'c', 'queue_number' => '#3333', 'action' => 'session.started', 'metadata' => []]);

        $html = $this->actingAs($admin, 'auth')
            ->get('/admin/logs?sort=total&dir=desc')
            ->assertOk()
            ->assertSee('#1111')
            ->assertSee('#2222')
            ->assertDontSee('#3333')
            ->getContent();

        // total terbesar muncul lebih dulu
        $this->assertLessThan(strpos($html, '#1111'), strpos($html, '#2222'));

        $this->actingAs($admin, 'auth')->get('/admin')->assertOk()->assertSee('Pendapatan Hari Ini');

        $this->actingAs($admin, 'auth')->get('/admin/logs?metode=cash')
            ->assertOk()->assertSee('#2222')->assertDontSee('#1111');
    }
}
