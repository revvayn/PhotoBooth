<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AppearanceController extends Controller
{
    /** Pilihan font yang didukung (dimuat dari Google Fonts). */
    public const FONTS = [
        'Baloo 2',
        'Poppins',
        'Fredoka',
        'Quicksand',
        'Nunito',
        'Inter',
        'DM Sans',
        'Space Grotesk',
        'Plus Jakarta Sans',
    ];

    /** Pilihan skala sudut (border radius). */
    public const RADIUS = ['sm', 'md', 'lg', 'xl'];

    public const DEFAULTS = [
        // Identitas brand
        'brand_name' => 'Photobooth',
        'brand_tagline' => 'Sesi foto kamu',
        'brand_welcome' => 'Abadikan momen terbaikmu bareng teman dan keluarga. Foto langsung jadi — tinggal cetak atau kirim ke email.',
        'brand_footer' => 'Photobooth',

        // Warna teks
        'brand_text' => '#334155',
        'brand_heading' => '#0f172a',
        'brand_muted' => '#64748b',

        // Warna brand
        'brand_primary' => '#4f46e5',
        'brand_secondary' => '#0891b2',
        'brand_accent' => '#7c3aed',
        'brand_success' => '#059669',
        'brand_warning' => '#d97706',
        'brand_danger' => '#e11d48',
        'brand_bg_from' => '#eef2ff',
        'brand_bg_via' => '#f8fafc',
        'brand_bg_to' => '#ecfeff',

        // Tipografi & bentuk
        'brand_heading_font' => 'Plus Jakarta Sans',
        'brand_body_font' => 'Inter',
        'brand_radius' => 'md',
    ];

    public function index()
    {
        $settings = [];
        foreach (self::DEFAULTS as $key => $default) {
            $settings[$key] = Setting::get($key, $default) ?? $default;
        }

        $settings['brand_logo'] = Setting::get('brand_logo');
        $settings['brand_bg_image'] = Setting::get('brand_bg_image');

        return view('admin.appearance.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'brand_name' => ['required', 'string', 'max:50'],
            'brand_tagline' => ['nullable', 'string', 'max:100'],
            'brand_welcome' => ['nullable', 'string', 'max:500'],
            'brand_footer' => ['nullable', 'string', 'max:100'],

            'brand_name' => ['required', 'string', 'max:50'],
            'brand_tagline' => ['nullable', 'string', 'max:100'],
            'brand_welcome' => ['nullable', 'string', 'max:500'],
            'brand_footer' => ['nullable', 'string', 'max:100'],

            'brand_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
            'brand_bg_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:8192'],
            'remove_brand_logo' => ['nullable', 'boolean'],
            'remove_brand_bg_image' => ['nullable', 'boolean'],
        ]);

        foreach (self::DEFAULTS as $key => $default) {
            $value = array_key_exists($key, $data) ? $data[$key] : $default;
            Setting::set($key, (string) ($value ?? ''));
        }

        $this->handleImage($request, 'brand_logo', 'brand/logo', 'remove_brand_logo');
        $this->handleImage($request, 'brand_bg_image', 'brand/background', 'remove_brand_bg_image');

        return back()->with('success', 'Tampilan photobooth berhasil disimpan.');
    }

    private function handleImage(Request $request, string $field, string $target, string $removeField): void
    {
        $old = Setting::get($field);

        if ($request->hasFile($field)) {
            $ext = strtolower($request->file($field)->getClientOriginalExtension()) ?: 'png';
            $path = $request->file($field)->storeAs(dirname($target), basename($target).'.'.$ext, 'public');

            if ($old && $old !== $path) {
                Storage::disk('public')->delete($old);
            }

            Setting::set($field, $path);

            return;
        }

        if ($request->boolean($removeField)) {
            if ($old) {
                Storage::disk('public')->delete($old);
            }
            Setting::set($field, '');
        }
    }
}
