@extends('layouts.app')

@section('title', 'Pilih Frame — Photobooth')

@section('content')
<div class="flex-1 flex flex-col">
    <div class="text-center mb-6">
        <h2 class="font-display font-bold text-3xl">Pilih Frame</h2>
        <p class="text-slate-500 text-sm mt-1">Klik frame untuk menambah ke keranjang, atur jumlah lalu lanjut sesi foto</p>
    </div>

    <form method="POST" action="{{ route('frame') }}" data-frame-form class="flex flex-col flex-1">
        @csrf
        <div id="frame-picks" class="hidden"></div>

        <div class="flex flex-wrap gap-2 justify-center mb-6">
            <button type="button" data-category="semua" data-category-chip
                    class="px-5 py-2 rounded-full text-sm font-semibold transition-all ring-1 ring-rose-200 text-rose-600 bg-white/80 bg-rose-500 text-white ring-rose-500 shadow-md shadow-rose-200">
                Semua
            </button>
            @foreach ($categories as $cat)
                <button type="button" data-category="{{ \Illuminate\Support\Str::slug($cat) }}" data-category-chip
                        class="px-5 py-2 rounded-full text-sm font-semibold transition-all ring-1 ring-rose-200 text-rose-600 bg-white/80">
                    {{ $cat }}
                </button>
            @endforeach
        </div>

        <div class="grid lg:grid-cols-[1fr_330px] gap-6 items-start">
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4" data-frame-grid>
                @foreach ($allFrames as $frame)
                    @php($slotLabel = $frame->photo_count ? $frame->photo_count.'x' : '')
                    @php($inCart = isset($cart[$frame->slug]) && $cart[$frame->slug] > 0)
                    <button type="button" data-frame-option="{{ $frame->slug }}"
                            data-category="{{ \Illuminate\Support\Str::slug($frame->category) }}"
                            data-price="{{ $frame->price }}"
                            data-name="{{ $frame->name }}"
                            data-qty="{{ $cart[$frame->slug] ?? 0 }}"
                            class="group relative rounded-2xl bg-white/90 backdrop-blur p-2 ring-1 ring-rose-100 transition-all hover:shadow-md {{ $inCart ? 'ring-2 ring-rose-500 shadow-lg shadow-rose-100' : '' }}">
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
                            <span data-add-badge class="absolute -top-1 -left-1 w-6 h-6 rounded-full bg-emerald-500 text-white text-xs font-bold flex items-center justify-center shadow {{ $inCart ? '' : 'hidden' }}">✓</span>
                        </div>
                        <div class="py-2 text-center space-y-0.5">
                            <span class="block text-sm font-semibold">{{ $frame->name }}</span>
                            <span class="block text-xs text-slate-400">Rp. {{ number_format($frame->price, 0, ',', '.') }}</span>
                        </div>
                    </button>
                @endforeach
            </div>

            <aside class="rounded-3xl bg-white/90 backdrop-blur ring-1 ring-rose-100 p-5 lg:sticky lg:top-6 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-display font-bold text-lg">Keranjang</h3>
                    <span class="text-xs text-slate-400" data-cart-count>0 item</span>
                </div>

                <div data-cart-list class="space-y-3 min-h-[2.5rem] max-h-72 overflow-y-auto pr-1">
                    <p data-cart-empty class="text-sm text-slate-400">Belum ada frame dipilih, klik frame di samping.</p>
                </div>

                <div class="mt-4 pt-4 border-t border-rose-100 flex items-center justify-between">
                    <span class="text-sm font-medium text-slate-500">Total</span>
                    <span class="font-display font-bold text-xl text-slate-800" data-cart-total>Rp. 0</span>
                </div>

                <button type="submit" data-cart-submit disabled
                        class="mt-4 w-full px-6 py-3.5 rounded-full bg-gradient-to-r from-rose-500 to-violet-600 text-white font-display font-bold shadow-lg shadow-rose-200 disabled:opacity-40 disabled:cursor-not-allowed hover:scale-[1.02] transition-transform">
                    Lanjut ke Sesi Foto →
                </button>
            </aside>
        </div>
    </form>
</div>
@endsection