<div class="flex-1 min-h-0 flex flex-col">
    <div class="text-center mb-2">
        <h2 class="font-display font-bold text-2xl">Pembayaran</h2>
        <p class="text-slate-500 text-sm mt-0.5">Selesaikan pembayaran, lalu lanjut melihat hasil fotomu</p>
    </div>

    <div class="flex flex-col items-center justify-center flex-1 min-h-0">
        @if ($metode === 'qris')
            <div class="w-full max-w-sm bg-white/95 backdrop-blur rounded-[2rem] shadow-xl shadow-rose-100 ring-1 ring-rose-100 p-5 text-center">
                <div class="w-10 h-10 mx-auto rounded-2xl bg-rose-500 text-white flex items-center justify-center mb-2"><x-icon name="card" class="w-5 h-5 text-white" /></div>
                @if (!empty($qris_image))
                    <div class="w-44 h-44 mx-auto flex items-center justify-center mb-2">
                        <img src="{{ asset('storage/' . $qris_image) }}" alt="QRIS" class="w-full h-full object-contain">
                    </div>
                @else
                    <div class="w-44 h-44 mx-auto bg-slate-50 rounded-2xl ring-1 ring-slate-200 flex items-center justify-center mb-2" id="qrcode" data-nmid="{{ $nmid }}" data-merchant="{{ $merchant }}" data-queue="{{ $queue }}"></div>
                @endif
                <p class="text-2xl font-display font-bold text-slate-800 mb-1 tabular-nums" data-qris-price>Rp. {{ number_format($total, 0, ',', '.') }}</p>
                <p class="text-sm text-slate-500">{{ $merchant }} &middot; NMID {{ $nmid }}</p>
                <p class="text-sm text-slate-400 mt-1.5">Antrian <span class="font-bold text-rose-500">{{ $queue }}</span></p>
            </div>
        @else
            <div class="w-full max-w-sm bg-white/95 backdrop-blur rounded-[2rem] shadow-xl shadow-rose-100 ring-1 ring-rose-100 p-6 text-center">
                <div class="w-10 h-10 mx-auto rounded-2xl from-amber-400 to-emerald-500 bg-gradient-to-br text-white flex items-center justify-center mb-3"><x-icon name="cash" class="w-5 h-5 text-white" /></div>
                <div class="rounded-2xl bg-rose-50 ring-1 ring-rose-100 py-3 px-6 mb-3">
                    <p class="text-sm text-slate-400 mb-0.5">Total Pembayaran</p>
                    <p class="text-3xl font-display font-bold text-slate-800 tabular-nums">Rp. {{ number_format($total, 0, ',', '.') }}</p>
                </div>
                <p class="text-sm text-slate-500 leading-relaxed">Bayar tunai di kasir dan sebutkan nomor antrian <span class="font-bold text-rose-500">{{ $queue }}</span>.</p>
            </div>
        @endif

        <form method="POST" action="{{ route('pembayaran') }}" data-pay-form class="mt-4 w-full max-w-sm space-y-3">
            @csrf
            <input type="hidden" name="from_spa" value="1">

            @if (!empty($needs_pin))
                <div>
                    <label for="pin-input" class="block text-sm font-semibold text-slate-700 text-center mb-1.5">
                        PIN Kasir <span class="font-normal text-slate-400">&mdash; diisi kasir setelah pembayaran diterima</span>
                    </label>
                    <input type="password" id="pin-input" name="pin" inputmode="numeric" autocomplete="off" maxlength="8" required
                           class="w-full text-center tracking-[0.5em] text-xl font-bold rounded-2xl bg-white ring-1 ring-rose-200 px-4 py-3 focus:ring-2 focus:ring-rose-400 focus:outline-none @error('pin') ring-2 ring-red-400 @enderror"
                           placeholder="&bull;&bull;&bull;&bull;">
                    @error('pin')
                        <p class="mt-1.5 text-sm text-red-600 text-center">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <button type="submit" data-pay-submit
                    class="w-full px-9 py-3.5 rounded-full bg-gradient-to-r from-emerald-500 to-teal-500 text-white font-display font-bold text-lg shadow-lg shadow-emerald-200 hover:scale-[1.02] active:scale-95 disabled:opacity-60 transition-transform">
                {{ !empty($needs_pin) ? 'Konfirmasi Pembayaran' : 'Sudah Bayar, Lanjut' }}
            </button>

            <a href="{{ route('spa', ['step' => 'metode']) }}" data-back-btn
               class="flex items-center justify-center gap-1.5 text-sm font-semibold text-slate-500 hover:text-rose-600 transition">
                <x-icon name="arrow-left" class="w-4 h-4" /> Ganti metode pembayaran
            </a>
        </form>
    </div>
</div>
