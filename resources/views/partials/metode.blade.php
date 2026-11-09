@php($brand = $brand ?? \App\Support\Brand::get())
@php($sel = in_array($metode ?? null, ['qris', 'voucher'], true) ? $metode : 'qris')
<div class="flex-1 min-h-0 flex flex-col items-center justify-center py-2">
    <div class="w-full max-w-xl text-center mb-4 sm:mb-5">
        <div class="inline-flex items-center gap-2 rounded-full bg-white/70 backdrop-blur px-3 py-1 text-[11px] font-bold text-slate-500 ring-1 ring-rose-100 mb-2">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            Langkah 1 &middot; Bayar dulu, lalu foto
        </div>
        <h2 class="font-display font-bold text-2xl sm:text-3xl mb-1">{{ $brand['method_title'] }}</h2>
        <p class="text-slate-500 text-sm sm:text-base">{{ $brand['method_subtitle'] }}</p>
    </div>

    <form method="POST" action="{{ route('metode') }}" class="w-full max-w-xl" data-metode-form>
        @csrf
        <input type="hidden" name="from_spa" value="{{ ($fromSpa ?? true) ? '1' : '0' }}">
        <input type="hidden" name="metode" id="metode-input" value="{{ old('metode', $sel) }}">

        @if ($errors->any())
            <div class="mb-3 rounded-2xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 mb-4">
            <button type="button" data-metode-option="qris" aria-pressed="{{ old('metode', $sel) === 'qris' ? 'true' : 'false' }}"
                    class="group text-left rounded-3xl bg-white/90 backdrop-blur p-4 sm:p-5 transition-all hover:shadow-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-400 {{ old('metode', $sel) === 'qris' ? 'ring-2 ring-rose-500 shadow-lg shadow-rose-100' : 'ring-1 ring-rose-100' }}">
                <div class="flex items-start justify-between gap-3">
                    <span class="w-12 h-12 rounded-2xl bg-rose-500 text-white flex items-center justify-center shadow-md shadow-rose-200">
                        <x-icon name="qr" class="w-6 h-6 text-white" />
                    </span>
                    <span data-metode-check class="w-5 h-5 rounded-full border-2 flex items-center justify-center {{ old('metode', $sel) === 'qris' ? 'border-rose-500 bg-rose-500' : 'border-slate-300' }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-white {{ old('metode', $sel) === 'qris' ? '' : 'hidden' }}"></span>
                    </span>
                </div>
                <p class="font-display font-bold text-lg mt-3">QRIS</p>
                <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">Pilih frame dulu agar totalnya pas, lalu scan QRIS sebelum sesi foto.</p>
            </button>

            <button type="button" data-metode-option="voucher" aria-pressed="{{ old('metode', $sel) === 'voucher' ? 'true' : 'false' }}"
                    class="group text-left rounded-3xl bg-white/90 backdrop-blur p-4 sm:p-5 transition-all hover:shadow-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-400 {{ old('metode', $sel) === 'voucher' ? 'ring-2 ring-rose-500 shadow-lg shadow-rose-100' : 'ring-1 ring-rose-100' }}">
                <div class="flex items-start justify-between gap-3">
                    <span class="w-12 h-12 rounded-2xl bg-violet-500 text-white flex items-center justify-center shadow-md shadow-violet-200">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                    </span>
                    <span data-metode-check class="w-5 h-5 rounded-full border-2 flex items-center justify-center {{ old('metode', $sel) === 'voucher' ? 'border-rose-500 bg-rose-500' : 'border-slate-300' }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-white {{ old('metode', $sel) === 'voucher' ? '' : 'hidden' }}"></span>
                    </span>
                </div>
                <p class="font-display font-bold text-lg mt-3">Voucher</p>
                <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">Frame dan jumlah cetak sudah ditentukan. Langsung masuk sesi foto.</p>
            </button>
        </div>

        <div data-voucher-box class="{{ old('metode', $sel) === 'voucher' ? '' : 'hidden' }} mb-4 rounded-2xl bg-white/90 backdrop-blur p-4 ring-1 ring-rose-100 shadow-sm">
            <label for="voucher-code" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Kode Voucher</label>
            <input type="text" id="voucher-code" name="voucher_code" value="{{ old('voucher_code') }}"
                   autocomplete="off" autocapitalize="characters" spellcheck="false" maxlength="50"
                   placeholder="Contoh: PB-OPENING"
                   class="w-full rounded-xl bg-slate-50 border border-slate-200 px-4 py-3 text-center font-mono text-lg font-bold tracking-[0.2em] uppercase text-slate-800 placeholder:text-slate-300 placeholder:tracking-normal focus:outline-none focus:ring-2 focus:ring-rose-400 @error('voucher_code') border-red-300 @enderror">
            <p class="mt-2 text-[11px] text-slate-400 text-center">Masukkan kode dari kasir. Voucher langsung dipakai saat tombol di bawah ditekan.</p>
            @error('voucher_code')<p class="mt-1.5 text-xs text-red-500 text-center">{{ $message }}</p>@enderror
        </div>

        <button type="submit" id="metode-submit"
                class="w-full rounded-full bg-gradient-to-r from-rose-500 to-violet-600 text-white font-display font-bold px-6 py-3.5 shadow-lg shadow-rose-200 hover:scale-[1.01] active:scale-[0.99] transition-transform">
            {{ old('metode', $sel) === 'voucher' ? 'Gunakan Voucher & Mulai Foto' : 'Lanjut Pilih Frame' }}
        </button>

        <p class="mt-3 text-center text-[11px] text-slate-400">
            Dengan voucher, kamu tidak perlu memilih frame atau jumlah cetak.
        </p>
    </form>
</div>
