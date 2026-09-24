@extends('layouts.app')

@section('title', 'Tutorial — Photobooth')

@section('content')
<div class="flex-1 flex flex-col items-center justify-center py-4">
    <div class="w-full max-w-lg bg-white/90 backdrop-blur rounded-[2rem] shadow-xl shadow-rose-100 ring-1 ring-rose-100 p-5 sm:p-8">
        <div class="text-center mb-5">
            <h2 class="font-display font-bold text-2xl text-slate-800 mb-1">Cara Pakai Photobooth</h2>
            <p class="text-sm text-slate-500">Ikuti 6 langkah mudah di bawah ini</p>
        </div>

        <div class="space-y-4">
            <div class="flex items-start gap-3">
                <span class="shrink-0 w-8 h-8 rounded-full bg-gradient-to-br from-rose-400 to-fuchsia-500 text-white text-sm font-bold flex items-center justify-center shadow-md">1</span>
                <div class="pt-0.5">
                    <h3 class="font-semibold text-slate-800 text-sm">Pilih Frame</h3>
                    <p class="text-xs text-slate-500">Klik frame favoritmu, atur jumlahnya di keranjang.</p>
                </div>
            </div>

            <div class="flex items-start gap-3">
                <span class="shrink-0 w-8 h-8 rounded-full bg-gradient-to-br from-rose-400 to-fuchsia-500 text-white text-sm font-bold flex items-center justify-center shadow-md">2</span>
                <div class="pt-0.5">
                    <h3 class="font-semibold text-slate-800 text-sm">Sesi Foto</h3>
                    <p class="text-xs text-slate-500">Ambil foto bergaya photobooth di depan kamera.</p>
                </div>
            </div>

            <div class="flex items-start gap-3">
                <span class="shrink-0 w-8 h-8 rounded-full bg-gradient-to-br from-rose-400 to-fuchsia-500 text-white text-sm font-bold flex items-center justify-center shadow-md">3</span>
                <div class="pt-0.5">
                    <h3 class="font-semibold text-slate-800 text-sm">Pilih Filter</h3>
                    <p class="text-xs text-slate-500">Sesuaikan warna fotomu dengan seleramu.</p>
                </div>
            </div>

            <div class="flex items-start gap-3">
                <span class="shrink-0 w-8 h-8 rounded-full bg-gradient-to-br from-violet-400 to-purple-500 text-white text-sm font-bold flex items-center justify-center shadow-md">4</span>
                <div class="pt-0.5">
                    <h3 class="font-semibold text-slate-800 text-sm">Metode Pembayaran</h3>
                    <p class="text-xs text-slate-500">Pilih bayar via QRIS atau tunai di kasir.</p>
                </div>
            </div>

            <div class="flex items-start gap-3">
                <span class="shrink-0 w-8 h-8 rounded-full bg-gradient-to-br from-violet-400 to-purple-500 text-white text-sm font-bold flex items-center justify-center shadow-md">5</span>
                <div class="pt-0.5">
                    <h3 class="font-semibold text-slate-800 text-sm">Pembayaran <span class="text-rose-500 font-bold ml-1">{{ $queue }}</span></h3>
                    <p class="text-xs text-slate-500">Scan QR atau gunakan nomor antrianmu.</p>
                </div>
            </div>

            <div class="flex items-start gap-3">
                <span class="shrink-0 w-8 h-8 rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 text-white text-sm font-bold flex items-center justify-center shadow-md">6</span>
                <div class="pt-0.5">
                    <h3 class="font-semibold text-slate-800 text-sm">Review & Cetak</h3>
                    <p class="text-xs text-slate-500">Periksa hasilnya, cetak, dan kirim softfile ke email.</p>
                </div>
            </div>
        </div>

        <div class="mt-5">
            <p class="text-center text-sm text-slate-500 mb-4">Siap? Langsung mulai sesi fotomu!</p>
        </div>
    </div>

    <div class="mt-6 flex justify-end w-full max-w-lg">
                <a href="{{ route('spa') }}" class="px-10 py-4 rounded-full bg-gradient-to-r from-rose-500 to-violet-600 text-white font-display font-bold text-lg shadow-lg shadow-rose-200 hover:shadow-xl hover:scale-105 transition-all">
            Lanjut →
        </a>
    </div>
</div>
@endsection