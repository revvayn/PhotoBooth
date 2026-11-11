<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Frame;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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

    public function test_photo_results_are_archived_and_listed_in_admin_logs(): void
    {
        Storage::fake('public');

        $this->flowUntilPayment();

        $frame = Frame::first();
        $frame->image_path = 'frames/kpop.png';
        $frame->save();
        Storage::disk('public')->put('frames/kpop.png', 'frame');

        $this->post('/pembayaran');

        $photos = array_map(fn () => $this->photo(), range(1, 3));
        $this->post('/foto', ['photos' => $photos])->assertOk();

        $log = ActivityLog::first();
        $metadata = $log->fresh()->metadata;

        $this->assertCount(1, $metadata['items']);
        $this->assertCount(3, $metadata['items'][0]['photos']);
        $this->assertArrayHasKey('archive_dir', $metadata);
        Storage::disk('public')->assertExists($metadata['archive_dir'].'/foto-1.jpg');
        Storage::disk('public')->assertExists($metadata['archive_dir'].'/frame-kpop.png');
        $this->assertStringStartsWith('/storage/'.$metadata['archive_dir'].'/', $metadata['items'][0]['photos'][0]);
        $this->assertStringStartsWith('/storage/'.$metadata['archive_dir'].'/', $metadata['items'][0]['image_url']);

        $admin = User::factory()->create();

        $this->actingAs($admin, 'auth')
            ->get('/admin/logs')
            ->assertOk()
            ->assertSee('foto siap diunduh')
            ->assertSee('data-date-picker', false)
            ->assertSee('data-log-modal', false)
            ->assertSee('data-bulk-delete-modal', false)
            ->assertSee('Hapus filter')
            ->assertDontSee('<input type="date"', false);
    }

    public function test_admin_can_download_archived_photos_and_frame(): void
    {
        Storage::fake('public');

        $log = ActivityLog::create([
            'session_id' => 'download-session',
            'queue_number' => '#9001',
            'action' => ActivityLog::PAYMENT,
            'metadata' => [
                'metode' => 'qris',
                'total' => 30000,
                'photo_count' => 1,
                'items' => [],
            ],
        ]);

        $log->metadata = array_merge($log->metadata, [
            'items' => [[
                'slug' => 'kpop',
                'name' => 'K-pop Frame',
                'qty' => 1,
                'price' => 30000,
                'photo_count' => 1,
                'image_url' => '/storage/frames/frame.png',
                'slots' => [['x' => 0.1, 'y' => 0.1, 'w' => 0.8, 'h' => 0.8]],
                'photos' => ['/storage/arsip/'.$log->id.'/foto-1.jpg'],
                'filters' => ['asli'],
            ]],
        ]);
        $log->save();

        Storage::disk('public')->put('arsip/'.$log->id.'/foto-1.jpg', 'photo');
        Storage::disk('public')->put('frames/frame.png', 'frame');

        $admin = User::factory()->create();

        $response = $this->actingAs($admin, 'auth')
            ->get(route('admin.logs.download', $log))
            ->assertOk();

        $this->assertStringContainsString('.zip', (string) $response->headers->get('content-disposition'));
    }

    public function test_admin_can_delete_payment_log_and_archived_photos(): void
    {
        Storage::fake('public');

        $log = ActivityLog::create([
            'session_id' => 'delete-session',
            'queue_number' => '#9002',
            'action' => ActivityLog::PAYMENT,
            'metadata' => ['metode' => 'qris', 'total' => 30000, 'photo_count' => 1, 'items' => [], 'archive_dir' => 'arsip/test-delete'],
        ]);

        Storage::disk('public')->put('arsip/test-delete/foto-1.jpg', 'photo');

        $admin = User::factory()->create();

        $this->actingAs($admin, 'auth')
            ->delete(route('admin.logs.destroy', $log))
            ->assertRedirect(route('admin.logs.index'));

        $this->assertDatabaseMissing('activity_logs', ['id' => $log->id]);
        Storage::disk('public')->assertMissing('arsip/test-delete/foto-1.jpg');
    }

    public function test_admin_can_delete_logs_matching_filter(): void
    {
        Storage::fake('public');

        $qris = ActivityLog::create([
            'session_id' => 'bulk-qris',
            'queue_number' => '#9101',
            'action' => ActivityLog::PAYMENT,
            'metadata' => ['metode' => 'qris', 'total' => 30000, 'photo_count' => 0, 'items' => [], 'archive_dir' => 'arsip/bulk-qris'],
        ]);
        ActivityLog::create([
            'session_id' => 'bulk-cash',
            'queue_number' => '#9102',
            'action' => ActivityLog::PAYMENT,
            'metadata' => ['metode' => 'cash', 'total' => 45000, 'photo_count' => 0, 'items' => []],
        ]);
        ActivityLog::create(['session_id' => 'not-payment', 'queue_number' => '#9103', 'action' => 'session.started', 'metadata' => []]);
        Storage::disk('public')->put('arsip/bulk-qris/foto-1.jpg', 'photo');

        $admin = User::factory()->create();

        // Tanpa filter, endpoint menolak agar seluruh riwayat tidak terhapus.
        $this->actingAs($admin, 'auth')
            ->delete(route('admin.logs.destroy-filtered'))
            ->assertRedirect(route('admin.logs.index'))
            ->assertSessionHas('error');
        $this->assertSame(2, ActivityLog::where('action', ActivityLog::PAYMENT)->count());

        $this->actingAs($admin, 'auth')
            ->delete(route('admin.logs.destroy-filtered', ['metode' => 'qris']))
            ->assertRedirect(route('admin.logs.index', ['metode' => 'qris']))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('activity_logs', ['id' => $qris->id]);
        $this->assertSame(1, ActivityLog::where('action', ActivityLog::PAYMENT)->count());
        $this->assertSame(1, ActivityLog::where('action', 'session.started')->count());
        Storage::disk('public')->assertMissing('arsip/bulk-qris/foto-1.jpg');
    }

    public function test_admin_can_filter_logs_by_date_range(): void
    {
        $old = ActivityLog::create([
            'session_id' => 'old',
            'queue_number' => '#8001',
            'action' => ActivityLog::PAYMENT,
            'metadata' => ['metode' => 'qris', 'total' => 10000, 'photo_count' => 1, 'items' => []],
        ]);
        $old->created_at = now()->subDays(10);
        $old->save();

        ActivityLog::create([
            'session_id' => 'new',
            'queue_number' => '#8002',
            'action' => ActivityLog::PAYMENT,
            'metadata' => ['metode' => 'qris', 'total' => 10000, 'photo_count' => 1, 'items' => []],
        ]);

        $admin = User::factory()->create();

        $this->actingAs($admin, 'auth')
            ->get('/admin/logs?from='.now()->subDay()->toDateString().'&to='.now()->toDateString())
            ->assertOk()
            ->assertSee('#8002')
            ->assertDontSee('#8001');
    }
}
