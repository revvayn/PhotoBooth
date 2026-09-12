@extends('layouts.app')

@section('title', 'Pilih Frame — Photobooth')

@section('content')
<div class="flex-1 flex flex-col">
    <div class="text-center mb-6">
        <h2 class="font-display font-bold text-3xl">Pilih Frame</h2>
        <p class="text-slate-500 text-sm mt-1">Klik kategori di bawah</p>
    </div>

    <div class="flex flex-wrap gap-2 justify-center mb-6">
        <button type="button" data-category="semua"
                class="px-5 py-2 rounded-full text-sm font-semibold transition-all ring-1 ring-rose-200 text-rose-600 bg-white/80 bg-rose-500 text-white ring-rose-500 shadow-md shadow-rose-200">
            Semua
        </button>
        @foreach ($categories as $cat)
            <button type="button" data-category="{{ \Illuminate\Support\Str::slug($cat) }}"
                    class="px-5 py-2 rounded-full text-sm font-semibold transition-all ring-1 ring-rose-200 text-rose-600 bg-white/80">
                {{ $cat }}
            </button>
        @endforeach
    </div>

    <form method="POST" action="{{ route('frame') }}" data-frame-form class="flex flex-col flex-1">
        @csrf
        <input type="hidden" name="frame" id="frame-input" value="{{ $allFrames->keys()->first() }}">

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4" data-frame-grid>
            @foreach ($allFrames as $frame)
                @php($slotLabel = $frame->photo_count ? $frame->photo_count.'x' : '')
                <button type="button" data-frame-option="{{ $frame->slug }}" data-category="{{ \Illuminate\Support\Str::slug($frame->category) }}"
                        class="group rounded-2xl bg-white/90 backdrop-blur p-2 ring-1 ring-rose-100 transition-all hover:shadow-md {{ $loop->first ? 'ring-2 ring-rose-500 shadow-lg shadow-rose-100' : '' }}">
                    <div class="relative">
                        <div class="aspect-[3/4] rounded-xl overflow-hidden bg-white ring-1 ring-slate-100">
                            @if ($frame->image_url)
                                <img src="{{ $frame->image_url }}" alt="{{ $frame->name }}" class="w-full h-full object-contain">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-slate-300">Tanpa gambar</div>
                            @endif
                        </div>
                        @if ($slotLabel)
                            <span class="absolute -top-1 -right-1 bg-rose-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow">{{ $slotLabel }}</span>
                        @endif
                    </div>
                    <div class="py-2 text-center">
                        <span class="text-sm font-semibold">{{ $frame->name }}</span>
                    </div>
                </button>
            @endforeach
        </div>

        <div class="mt-auto pt-8 flex items-center justify-between gap-4">
            <div>
                <p class="text-xs text-slate-400">Harga mulai</p>
                <p class="text-2xl font-display font-bold text-slate-800">Rp. 30.000</p>
            </div>
            <button type="submit" class="px-12 py-4 rounded-full bg-gradient-to-r from-rose-500 to-violet-600 text-white font-display font-bold text-lg shadow-lg shadow-rose-200 hover:scale-105 transition-transform">
                Lanjut →
            </button>
        </div>
    </form>
</div>
@endsection