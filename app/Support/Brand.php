<?php

namespace App\Support;

use App\Http\Controllers\Admin\AppearanceController;
use App\Models\Setting;

class Brand
{
    public const DEFAULTS = [
        'name' => 'Photobooth',
        'tagline' => 'Sesi foto kamu',
        'primary' => '#4f46e5',
        'secondary' => '#0891b2',
        'accent' => '#7c3aed',
        'success' => '#059669',
        'warning' => '#d97706',
        'danger' => '#e11d48',
        'text' => '#334155',
        'heading' => '#0f172a',
        'muted' => '#64748b',
        'bg_from' => '#eef2ff',
        'bg_via' => '#f8fafc',
        'bg_to' => '#ecfeff',
        'welcome' => 'Abadikan momen terbaikmu bareng teman dan keluarga. Foto langsung jadi — tinggal cetak atau kirim ke email.',
        'footer' => 'Photobooth',
        'start_label' => 'Mulai',
        'welcome_badge' => 'Siap dipakai',
        'method_title' => 'Pilih Pembayaran',
        'method_subtitle' => 'Pilih QRIS atau gunakan voucher sebelum memilih frame.',
        'frame_title' => 'Pilih Frame',
        'frame_subtitle' => 'Metode QRIS aktif. Pilih frame, lalu konfirmasi pembayaran sebelum sesi foto.',
        'payment_title' => 'Konfirmasi QRIS',
        'payment_subtitle' => 'Bayar sekarang, lalu lanjut ke sesi foto.',
        'payment_note' => 'Buka aplikasi e-wallet / m-banking, scan QR di atas, lalu tekan konfirmasi.',
        'photo_title' => 'Sesi Foto',
        'photo_hint' => 'Hadapkan wajah ke kamera & tersenyum',
        'review_title' => 'Fotomu Sudah Jadi!',
        'review_subtitle' => 'Cetak, unduh, atau kirim hasilnya ke email',
        'finish_label' => 'Selesai & Sesi Baru',
        'step_choose' => 'Pilih Bayar',
        'step_method' => 'Bayar',
        'step_voucher' => 'Voucher',
        'step_frame' => 'Frame',
        'step_payment' => 'Konfirmasi',
        'step_photo' => 'Foto',
        'step_review' => 'Selesai',
        'heading_font' => 'Plus Jakarta Sans',
        'body_font' => 'Inter',
        'radius' => 'md',
    ];

