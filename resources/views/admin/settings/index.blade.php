@extends('layouts.admin')

@section('title', 'Pengaturan - Photobooth Admin')
@section('page-title', 'Pengaturan')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    @if (session('success'))
        <div class="px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="font-heading text-lg font-bold text-slate-800">QRIS Pembayaran</h2>
                <p class="text-xs text-slate-400 mt-0.5">Ditampilkan di panel pembayaran kiosk & QR invoice.</p>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="qris_merchant" class="block text-sm font-medium text-slate-700 mb-1.5">Nama Merchant <span class="text-red-500">*</span></label>
                    <input type="text" id="qris_merchant" name="qris_merchant" value="{{ old('qris_merchant', $settings['qris_merchant']) }}" required maxlength="100"
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-transparent transition-all duration-200 @error('qris_merchant') border-red-300 focus:ring-red-400 @enderror"
                           placeholder="Contoh: PERKAKASKU">
                    @error('qris_merchant')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="qris_nmid" class="block text-sm font-medium text-slate-700 mb-1.5">NMID <span class="text-red-500">*</span></label>
                    <input type="text" id="qris_nmid" name="qris_nmid" value="{{ old('qris_nmid', $settings['qris_nmid']) }}" required maxlength="30"
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-transparent transition-all duration-200 @error('qris_nmid') border-red-300 focus:ring-red-400 @enderror"
                           placeholder="Contoh: 102020034073193">
                    @error('qris_nmid')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="font-heading text-lg font-bold text-slate-800">Kamera Kiosk</h2>
                <p class="text-xs text-slate-400 mt-0.5">Berlaku untuk sesi foto di semua perangkat.</p>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div>
                    <label for="cam_width" class="block text-sm font-medium text-slate-700 mb-1.5">Lebar (px) <span class="text-red-500">*</span></label>
                    <select id="cam_width" name="cam_width" required
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-transparent transition-all duration-200 @error('cam_width') border-red-300 focus:ring-red-400 @enderror">
                        @foreach (['640', '1024', '1280', '1920'] as $w)
                            <option value="{{ $w }}" {{ old('cam_width', $settings['cam_width']) === $w ? 'selected' : '' }}>{{ $w }}</option>
                        @endforeach
                    </select>
                    @error('cam_width')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="cam_height" class="block text-sm font-medium text-slate-700 mb-1.5">Tinggi (px) <span class="text-red-500">*</span></label>
                    <select id="cam_height" name="cam_height" required
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-transparent transition-all duration-200 @error('cam_height') border-red-300 focus:ring-red-400 @enderror">
                        @foreach (['480', '720', '768', '1080'] as $h)
                            <option value="{{ $h }}" {{ old('cam_height', $settings['cam_height']) === $h ? 'selected' : '' }}>{{ $h }}</option>
                        @endforeach
                    </select>
                    @error('cam_height')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="countdown" class="block text-sm font-medium text-slate-700 mb-1.5">Hitung mundur (detik) <span class="text-red-500">*</span></label>
                    <select id="countdown" name="countdown" required
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-transparent transition-all duration-200 @error('countdown') border-red-300 focus:ring-red-400 @enderror">
                        @foreach (['3', '5', '10'] as $c)
                            <option value="{{ $c }}" {{ old('countdown', $settings['countdown']) === $c ? 'selected' : '' }}>{{ $c }} detik</option>
                        @endforeach
                    </select>
                    @error('countdown')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-8 py-3 rounded-xl bg-rose-500 text-white font-semibold shadow-md shadow-rose-200 hover:bg-rose-600 transition">
                Simpan Pengaturan
            </button>
        </div>
    </form>
</div>
@endsection
