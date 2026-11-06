<div class="flex-1 min-h-0 flex flex-col">
    <div class="text-center mb-2">
        <div class="inline-flex items-center justify-center gap-2">
            <span class="w-8 h-8 rounded-xl bg-gradient-to-br from-rose-400 to-violet-500 text-white flex items-center justify-center shadow-md shadow-rose-200">
                <x-icon name="photo" class="w-4 h-4 text-white" />
            </span>
            <h2 class="font-display font-bold text-2xl">Pilih Frame</h2>
        </div>
        <p class="text-slate-500 text-sm mt-1">Ketuk frame untuk menambah ke keranjang, lalu lanjut</p>
    </div>

    <form method="POST" action="{{ route('frame') }}" data-frame-form class="flex flex-col flex-1 min-h-0">
        @csrf
        <input type="hidden" name="from_spa" value="1">
        <div id="frame-picks" class="hidden"></div>

        <div class="flex justify-center mb-2">
            <div class="inline-flex flex-wrap justify-center gap-1.5 rounded-full bg-white/70 backdrop-blur ring-1 ring-rose-100 px-2 py-1.5 shadow-sm">
                <button type="button" data-category="semua" data-category-chip
                        class="px-4 py-2 rounded-full text-sm font-semibold transition-all ring-1 bg-rose-500 text-white ring-rose-500 shadow-md shadow-rose-200">
                    Semua
                </button>
                @foreach ($categories as $cat)
                    <button type="button" data-category="{{ \Illuminate\Support\Str::slug($cat) }}" data-category-chip
                            class="px-4 py-2 rounded-full text-sm font-semibold transition-all ring-1 bg-white/80 text-rose-600 ring-rose-200 hover:bg-white">
                        {{ $cat }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="flex-1 min-h-0 grid md:grid-cols-[1fr_248px] gap-3 items-stretch">
             {{-- Grid responsif: 3 kolom di layar sempit, 5 kolom di desktop.
                  justify-center membuat baris sisa tetap di tengah (simetris),
                  content-center mengisi ruang vertikal yang tersedia. --}}
            <div class="flex flex-wrap justify-center content-center gap-1.5 md:gap-2.5 min-w-0 py-0.5 md:py-1" data-frame-grid>
                @foreach ($allFrames as $frame)
                    @php($slotLabel = $frame->photo_count ? $frame->photo_count.'x' : '')
                    @php($inCart = isset($cart[$frame->slug]) && $cart[$frame->slug] > 0)
                    {{-- Label tampil: buang akhiran "Frame" yang redundan agar nama
                         muat utuh di kartu sempit (tanpa terpotong). Nama lengkap
                         tetap dibawa di data-name untuk keranjang. --}}
                    @php($label = trim(preg_replace('/\s+frame$/i', '', $frame->name)) ?: $frame->name)
                    <button type="button" data-frame-option="{{ $frame->slug }}"
                            data-category="{{ \Illuminate\Support\Str::slug($frame->category) }}"
                            data-price="{{ $frame->price }}"
                            data-name="{{ $frame->name }}"
                            data-qty="{{ $cart[$frame->slug] ?? 0 }}"
                            title="{{ $frame->name }}"
                            class="group relative flex flex-col min-w-0 w-[calc((100%_-_24px)/5)] md:w-[calc((100%_-_40px)/5)] rounded-2xl bg-white/90 backdrop-blur p-1.5 md:p-2 ring-1 ring-rose-100 transition-all duration-200 hover:ring-rose-300 hover:shadow-lg hover:-translate-y-0.5 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-400 {{ $inCart ? 'ring-2 ring-rose-500 shadow-lg shadow-rose-100' : '' }}">
                        <div class="relative">
                            <div class="aspect-square md:aspect-[4/5] rounded-xl overflow-hidden bg-gradient-to-b from-slate-50 to-white ring-1 ring-slate-100">
                                @if ($frame->image_url)
                                    <img src="{{ $frame->image_url }}" alt="{{ $frame->name }}" class="w-full h-full object-contain p-1">
                                @else
                                    <div class="w-full h-full flex flex-col items-center justify-center gap-1 text-slate-300">
                                        <x-icon name="photo" class="w-6 h-6" />
                                        <span class="text-[10px] font-medium">Tanpa gambar</span>
                                    </div>
                                @endif
                            </div>
                            @if ($slotLabel)
                                <span class="absolute -top-2 -right-2 bg-rose-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full ring-2 ring-white shadow">{{ $slotLabel }}</span>
                            @endif
                            <span data-add-badge class="absolute -top-2 -left-2 z-10 h-6 min-w-6 px-1.5 rounded-full bg-emerald-500 text-white text-[11px] font-bold flex items-center justify-center gap-0.5 ring-2 ring-white shadow {{ $inCart ? '' : 'hidden' }}">
                                <x-icon name="check" class="w-3.5 h-3.5 text-white" />
                                <span data-add-qty>{{ $cart[$frame->slug] ?? 0 }}</span>
                            </span>
                        </div>
                        <div class="mt-auto pt-1 md:pt-1.5 text-center leading-tight min-w-0">
                            <span class="block text-[11px] md:text-xs font-semibold text-slate-700 min-h-0 md:min-h-[2rem] leading-tight truncate md:whitespace-normal md:overflow-visible">{{ $label }}</span>
                            <span class="block text-[10px] md:text-[11px] font-semibold text-rose-600 tabular-nums truncate">Rp. {{ number_format($frame->price, 0, ',', '.') }}</span>
                        </div>
                    </button>
                @endforeach
            </div>

            <aside class="flex flex-col rounded-3xl bg-white/90 backdrop-blur ring-1 ring-rose-100 p-4 shadow-sm">
                <div class="flex items-center justify-between gap-2 mb-3">
                    <h3 class="flex items-center gap-2 font-display font-bold text-base">
                        <span class="w-7 h-7 rounded-lg bg-rose-50 ring-1 ring-rose-100 flex items-center justify-center text-rose-500">
                            <x-icon name="bag" class="w-4 h-4" />
                        </span>
                        Keranjang
                    </h3>
                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-rose-50 text-rose-600 ring-1 ring-rose-100" data-cart-count>0 item</span>
                </div>

                <div data-cart-list class="flex-1 min-h-[2rem] md:min-h-[4rem] space-y-2.5 overflow-y-auto pr-1">
                    <div data-cart-empty class="flex items-center gap-2.5 rounded-xl bg-rose-50/60 ring-1 ring-rose-100 px-3 py-2.5">
                        <span class="w-8 h-8 rounded-full bg-white ring-1 ring-rose-100 flex items-center justify-center text-rose-400 shrink-0">
                            <x-icon name="bag" class="w-4 h-4" />
                        </span>
                        <p class="text-sm text-slate-400 leading-snug">Belum ada frame. Ketuk kartu di kiri untuk menambah.</p>
                    </div>
                </div>

                <div class="mt-3 pt-3 border-t border-dashed border-rose-100 flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total</span>
                    <span class="font-display font-bold text-lg text-slate-800 tabular-nums" data-cart-total>Rp. 0</span>
                </div>

                <button type="submit" data-cart-submit disabled
                        class="mt-3 w-full inline-flex items-center justify-center gap-1.5 px-4 py-3.5 rounded-full bg-gradient-to-r from-rose-500 to-violet-600 text-white font-display font-bold text-base whitespace-nowrap shadow-lg shadow-rose-200 disabled:opacity-40 disabled:cursor-not-allowed hover:scale-[1.02] active:scale-95 transition-transform">
                    Lanjut ke Sesi Foto
                    <x-icon name="arrow-right" class="w-4 h-4 text-white" />
                </button>
            </aside>
        </div>
    </form>
</div>
