<?php

namespace App\Support;

/**
 * Tautan hasil yang harus bisa dibuka dari perangkat lain.
 *
 * Kiosk diakses lewat localhost oleh Electron, tetapi QR dipindai dari HP.
 * Karena itu URL QR tidak boleh memakai host request saat itu, melainkan
 * PUBLIC_URL (domain statis ngrok) yang sudah ditentukan.
 */
class DownloadLink
{
    public static function base(): string
    {
        $url = config('app.public_url') ?: config('app.url');

        return rtrim((string) $url, '/');
    }

    public static function index(): string
    {
        return self::base().'/hasil';
    }

    public static function show(string $token): string
    {
        return self::index().'/'.rawurlencode($token);
    }
}
