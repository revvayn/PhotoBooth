<div class="flex-1 min-h-0 flex flex-col">
    <div class="mb-1">
        <a href="{{ route('spa', ['step' => 'metode']) }}" class="text-xs font-semibold text-slate-400 hover:text-rose-500 transition">← Kembali</a>
    </div>
    <div class="text-center mb-3">
        <h2 class="font-display font-bold text-2xl">Pembayaran</h2>
        <p class="text-slate-500 text-sm mt-1">Menerima pembayaran melalui</p>
    </div>

    <div class="flex flex-col items-center justify-center flex-1">
        @if ($metode === 'qris')
            <div class="w-full max-w-sm bg-white/95 backdrop-blur rounded-[2rem] shadow-xl shadow-rose-100 ring-1 ring-rose-100 p-5 sm:p-8 text-center">
                <div class="w-10 h-10 mx-auto rounded-2xl bg-rose-500 text-white flex items-center justify-center text-lg mb-3"></div>
                <div class="w-40 h-40 sm:w-52 sm:h-52 mx-auto bg-slate-50 rounded-2xl ring-1 ring-slate-200 flex items-center justify-center mb-3" id="qrcode"></div>
                <p class="text-2xl font-display font-bold text-slate-800 mb-1" data-qris-price>Rp. {{ number_format($total, 0, ',', '.') }}</p>
                <p class="text-xs text-slate-500 leading-relaxed">QR code bisa di foto sebagai invoice pembayaran.</p>
                <p class="text-xs text-slate-400 mt-2">Antrian <span class="font-bold text-rose-500">{{ $queue }}</span></p>
            </div>
        @else
            <div class="w-full max-w-sm bg-white/95 backdrop-blur rounded-[2rem] shadow-xl shadow-rose-100 ring-1 ring-rose-100 p-8 text-center">
                <div class="w-10 h-10 mx-auto rounded-2xl from-amber-400 to-emerald-500 bg-gradient-to-br text-white flex items-center justify-center text-lg mb-4"></div>
                <p class="text-lg font-semibold text-slate-800 mb-1">{{ $merchant }}</p>
                <p class="text-xs text-slate-500 mb-6">NMID : {{ $nmid }}</p>
                <div class="rounded-2xl bg-rose-50 ring-1 ring-rose-100 py-4 px-6 mb-6">
                    <p class="text-xs text-slate-400 mb-1">Total Pembayaran</p>
                    <p class="text-3xl font-display font-bold text-slate-800">Rp. {{ number_format($total, 0, ',', '.') }}</p>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed">Silakan bayar tunai di kasir menggunakan nomor antrian <span class="font-bold text-rose-500">{{ $queue }}</span>.</p>
            </div>
        @endif

        <div class="mt-5 flex justify-end w-full max-w-sm">
            <form method="POST" action="{{ route('pembayaran') }}" class="flex-1">
                @csrf
                <input type="hidden" name="from_spa" value="1">
                <button type="submit" class="w-full px-10 py-3 rounded-full bg-gradient-to-r from-emerald-500 to-teal-500 text-white font-display font-bold text-lg shadow-lg shadow-emerald-200 hover:scale-105 transition-transform">
                    Sudah Bayar, Lanjut
                </button>
            </form>
        </div>
    </div>
</div>
