@extends('layouts.admin')

@section('title', 'Pengaturan - '.\App\Support\Brand::get()['name'].' Admin')
@section('page-title', 'Pengaturan')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="font-heading text-lg font-bold text-slate-800">QRIS Pembayaran</h2>
                <p class="text-xs text-slate-400 mt-0.5">Ditampilkan di panel pembayaran kiosk & QR invoice.</p>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="qris_merchant" class="block text-sm font-medium text-slate-700 mb-1.5">Nama Merchant <span class="text-red-500">*</span></label>
                    <input type="text" id="qris_merchant" name="qris_merchant" value="{{ old('qris_merchant', $settings['qris_merchant']) }}" required maxlength="100"
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-transparent transition-all duration-200 @error('qris_merchant') border-red-300 focus:ring-red-400 @enderror"
                           placeholder="Contoh: PERKAKASKU">
                    @error('qris_merchant')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="qris_nmid" class="block text-sm font-medium text-slate-700 mb-1.5">NMID <span class="text-red-500">*</span></label>
                    <input type="text" id="qris_nmid" name="qris_nmid" value="{{ old('qris_nmid', $settings['qris_nmid']) }}" required maxlength="30"
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-transparent transition-all duration-200 @error('qris_nmid') border-red-300 focus:ring-red-400 @enderror"
                           placeholder="Contoh: 102020034073193">
                    @error('qris_nmid')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Gambar QRIS (opsional)</label>
                    <p class="text-xs text-slate-400 mb-2">Unggah gambar QRIS resmi. Bila diisi, kiosk menampilkan gambar ini; bila kosong, QR dibuat otomatis dari NMID.</p>
                    <div class="flex items-start gap-4">
                        <div class="w-24 h-24 bg-gray-50 border border-gray-200 rounded-2xl overflow-hidden flex items-center justify-center shrink-0">
                            @if (!empty($settings['qris_image']))
                                <img src="{{ asset('storage/' . $settings['qris_image']) }}" alt="QRIS" class="w-full h-full object-contain">
                            @else
                                <span class="text-slate-300 text-xs text-center px-2">Belum ada gambar</span>
                            @endif
                        </div>
                        <div class="flex-1">
                            <input type="file" id="qris_image" name="qris_image" accept="image/png,image/jpeg"
                                   class="w-full text-sm text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-2xl file:border-0 file:text-sm file:font-semibold file:bg-rose-50 file:text-rose-600 hover:file:bg-rose-100 transition">
                            @error('qris_image')
                                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                            @if (!empty($settings['qris_image']))
                                <label class="mt-2 flex items-center gap-1.5 text-xs text-slate-500 cursor-pointer">
                                    <input type="checkbox" name="remove_qris_image" value="1" class="rounded border-gray-300 text-rose-500 focus:ring-rose-400">
                                    Hapus gambar QRIS yang ada
                                </label>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="font-heading text-lg font-bold text-slate-800">Kamera Kiosk</h2>
                <p class="text-xs text-slate-400 mt-0.5">Berlaku untuk sesi foto di semua perangkat.</p>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div>
                    <label for="cam_width" class="block text-sm font-medium text-slate-700 mb-1.5">Lebar (px) <span class="text-red-500">*</span></label>
                    <select id="cam_width" name="cam_width" required
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-transparent transition-all duration-200 @error('cam_width') border-red-300 focus:ring-red-400 @enderror">
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
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-transparent transition-all duration-200 @error('cam_height') border-red-300 focus:ring-red-400 @enderror">
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
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-transparent transition-all duration-200 @error('countdown') border-red-300 focus:ring-red-400 @enderror">
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

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="font-heading text-lg font-bold text-slate-800">Sesi & Kasir</h2>
                <p class="text-xs text-slate-400 mt-0.5">Batas waktu pelanggan dan verifikasi pembayaran oleh kasir.</p>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="session_selection_minutes" class="block text-sm font-medium text-slate-700 mb-1.5">Durasi Pilih Metode &amp; Frame (menit) <span class="text-red-500">*</span></label>
                    <input type="number" id="session_selection_minutes" name="session_selection_minutes" min="1" max="60" required
                           value="{{ old('session_selection_minutes', $settings['session_selection_minutes']) }}"
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-400 @error('session_selection_minutes') border-red-300 @enderror">
                    <p class="mt-1.5 text-xs text-slate-400">Waktu pelanggan untuk memilih metode pembayaran, frame, dan konfirmasi QRIS sebelum waktu habis.</p>
                    @error('session_selection_minutes')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="session_photo_minutes" class="block text-sm font-medium text-slate-700 mb-1.5">Durasi Sesi Foto (menit) <span class="text-red-500">*</span></label>
                    <input type="number" id="session_photo_minutes" name="session_photo_minutes" min="1" max="60" required
                           value="{{ old('session_photo_minutes', $settings['session_photo_minutes']) }}"
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-400 @error('session_photo_minutes') border-red-300 @enderror">
                    <p class="mt-1.5 text-xs text-slate-400">Waktu pelanggan untuk mengambil semua foto setelah pembayaran dikonfirmasi.</p>
                    @error('session_photo_minutes')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="session_review_minutes" class="block text-sm font-medium text-slate-700 mb-1.5">Durasi Review &amp; Unduh (menit) <span class="text-red-500">*</span></label>
                    <input type="number" id="session_review_minutes" name="session_review_minutes" min="1" max="60" required
                           value="{{ old('session_review_minutes', $settings['session_review_minutes']) }}"
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-400 @error('session_review_minutes') border-red-300 @enderror">
                    <p class="mt-1.5 text-xs text-slate-400">Waktu pelanggan untuk review, cetak, dan unduh setelah foto selesai.</p>
                    @error('session_review_minutes')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="cashier_pin" class="block text-sm font-medium text-slate-700 mb-1.5">PIN Kasir (opsional)</label>
                    <input type="password" id="cashier_pin" name="cashier_pin" inputmode="numeric" maxlength="8" autocomplete="new-password"
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-400 @error('cashier_pin') border-red-300 @enderror"
                           placeholder="{{ $settings['has_cashier_pin'] ? 'Tersimpan — isi untuk mengganti' : '4–8 angka' }}">
                    <p class="mt-1.5 text-xs text-slate-400">Bila diisi, tombol "Sudah Bayar" di kiosk meminta PIN ini sehingga pelanggan tidak bisa lanjut tanpa bayar.</p>
                    @error('cashier_pin')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                    @if ($settings['has_cashier_pin'])
                        <label class="mt-2 flex items-center gap-1.5 text-xs text-slate-500 cursor-pointer">
                            <input type="checkbox" name="remove_cashier_pin" value="1" class="rounded border-gray-300 text-rose-500 focus:ring-rose-400">
                            Nonaktifkan PIN kasir
                        </label>
                    @endif
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="font-heading text-lg font-bold text-slate-800">QR Download Hasil</h2>
                <p class="text-xs text-slate-400 mt-0.5">Pelanggan memindai QR untuk membuka halaman unduh hasil.</p>
            </div>
            <div class="p-6 flex flex-col sm:flex-row items-center gap-6">
                <div class="shrink-0 rounded-2xl border border-gray-200 bg-white p-3">
                    <canvas data-download-qr data-download-qr-url="{{ $settings['download_url'] }}" class="w-40 h-40"></canvas>
                </div>
                <div class="min-w-0 text-sm text-slate-600 space-y-2">
                    <p>QR ini membuka halaman <strong>Unduh Hasil</strong>. Pelanggan memasukkan token/kode antrian yang tersedia di kiosk.</p>
                    <p>QR khusus per transaksi otomatis tampil di <strong>Log Pembayaran</strong> dan halaman <strong>Selesai</strong> setelah foto diambil.</p>
                    <a href="{{ $settings['download_url'] }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-rose-50 text-rose-600 text-xs font-bold hover:bg-rose-100 transition">
                        Buka halaman unduh
                    </a>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-8 py-3 rounded-2xl bg-rose-500 text-white font-semibold shadow-md shadow-rose-200 hover:bg-rose-600 transition">
                Simpan Pengaturan
            </button>
        </div>
    </form>
</div>
@endsection
