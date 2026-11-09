@extends('layouts.app')

@php
    $brand = \App\Support\Brand::get();
@endphp

@section('title', 'Tutorial — '.$brand['name'])

@section('content')
@php
    $steps = [
        [$brand['method_title'], 'Pilih QRIS atau masukkan kode voucher di awal, sebelum memilih frame.'],
        [$brand['frame_title'], 'Khusus QRIS: ketuk frame favorit dan atur jumlah cetak. Pemakai voucher langsung ke sesi foto.'],
        [$brand['photo_title'], 'Berdiri di depan kamera dan ambil semua foto. Bisa diulang bila kurang pas.'],
        ['Cetak & Kirim', 'Cetak hasilnya, unduh softfile, atau kirim ke email.'],
    ];
@endphp
<div class="flex-1 min-h-0 flex flex-col items-center justify-center py-2">
    <div class="w-full max-w-2xl bg-white/90 backdrop-blur rounded-[2rem] shadow-xl shadow-rose-100 ring-1 ring-rose-100 p-4 sm:p-6">
        <div class="text-center mb-4">
            <div class="inline-flex items-center gap-2 rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-600 ring-1 ring-rose-100 mb-2">
                Antrian {{ $queue }}
            </div>
            <h2 class="font-display font-bold text-2xl text-slate-800 mb-1">Cara Pakai {{ $brand['name'] }}</h2>
            <p class="text-sm text-slate-500">
                4 langkah mudah &middot; perkiraan {{ $durationMinutes ?? 10 }} menit
            </p>
        </div>

        <div class="grid sm:grid-cols-2 gap-2.5">
            @foreach ($steps as $i => $step)
                <div class="flex items-start gap-3 rounded-xl bg-rose-50/60 ring-1 ring-rose-100 p-3 h-full">
                    <span class="shrink-0 w-9 h-9 rounded-full bg-gradient-to-br from-rose-500 to-violet-600 text-white text-sm font-bold flex items-center justify-center shadow-md">{{ $i + 1 }}</span>
                    <div class="pt-0.5 min-w-0">
                        <h3 class="font-semibold text-slate-800 text-sm sm:text-base">{{ $step[0] }}</h3>
                        <p class="text-xs sm:text-sm text-slate-500 leading-snug">{{ $step[1] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-3 flex items-start gap-2 rounded-xl bg-amber-50 ring-1 ring-amber-100 px-3 py-2 text-xs text-amber-800">
            <x-icon name="clock" class="w-4 h-4 shrink-0 mt-0.5" />
            <span>Waktu sesi berjalan otomatis. Selesaikan sebelum waktu habis ya.</span>
        </div>

        <div class="mt-4 flex justify-center">
            <a href="{{ route('spa') }}" class="inline-flex items-center justify-center w-full max-w-xs px-8 py-3.5 rounded-full bg-gradient-to-r from-rose-500 to-violet-600 text-white font-display font-bold text-lg shadow-lg shadow-rose-200 hover:shadow-xl hover:scale-105 active:scale-95 transition-all focus:outline-none focus-visible:ring-4 focus-visible:ring-rose-300">
                {{ $brand['start_label'] }} &amp; {{ $brand['method_title'] }}
                <x-icon name="arrow-right" class="w-5 h-5 text-white ml-1.5" />
            </a>
        </div>
    </div>
</div>
@endsection
