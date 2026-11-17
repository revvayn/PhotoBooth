@php($brand = $brand ?? \App\Support\Brand::get())
@php($sel = in_array($metode ?? null, ['qris', 'voucher'], true) ? $metode : 'qris')
@php($cur = old('metode', $sel))
<div class="flex-1 min-h-0 flex flex-col items-center justify-center py-2 overflow-y-auto">
    <div class="w-full max-w-2xl">

        <div class="k-step-head mb-5 shrink-0">
            <span aria-hidden="true"></span>
            <div class="k-step-head-title">
                <h2 class="k-title">
                    <span class="k-icon-soft w-9 h-9 rounded-xl">
                        <x-icon name="card" class="w-4 h-4" />
                    </span>
                    {{ $brand['method_title'] }}
                </h2>
                <p class="text-slate-500 text-xs sm:text-sm">{{ $brand['method_subtitle'] }}</p>
            </div>
            <span class="k-pill justify-self-end !text-xs hidden sm:inline-flex">
                <x-icon name="sparkles" class="w-4 h-4 text-rose-500" />
                Pilih satu
            </span>
        </div>

        <form method="POST" action="{{ route('metode') }}" class="w-full" data-metode-form>
            @csrf
            <input type="hidden" name="from_spa" value="{{ ($fromSpa ?? true) ? '1' : '0' }}">
            <input type="hidden" name="metode" id="metode-input" value="{{ $cur }}">

            @if ($errors->any())
                <div class="mb-3 rounded-2xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 mb-3">
                @foreach ([
                    ['qris', 'qr', 'QRIS', 'Scan dan bayar di kasir', 'Bayar dengan aplikasi e-wallet atau m-banking.'],
                    ['voucher', 'ticket', 'Voucher', 'Langsung mulai sesi foto', 'Masukkan kode voucher dari kasir tanpa antre bayar.'],
                ] as $opt)
                    <button type="button" data-metode-option="{{ $opt[0] }}" aria-pressed="{{ $cur === $opt[0] ? 'true' : 'false' }}"
                            class="k-card k-option group relative flex h-full flex-col items-center gap-2.5 p-5 sm:p-6 text-center focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-400">
                        <span class="k-check absolute right-3 top-3" aria-hidden="true">
                            <x-icon name="check" class="w-3.5 h-3.5" />
                        </span>
                        <span class="k-icon-soft w-14 h-14 sm:w-16 sm:h-16 rounded-2xl">
                            <x-icon :name="$opt[1]" class="w-7 h-7 sm:w-8 sm:h-8" />
                        </span>
                        <span class="font-display font-extrabold text-lg sm:text-xl text-slate-900 leading-none">{{ $opt[2] }}</span>
                        <span class="text-xs font-semibold text-rose-500 leading-none">{{ $opt[3] }}</span>
                        <span class="text-xs text-slate-500 leading-relaxed max-w-[220px]">{{ $opt[4] }}</span>
                    </button>
                @endforeach
            </div>

            <div data-voucher-box class="k-inset mb-3 p-4" @if ($cur !== 'voucher') hidden @endif>
                <label for="voucher-code" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2 text-center">Kode Voucher</label>
                <input type="text" id="voucher-code" name="voucher_code" value="{{ old('voucher_code') }}"
                       autocomplete="off" autocapitalize="characters" spellcheck="false" maxlength="50"
                       placeholder="Contoh: PB-OPENING"
                       class="k-field text-center font-mono text-xl font-bold tracking-[0.2em] uppercase placeholder:text-slate-300 placeholder:text-base placeholder:tracking-widest @error('voucher_code') !border-red-300 @enderror">
                <p class="mt-2 text-[11px] text-slate-400 text-center">Minta kode voucher ke kasir bila belum punya.</p>
                @error('voucher_code')<p class="mt-2 text-sm text-red-500 text-center">{{ $message }}</p>@enderror
            </div>

            <div class="mx-auto w-full max-w-sm">
                <button type="submit" id="metode-submit" class="k-btn w-full">
                    {{ $cur === 'voucher' ? 'Gunakan Voucher & Mulai Foto' : 'Lanjut Pilih Frame' }}
                    <x-icon name="arrow-right" class="w-5 h-5 text-white" />
                </button>
            </div>
        </form>
    </div>
</div>
