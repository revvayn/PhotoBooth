@extends('layouts.app')

@section('title', 'Tutorial — Photobooth')

@section('content')
<div class="flex-1 flex flex-col items-center justify-center py-4">
    <div class="w-full max-w-lg bg-white/90 backdrop-blur rounded-[2rem] shadow-xl shadow-rose-100 ring-1 ring-rose-100 p-8">
        <div class="text-center mb-6">
            <h2 class="font-display font-bold text-2xl text-slate-800 mb-1">Cara Pakai Photobooth</h2>
            <p class="text-sm text-slate-500">Ikuti 3 langkah mudah di bawah ini</p>
        </div>

        <div class="space-y-6">
            <div class="flex items-start gap-4">
                <span class="shrink-0 w-10 h-10 rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 text-white font-bold flex items-center justify-center shadow-md">1</span>
                <div class="pt-0.5">
                    <h3 class="font-semibold text-slate-800">Cek Pembayaran <span class="text-rose-500 font-bold ml-1">{{ $queue }}</span></h3>
                    <p class="text-sm text-slate-500">Bayar dulu di kasir, lalu gunakan nomor antrianmu untuk sesi foto.</p>
                </div>
            </div>

            <div class="flex items-start gap-4">
                <span class="shrink-0 w-10 h-10 rounded-full bg-gradient-to-br from-rose-400 to-fuchsia-500 text-white font-bold flex items-center justify-center shadow-md">2</span>
                <div class="pt-0.5">
                    <h3 class="font-semibold text-slate-800">Sesi Foto</h3>
                    <p class="text-sm text-slate-500">Ambil 3 foto dengan gaya photobooth, pilih filter dan frame favoritmu.</p>
                </div>
            </div>

            <div class="flex items-start gap-4">
                <span class="shrink-0 w-10 h-10 rounded-full bg-gradient-to-br from-violet-400 to-purple-500 text-white font-bold flex items-center justify-center shadow-md">3</span>
                <div class="pt-0.5">
                    <h3 class="font-semibold text-slate-800">Print & Download</h3>
                    <p class="text-sm text-slate-500">Cetak fotomu dan unduh softfilenya langsung, atau kirim ke email.</p>
                </div>
            </div>
        </div>

        <div class="mt-8">
            <p class="text-center text-sm text-slate-500 mb-4">Siap? Langsung mulai sesi fotomu!</p>
        </div>
    </div>

    <div class="mt-6 flex justify-end w-full max-w-lg">
        <a href="{{ route('frame') }}" class="px-10 py-4 rounded-full bg-gradient-to-r from-rose-500 to-violet-600 text-white font-display font-bold text-lg shadow-lg shadow-rose-200 hover:shadow-xl hover:scale-105 transition-all">
            Lanjut →
        </a>
    </div>
</div>
@endsection