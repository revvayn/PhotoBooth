<div class="flex-1 min-h-0 flex flex-col">
    <div class="text-center mb-2">
        <h2 class="font-display font-bold text-xl">Pembayaran</h2>
    </div>

    <div class="flex flex-col items-center justify-center flex-1">
        @if ($metode === 'qris')
            <div class="w-full max-w-sm bg-white/95 backdrop-blur rounded-[2rem] shadow-xl shadow-rose-100 ring-1 ring-rose-100 p-4 sm:p-6 text-center">
                <div class="w-9 h-9 mx-auto rounded-2xl bg-rose-500 text-white flex items-center justify-center mb-2"><x-icon name="card" class="w-5 h-5 text-white" /></div>
                @if (!empty($qris_image))
                    <div class="w-36 h-36 sm:w-40 sm:h-40 mx-auto flex items-center justify-center mb-2">
                        <img src="{{ asset('storage/' . $qris_image) }}" alt="QRIS" class="w-full h-full object-contain">
                    </div>
                @else
                    <div class="w-36 h-36 sm:w-40 sm:h-40 mx-auto bg-slate-50 rounded-2xl ring-1 ring-slate-200 flex items-center justify-center mb-2" id="qrcode" data-nmid="{{ $nmid }}" data-merchant="{{ $merchant }}"></div>
                @endif
                <p class="text-xl font-display font-bold text-slate-800 mb-1" data-qris-price>Rp. {{ number_format($total, 0, ',', '.') }}</p>
                <p class="text-xs text-slate-500 leading-relaxed">QR code bisa di foto sebagai invoice pembayaran.</p>
                <p class="text-xs text-slate-400 mt-1.5">Antrian <span class="font-bold text-rose-500">{{ $queue }}</span></p>
            </div>
        @else
            <div class="w-full max-w-sm bg-white/95 backdrop-blur rounded-[2rem] shadow-xl shadow-rose-100 ring-1 ring-rose-100 p-4 sm:p-5 text-center">
                <div class="w-9 h-9 mx-auto rounded-2xl from-amber-400 to-emerald-500 bg-gradient-to-br text-white flex items-center justify-center mb-2"><x-icon name="cash" class="w-5 h-5 text-white" /></div>
                <p class="text-base font-semibold text-slate-800 mb-3">{{ $merchant }} <span class="text-slate-400 font-normal">· NMID {{ $nmid }}</span></p>
                <div class="rounded-2xl bg-rose-50 ring-1 ring-rose-100 py-2.5 px-6 mb-3">
                    <p class="text-xs text-slate-400 mb-0.5">Total Pembayaran</p>
                    <p class="text-2xl font-display font-bold text-slate-800">Rp. {{ number_format($total, 0, ',', '.') }}</p>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed">Silakan bayar tunai di kasir menggunakan nomor antrian <span class="font-bold text-rose-500">{{ $queue }}</span>.</p>
            </div>
        @endif

        <div class="mt-4 flex justify-end w-full max-w-sm">
            <form method="POST" action="{{ route('pembayaran') }}" class="flex-1">
                @csrf
                <input type="hidden" name="from_spa" value="1">
                <button type="submit" class="w-full px-9 py-2.5 rounded-full bg-gradient-to-r from-emerald-500 to-teal-500 text-white font-display font-bold text-lg shadow-lg shadow-emerald-200 hover:scale-105 transition-transform">
                    Sudah Bayar, Lanjut
                </button>
            </form>
        </div>
    </div>
</div>
