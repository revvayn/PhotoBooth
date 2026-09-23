<?php

namespace Tests\Feature;

use App\Models\Frame;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrameCartFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            ['name' => 'K-pop Frame', 'slug' => 'kpop', 'category' => 'K-pop', 'photo_count' => 3, 'price' => 30000, 'slots' => [
                ['x' => 0.1, 'y' => 0.1, 'w' => 0.8, 'h' => 0.2],
                ['x' => 0.1, 'y' => 0.4, 'w' => 0.8, 'h' => 0.2],
                ['x' => 0.1, 'y' => 0.7, 'w' => 0.8, 'h' => 0.2],
            ]],
            ['name' => 'Neon Frame', 'slug' => 'neon', 'category' => 'Neon', 'photo_count' => 4, 'price' => 30000, 'slots' => [
                ['x' => 0.1, 'y' => 0.1, 'w' => 0.8, 'h' => 0.1],
                ['x' => 0.1, 'y' => 0.3, 'w' => 0.8, 'h' => 0.1],
                ['x' => 0.1, 'y' => 0.5, 'w' => 0.8, 'h' => 0.1],
                ['x' => 0.1, 'y' => 0.7, 'w' => 0.8, 'h' => 0.1],
            ]],
        ] as $f) {
            Frame::create($f);
        }
    }

    private function photo(): string
    {
        $bytes = base64_decode('/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/wAALCAABAAEBAREA/8QAFAABAAAAAAAAAAAAAAAAAAAACf/EABQQAQAAAAAAAAAAAAAAAAAAAAD/2gAIAQEAAD8AKp//2Q==');

        return 'data:image/jpeg;base64,'.base64_encode($bytes);
    }

    public function test_frame_cart_till_done(): void
    {
        $this->post('/mulai');

        $this->get('/frame')->assertOk()->assertSee('data-frame-option')->assertSee('data-cart-total');

        // cart: 2x kpop + 1x neon
        $this->post('/frame', ['cart' => ['kpop' => 2, 'neon' => 1]])->assertRedirect('/foto');

        $this->assertSame(['kpop' => 2, 'neon' => 1], session('cart'));

        $foto = $this->get('/foto');
        $foto->assertOk();
        $foto->assertSee('data-photo-count=');
        $foto->assertSee('<strong>7 foto</strong>', false);
        $foto->assertSee('data-frame-tab');
        $foto->assertSee('K-pop Frame');
        $foto->assertSee('Neon Frame');

        // 7 foto: 3 kpop + 4 neon
        $photos = array_map(fn () => $this->photo(), range(1, 7));
        $this->post('/foto', ['photos' => $photos])
            ->assertJsonFragment(['redirect' => 'http://localhost/filter']);

        $saved = session('photos');
        $this->assertCount(3, $saved['kpop']);
        $this->assertCount(4, $saved['neon']);

        $this->post('/filter', ['filter' => 'asli'])->assertRedirect('/metode');

        $this->get('/metode')->assertOk()->assertSee('90.000');

        $this->post('/metode', ['metode' => 'qris'])->assertRedirect('/pembayaran');
        $this->get('/pembayaran')->assertOk()->assertSee('90.000');

        $this->post('/pembayaran')->assertRedirect('/review');
        $this->get('/review')->assertOk()->assertSee('data-strip-canvas');
        $this->assertStringContainsString('K-pop', $this->get('/review')->getContent());
        $this->assertStringContainsString('Neon', $this->get('/review')->getContent());

        $this->get('/selesai')->assertOk()->assertSee('data-strip-canvas');
    }

    public function test_cart_price_is_per_frame_qty(): void
    {
        $this->post('/mulai');

        $this->post('/frame', ['cart' => ['kpop' => 3, 'neon' => 2]])->assertRedirect('/foto');

        $photos = array_map(fn () => $this->photo(), range(1, 7));
        $this->post('/foto', ['photos' => $photos])->assertJsonPath('redirect', 'http://localhost/filter');

        $this->post('/filter', ['filter' => 'asli']);

        $this->get('/metode')->assertSee('150.000');
        $this->post('/metode', ['metode' => 'cash']);
        $this->get('/pembayaran')->assertSee('150.000');
    }

    public function test_empty_cart_is_rejected(): void
    {
        $this->post('/mulai');

        $this->post('/frame', ['cart' => []])->assertSessionHasErrors('cart');
    }
}