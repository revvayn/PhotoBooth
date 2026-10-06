<div class="flex-1 min-h-0 flex flex-col">
    <div class="text-center mb-2">
        <div class="inline-flex items-center justify-center gap-2">
            <span class="w-8 h-8 rounded-xl bg-gradient-to-br from-rose-400 to-violet-500 text-white flex items-center justify-center shadow-md shadow-rose-200">
                <x-icon name="photo" class="w-4 h-4 text-white" />
            </span>
            <h2 class="font-display font-bold text-xl">Pilih Frame</h2>
        </div>
        <p class="text-slate-500 text-xs mt-1">Klik frame untuk menambah ke keranjang, lalu lanjut</p>
    </div>

    <form method="POST" action="{{ route('frame') }}" data-frame-form class="flex flex-col flex-1">
        @csrf
        <input type="hidden" name="from_spa" value="1">
        <div id="frame-picks" class="hidden"></div>

        <div class="flex justify-center mb-2">
            <div class="inline-flex flex-wrap justify-center gap-1.5 rounded-full bg-white/70 backdrop-blur ring-1 ring-rose-100 px-2 py-1.5 shadow-sm">
                <button type="button" data-category="semua" data-category-chip
                        class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition-all ring-1 bg-rose-500 text-white ring-rose-500 shadow-md shadow-rose-200">
                    Semua
                </button>
                @foreach ($categories as $cat)
                    <button type="button" data-category="{{ \Illuminate\Support\Str::slug($cat) }}" data-category-chip
                            class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition-all ring-1 bg-white/80 text-rose-600 ring-rose-200 hover:bg-white">
                        {{ $cat }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="grid md:grid-cols-[1fr_230px] gap-3 items-start">
            {{-- Flex-wrap: jumlah kolom menyesuaikan layar, baris sisa tetap di tengah
                 (simetris), dan lebar kartu tak pernah membengkak saat difilter. --}}
            <div class="flex flex-wrap justify-center gap-2 min-w-0" data-frame-grid>
                @foreach ($allFrames as $frame)
                    @php($slotLabel = $frame->photo_count ? $frame->photo_count.'x' : '')
                    @php($inCart = isset($cart[$frame->slug]) && $cart[$frame->slug] > 0)
                    <button type="button" data-frame-option="{{ $frame->slug }}"
                            data-category="{{ \Illuminate\Support\Str::slug($frame->category) }}"
                            data-price="{{ $frame->price }}"
                            data-name="{{ $frame->name }}"
                            data-qty="{{ $cart[$frame->slug] ?? 0 }}"
                            class="group relative flex flex-col min-w-0 w-[calc((100%_-_32px)/5)] rounded-2xl bg-white/90 backdrop-blur p-2 ring-1 ring-rose-100 transition-all duration-200 hover:ring-rose-300 hover:shadow-lg hover:-translate-y-0.5 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-400 {{ $inCart ? 'ring-2 ring-rose-500 shadow-lg shadow-rose-100' : '' }}">
                        <div class="relative">
                            <div class="aspect-square rounded-xl overflow-hidden bg-gradient-to-b from-slate-50 to-white ring-1 ring-slate-100">
                                @if ($frame->image_url)
                                    <img src="{{ $frame->image_url }}" alt="{{ $frame->name }}" class="w-full h-full object-contain">
                                @else
                                    <div class="w-full h-full flex flex-col items-center justify-center gap-1 text-slate-300">
                                        <x-icon name="photo" class="w-6 h-6" />
                                        <span class="text-[10px] font-medium">Tanpa gambar</span>
                                    </div>
                                @endif
                            </div>
                            @if ($slotLabel)
                                <span class="absolute -top-1.5 -right-1.5 bg-rose-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full ring-2 ring-white shadow">{{ $slotLabel }}</span>
                            @endif
                            <span data-add-badge class="absolute -top-1.5 -left-1.5 w-6 h-6 rounded-full bg-emerald-500 text-white text-xs font-bold flex items-center justify-center ring-2 ring-white shadow {{ $inCart ? '' : 'hidden' }}"><x-icon name="check" class="w-4 h-4 text-white" /></span>
                        </div>
                        <div class="mt-auto pt-1.5 text-center leading-tight min-w-0">
                            <span class="block text-xs font-semibold truncate">{{ $frame->name }}</span>
                            <span class="block text-[11px] font-semibold text-rose-600 tabular-nums truncate">Rp. {{ number_format($frame->price, 0, ',', '.') }}</span>
                        </div>
                    </button>
                @endforeach
            </div>

            <aside class="rounded-3xl bg-white/90 backdrop-blur ring-1 ring-rose-100 p-4 shadow-sm">
                <div class="flex items-center justify-between gap-2 mb-3">
                    <h3 class="flex items-center gap-2 font-display font-bold text-base">
                        <span class="w-7 h-7 rounded-lg bg-rose-50 ring-1 ring-rose-100 flex items-center justify-center text-rose-500">
                            <x-icon name="bag" class="w-4 h-4" />
                        </span>
                        Keranjang
                    </h3>
                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-rose-50 text-rose-600 ring-1 ring-rose-100" data-cart-count>0 item</span>
                </div>

                <div data-cart-list class="space-y-2.5 min-h-[2rem] max-h-44 overflow-y-auto pr-1">
                    <div data-cart-empty class="flex items-center gap-2.5 rounded-xl bg-rose-50/60 ring-1 ring-rose-100 px-3 py-2.5">
                        <span class="w-8 h-8 rounded-full bg-white ring-1 ring-rose-100 flex items-center justify-center text-rose-400 shrink-0">
                            <x-icon name="bag" class="w-4 h-4" />
                        </span>
                        <p class="text-xs text-slate-400 leading-snug">Belum ada frame. Ketuk kartu di kiri untuk menambah.</p>
                    </div>
                </div>

                <div class="mt-3 pt-3 border-t border-dashed border-rose-100 flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total</span>
                    <span class="font-display font-bold text-lg text-slate-800 tabular-nums" data-cart-total>Rp. 0</span>
                </div>

                <button type="submit" data-cart-submit disabled
                        class="mt-3 w-full inline-flex items-center justify-center gap-1.5 px-4 py-3 rounded-full bg-gradient-to-r from-rose-500 to-violet-600 text-white font-display font-bold text-sm whitespace-nowrap shadow-lg shadow-rose-200 disabled:opacity-40 disabled:cursor-not-allowed hover:scale-[1.02] active:scale-95 transition-transform">
                    Lanjut ke Sesi Foto
                    <x-icon name="arrow-right" class="w-4 h-4 text-white" />
                </button>
            </aside>
        </div>
    </form>
</div>
