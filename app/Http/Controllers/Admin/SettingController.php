<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'qris_merchant' => Setting::get('qris_merchant', 'PERKAKASKU'),
            'qris_nmid' => Setting::get('qris_nmid', '102020034073193'),
            'cam_width' => Setting::get('cam_width', '1024'),
            'cam_height' => Setting::get('cam_height', '768'),
            'countdown' => Setting::get('countdown', '3'),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'qris_merchant' => 'required|string|max:100',
            'qris_nmid' => 'required|string|max:30',
            'cam_width' => 'required|in:640,1024,1280,1920',
            'cam_height' => 'required|in:480,768,720,1080',
            'countdown' => 'required|integer|min:3|max:10',
        ]);

        foreach ($data as $key => $value) {
            Setting::set($key, (string) $value);
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
