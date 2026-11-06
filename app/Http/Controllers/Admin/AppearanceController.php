<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AppearanceController extends Controller
{
    public const DEFAULTS = [
        'brand_name' => 'Photobooth',
        'brand_tagline' => 'Sesi foto kamu',
        'brand_primary' => '#f43f5e',
        'brand_secondary' => '#7c3aed',
        'brand_accent' => '#d946ef',
        'brand_bg_from' => '#ffe4e6',
        'brand_bg_via' => '#fdf4ff',
        'brand_bg_to' => '#ede9fe',
        'brand_welcome' => 'Abadikan momen terbaikmu bareng teman dan keluarga. Foto langsung jadi — tinggal cetak atau kirim ke email.',
        'brand_footer' => 'Photobooth',
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
            'brand_primary' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'brand_secondary' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'brand_accent' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'brand_bg_from' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'brand_bg_via' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'brand_bg_to' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
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
