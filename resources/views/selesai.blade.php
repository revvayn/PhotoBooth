@extends('layouts.app')

@section('title', 'Selesai — Photobooth')

@section('content')
<div class="flex-1 min-h-0 flex flex-col items-center">
    <div class="text-center mb-2">
        <h2 class="font-display font-bold text-xl">Selesai</h2>
    </div>

    <div class="space-y-3 w-full max-w-[210px]">
        @forelse ($items as $item)
            <div class="mx-auto overflow-hidden rounded-2xl shadow-xl shadow-rose-100 ring-1 ring-rose-100 bg-white" data-strip-container>
                <canvas class="w-full h-auto block" data-strip-canvas
                        data-photos='@json($item['photos'] ?? [])'
                        data-filters='@json($item['filter_map'] ?? [])'
                        data-filter="{{ $filter }}"
                        data-frame="{{ $item['slug'] }}"
                        data-frame-image="{{ $item['image_url'] }}"
                        data-frame-slots='@json($item['slots'] ?? [])'></canvas>
                <div class="px-4 py-2 text-center text-xs font-semibold text-slate-500 border-t border-rose-100">
                    {{ $item['name'] }}@if ($item['qty'] > 1) ×{{ $item['qty'] }}@endif
                </div>
            </div>
        @empty
            <p class="text-center text-slate-400">Belum ada frame yang dipilih.</p>
        @endforelse
    </div>

    @if ($email)
        <p class="text-xs text-slate-500 mt-2"><x-icon name="envelope" class="w-4 h-4 inline-block align-middle" /> Softfile telah dikirim ke <span class="font-semibold text-slate-700">{{ $email }}</span></p>
    @endif

        <div class="mt-3 flex flex-col sm:flex-row gap-2 w-full max-w-sm">
        <button type="button" data-strip-download class="flex-1 text-center rounded-full bg-gradient-to-r from-rose-500 to-violet-600 text-white font-display font-bold px-6 py-2.5 shadow-lg shadow-rose-200 hover:scale-105 transition-transform">
            Download Softfile
        </button>

        <form method="POST" action="{{ route('selesai.store') }}" class="flex-1">

            @csrf
            <button type="submit" class="w-full rounded-full bg-slate-800 text-white font-display font-bold px-6 py-2.5 shadow-md hover:scale-105 transition-transform">
                Selesai
            </button>
        </form>
    </div>
</div>
@endsection
