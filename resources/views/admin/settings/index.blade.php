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

    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
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

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Gambar QRIS (opsional)</label>
                    <p class="text-xs text-slate-400 mb-2">Unggah gambar QRIS resmi. Bila diisi, kiosk menampilkan gambar ini; bila kosong, QR dibuat otomatis dari NMID.</p>
                    <div class="flex items-start gap-4">
                        <div class="w-24 h-24 bg-gray-50 border border-gray-200 rounded-xl overflow-hidden flex items-center justify-center shrink-0">
                            @if (!empty($settings['qris_image']))
                                <img src="{{ asset('storage/' . $settings['qris_image']) }}" alt="QRIS" class="w-full h-full object-contain">
                            @else
                                <span class="text-slate-300 text-xs text-center px-2">Belum ada gambar</span>
                            @endif
                        </div>
                        <div class="flex-1">
                            <input type="file" id="qris_image" name="qris_image" accept="image/png,image/jpeg"
                                   class="w-full text-sm text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-rose-50 file:text-rose-600 hover:file:bg-rose-100 transition">
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

        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="font-heading text-lg font-bold text-slate-800">Pengaturan Email (SMTP)</h2>
                <p class="text-xs text-slate-400 mt-0.5">Digunakan untuk mengirimkan hasil foto ke email pengguna.</p>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="mail_host" class="block text-sm font-medium text-slate-700 mb-1.5">SMTP Host</label>
                    <input type="text" id="mail_host" name="mail_host" value="{{ old('mail_host', $settings['mail_host']) }}"
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-400"
                           placeholder="Contoh: smtp.gmail.com">
                </div>

                <div>
                    <label for="mail_port" class="block text-sm font-medium text-slate-700 mb-1.5">SMTP Port</label>
                    <input type="text" id="mail_port" name="mail_port" value="{{ old('mail_port', $settings['mail_port']) }}"
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-400"
                           placeholder="587 atau 465">
                </div>

                <div>
                    <label for="mail_username" class="block text-sm font-medium text-slate-700 mb-1.5">SMTP Username / Email</label>
                    <input type="text" id="mail_username" name="mail_username" value="{{ old('mail_username', $settings['mail_username']) }}"
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-400"
                           placeholder="emailanda@gmail.com">
                </div>

                <div>
                    <label for="mail_password" class="block text-sm font-medium text-slate-700 mb-1.5">SMTP Password / App Password</label>
                    <input type="password" id="mail_password" name="mail_password" value="{{ old('mail_password', $settings['mail_password']) }}"
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-400"
                           placeholder="App Password 16 karakter">
                </div>

                <div>
                    <label for="mail_encryption" class="block text-sm font-medium text-slate-700 mb-1.5">Enkripsi</label>
                    <select id="mail_encryption" name="mail_encryption"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-400">
                        <option value="tls" {{ old('mail_encryption', $settings['mail_encryption']) === 'tls' ? 'selected' : '' }}>TLS (Port 587)</option>
                        <option value="ssl" {{ old('mail_encryption', $settings['mail_encryption']) === 'ssl' ? 'selected' : '' }}>SSL (Port 465)</option>
                        <option value="null" {{ old('mail_encryption', $settings['mail_encryption']) === 'null' ? 'selected' : '' }}>Tanpa Enkripsi</option>
                    </select>
                </div>

                <div>
                    <label for="mail_from_address" class="block text-sm font-medium text-slate-700 mb-1.5">Pengirim (From Email)</label>
                    <input type="email" id="mail_from_address" name="mail_from_address" value="{{ old('mail_from_address', $settings['mail_from_address']) }}"
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-400"
                           placeholder="no-reply@domain.com">
                </div>

                <div class="sm:col-span-2">
                    <label for="mail_from_name" class="block text-sm font-medium text-slate-700 mb-1.5">Nama Pengirim (From Name)</label>
                    <input type="text" id="mail_from_name" name="mail_from_name" value="{{ old('mail_from_name', $settings['mail_from_name']) }}"
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-400"
                           placeholder="Photobooth Studio">
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
