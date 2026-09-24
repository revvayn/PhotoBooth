<div class="flex-1 min-h-0 flex flex-col items-center justify-center py-2">
    <div class="w-full max-w-lg mb-1">
        <a href="{{ route('spa', ['step' => 'filter']) }}" class="text-xs font-semibold text-slate-400 hover:text-rose-500 transition">← Kembali</a>
    </div>
    <div class="w-full max-w-lg text-center mb-4">
        <h2 class="font-display font-bold text-2xl sm:text-3xl mb-1">Metode Pembayaran</h2>
        <p class="text-slate-500 text-sm">Menerima pembayaran melalui</p>
    </div>

    <form method="POST" action="{{ route('metode') }}" class="w-full max-w-lg">
        @csrf
        <input type="hidden" name="from_spa" value="1">
        <input type="hidden" name="metode" id="metode-input" value="qris">

        <div class="grid grid-cols-2 gap-3 sm:gap-4 mb-5">
            <button type="button" data-metode-option="qris"
                    class="flex flex-col items-center gap-2 rounded-3xl bg-white/90 backdrop-blur p-5 sm:p-8 ring-2 ring-rose-500 shadow-lg shadow-rose-100 transition-all hover:shadow-xl">
                <span class="text-rose-500"><x-icon name="qr" class="w-10 h-10 sm:w-12 sm:h-12" /></span>
                <span class="font-display font-bold text-lg sm:text-xl">QRIS</span>
                <span class="text-xs text-slate-400">Scan pembayaran</span>
            </button>
            <button type="button" data-metode-option="cash"
                    class="flex flex-col items-center gap-2 rounded-3xl bg-white/90 backdrop-blur p-5 sm:p-8 ring-1 ring-rose-100 transition-all hover:shadow-xl">
                <span class="text-rose-500"><x-icon name="cash" class="w-10 h-10 sm:w-12 sm:h-12" /></span>
                <span class="font-display font-bold text-lg sm:text-xl">CASH</span>
                <span class="text-xs text-slate-400">Bayar tunai di kasir</span>
            </button>
        </div>

        <div class="rounded-3xl bg-white/80 ring-1 ring-rose-100 p-4 sm:p-5 flex items-center justify-between mb-5">
            <span class="text-sm font-medium text-slate-500">Total Pembayaran</span>
            <span class="font-display font-bold text-2xl text-slate-800">Rp. {{ number_format($total, 0, ',', '.') }}</span>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-10 py-3 rounded-full bg-gradient-to-r from-rose-500 to-violet-600 text-white font-display font-bold text-lg shadow-lg shadow-rose-200 hover:scale-105 transition-transform">
                Lanjut
            </button>
        </div>
    </form>
</div>
