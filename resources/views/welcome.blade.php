@extends('layouts.app')

@php
    $brand = \App\Support\Brand::get();
@endphp

@section('title', 'Selamat Datang — '.$brand['name'])

@section('content')
<div class="flex-1 min-h-0 flex flex-col items-center justify-center text-center py-4">
    <div class="inline-flex items-center gap-2 rounded-full bg-white/70 backdrop-blur px-4 py-1.5 text-xs font-semibold text-rose-600 ring-1 ring-rose-100 shadow-sm mb-5">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
        {{ $brand['welcome_badge'] }} &middot; {{ $frameCount ?? 0 }} frame tersedia
    </div>

    <x-brand-mark size="w-24 h-24 sm:w-28 sm:h-28" rounded="rounded-[2rem]" icon="w-12 h-12 sm:w-14 sm:h-14" class="mb-6 shadow-2xl" />

    <h1 class="font-display font-extrabold tracking-tight text-5xl sm:text-7xl bg-gradient-to-r from-rose-500 to-violet-600 bg-clip-text text-transparent pb-1 mb-3">
        {{ $brand['name'] }}
    </h1>
    <p class="text-slate-500 text-lg sm:text-xl max-w-md mb-8 leading-snug">
        {{ $brand['welcome'] }}
    </p>

    <form method="POST" action="{{ route('mulai') }}">
        @csrf
        <button type="submit" class="k-btn !px-14 sm:!px-20 !py-5 !text-2xl hover:scale-105">
            {{ $brand['start_label'] }}
            <x-icon name="arrow-right" class="w-6 h-6 text-white" />
        </button>
    </form>

    <p class="mt-4 text-xs sm:text-sm text-slate-400">
        &plusmn; {{ $durationMinutes ?? 10 }} menit
        @if (!empty($minPrice))
            &middot; mulai Rp. {{ number_format($minPrice, 0, ',', '.') }}
        @endif
    </p>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-3 mt-10 w-full max-w-2xl">
        @foreach ([['card', 'Bayar / Voucher'], ['photo', 'Pilih Frame'], ['camera', 'Sesi Foto'], ['printer', 'Cetak & Kirim']] as $item)
            <div class="k-card flex flex-col items-center gap-2.5 px-2 py-4 sm:px-3 sm:py-5">
                <span class="k-icon w-12 h-12 sm:w-14 sm:h-14 rounded-2xl">
                    <x-icon :name="$item[0]" class="w-6 h-6 sm:w-7 sm:h-7 text-white" />
                </span>
                <span class="text-xs sm:text-sm font-semibold text-slate-600 text-center leading-tight">{{ $item[1] }}</span>
            </div>
        @endforeach
    </div>

    <p class="mt-4 text-[11px] sm:text-xs text-slate-400 max-w-md">
        Punya voucher? Pilih <strong>Voucher</strong> di awal &mdash; frame dan jumlah cetak otomatis, langsung ke sesi foto.
    </p>
</div>
@endsection
