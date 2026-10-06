@extends('layouts.app')

@section('title', 'Tutorial — Photobooth')

@section('content')
@php
    $steps = [
        ['Pilih Frame', 'Klik frame favoritmu, atur jumlahnya di keranjang.'],
        ['Sesi Foto', 'Ambil foto bergaya photobooth di depan kamera.'],
        ['Pilih Filter', 'Sesuaikan warna fotomu dengan seleramu.'],
        ['Metode Pembayaran', 'Pilih bayar via QRIS atau tunai di kasir.'],
        ['Pembayaran', 'Scan QR atau gunakan nomor antrian '.$queue.'.'],
        ['Review & Cetak', 'Periksa hasilnya, cetak, dan kirim softfile ke email.'],
    ];
@endphp
<div class="flex-1 min-h-0 flex flex-col items-center justify-center py-2">
    <div class="w-full max-w-2xl bg-white/90 backdrop-blur rounded-[2rem] shadow-xl shadow-rose-100 ring-1 ring-rose-100 p-4 sm:p-6">
        <div class="text-center mb-4">
            <h2 class="font-display font-bold text-2xl text-slate-800 mb-1">Cara Pakai Photobooth</h2>
            <p class="text-sm text-slate-500">Ikuti 6 langkah mudah di bawah ini</p>
        </div>

        <div class="grid sm:grid-cols-2 gap-3">
            @foreach ($steps as $i => $step)
                <div class="flex items-start gap-3 rounded-xl bg-rose-50/60 ring-1 ring-rose-100 p-3 h-full">
                    <span class="shrink-0 w-9 h-9 rounded-full bg-gradient-to-br from-rose-500 to-violet-600 text-white text-sm font-bold flex items-center justify-center shadow-md">{{ $i + 1 }}</span>
                    <div class="pt-0.5">
                        <h3 class="font-semibold text-slate-800 text-sm">{{ $step[0] }}</h3>
                        <p class="text-xs text-slate-500 leading-snug">{{ $step[1] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4 flex justify-center">
            <a href="{{ route('spa') }}" class="inline-flex items-center justify-center w-full max-w-xs px-8 py-3.5 rounded-full bg-gradient-to-r from-rose-500 to-violet-600 text-white font-display font-bold text-lg shadow-lg shadow-rose-200 hover:shadow-xl hover:scale-105 transition-all">
                Lanjut <span class="ml-1.5">→</span>
            </a>
        </div>
    </div>
</div>
@endsection