@extends('layouts.app')

@section('title', 'Selesai — Photobooth')

@section('content')
<div class="flex-1 flex flex-col items-center">
    <div class="text-center mb-6">
        <h2 class="font-display font-bold text-3xl">Review Foto</h2>
        <p class="text-slate-500 text-sm mt-1">Berikut hasil frame yang sudah dibuat</p>
    </div>

    <div class="w-full max-w-sm mx-auto overflow-hidden rounded-[2rem] shadow-xl shadow-rose-100 ring-1 ring-rose-100 bg-white" data-strip-container>
        <canvas class="w-full h-auto block" data-strip-canvas
                data-photos='@json($photos)'
                data-filter="{{ $filter }}"
                data-frame="{{ $frame }}"
                data-frame-image="{{ $frameImage }}"
                data-frame-slots='@json($frameSlots)'></canvas>
    </div>

    @if ($email)
        <p class="text-sm text-slate-500 mt-4">📩 Softfile telah dikirim ke <span class="font-semibold text-slate-700">{{ $email }}</span></p>
    @endif

    <div class="mt-8 flex flex-col sm:flex-row gap-3 w-full max-w-sm">
        <a href="{{ route('softfile.download') }}" data-strip-download class="flex-1 text-center rounded-full bg-gradient-to-r from-rose-500 to-violet-600 text-white font-display font-bold px-8 py-4 shadow-lg shadow-rose-200 hover:scale-105 transition-transform">
            ⬇️ Download Softfile
        </a>

        <form method="POST" action="{{ route('selesai.store') }}" class="flex-1">
            @csrf
            <button type="submit" class="w-full rounded-full bg-slate-800 text-white font-display font-bold px-8 py-4 shadow-md hover:scale-105 transition-transform">
                Selesai
            </button>
        </form>
    </div>
</div>
@endsection