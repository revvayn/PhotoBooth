@extends('layouts.app')

@section('title', 'Pilih Filter — Photobooth')

@section('content')
<div class="flex-1 flex flex-col">
    <div class="text-center mb-6">
        <h2 class="font-display font-bold text-3xl">Pilih Filter</h2>
        <p class="text-slate-500 text-sm mt-1">Sesuaikan dengan selera anda</p>
    </div>

    <form method="POST" action="{{ route('filter') }}" data-filter-form class="flex flex-col flex-1">
        @csrf
        <input type="hidden" name="filter" id="filter-input" value="asli">

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @foreach ($filters as $key => $label)
                @php($preview = $photos[0] ?? '')
                <button type="button" data-filter-option="{{ $key }}"
                        class="group rounded-2xl bg-white/90 backdrop-blur p-2 ring-1 ring-rose-100 transition-all hover:shadow-md {{ $key === 'asli' ? 'ring-2 ring-rose-500 shadow-lg shadow-rose-100' : '' }}">
                    <div class="aspect-[3/4] rounded-xl overflow-hidden bg-slate-100 relative">
                        @if ($preview)
                            <img src="{{ $preview }}" alt="{{ $label }}"
                                 class="w-full h-full object-cover transition-transform group-hover:scale-105"
                                 style="filter: {{ match ($key) {
                                    'asli' => 'none',
                                    'bw' => 'grayscale(1)',
                                    'vintage' => 'sepia(0.55) contrast(1.05) brightness(0.95)',
                                    'warm' => 'sepia(0.35) saturate(1.4) brightness(1.05)',
                                    'cool' => 'hue-rotate(180deg) saturate(0.75)',
                                    'fade' => 'contrast(0.9) brightness(1.12) saturate(0.7)',
                                    'contrast' => 'contrast(1.5) saturate(1.2)',
                                    'neon' => 'saturate(1.8) contrast(1.2) hue-rotate(-8deg)',
                                } }}">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-slate-300"><x-icon name="photo" class="w-10 h-10" /></div>
                        @endif
                    </div>
                    <div class="py-2 text-center">
                        <span class="text-sm font-semibold" data-filter-label>{{ $label }}</span>
                    </div>
                </button>
            @endforeach
        </div>

        <div class="mt-auto pt-8 flex justify-end">
            <button type="submit" class="px-12 py-4 rounded-full bg-gradient-to-r from-rose-500 to-violet-600 text-white font-display font-bold text-lg shadow-lg shadow-rose-200 hover:scale-105 transition-transform">
                Lanjut →
            </button>
        </div>
    </form>
</div>
@endsection
