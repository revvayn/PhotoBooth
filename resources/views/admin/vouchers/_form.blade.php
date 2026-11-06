@php
    $isEdit = isset($voucher);
    $selectedFrames = old('frames', $isEdit ? ($voucher->items ?? []) : []);
    $codeValue = old('code', $isEdit ? $voucher->code : '');
    $expiresValue = old('expires_at', $isEdit && $voucher->expires_at ? $voucher->expires_at->format('Y-m-d') : '');
    $activeValue = old('is_active', $isEdit ? $voucher->is_active : true);
@endphp

<form method="POST" action="{{ $isEdit ? route('admin.vouchers.update', $voucher) : route('admin.vouchers.store') }}" class="space-y-6">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="font-heading text-lg font-bold text-slate-800">Informasi Voucher</h2>
            <p class="text-xs text-slate-400 mt-0.5">Kode ini yang dimasukkan pelanggan di layar photobooth.</p>
        </div>
        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label for="name" class="block text-sm font-medium text-slate-700 mb-1.5">Nama Voucher <span class="text-red-500">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name', $isEdit ? $voucher->name : '') }}" required maxlength="100"
                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-400 @error('name') border-red-300 @enderror"
                       placeholder="Contoh: Voucher Opening Store">
                @error('name')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="code" class="block text-sm font-medium text-slate-700 mb-1.5">Kode Voucher</label>
                <input type="text" id="code" name="code" value="{{ $codeValue }}" maxlength="30"
                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-slate-800 uppercase tracking-wider focus:outline-none focus:ring-2 focus:ring-rose-400 @error('code') border-red-300 @enderror"
                       placeholder="Kosongkan untuk dibuat otomatis">
                <p class="mt-1.5 text-xs text-slate-400">Contoh: PB-OPENING. Kosongkan agar sistem membuat kode acak.</p>
                @error('code')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
                <label for="description" class="block text-sm font-medium text-slate-700 mb-1.5">Keterangan</label>
                <input type="text" id="description" name="description" value="{{ old('description', $isEdit ? $voucher->description : '') }}" maxlength="255"
                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-400 @error('description') border-red-300 @enderror"
                       placeholder="Opsional, misalnya digunakan untuk event tertentu">
                @error('description')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="max_uses" class="block text-sm font-medium text-slate-700 mb-1.5">Batas Pemakaian <span class="text-red-500">*</span></label>
                <input type="number" id="max_uses" name="max_uses" min="1" max="100000" required
                       value="{{ old('max_uses', $isEdit ? $voucher->max_uses : 1) }}"
                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-400 @error('max_uses') border-red-300 @enderror">
                <p class="mt-1.5 text-xs text-slate-400">Berapa kali kode ini boleh dipakai. 1 = sekali pakai.</p>
                @error('max_uses')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="expires_at" class="block text-sm font-medium text-slate-700 mb-1.5">Berlaku Sampai</label>
                <input type="date" id="expires_at" name="expires_at" value="{{ $expiresValue }}"
                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-400 @error('expires_at') border-red-300 @enderror">
                <p class="mt-1.5 text-xs text-slate-400">Kosongkan bila voucher berlaku tanpa batas waktu.</p>
                @error('expires_at')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <label class="sm:col-span-2 flex items-center gap-2.5 text-sm text-slate-600 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" @checked($activeValue) class="rounded border-gray-300 text-rose-500 focus:ring-rose-400">
                Aktifkan voucher ini untuk pelanggan
            </label>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="font-heading text-lg font-bold text-slate-800">Frame yang Didapat</h2>
            <p class="text-xs text-slate-400 mt-0.5">Tentukan frame dan jumlah cetak yang otomatis dipakai pelanggan. Pelanggan tidak perlu memilih frame lagi.</p>
        </div>
        <div class="p-6 space-y-3">
            @error('frames')
                <div class="rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">{{ $message }}</div>
            @enderror

            @forelse ($frames as $frame)
                <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50/70 p-3">
                    <div class="w-12 h-14 rounded-lg bg-white ring-1 ring-gray-200 overflow-hidden flex items-center justify-center shrink-0">
                        @if ($frame->image_url)
                            <img src="{{ $frame->image_url }}" alt="{{ $frame->name }}" class="w-full h-full object-contain p-1">
                        @else
                            <x-icon name="photo" class="w-5 h-5 text-slate-300" />
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-slate-800 truncate">{{ $frame->name }}</p>
                        <p class="text-xs text-slate-400">
                            {{ $frame->photo_count }} foto/frame
                            &middot; Rp. {{ number_format($frame->price, 0, ',', '.') }}
                            @if (! $frame->is_active) &middot; <span class="text-amber-600">nonaktif</span>@endif
                        </p>
                    </div>
                    <div class="w-24 shrink-0">
                        <label for="frame-{{ $frame->id }}" class="block text-[10px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Jumlah</label>
                        <input type="number" id="frame-{{ $frame->id }}" name="frames[{{ $frame->slug }}]" min="0" max="50"
                               value="{{ (int) ($selectedFrames[$frame->slug] ?? 0) }}"
                               class="w-full px-3 py-2 text-center rounded-lg bg-white border border-gray-200 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-400">
                    </div>
                </div>
            @empty
                <div class="rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">
                    Belum ada frame. Tambahkan frame terlebih dahulu di menu Frames.
                </div>
            @endforelse

            <p class="text-xs text-slate-400">Isi 0 untuk frame yang tidak termasuk dalam voucher.</p>
        </div>
    </div>

    <div class="flex items-center justify-between gap-3">
        <a href="{{ route('admin.vouchers.index') }}" class="px-5 py-3 rounded-xl text-sm font-semibold text-slate-600 hover:bg-gray-100 transition">Kembali</a>
        <button type="submit" class="px-8 py-3 rounded-xl bg-rose-500 text-white font-semibold shadow-md shadow-rose-200 hover:bg-rose-600 transition">
            {{ $isEdit ? 'Simpan Perubahan' : 'Buat Voucher' }}
        </button>
    </div>
</form>
