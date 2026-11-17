@extends('layouts.app')

@php
    $brand = \App\Support\Brand::get();
@endphp

@section('title', 'Selamat Datang — '.$brand['name'])

@section('content')
<div class="flex-1 min-h-0 flex flex-col items-center justify-center text-center py-3">
    <div class="w-full max-w-3xl flex flex-col items-center">

        <span class="k-pill !py-1.5 !px-3.5 !text-xs mb-5">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            {{ $brand['welcome_badge'] }}
        </span>

        <x-brand-mark size="w-20 h-20 sm:w-24 sm:h-24" rounded="rounded-[1.75rem]" icon="w-10 h-10 sm:w-12 sm:h-12" class="mb-5" />

        <h1 class="k-title !text-5xl sm:!text-6xl tracking-tight">
            {{ $brand['name'] }}
        </h1>
        <p class="text-slate-500 text-base sm:text-lg max-w-md mt-3 mb-7 leading-snug">
            {{ $brand['welcome'] }}
        </p>

        <form method="POST" action="{{ route('mulai') }}" class="w-full max-w-sm">
            @csrf
            <button type="submit" class="k-btn w-full !py-4 !text-xl">
                {{ $brand['start_label'] }}
                <x-icon name="arrow-right" class="w-5 h-5 text-white" />
            </button>
        </form>

        <p class="mt-3 text-xs text-slate-400">
            &plusmn; {{ $durationMinutes ?? 10 }} menit
            @if (!empty($frameCount))
                &middot; {{ $frameCount }} frame tersedia
            @endif
        </p>

        {{-- Ribbon 4 langkah dengan gaya seragam. --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-3 mt-8 w-full text-left">
            @foreach ([['card', 'Bayar / Voucher'], ['photo', 'Pilih Frame'], ['camera', 'Sesi Foto'], ['printer', 'Cetak & Kirim']] as $item)
                <div class="k-card flex items-center gap-2.5 px-3 py-3">
                    <span class="k-icon-soft w-9 h-9 rounded-xl shrink-0">
                        <x-icon :name="$item[0]" class="w-[18px] h-[18px]" />
                    </span>
                    <span class="text-xs sm:text-[13px] font-semibold text-slate-600 leading-tight">{{ $item[1] }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
