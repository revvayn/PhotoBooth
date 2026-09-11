@extends('layouts.app')

@section('title', 'Pilih Frame — Photobooth')

@section('content')
<div class="flex-1 flex flex-col">
    <div class="text-center mb-6">
        <h2 class="font-display font-bold text-3xl">Pilih Frame</h2>
        <p class="text-slate-500 text-sm mt-1">Klik kategori di bawah</p>
    </div>

    <div class="flex flex-wrap gap-2 justify-center mb-6">
        @foreach ($categories as $cat)
            <button type="button" data-category="{{ \Illuminate\Support\Str::slug($cat) }}"
                    class="px-5 py-2 rounded-full text-sm font-semibold transition-all ring-1 ring-rose-200 text-rose-600 bg-white/80 {{ $loop->first ? 'bg-rose-500 text-white ring-rose-500 shadow-md shadow-rose-200' : '' }}">
                {{ $cat }}
            </button>
        @endforeach
    </div>

    <form method="POST" action="{{ route('frame') }}" data-frame-form class="flex flex-col flex-1">
        @csrf
        <input type="hidden" name="frame" id="frame-input" value="{{ $allFrames->keys()->first() }}">

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4" data-frame-grid>
            @foreach ($allFrames as $frame)
                <button type="button" data-frame-option="{{ $frame->slug }}" data-cat="{{ \Illuminate\Support\Str::slug($frame->category) }}"
                        class="group rounded-2xl bg-white/90 backdrop-blur p-2 ring-1 ring-rose-100 transition-all hover:shadow-md {{ $loop->first ? 'ring-2 ring-rose-500 shadow-lg shadow-rose-100' : 'hidden' }}">
                    <div class="aspect-[3/4] rounded-xl overflow-hidden bg-gradient-to-br {{ $frame->color_class }} relative">
                        <div class="absolute inset-2 rounded-lg border-2 {{ $frame->border_class }}"></div>
                        <div class="absolute inset-0 flex items-center justify-center gap-1.5 px-4">
                            @for ($i = 0; $i < 3; $i++)
                                <div class="w-1/3 aspect-[3/4] rounded-md bg-white/80 ring-1 ring-rose-200/60"></div>
                            @endfor
                        </div>
                        @if ($frame->caption)
                            <span class="absolute bottom-2 inset-x-0 text-center text-[10px] font-bold tracking-widest uppercase {{ $frame->accent_class }}">{{ $frame->caption }}</span>
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