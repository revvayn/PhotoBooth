<div class="flex-1 min-h-0 flex flex-col">
    <div class="text-center mb-2">
        <div class="inline-flex items-center justify-center gap-2">
            <span class="w-8 h-8 rounded-xl bg-gradient-to-br from-rose-400 to-violet-500 text-white flex items-center justify-center shadow-md shadow-rose-200">
                <x-icon name="photo" class="w-4 h-4 text-white" />
            </span>
            <h2 class="font-display font-bold text-2xl">Pilih Frame</h2>
        </div>
        <p class="text-slate-500 text-sm mt-1">Metode QRIS aktif. Pilih frame, lalu konfirmasi pembayaran sebelum sesi foto.</p>
        <a href="{{ ($fromSpa ?? true) ? route('spa', ['step' => 'metode']) : route('metode') }}" class="mt-1 inline-flex items-center gap-1 text-[11px] font-semibold text-slate-400 hover:text-rose-600 transition">
            <x-icon name="arrow-left" class="w-3 h-3" /> Ganti metode pembayaran
        </a>
    </div>

    <form method="POST" action="{{ route('frame') }}" data-frame-form class="flex flex-col flex-1 min-h-0">
        @csrf
        <input type="hidden" name="from_spa" value="{{ ($fromSpa ?? true) ? '1' : '0' }}">
        <div id="frame-picks" class="hidden"></div>

        <div class="flex justify-center mb-2">
            <div class="inline-flex max-w-full flex-wrap justify-center gap-1.5 rounded-2xl sm:rounded-full bg-white/70 backdrop-blur ring-1 ring-rose-100 px-2 py-1.5 shadow-sm">
                <button type="button" data-category="semua" data-category-chip aria-pressed="true"
                        class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-full text-xs sm:text-sm font-semibold transition-all ring-1 bg-rose-500 text-white ring-rose-500 shadow-md shadow-rose-200">
                    Semua
                    <span class="ml-1 text-[10px] font-bold opacity-80">{{ $allFrames->count() }}</span>
                </button>
                @foreach ($categories as $cat)
                    <button type="button" data-category="{{ \Illuminate\Support\Str::slug($cat) }}" data-category-chip aria-pressed="false"
                            class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-full text-xs sm:text-sm font-semibold transition-all ring-1 bg-white/80 text-rose-600 ring-rose-200 hover:bg-white">
                        {{ $cat }}
                        <span class="ml-1 text-[10px] font-bold opacity-60">{{ $allFrames->where('category', $cat)->count() }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        <div class="flex-1 min-h-0 grid md:grid-cols-[1fr_248px] gap-3 items-stretch">
            {{-- Grid responsif: 2 kolom di ponsel, 5 kolom di desktop.
                 content-center membuat baris sisa selalu seimbang di tengah. --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 auto-rows-min content-center gap-2 sm:gap-2.5 min-w-0 py-0.5 md:py-1" data-frame-grid>
                @forelse ($allFrames as $frame)
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
                            aria-label="Tambah {{ $frame->name }} ke keranjang"
                            aria-pressed="{{ $inCart ? 'true' : 'false' }}"
                            class="group relative flex flex-col min-w-0 rounded-2xl bg-white/90 backdrop-blur p-1.5 md:p-2 ring-1 ring-rose-100 transition-all duration-200 hover:ring-rose-300 hover:shadow-lg hover:-translate-y-0.5 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-400 {{ $inCart ? 'ring-2 ring-rose-500 shadow-lg shadow-rose-100' : '' }}">
                        <div class="relative">
                            <div class="aspect-[4/5] rounded-xl overflow-hidden bg-gradient-to-b from-slate-50 to-white ring-1 ring-slate-100">
                                @if ($frame->image_url)
                                    <img src="{{ $frame->image_url }}" alt="{{ $frame->name }}" loading="lazy" class="w-full h-full object-contain p-1">
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
                        <div class="mt-auto pt-1.5 text-center leading-tight min-w-0">
                            <span class="block text-[11px] sm:text-xs font-semibold text-slate-700 leading-tight truncate">{{ $label }}</span>
                            <span class="block text-[10px] sm:text-[11px] font-semibold text-rose-600 tabular-nums truncate">Rp. {{ number_format($frame->price, 0, ',', '.') }}</span>
                        </div>
                    </button>
                @empty
                    <div class="col-span-full flex flex-col items-center justify-center gap-2 py-10 text-center">
                        <span class="w-12 h-12 rounded-full bg-white ring-1 ring-rose-100 flex items-center justify-center text-rose-300">
                            <x-icon name="photo" class="w-6 h-6" />
                        </span>
                        <p class="text-sm font-semibold text-slate-600">Belum ada frame aktif</p>
                        <p class="text-xs text-slate-400">Minta admin menambahkan frame terlebih dulu.</p>
                    </div>
                @endforelse
            </div>

            <aside class="flex flex-col rounded-3xl bg-white/90 backdrop-blur ring-1 ring-rose-100 p-4 shadow-sm">
                <div class="flex items-center justify-between gap-2 mb-3">
                    <h3 class="flex items-center gap-2 font-display font-bold text-base">
                        <span class="w-7 h-7 rounded-lg bg-rose-50 ring-1 ring-rose-100 flex items-center justify-center text-rose-500">
                            <x-icon name="bag" class="w-4 h-4" />
                        </span>
                        Keranjang
                    </h3>
                    <div class="flex items-center gap-1.5">
                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-rose-50 text-rose-600 ring-1 ring-rose-100" data-cart-count aria-live="polite">0 item</span>
                        <button type="button" data-cart-clear class="text-[11px] font-semibold text-slate-400 hover:text-rose-600 transition hidden" title="Kosongkan keranjang">Kosongkan</button>
                    </div>
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
                    <span class="font-display font-bold text-lg text-slate-800 tabular-nums" data-cart-total aria-live="polite">Rp. 0</span>
                </div>

                <button type="submit" data-cart-submit disabled
                        class="mt-3 w-full inline-flex items-center justify-center gap-1.5 px-4 py-3.5 rounded-full bg-gradient-to-r from-rose-500 to-violet-600 text-white font-display font-bold text-base whitespace-nowrap shadow-lg shadow-rose-200 disabled:opacity-40 disabled:cursor-not-allowed hover:scale-[1.02] active:scale-95 transition-transform focus:outline-none focus-visible:ring-4 focus-visible:ring-rose-300">
                    Lanjut ke Pembayaran
                    <x-icon name="arrow-right" class="w-4 h-4 text-white" />
                </button>
                <p class="mt-1.5 text-[10px] text-center text-slate-400">Harga dihitung per frame &times; jumlah cetak</p>
            </aside>
        </div>
    </form>
</div>
