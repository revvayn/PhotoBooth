@php($sel = ($metode ?? null) === 'cash' ? 'cash' : 'qris')
<div class="flex-1 min-h-0 flex flex-col items-center justify-center py-2">
    <div class="w-full max-w-xl text-center mb-5">
        <h2 class="font-display font-bold text-3xl mb-1">Metode Pembayaran</h2>
        <p class="text-slate-500">Pilih cara bayar yang kamu suka</p>
    </div>

    <form method="POST" action="{{ route('metode') }}" class="w-full max-w-xl">
        @csrf
        <input type="hidden" name="from_spa" value="1">
        <input type="hidden" name="metode" id="metode-input" value="{{ $sel }}">

        <div class="grid grid-cols-2 gap-4 mb-4">
            <button type="button" data-metode-option="qris"
                    class="flex flex-col items-center gap-2 rounded-3xl bg-white/90 backdrop-blur p-6 transition-all hover:shadow-xl {{ $sel === 'qris' ? 'ring-2 ring-rose-500 shadow-lg shadow-rose-100' : 'ring-1 ring-rose-100' }}">
                <span class="text-rose-500"><x-icon name="qr" class="w-12 h-12" /></span>
                <span class="font-display font-bold text-xl">QRIS</span>
                <span class="text-sm text-slate-400">Scan dengan e-wallet / m-banking</span>
            </button>
            <button type="button" data-metode-option="cash"
                    class="flex flex-col items-center gap-2 rounded-3xl bg-white/90 backdrop-blur p-6 transition-all hover:shadow-xl {{ $sel === 'cash' ? 'ring-2 ring-rose-500 shadow-lg shadow-rose-100' : 'ring-1 ring-rose-100' }}">
                <span class="text-rose-500"><x-icon name="cash" class="w-12 h-12" /></span>
                <span class="font-display font-bold text-xl">TUNAI</span>
                <span class="text-sm text-slate-400">Bayar langsung di kasir</span>
            </button>
        </div>

        <div class="rounded-3xl bg-white/80 ring-1 ring-rose-100 p-5 flex items-center justify-between mb-5">
            <span class="font-medium text-slate-500">Total Pembayaran</span>
            <span class="font-display font-bold text-3xl text-slate-800 tabular-nums">Rp. {{ number_format($total, 0, ',', '.') }}</span>
        </div>

        <div class="flex items-center justify-between gap-3">
            <a href="{{ route('spa', ['step' => 'filter']) }}" data-back-btn
               class="inline-flex items-center gap-1.5 rounded-full bg-white/80 ring-1 ring-rose-100 px-5 py-3 text-sm font-semibold text-slate-600 hover:bg-white transition">
                <x-icon name="arrow-left" class="w-4 h-4" /> Kembali
            </a>
            <button type="submit" class="inline-flex items-center gap-2 px-10 py-3.5 rounded-full bg-gradient-to-r from-rose-500 to-violet-600 text-white font-display font-bold text-lg shadow-lg shadow-rose-200 hover:scale-105 transition-transform">
                Lanjut <x-icon name="arrow-right" class="w-5 h-5 text-white" />
            </button>
        </div>
    </form>
</div>
