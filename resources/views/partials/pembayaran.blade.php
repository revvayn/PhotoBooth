<div class="flex-1 min-h-0 flex flex-col">
    <div class="text-center mb-2">
        <div class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1 text-[11px] font-bold text-amber-700 ring-1 ring-amber-100 mb-2">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
            Menunggu pembayaran
        </div>
        <h2 class="font-display font-bold text-2xl">Konfirmasi QRIS</h2>
        <p class="text-slate-500 text-sm mt-0.5">Bayar sekarang, lalu lanjut ke sesi foto.</p>
    </div>

    <div class="flex flex-col items-center justify-center flex-1 min-h-0">
        <div class="w-full max-w-sm bg-white/95 backdrop-blur rounded-[2rem] shadow-xl shadow-rose-100 ring-1 ring-rose-100 p-4 sm:p-5 text-center">
            <div class="w-10 h-10 mx-auto rounded-2xl bg-rose-500 text-white flex items-center justify-center mb-2"><x-icon name="card" class="w-5 h-5 text-white" /></div>
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Scan QRIS</p>
            @if (!empty($qris_image))
                <div class="w-40 h-40 sm:w-44 sm:h-44 mx-auto flex items-center justify-center mb-2">
                    <img src="{{ asset('storage/' . $qris_image) }}" alt="QRIS" class="w-full h-full object-contain">
                </div>
            @else
                <div class="w-40 h-40 sm:w-44 sm:h-44 mx-auto bg-slate-50 rounded-2xl ring-1 ring-slate-200 flex items-center justify-center mb-2" id="qrcode" data-nmid="{{ $nmid }}" data-merchant="{{ $merchant }}" data-queue="{{ $queue }}"></div>
            @endif
            <p class="text-2xl font-display font-bold text-slate-800 mb-0.5 tabular-nums" data-qris-price>Rp. {{ number_format($total, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-500">{{ $merchant }} &middot; NMID {{ $nmid }}</p>
            <p class="text-sm text-slate-400 mt-1.5">Antrian <span class="font-bold text-rose-500">{{ $queue }}</span></p>
            <p class="mt-3 rounded-xl bg-slate-50 px-3 py-2 text-[11px] text-slate-500 leading-snug">
                Buka aplikasi e-wallet / m-banking, scan QR di atas, lalu tekan konfirmasi.
            </p>
        </div>

        <form method="POST" action="{{ route('pembayaran') }}" data-pay-form class="mt-4 w-full max-w-sm space-y-3">
            @csrf
            <input type="hidden" name="from_spa" value="{{ ($fromSpa ?? true) ? '1' : '0' }}">

            @if (!empty($needs_pin))
                <div>
                    <label for="pin-input" class="block text-sm font-semibold text-slate-700 text-center mb-1.5">
                        PIN Kasir <span class="font-normal text-slate-400">&mdash; diisi kasir setelah pembayaran diterima</span>
                    </label>
                    <input type="password" id="pin-input" name="pin" inputmode="numeric" pattern="[0-9]*" autocomplete="off" maxlength="8" required autofocus
                           class="w-full text-center tracking-[0.5em] text-xl font-bold rounded-2xl bg-white ring-1 ring-rose-200 px-4 py-3 focus:ring-2 focus:ring-rose-400 focus:outline-none @error('pin') ring-2 ring-red-400 @enderror"
                           placeholder="&bull;&bull;&bull;&bull;">
                    @error('pin')
                        <p class="mt-1.5 text-sm text-red-600 text-center">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <button type="submit" data-pay-submit
                    class="w-full px-9 py-3.5 rounded-full bg-gradient-to-r from-emerald-500 to-teal-500 text-white font-display font-bold text-lg shadow-lg shadow-emerald-200 hover:scale-[1.02] active:scale-95 disabled:opacity-60 transition-transform focus:outline-none focus-visible:ring-4 focus-visible:ring-emerald-300">
                {{ !empty($needs_pin) ? 'Konfirmasi Pembayaran' : 'Sudah Bayar, Lanjut Foto' }}
            </button>

            <a href="{{ ($fromSpa ?? true) ? route('spa', ['step' => 'frame']) : route('frame') }}" data-back-btn
               class="flex items-center justify-center gap-1.5 text-sm font-semibold text-slate-500 hover:text-rose-600 transition">
                <x-icon name="arrow-left" class="w-4 h-4" /> Ubah pilihan frame
            </a>
        </form>
    </div>
</div>
