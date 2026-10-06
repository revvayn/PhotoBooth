<div class="flex-1 min-h-0 flex flex-col">
    <div class="mb-1">
        <a href="{{ route('tutorial') }}" class="text-xs font-semibold text-slate-400 hover:text-rose-500 transition">← Kembali</a>
    </div>
    <div class="text-center mb-2">
        <h2 class="font-display font-bold text-xl">Pilih Frame</h2>
        <p class="text-slate-500 text-xs mt-0.5">Klik frame untuk menambah ke keranjang, lalu lanjut</p>
    </div>

    <form method="POST" action="{{ route('frame') }}" data-frame-form class="flex flex-col flex-1">
        @csrf
        <input type="hidden" name="from_spa" value="1">
        <div id="frame-picks" class="hidden"></div>

        <div class="flex flex-wrap gap-1.5 justify-center mb-2">
            <button type="button" data-category="semua" data-category-chip
                    class="px-4 py-1.5 rounded-full text-xs font-semibold transition-all ring-1 ring-rose-200 text-rose-600 bg-white/80 bg-rose-500 text-white ring-rose-500 shadow-md shadow-rose-200">
                Semua
            </button>
            @foreach ($categories as $cat)
                <button type="button" data-category="{{ \Illuminate\Support\Str::slug($cat) }}" data-category-chip
                        class="px-4 py-1.5 rounded-full text-xs font-semibold transition-all ring-1 ring-rose-200 text-rose-600 bg-white/80">
                    {{ $cat }}
                </button>
            @endforeach
        </div>

        <div class="grid lg:grid-cols-[1fr_300px] gap-4 items-start">
            <div class="grid grid-cols-3 sm:grid-cols-5 gap-2.5" data-frame-grid>
                @foreach ($allFrames as $frame)
                    @php($slotLabel = $frame->photo_count ? $frame->photo_count.'x' : '')
                    @php($inCart = isset($cart[$frame->slug]) && $cart[$frame->slug] > 0)
                    <button type="button" data-frame-option="{{ $frame->slug }}"
                            data-category="{{ \Illuminate\Support\Str::slug($frame->category) }}"
                            data-price="{{ $frame->price }}"
                            data-name="{{ $frame->name }}"
                            data-qty="{{ $cart[$frame->slug] ?? 0 }}"
                            class="group relative rounded-xl bg-white/90 backdrop-blur p-1.5 ring-1 ring-rose-100 transition-all hover:shadow-md {{ $inCart ? 'ring-2 ring-rose-500 shadow-lg shadow-rose-100' : '' }}">
                        <div class="relative">
                            <div class="aspect-[3/4] rounded-lg overflow-hidden bg-white ring-1 ring-slate-100">
                                @if ($frame->image_url)
                                    <img src="{{ $frame->image_url }}" alt="{{ $frame->name }}" class="w-full h-full object-contain">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-300">Tanpa gambar</div>
                                @endif
                            </div>
                            @if ($slotLabel)
                                <span class="absolute -top-1 -right-1 bg-rose-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow">{{ $slotLabel }}</span>
                            @endif
                            <span data-add-badge class="absolute -top-1 -left-1 w-6 h-6 rounded-full bg-emerald-500 text-white text-xs font-bold flex items-center justify-center shadow {{ $inCart ? '' : 'hidden' }}"><x-icon name="check" class="w-4 h-4 text-white" /></span>
                        </div>
                        <div class="py-1.5 text-center space-y-0.5">
                            <span class="block text-xs font-semibold leading-tight">{{ $frame->name }}</span>
                            <span class="block text-[11px] text-slate-400">Rp. {{ number_format($frame->price, 0, ',', '.') }}</span>
                        </div>
                    </button>
                @endforeach
            </div>

            <aside class="rounded-3xl bg-white/90 backdrop-blur ring-1 ring-rose-100 p-4 lg:sticky lg:top-4 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-display font-bold text-base">Keranjang</h3>
                    <span class="text-xs text-slate-400" data-cart-count>0 item</span>
                </div>

                <div data-cart-list class="space-y-2.5 min-h-[2rem] max-h-52 overflow-y-auto pr-1">
                    <p data-cart-empty class="text-sm text-slate-400">Belum ada frame dipilih, klik frame di samping.</p>
                </div>

                <div class="mt-3 pt-3 border-t border-rose-100 flex items-center justify-between">
                    <span class="text-sm font-medium text-slate-500">Total</span>
                    <span class="font-display font-bold text-lg text-slate-800" data-cart-total>Rp. 0</span>
                </div>

                <button type="submit" data-cart-submit disabled
                        class="mt-3 w-full px-6 py-3 rounded-full bg-gradient-to-r from-rose-500 to-violet-600 text-white font-display font-bold shadow-lg shadow-rose-200 disabled:opacity-40 disabled:cursor-not-allowed hover:scale-[1.02] transition-transform">
                    Lanjut ke Sesi Foto
                </button>
            </aside>
        </div>
    </form>
</div>
