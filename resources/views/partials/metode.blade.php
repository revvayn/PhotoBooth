@php($brand = $brand ?? \App\Support\Brand::get())
@php($sel = in_array($metode ?? null, ['qris', 'voucher'], true) ? $metode : 'qris')
@php($cur = old('metode', $sel))
<div class="flex-1 min-h-0 flex flex-col items-center justify-center py-2">
    <div class="w-full max-w-xl text-center mb-4 sm:mb-5">

        <h2 class="k-title">{{ $brand['method_title'] }}</h2>
        <p class="text-slate-500 text-sm sm:text-base mt-1">{{ $brand['method_subtitle'] }}</p>
    </div>

    <form method="POST" action="{{ route('metode') }}" class="w-full max-w-xl" data-metode-form>
        @csrf
        <input type="hidden" name="from_spa" value="{{ ($fromSpa ?? true) ? '1' : '0' }}">
        <input type="hidden" name="metode" id="metode-input" value="{{ $cur }}">

        @if ($errors->any())
            <div class="mb-3 rounded-2xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 mb-4">
            <button type="button" data-metode-option="qris" aria-pressed="{{ $cur === 'qris' ? 'true' : 'false' }}"
                    class="k-card k-option flex h-full flex-col items-center gap-2.5 p-5 sm:p-6 text-center focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-400 {{ $cur === 'qris' ? 'ring-2 ring-rose-500' : '' }}">
                <span class="k-icon w-14 h-14 sm:w-16 sm:h-16 rounded-2xl"><x-icon name="qr" class="w-7 h-7 sm:w-8 sm:h-8 text-white" /></span>
                <span class="font-display font-extrabold text-xl text-slate-900 leading-none">QRIS</span>
                <span class="text-xs text-slate-500 leading-relaxed">Scan QRIS.</span>
            </button>

            <button type="button" data-metode-option="voucher" aria-pressed="{{ $cur === 'voucher' ? 'true' : 'false' }}"
                    class="k-card k-option flex h-full flex-col items-center gap-2.5 p-5 sm:p-6 text-center focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-400 {{ $cur === 'voucher' ? 'ring-2 ring-rose-500' : '' }}">
                <span class="k-icon w-14 h-14 sm:w-16 sm:h-16 rounded-2xl">
                    <svg class="w-7 h-7 sm:w-8 sm:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                </span>
                <span class="font-display font-extrabold text-xl text-slate-900 leading-none">Voucher</span>
                <span class="text-xs text-slate-500 leading-relaxed">Langsung foto.</span>
            </button>
        </div>

        <div data-voucher-box class="{{ $cur === 'voucher' ? '' : 'hidden' }} mb-4">
            <label for="voucher-code" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2 text-center">Kode Voucher</label>
            <input type="text" id="voucher-code" name="voucher_code" value="{{ old('voucher_code') }}"
                   autocomplete="off" autocapitalize="characters" spellcheck="false" maxlength="50"
                   placeholder="Contoh: PB-OPENING"
                   class="k-field text-center font-mono text-xl font-bold tracking-[0.2em] uppercase placeholder:text-slate-300 placeholder:text-base placeholder:tracking-widest @error('voucher_code') !border-red-300 @enderror">
            <p class="mt-2 text-[11px] text-slate-400 text-center">Kode dari kasir.</p>
            @error('voucher_code')<p class="mt-2 text-sm text-red-500 text-center">{{ $message }}</p>@enderror
        </div>

        <button type="submit" id="metode-submit" class="k-btn w-full">
            {{ $cur === 'voucher' ? 'Gunakan Voucher & Mulai Foto' : 'Lanjut Pilih Frame' }}
            <x-icon name="arrow-right" class="w-5 h-5 text-white" />
        </button>


    </form>
</div>
