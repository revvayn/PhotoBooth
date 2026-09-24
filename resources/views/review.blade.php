@extends('layouts.app')

@section('title', 'Review Foto — Photobooth')

@section('content')
<div class="flex-1 flex flex-col items-center">
    <div class="text-center mb-6">
        <h2 class="font-display font-bold text-3xl">Review Foto</h2>
        <p class="text-slate-500 text-sm mt-1">Berikut hasil frame yang sudah dibuat</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-[1fr_340px] gap-6 w-full max-w-3xl items-start">
        <div class="space-y-6 w-full">
            @forelse ($items as $item)
                <div class="mx-auto max-w-sm overflow-hidden rounded-[2rem] shadow-xl shadow-rose-100 ring-1 ring-rose-100 bg-white" data-strip-container>
                    <canvas class="w-full h-auto block" data-strip-canvas
                            data-photos='@json($item['photos'] ?? [])'
                            data-filters='@json($item['filter_map'] ?? [])'
                            data-filter="{{ $filter }}"
                            data-frame="{{ $item['slug'] }}"
                            data-frame-image="{{ $item['image_url'] }}"
                            data-frame-slots='@json($item['slots'] ?? [])'></canvas>
                    <div class="px-5 py-3 flex items-center justify-between text-xs text-slate-500 border-t border-rose-100">
                        <span class="font-semibold text-slate-700">{{ $item['name'] }}@if ($item['qty'] > 1) ×{{ $item['qty'] }}@endif</span>
                        <span class="font-bold text-slate-700">Rp. {{ number_format($item['price'] * $item['qty'], 0, ',', '.') }}</span>
                    </div>
                </div>
            @empty
                <p class="text-center text-slate-400">Belum ada frame yang dipilih.</p>
            @endforelse
        </div>

        <div class="flex flex-col justify-center gap-4 lg:sticky lg:top-6">
            <div class="rounded-3xl bg-white/90 backdrop-blur ring-1 ring-rose-100 p-5">
                <p class="text-xs text-slate-400 mb-1">Ringkasan Pesanan</p>
                <div class="text-sm text-slate-600 space-y-1">
                    <p>Antrian: <span class="font-bold text-rose-500">{{ $queue }}</span></p>
                    @foreach ($items as $item)
                        <p class="flex justify-between">
                            <span>{{ $item['name'] }}@if ($item['qty'] > 1) ×{{ $item['qty'] }}@endif</span>
                            <span>Rp. {{ number_format($item['price'] * $item['qty'], 0, ',', '.') }}</span>
                        </p>
                    @endforeach
                    <p class="flex justify-between pt-1 mt-1 border-t border-rose-100">
                        <span class="font-bold text-slate-800">Total</span>
                        <span class="font-bold text-rose-600">Rp. {{ number_format($total, 0, ',', '.') }}</span>
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('email.send') }}" data-email-form class="rounded-3xl bg-white/90 backdrop-blur ring-1 ring-rose-100 p-5 space-y-3">
                @csrf
                <label class="block text-sm font-semibold text-slate-700">Masukkan Email</label>
                <input type="email" name="email" id="email-input" placeholder="email@contoh.com" required
                       class="w-full rounded-2xl bg-slate-50 ring-1 ring-slate-200 px-4 py-3 text-sm focus:ring-2 focus:ring-rose-400 focus:outline-none">
                <button type="submit" class="w-full rounded-full bg-gradient-to-r from-rose-500 to-violet-600 text-white font-display font-bold py-3.5 shadow-md hover:scale-[1.02] transition-transform">
                    <x-icon name="envelope" class="w-5 h-5 inline-block align-middle" /> Kirim Softfile
                </button>
            </form>

            <a href="{{ route('selesai') }}" class="rounded-full bg-white ring-1 ring-rose-200 text-rose-600 font-semibold py-3.5 text-center hover:bg-rose-50 transition">
                Lewati, langsung selesai →
            </a>
        </div>
    </div>

    <div class="fixed inset-0 z-[80] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm" data-printing-modal>
        <div class="bg-white rounded-3xl p-10 text-center max-w-sm mx-4 shadow-2xl">
            <div class="relative w-16 h-16 mx-auto mb-4">
                <div class="animate-spin w-16 h-16 rounded-full border-4 border-rose-100 border-t-rose-500"></div>
                <span class="absolute inset-0 flex items-center justify-center text-slate-600"><x-icon name="printer" class="w-8 h-8" /></span>
            </div>
            <h2 class="font-display font-bold text-xl mb-1">Fotomu sedang di cetak</h2>
            <p class="text-sm text-slate-500">Mohon tunggu</p>
        </div>
    </div>
</div>
@endsection