    public static function get(): array
    {
        // Ambil seluruh setting dalam satu query agar partial branding tidak
        // memicu puluhan query di setiap halaman kiosk.
        $all = Setting::query()->pluck('value', 'key');

        $value = fn (string $key, string $default): string => (string) ($all[$key] ?? $default);
        $text = fn (string $key, string $default): string => trim($value($key, $default));
        $hex = fn (string $key, string $default): string => self::hexValue($value($key, $default), $default);

        $logo = trim($value('brand_logo', ''));
        $background = trim($value('brand_bg_image', ''));

        $headingFont = $text('brand_heading_font', self::DEFAULTS['heading_font']);
        if (! in_array($headingFont, AppearanceController::FONTS, true)) {
            $headingFont = self::DEFAULTS['heading_font'];
        }

        $bodyFont = $text('brand_body_font', self::DEFAULTS['body_font']);
        if (! in_array($bodyFont, AppearanceController::FONTS, true)) {
            $bodyFont = self::DEFAULTS['body_font'];
        }

        $radius = $text('brand_radius', self::DEFAULTS['radius']);
        if (! in_array($radius, AppearanceController::RADIUS, true)) {
            $radius = self::DEFAULTS['radius'];
        }

        return [
            'name' => $text('brand_name', self::DEFAULTS['name']) ?: self::DEFAULTS['name'],
            'tagline' => $text('brand_tagline', self::DEFAULTS['tagline']),
            'primary' => $hex('brand_primary', self::DEFAULTS['primary']),
            'secondary' => $hex('brand_secondary', self::DEFAULTS['secondary']),
            'accent' => $hex('brand_accent', self::DEFAULTS['accent']),
            'success' => $hex('brand_success', self::DEFAULTS['success']),
            'warning' => $hex('brand_warning', self::DEFAULTS['warning']),
            'danger' => $hex('brand_danger', self::DEFAULTS['danger']),
            'text' => $hex('brand_text', self::DEFAULTS['text']),
            'heading' => $hex('brand_heading', self::DEFAULTS['heading']),
            'muted' => $hex('brand_muted', self::DEFAULTS['muted']),
            'bg_from' => $hex('brand_bg_from', self::DEFAULTS['bg_from']),
            'bg_via' => $hex('brand_bg_via', self::DEFAULTS['bg_via']),
            'bg_to' => $hex('brand_bg_to', self::DEFAULTS['bg_to']),
            'welcome' => $text('brand_welcome', self::DEFAULTS['welcome']),
            'footer' => $text('brand_footer', self::DEFAULTS['footer']) ?: self::DEFAULTS['footer'],

            'start_label' => $text('brand_start_label', self::DEFAULTS['start_label']) ?: self::DEFAULTS['start_label'],
            'welcome_badge' => $text('brand_welcome_badge', self::DEFAULTS['welcome_badge']),
            'method_title' => $text('brand_method_title', self::DEFAULTS['method_title']) ?: self::DEFAULTS['method_title'],
            'method_subtitle' => $text('brand_method_subtitle', self::DEFAULTS['method_subtitle']),
            'frame_title' => $text('brand_frame_title', self::DEFAULTS['frame_title']) ?: self::DEFAULTS['frame_title'],
            'frame_subtitle' => $text('brand_frame_subtitle', self::DEFAULTS['frame_subtitle']),
            'payment_title' => $text('brand_payment_title', self::DEFAULTS['payment_title']) ?: self::DEFAULTS['payment_title'],
            'payment_subtitle' => $text('brand_payment_subtitle', self::DEFAULTS['payment_subtitle']),
            'payment_note' => $text('brand_payment_note', self::DEFAULTS['payment_note']),
            'photo_title' => $text('brand_photo_title', self::DEFAULTS['photo_title']) ?: self::DEFAULTS['photo_title'],
            'photo_hint' => $text('brand_photo_hint', self::DEFAULTS['photo_hint']),
            'review_title' => $text('brand_review_title', self::DEFAULTS['review_title']) ?: self::DEFAULTS['review_title'],
            'review_subtitle' => $text('brand_review_subtitle', self::DEFAULTS['review_subtitle']),
            'finish_label' => $text('brand_finish_label', self::DEFAULTS['finish_label']) ?: self::DEFAULTS['finish_label'],

            'step_choose' => $text('brand_step_choose', self::DEFAULTS['step_choose']) ?: self::DEFAULTS['step_choose'],
            'step_method' => $text('brand_step_method', self::DEFAULTS['step_method']) ?: self::DEFAULTS['step_method'],
            'step_voucher' => $text('brand_step_voucher', self::DEFAULTS['step_voucher']) ?: self::DEFAULTS['step_voucher'],
            'step_frame' => $text('brand_step_frame', self::DEFAULTS['step_frame']) ?: self::DEFAULTS['step_frame'],
            'step_payment' => $text('brand_step_payment', self::DEFAULTS['step_payment']) ?: self::DEFAULTS['step_payment'],
            'step_photo' => $text('brand_step_photo', self::DEFAULTS['step_photo']) ?: self::DEFAULTS['step_photo'],
            'step_review' => $text('brand_step_review', self::DEFAULTS['step_review']) ?: self::DEFAULTS['step_review'],

            'heading_font' => $headingFont,
            'body_font' => $bodyFont,
            'radius' => $radius,

            'logo_url' => $logo !== '' ? asset('storage/'.$logo) : null,
            'bg_image_url' => $background !== '' ? asset('storage/'.$background) : null,
        ];
    }

    private static function hexValue(string $value, string $default): string
    {
        $value = trim($value);

        return preg_match('/^#[0-9a-fA-F]{6}$/', $value) ? $value : $default;
    }
}
