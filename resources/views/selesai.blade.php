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

    <div class="k-card w-full max-w-3xl p-4 mb-3">
        <div class="k-inset flex gap-4 overflow-x-auto max-w-full p-3">
            @forelse ($items as $item)
                <div class="shrink-0 w-[150px] sm:w-[180px] overflow-hidden rounded-2xl shadow-xl shadow-slate-900/10 ring-1 ring-black/5 bg-white" data-strip-container>
                    <canvas class="w-full h-auto block" data-strip-canvas
                            data-photos='@json($item['photos'] ?? [])'
                            data-filters='@json($item['filter_map'] ?? [])'
                            data-filter="{{ $filter }}"
                            data-frame="{{ $item['slug'] }}"
                            data-frame-image="{{ $item['image_url'] }}"
                            data-frame-slots='@json($item['slots'] ?? [])'
                            data-print-width="{{ $item['size_width_cm'] ?? '' }}"
                            data-stickers='@json($item['sticker_map'] ?? [])'></canvas>
                    <div class="px-3 py-2 text-center text-[11px] font-semibold text-slate-500 border-t border-slate-100">
                        {{ $item['name'] }}@if ($item['qty'] > 1) &times;{{ $item['qty'] }}@endif
                        @if (!empty($item['size_name']))
                            <span class="block text-[10px] font-normal text-slate-400">{{ $item['size_name'] }}@if (!empty($item['size_label'])) &middot; {{ $item['size_label'] }}@endif</span>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-center text-slate-400 self-center mx-auto">Belum ada frame yang dipilih.</p>
            @endforelse
        </div>
    </div>

    @if ($downloadToken)
        <div class="k-card w-full max-w-3xl p-3 mb-3 flex flex-col sm:flex-row items-center gap-4">
            <div class="shrink-0 rounded-xl bg-white p-2 ring-1 ring-slate-200">
                <canvas data-download-qr
                        data-download-qr-url="{{ \App\Support\DownloadLink::show($downloadToken) }}"
                        class="w-24 h-24"></canvas>
            </div>
            <div class="min-w-0 text-center sm:text-left">
                <p class="text-sm font-bold text-slate-800">Unduh hasil lewat HP</p>
                <p class="text-[11px] text-slate-500 mt-0.5">Pindai QR ini, atau simpan token di bawah lalu masukkan di halaman unduh.</p>
                <p class="mt-1 inline-block rounded-lg bg-slate-100 px-2 py-1 font-mono text-[11px] font-bold tracking-wide text-slate-700 break-all">{{ $downloadToken }}</p>
            </div>
        </div>
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
