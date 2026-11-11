@extends('layouts.app')

@php
    $brand = \App\Support\Brand::get();
@endphp

@section('title', 'Selesai — '.$brand['name'])

@section('content')
<div class="flex-1 min-h-0 flex flex-col items-center justify-center py-2">
    <div class="text-center mb-4">
        <div class="mx-auto mb-3 w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center ring-8 ring-emerald-50">
            <x-icon name="check" class="w-8 h-8" />
        </div>
        <h2 class="k-title">Terima kasih!</h2>
        <p class="text-slate-500 text-sm mt-1">Sesi fotomu sudah selesai. Sampai jumpa lagi!</p>
    </div>

    <div class="flex gap-4 overflow-x-auto max-w-full px-2 pt-1 pb-3 mb-3">
        @forelse ($items as $item)
            <div class="shrink-0 w-[150px] sm:w-[180px] overflow-hidden rounded-2xl shadow-xl shadow-rose-100/60 ring-1 ring-black/5 bg-white" data-strip-container>
                <canvas class="w-full h-auto block" data-strip-canvas
                        data-photos='@json($item['photos'] ?? [])'
                        data-filters='@json($item['filter_map'] ?? [])'
                        data-filter="{{ $filter }}"
                        data-frame="{{ $item['slug'] }}"
                        data-frame-image="{{ $item['image_url'] }}"
                        data-frame-slots='@json($item['slots'] ?? [])'></canvas>
                <div class="px-3 py-2 text-center text-[11px] font-semibold text-slate-500 border-t border-rose-100">
                    {{ $item['name'] }}@if ($item['qty'] > 1) &times;{{ $item['qty'] }}@endif
                </div>
            </div>
        @empty
            <p class="text-center text-slate-400">Belum ada frame yang dipilih.</p>
        @endforelse
    </div>

    @if ($email)
        <p class="text-xs text-slate-500 mb-3 text-center">
            <x-icon name="envelope" class="w-4 h-4 inline-block align-middle" />
            Softfile telah dikirim ke <span class="font-semibold text-slate-700">{{ $email }}</span>
        </p>
    @endif

    <div class="flex flex-wrap items-center justify-center gap-3">
        <button type="button" data-strip-download class="k-btn">
            <x-icon name="photo" class="w-4 h-4 text-white" /> Unduh Softfile
        </button>

        <form method="POST" action="{{ route('selesai.store') }}">
            @csrf
            <button type="submit" class="k-btn k-btn-dark">Sesi Baru</button>
        </form>
    </div>

    <p class="mt-3 text-[11px] text-slate-400 text-center">Foto di server akan dihapus setelah kamu menekan Sesi Baru.</p>
</div>
@endsection
