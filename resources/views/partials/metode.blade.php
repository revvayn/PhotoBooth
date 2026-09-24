<div class="flex-1 flex flex-col items-center justify-center py-4">
    <div class="w-full max-w-lg text-center mb-8">
        <h2 class="font-display font-bold text-3xl mb-2">Metode Pembayaran</h2>
        <p class="text-slate-500 text-sm">Menerima pembayaran melalui</p>
    </div>

    <form method="POST" action="{{ route('metode') }}" class="w-full max-w-lg">
        @csrf
        <input type="hidden" name="from_spa" value="1">
        <input type="hidden" name="metode" id="metode-input" value="qris">

        <div class="grid grid-cols-2 gap-4 mb-8">
            <button type="button" data-metode-option="qris"
                    class="flex flex-col items-center gap-3 rounded-3xl bg-white/90 backdrop-blur p-8 ring-2 ring-rose-500 shadow-lg shadow-rose-100 transition-all hover:shadow-xl">
                <span class="text-5xl">ðŸ“±</span>
                <span class="font-display font-bold text-xl">QRIS</span>
                <span class="text-xs text-slate-400">Scan pembayaran</span>
            </button>
            <button type="button" data-metode-option="cash"
                    class="flex flex-col items-center gap-3 rounded-3xl bg-white/90 backdrop-blur p-8 ring-1 ring-rose-100 transition-all hover:shadow-xl">
                <span class="text-5xl">ðŸ’°</span>
                <span class="font-display font-bold text-xl">CASH</span>
                <span class="text-xs text-slate-400">Bayar tunai di kasir</span>
            </button>
        </div>

        <div class="rounded-3xl bg-white/80 ring-1 ring-rose-100 p-5 flex items-center justify-between mb-8">
            <span class="text-sm font-medium text-slate-500">Total Pembayaran</span>
            <span class="font-display font-bold text-2xl text-slate-800">Rp. {{ number_format($total, 0, ',', '.') }}</span>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-12 py-4 rounded-full bg-gradient-to-r from-rose-500 to-violet-600 text-white font-display font-bold text-lg shadow-lg shadow-rose-200 hover:scale-105 transition-transform">
                Lanjut â†’
            </button>
        </div>
    </form>
</div>
