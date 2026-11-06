<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'qris_merchant' => Setting::get('qris_merchant', 'PERKAKASKU'),
            'qris_nmid' => Setting::get('qris_nmid', '102020034073193'),
            'qris_image' => Setting::get('qris_image'),
            'cam_width' => Setting::get('cam_width', '1024'),
            'cam_height' => Setting::get('cam_height', '768'),
            'countdown' => Setting::get('countdown', '3'),
            'session_seconds' => (string) ((int) (Setting::get('session_seconds', '600') ?: 600) / 60),
            'has_cashier_pin' => ! empty(Setting::get('cashier_pin_hash')),
            'mail_host' => Setting::get('mail_host', ''),
            'mail_port' => Setting::get('mail_port', '587'),
            'mail_username' => Setting::get('mail_username', ''),
            // Password SMTP tidak pernah dikirim balik ke browser.
            'has_mail_password' => ! empty(Setting::get('mail_password')),
            'mail_encryption' => Setting::get('mail_encryption', 'tls'),
            'mail_from_address' => Setting::get('mail_from_address', ''),
            'mail_from_name' => Setting::get('mail_from_name', 'Photobooth Studio'),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'qris_merchant' => 'required|string|max:100',
            'qris_nmid' => 'required|string|max:30',
            'qris_image' => 'nullable|image|mimes:png,jpg,jpeg|max:4096',
            'remove_qris_image' => 'nullable|boolean',
            'cam_width' => 'required|in:640,1024,1280,1920',
            'cam_height' => 'required|in:480,768,720,1080',
            'countdown' => 'required|integer|min:3|max:10',
            'session_minutes' => 'required|integer|min:2|max:60',
            'cashier_pin' => 'nullable|digits_between:4,8',
            'remove_cashier_pin' => 'nullable|boolean',
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|string|max:10',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|string|in:tls,ssl,null',
            'mail_from_address' => 'nullable|email|max:255',
            'mail_from_name' => 'nullable|string|max:255',
        ]);

        // Nilai khusus diproses terpisah dari loop generik di bawah.
        Setting::set('session_seconds', (string) ((int) $data['session_minutes'] * 60));

        if ($request->boolean('remove_cashier_pin')) {
            Setting::set('cashier_pin_hash', '');
        } elseif (! empty($data['cashier_pin'])) {
            Setting::set('cashier_pin_hash', Hash::make($data['cashier_pin']));
        }

        // Password SMTP kosong = pertahankan yang sudah tersimpan.
        if (($data['mail_password'] ?? '') === '') {
            unset($data['mail_password']);
        }

        unset($data['session_minutes'], $data['cashier_pin'], $data['remove_cashier_pin'], $data['qris_image'], $data['remove_qris_image']);

        foreach ($data as $key => $value) {
            Setting::set($key, (string) ($value ?? ''));
        }

        // Upload / hapus gambar QRIS (opsional; bila ada, dipakai kiosk
        // menggantikan QR code yang di-generate otomatis).
        if ($request->hasFile('qris_image')) {
            $ext = strtolower($request->file('qris_image')->getClientOriginalExtension()) ?: 'png';
            $path = $request->file('qris_image')->storeAs('qris', 'qris-image.'.$ext, 'public');
            Setting::set('qris_image', $path);
        } elseif ($request->boolean('remove_qris_image')) {
            $old = Setting::get('qris_image');
            if ($old) {
                Storage::disk('public')->delete($old);
            }
            Setting::set('qris_image', '');
        }

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
