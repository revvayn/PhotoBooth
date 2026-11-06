@extends('layouts.app')

@php
    $brand = \App\Support\Brand::get();
@endphp

@section('title', 'Selamat Datang — '.$brand['name'])

@section('content')
<div class="flex-1 min-h-0 flex flex-col items-center justify-center text-center py-4">
    <div class="inline-flex items-center gap-2 rounded-full bg-white/70 backdrop-blur px-4 py-1.5 text-xs font-semibold text-rose-600 ring-1 ring-rose-100 shadow-sm mb-5">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
        Siap dipakai &middot; {{ $frameCount ?? 0 }} frame tersedia
    </div>

    <x-brand-mark size="w-24 h-24 sm:w-28 sm:h-28" rounded="rounded-[2rem]" icon="w-14 h-14 sm:w-16 sm:h-16" class="animate-bounce-slow mb-5" />

    <h1 class="font-display font-extrabold text-5xl sm:text-6xl bg-gradient-to-r from-rose-500 to-violet-600 bg-clip-text text-transparent mb-3">
        {{ $brand['name'] }}
    </h1>
    <p class="text-slate-500 text-base sm:text-lg max-w-md mb-8 leading-relaxed">
        {{ $brand['welcome'] }}
    </p>

    <form method="POST" action="{{ route('mulai') }}">
        @csrf
        <button type="submit" class="group relative px-14 sm:px-16 py-4 sm:py-5 rounded-full bg-gradient-to-r from-rose-500 to-violet-600 text-white text-xl sm:text-2xl font-display font-bold shadow-xl shadow-rose-300 hover:shadow-2xl hover:scale-105 active:scale-95 transition-all focus:outline-none focus-visible:ring-4 focus-visible:ring-rose-300">
            <span class="inline-flex items-center gap-2">
                Mulai
                <x-icon name="arrow-right" class="w-6 h-6 text-white" />
            </span>
            <span class="absolute inset-0 rounded-full bg-white/20 opacity-0 group-hover:animate-ping-slow group-hover:opacity-100 transition-opacity"></span>
        </button>
    </form>

    <p class="mt-4 text-xs sm:text-sm text-slate-400">
        &plusmn; {{ $durationMinutes ?? 10 }} menit
        @if (!empty($minPrice))
            &middot; mulai Rp. {{ number_format($minPrice, 0, ',', '.') }}
        @endif
    </p>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mt-10 w-full max-w-2xl">
        @foreach ([
            ['icon' => 'card', 'color' => 'bg-rose-100 text-rose-600', 'title' => 'Bayar / Voucher'],
            ['icon' => 'photo', 'color' => 'bg-fuchsia-100 text-fuchsia-600', 'title' => 'Pilih Frame'],
            ['icon' => 'camera', 'color' => 'bg-emerald-100 text-emerald-600', 'title' => 'Sesi Foto'],
            ['icon' => 'printer', 'color' => 'bg-violet-100 text-violet-600', 'title' => 'Cetak & Kirim'],
        ] as $i => $item)
            <div class="flex flex-col items-center gap-2 rounded-2xl bg-white/55 backdrop-blur px-2 py-3 ring-1 ring-white/60">
                <span class="relative w-11 h-11 rounded-2xl {{ $item['color'] }} flex items-center justify-center">
                    <x-icon :name="$item['icon']" class="w-5 h-5" />
                    <span class="absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full bg-white text-[9px] font-bold text-slate-500 ring-1 ring-slate-100 flex items-center justify-center">{{ $i + 1 }}</span>
                </span>
                <span class="text-xs sm:text-sm font-semibold text-slate-600">{{ $item['title'] }}</span>
            </div>
        @endforeach
    </div>

    <p class="mt-4 text-[11px] sm:text-xs text-slate-400">
        Punya voucher? Pilih <strong>Voucher</strong> di awal &mdash; frame dan jumlah cetak otomatis, langsung ke sesi foto.
    </p>
</div>
@endsection
