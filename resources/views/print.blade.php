@extends('layouts.app')

@section('title', 'Jumlah Print — Photobooth')

@section('content')
<div class="flex-1 flex flex-col">
    <div class="text-center mb-6">
        <h2 class="font-display font-bold text-3xl">Jumlah Print</h2>
        <p class="text-slate-500 text-sm mt-1">Pilih berapa banyak cetakan fotomu</p>
    </div>

    <form method="POST" action="{{ route('print') }}" data-print-form class="flex flex-col flex-1">
        @csrf
        <input type="hidden" name="copy" id="copy-input" value="1">

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @foreach ($prices as $qty => $price)
                <button type="button" data-copy-option="{{ $qty }}"
                        class="rounded-2xl bg-white/90 backdrop-blur p-6 ring-1 ring-rose-100 transition-all hover:shadow-md {{ $qty === 1 ? 'ring-2 ring-rose-500 shadow-lg shadow-rose-100' : '' }}">
                    <div class="text-4xl mb-2">{{ $qty === 4 ? '🖼️' : '🖨️' }}</div>
                    <div class="font-display font-bold text-slate-800 text-lg">{{ $qty }}x</div>
                    <div class="text-sm text-slate-500 mt-1" data-price-label data-base-price="{{ $price }}">Rp. {{ number_format($price, 0, ',', '.') }}</div>
                </button>
            @endforeach
        </div>

        <div class="mt-auto pt-8 flex items-center justify-between gap-4">
            <div>
                <p class="text-xs text-slate-400">Total</p>
                <p class="text-2xl font-display font-bold text-slate-800" data-total>Rp. {{ number_format($basePrice, 0, ',', '.') }}</p>
            </div>
            <button type="submit" class="px-12 py-4 rounded-full bg-gradient-to-r from-rose-500 to-violet-600 text-white font-display font-bold text-lg shadow-lg shadow-rose-200 hover:scale-105 transition-transform">
                Lanjut →
            </button>
        </div>
    </form>
</div>
@endsection