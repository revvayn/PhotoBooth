<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Frame;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SessionFlowTest extends TestCase
{
    use RefreshDatabase;

    private function frame(): void
    {
        Frame::create([
            'name' => 'K-pop Frame', 'slug' => 'kpop', 'category' => 'K-pop', 'photo_count' => 3, 'price' => 30000,
            'slots' => [
                ['x' => 0.1, 'y' => 0.1, 'w' => 0.8, 'h' => 0.2],
                ['x' => 0.1, 'y' => 0.4, 'w' => 0.8, 'h' => 0.2],
                ['x' => 0.1, 'y' => 0.7, 'w' => 0.8, 'h' => 0.2],
            ],
        ]);
    }

    private function photo(): string
    {
        $bytes = base64_decode('/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/wAALCAABAAEBAREA/8QAFAABAAAAAAAAAAAAAAAAAAAACf/EABQQAQAAAAAAAAAAAAAAAAAAAAD/2gAIAQEAAD8AKp//2Q==');

        return 'data:image/jpeg;base64,'.base64_encode($bytes);
    }

    private function payFlow(): void
    {
        $this->frame();
        $this->post('/mulai');
        $this->post('/metode', ['metode' => 'qris']);
        $this->post('/frame', ['cart' => ['kpop' => 1]]);
        $this->post('/pembayaran');
        $this->post('/foto', ['photos' => array_map(fn () => $this->photo(), range(1, 3))]);
    }

    public function test_expired_session_is_cleaned_and_redirected_home(): void
    {
        Storage::fake('public');
        $this->frame();
        $this->post('/mulai');

        $this->withSession([
            'started_at' => now()->timestamp - 700,
            'total_seconds' => 600,
        ]);

        $this->get('/frame')->assertRedirect('/');
        $this->assertFalse(session()->has('queue'));
        $this->assertTrue(session()->has('expired'));
    }

    public function test_spa_with_expired_session_redirects_home(): void
    {
        $this->post('/mulai');
        $this->withSession([
            'started_at' => now()->timestamp - 700,
            'total_seconds' => 600,
        ]);

        $this->get('/spa?step=frame')->assertRedirect('/');
    }

    public function test_non_image_photo_payload_is_rejected(): void
    {
        $this->frame();
        $this->post('/mulai');
        $this->post('/metode', ['metode' => 'qris']);
        $this->post('/frame', ['cart' => ['kpop' => 1]]);
        $this->post('/pembayaran');

        $this->postJson('/foto', [
            'photos' => ['data:image/jpeg;base64,'.base64_encode('bukan gambar'), 'x', 'y'],
        ])->assertStatus(422);

        $this->assertFalse(session()->has('photos'));
    }

    public function test_email_route_is_removed(): void
    {
        $this->postJson('/email', ['email' => 'tamu@example.com'])->assertNotFound();
    }

    public function test_download_page_reads_token_from_payment_log(): void
    {
        Storage::fake('public');
        $this->payFlow();

        $token = session('download_token');
        $this->assertIsString($token);
        $this->assertNotSame('', $token);

        $log = ActivityLog::payments()->latest('id')->firstOrFail();
        $this->assertSame($token, $log->metadata['download_token'] ?? null);

        $this->get('/hasil')->assertOk()->assertSee('Unduh Hasil Foto');
        $this->get('/hasil?token='.$token)->assertRedirect('/hasil/'.$token);
        $this->get('/hasil/'.$token)
            ->assertOk()
            ->assertSee('data-public-download-root')
            ->assertSee('data-public-canvas');
    }
}
