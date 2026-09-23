<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagesRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_public_pages_have_no_server_error(): void
    {
        $uris = ['/', route('tutorial'), route('frame'), route('foto'), route('filter'), route('metode'), route('pembayaran'), route('review'), route('selesai')];

        foreach ($uris as $uri) {
            $res = $this->get($uri);
            $status = $res->getStatusCode();
            // redirect antar-langkah (butuh sesi) itu wajar; yg dilarang = error server 5xx.
            $this->assertLessThan(500, $status, "Halaman {$uri} mengembalikan error server {$status}.");
        }
    }

    public function test_welcome_page_renders_core_markup(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Photobooth', false)
            ->assertSee('Mulai', false);
    }
}
