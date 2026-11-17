<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Paksa HTTPS hanya untuk permintaan yang memang datang lewat HTTPS
        // (proxy/ngrok). Memakai config('app.url') membuat server lokal tetap
        // menghasilkan URL aset https saat APP_URL menunjuk ke ngrok.
        if (
            request()->header('x-forwarded-proto') === 'https'
            || str_contains(request()->getHost(), 'ngrok')
        ) {
            URL::forceScheme('https');
        }
    }
}
