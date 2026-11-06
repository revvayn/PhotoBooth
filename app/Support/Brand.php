<?php

namespace App\Support;

use App\Models\Setting;

class Brand
{
    public const DEFAULTS = [
        'name' => 'Photobooth',
        'tagline' => 'Sesi foto kamu',
        'primary' => '#f43f5e',
        'secondary' => '#7c3aed',
        'accent' => '#d946ef',
        'bg_from' => '#ffe4e6',
        'bg_via' => '#fdf4ff',
        'bg_to' => '#ede9fe',
        'welcome' => 'Abadikan momen terbaikmu bareng teman dan keluarga. Foto langsung jadi — tinggal cetak atau kirim ke email.',
        'footer' => 'Photobooth',
    ];

    public static function get(): array
    {
        $primary = self::hex('brand_primary', self::DEFAULTS['primary']);
        $secondary = self::hex('brand_secondary', self::DEFAULTS['secondary']);
        $accent = self::hex('brand_accent', self::DEFAULTS['accent']);

        $logo = trim((string) Setting::get('brand_logo', ''));

        return [
            'name' => trim((string) Setting::get('brand_name', self::DEFAULTS['name'])) ?: self::DEFAULTS['name'],
            'tagline' => trim((string) Setting::get('brand_tagline', self::DEFAULTS['tagline'])),
            'primary' => $primary,
            'secondary' => $secondary,
            'accent' => $accent,
            'bg_from' => self::hex('brand_bg_from', self::DEFAULTS['bg_from']),
            'bg_via' => self::hex('brand_bg_via', self::DEFAULTS['bg_via']),
            'bg_to' => self::hex('brand_bg_to', self::DEFAULTS['bg_to']),
            'welcome' => trim((string) Setting::get('brand_welcome', self::DEFAULTS['welcome'])),
            'footer' => trim((string) Setting::get('brand_footer', self::DEFAULTS['footer'])) ?: self::DEFAULTS['footer'],
            'logo_url' => $logo !== '' ? asset('storage/'.$logo) : null,
            'bg_image_url' => ($background = trim((string) Setting::get('brand_bg_image', ''))) !== ''
                ? asset('storage/'.$background)
                : null,
        ];
    }

    private static function hex(string $key, string $default): string
    {
        $value = trim((string) Setting::get($key, $default));

        return preg_match('/^#[0-9a-fA-F]{6}$/', $value) ? $value : $default;
    }
}
