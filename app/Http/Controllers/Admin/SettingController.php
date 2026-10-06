<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            'mail_host' => Setting::get('mail_host', ''),
            'mail_port' => Setting::get('mail_port', '587'),
            'mail_username' => Setting::get('mail_username', ''),
            'mail_password' => Setting::get('mail_password', ''),
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
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|string|max:10',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|string|in:tls,ssl,null',
            'mail_from_address' => 'nullable|email|max:255',
            'mail_from_name' => 'nullable|string|max:255',
        ]);

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

        ActivityLog::create([
            'session_id' => session()->getId(),
            'queue_number' => 'admin',
            'action' => 'settings.updated',
            'metadata' => ['admin' => Auth::guard('auth')->user()?->email ?? 'admin'],
        ]);

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
