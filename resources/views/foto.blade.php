@extends('layouts.app')

@section('title', 'Sesi Foto — Photobooth')

@section('content')
<div class="flex-1 flex flex-col">
    <div class="pb-3 flex items-center justify-between">
        <h2 class="font-display font-bold text-2xl">Sesi Foto</h2>
    </div>

    <div class="grid grid-cols-3 gap-3 mb-4" data-shot-thumbs>
        @foreach (['Kamera 1', 'Kamera 2', 'Kamera 3'] as $i => $label)
            <div class="rounded-2xl overflow-hidden border border-rose-200 aspect-[3/4] bg-white shadow-sm relative" data-shot-item="{{ $i }}">
                <div class="absolute inset-0 flex flex-col items-center justify-center text-center gap-1">
                    <span class="text-3xl">🎞️</span>
                    <span class="text-xs font-semibold text-slate-500">{{ $label }}</span>
                    <span class="text-[10px] text-slate-400" data-shot-status>Menunggu</span>
                </div>
                <img class="hidden w-full h-full object-cover" data-shot-img alt="Foto {{ $i + 1 }}">
            </div>
        @endforeach
    </div>

    <div class="relative flex-1 rounded-[2rem] overflow-hidden bg-slate-900 shadow-2xl aspect-square sm:aspect-[4/3]">
        <video id="camera" class="w-full h-full object-cover" autoplay playsinline muted></video>

        <div class="absolute inset-0 flex flex-col items-center justify-center bg-slate-900/70 text-white hidden" data-camera-loading>
            <span class="animate-spin w-10 h-10 border-4 border-white/30 border-t-white rounded-full mb-3"></span>
            <p class="text-sm font-medium">Mempersiapkan kamera...</p>
        </div>

        <div class="absolute inset-0 flex flex-col items-center justify-center bg-slate-900/85 text-white hidden" data-camera-error>
            <span class="text-5xl mb-3">📷</span>
            <p class="font-semibold">Kamera tidak ditemukan</p>
            <p class="text-sm text-slate-300 mt-1 px-8 text-center">Izinkan akses kamera atau pastikan perangkatmu memiliki webcam.</p>
        </div>

        <div class="absolute inset-0 flex items-center justify-center hidden" data-countdown>
            <span class="font-display font-extrabold text-[9rem] sm:text-[12rem] leading-none text-white drop-shadow-[0_4px_20px_rgba(0,0,0,0.6)] animate-bounce" data-countdown-num>3</span>
        </div>

        <div class="absolute inset-0 bg-white hidden" data-flash-overlay></div>

        <div class="absolute inset-x-0 bottom-0 p-4 flex flex-col items-center gap-3">
            <button type="button" data-shoot-btn class="px-10 py-4 rounded-full bg-white text-slate-900 font-display font-bold text-lg shadow-xl hover:scale-105 transition-transform">
                📸 Mulai Foto
            </button>

            <div class="flex gap-3 w-full max-w-sm">
                <a href="{{ route('foto') }}" data-capture-again class="flex-1 text-center rounded-full bg-white/20 text-white backdrop-blur font-semibold px-6 py-3 ring-1 ring-white/40 hover:bg-white/30 transition">
                    Ulang
                </a>
                <a href="{{ route('filter') }}" data-next-btn class="hidden flex-1 text-center rounded-full bg-gradient-to-r from-rose-500 to-violet-600 text-white font-display font-bold px-6 py-3 shadow-lg hover:scale-105 transition-transform">
                    Lanjut →
                </a>
            </div>
        </div>
    </div>

    <p class="text-center text-xs text-slate-500 mt-4">Arahkan ke lensa & jangan bergerak. 3 foto diambil otomatis dengan jeda 3 detik.</p>
</div>
@endsection