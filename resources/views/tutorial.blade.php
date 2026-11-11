@extends('layouts.app')

@php
    $brand = \App\Support\Brand::get();
@endphp

@section('title', 'Tutorial — '.$brand['name'])

@section('content')
@php
    $steps = [
        ['card', $brand['method_title'], 'Pilih QRIS atau masukkan kode voucher di awal, sebelum memilih frame.'],
        ['photo', $brand['frame_title'], 'Khusus QRIS: ketuk frame favorit dan atur jumlah cetak. Pemakai voucher langsung ke sesi foto.'],
        ['camera', $brand['photo_title'], 'Berdiri di depan kamera dan ambil semua foto. Bisa diulang bila kurang pas.'],
        ['printer', 'Cetak & Kirim', 'Cetak hasilnya, unduh softfile, atau kirim ke email.'],
    ];
@endphp
<div class="flex-1 min-h-0 flex flex-col items-center justify-center py-2">
    <div class="w-full max-w-3xl">
        <div class="text-center mb-5">
            <div class="inline-flex items-center gap-2 rounded-full bg-white/70 backdrop-blur px-3 py-1 text-[11px] font-bold text-slate-500 ring-1 ring-rose-100 mb-2">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                Antrian {{ $queue ?? '-' }}
            </div>
            <h2 class="k-title">Cara Pakai {{ $brand['name'] }}</h2>
            <p class="text-sm text-slate-500 mt-1">4 langkah mudah &middot; perkiraan {{ $durationMinutes ?? 10 }} menit</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
            @foreach ($steps as $i => $step)
                <div class="k-card p-4 sm:p-5 flex items-start gap-4 shadow-xl shadow-rose-100/50">
                    <span class="k-icon shrink-0 w-12 h-12 rounded-2xl">
                        <x-icon :name="$step[0]" class="w-6 h-6 text-white" />
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-rose-500 tabular-nums">0{{ $i + 1 }}</p>
                        <h3 class="font-display font-bold text-slate-900 text-base sm:text-lg leading-tight">{{ $step[1] }}</h3>
                        <p class="text-sm text-slate-500 leading-snug mt-0.5">{{ $step[2] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-3 flex items-start gap-2 rounded-xl bg-amber-50 ring-1 ring-amber-100 px-3 py-2 text-xs text-amber-800">
            <x-icon name="clock" class="w-4 h-4 shrink-0 mt-0.5" />
            <span>Waktu sesi berjalan otomatis. Selesaikan sebelum waktu habis ya.</span>
        </div>

        <div class="mt-5 flex justify-center">
            <a href="{{ route('spa') }}" class="k-btn !px-12">
                {{ $brand['start_label'] }} &amp; {{ $brand['method_title'] }}
                <x-icon name="arrow-right" class="w-5 h-5 text-white" />
            </a>
        </div>
    </div>
</div>
@endsection
