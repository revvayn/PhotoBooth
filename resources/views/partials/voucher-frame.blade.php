<div class="flex-1 min-h-0 flex flex-col">
    @php($brand = $brand ?? \App\Support\Brand::get())
    @php($quotas = $voucherQuotas ?? [])
    @php($totalQuota = collect($quotas)->sum('qty'))
    <div class="k-step-head mb-3 shrink-0">
        <span aria-hidden="true"></span>
        <div class="k-step-head-title">
            <h2 class="k-title">
                <span class="k-icon-soft w-9 h-9 rounded-xl">
                    <x-icon name="photo" class="w-4 h-4" />
                </span>
                Pilih Frame Voucher
            </h2>
            <p class="text-slate-500 text-xs sm:text-sm">Pilih frame sesuai ukuran yang dipesan. Sisa kuota mengikuti voucher.</p>
        </div>
        <span class="k-pill justify-self-end !text-xs">
            <x-icon name="ticket" class="w-4 h-4 text-rose-500" />
            {{ $voucher['code'] ?? 'Voucher' }}
        </span>
    </div>

    <form method="POST" action="{{ route('frame') }}" data-voucher-frame-form class="flex flex-col flex-1 min-h-0">
        @csrf
        <input type="hidden" name="from_spa" value="{{ ($fromSpa ?? true) ? '1' : '0' }}">
        <div id="voucher-frame-picks" class="hidden"></div>
        <script type="application/json" data-voucher-quotas>@json(collect($quotas)->mapWithKeys(fn ($q) => [(string) $q['size_id'] => (int) $q['qty']]))</script>

        @if ($errors->any())
            <div class="mb-2 rounded-2xl bg-red-50 border border-red-200 px-4 py-2 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="flex flex-wrap items-center justify-center gap-2 mb-3 shrink-0">
            <div class="k-segment">
                <button type="button" data-voucher-category="semua" data-category-chip aria-pressed="true"
                        class="k-segment-item">
                    Semua
                    <span class="k-segment-count">{{ $allFrames->count() }}</span>
                </button>
                @foreach ($categories as $cat)
                    <button type="button" data-voucher-category="{{ \Illuminate\Support\Str::slug($cat) }}" data-category-chip aria-pressed="false"
                            class="k-segment-item">
                        {{ $cat }}
                        <span class="k-segment-count">{{ $allFrames->filter(fn ($frame) => $frame->size_label === $cat)->count() }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-2 mb-2 shrink-0" data-voucher-quota-bar>
            @foreach ($quotas as $q)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200" data-voucher-quota-chip="{{ $q['size_id'] }}">
                    {{ $q['name'] }}:
                    <span data-voucher-quota-selected>0</span>/{{ (int) $q['qty'] }}
                </span>
            @endforeach
            <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-600 ring-1 ring-rose-100">
                Total cetak: <span data-voucher-print-count>0</span>/{{ (int) $totalQuota }}
            </span>
        </div>

        <div class="flex-1 min-h-0 grid md:grid-cols-[minmax(0,1fr)_300px] md:grid-rows-[minmax(0,1fr)] gap-4 items-stretch">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 auto-rows-min [align-content:safe_center] gap-3 min-w-0 py-0.5 overflow-y-auto min-h-0 pr-0.5" data-voucher-frame-grid>
                @forelse ($allFrames as $frame)
                    @php($slotLabel = $frame->photo_count ? $frame->photo_count.'x' : '')
                    @php($inCart = isset($cart[$frame->slug]) && $cart[$frame->slug] > 0)
                    @php($sizeLabel = $frame->size_label ?: 'Tanpa ukuran')
                    @php($label = trim(preg_replace('/\s+frame$/i', '', $frame->name)) ?: $frame->name)
                    <button type="button" data-voucher-frame-option="{{ $frame->slug }}"
                            data-category="{{ \Illuminate\Support\Str::slug($sizeLabel) }}"
                            data-size-id="{{ $frame->size?->id ?? $frame->size_id }}"
                            data-size-name="{{ $sizeLabel }}"
                            data-name="{{ $frame->name }}"
                            data-photo-count="{{ $frame->photo_count }}"
                            data-qty="{{ $cart[$frame->slug] ?? 0 }}"
                            title="{{ $frame->name }}"
                            aria-label="Pilih {{ $frame->name }}"
                            aria-pressed="{{ $inCart ? 'true' : 'false' }}"
                            class="k-card k-option group relative flex flex-col min-w-0 !rounded-2xl p-2 text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-400">
                        <div class="relative">
                            <div class="k-frame-thumb rounded-xl overflow-hidden bg-slate-50 ring-1 ring-black/5">
                                @if ($frame->image_url)
                                    <img src="{{ $frame->image_url }}" alt="{{ $frame->name }}" loading="lazy" class="w-full h-full object-contain p-1">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-300">
                                        <x-icon name="photo" class="w-8 h-8" />
                                    </div>
                                @endif
                            </div>
                            @if ($slotLabel)
                                <span class="absolute -top-2 -right-2 bg-slate-900 text-white text-[11px] font-bold px-2 py-0.5 rounded-full ring-2 ring-white shadow">{{ $slotLabel }}</span>
                            @endif
                            <span data-add-badge @if (! $inCart) hidden @endif class="absolute -top-2 -left-2 z-10 h-6 min-w-6 px-1.5 rounded-full bg-emerald-500 text-white text-[11px] font-bold flex items-center justify-center gap-0.5 ring-2 ring-white shadow">
                                <x-icon name="check" class="w-3.5 h-3.5 text-white" />
                                <span data-add-qty>{{ $cart[$frame->slug] ?? 0 }}</span>
                            </span>
                        </div>
                        <div class="mt-2 text-center leading-tight min-w-0">
                            <span class="block text-sm font-bold text-slate-800 leading-tight truncate">{{ $label }}</span>
                            <span class="block text-[11px] font-medium text-slate-400 truncate">{{ $sizeLabel }} &middot; {{ $frame->photo_count }} foto</span>
                        </div>
                    </button>
                @empty
                    <div class="col-span-full flex flex-col items-center justify-center gap-2 py-10 text-center">
                        <span class="w-12 h-12 rounded-full bg-white ring-1 ring-rose-100 flex items-center justify-center text-rose-300">
                            <x-icon name="photo" class="w-6 h-6" />
                        </span>
                        <p class="text-sm font-semibold text-slate-600">Belum ada frame</p>
                    </div>
                @endforelse
            </div>

            <aside class="k-card flex flex-col p-4 min-h-0 overflow-hidden">
                <div class="flex items-center justify-between gap-2 mb-3 shrink-0">
                    <h3 class="flex items-center gap-2 font-display font-bold text-base">
                        <span class="k-icon-soft w-7 h-7 rounded-lg">
                            <x-icon name="bag" class="w-4 h-4" />
                        </span>
                        Keranjang
                    </h3>
                    <div class="flex items-center gap-1.5">
                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-rose-50 text-rose-600 ring-1 ring-rose-100" data-cart-count aria-live="polite">0</span>
                        <button type="button" data-cart-clear class="text-[11px] font-semibold text-slate-400 hover:text-rose-600 transition" title="Kosongkan keranjang">Kosongkan</button>
                    </div>
                </div>

                <div data-voucher-cart-list class="flex-1 min-h-0 space-y-2 overflow-y-auto pr-1">
                    <div data-cart-empty class="k-inset flex items-center gap-2.5 px-3 py-2.5">
                        <span class="w-8 h-8 rounded-full bg-white ring-1 ring-rose-100 flex items-center justify-center text-rose-400 shrink-0">
                            <x-icon name="bag" class="w-4 h-4" />
                        </span>
                        <p class="text-xs text-slate-400 leading-snug">Ketuk frame untuk menambah.</p>
                    </div>
                </div>

                <div class="mt-3 pt-3 border-t border-dashed border-slate-200 flex items-center justify-between shrink-0">
                    <span class="text-sm font-semibold text-slate-400">Total cetak</span>
                    <span class="font-display font-extrabold text-xl text-slate-900 tabular-nums" data-voucher-print-total>0</span>
                </div>

                <button type="submit" data-voucher-cart-submit disabled class="k-btn mt-3 w-full !text-base shrink-0">
                    Lanjut ke Sesi Foto
                    <x-icon name="arrow-right" class="w-5 h-5 text-white" />
                </button>
            </aside>
        </div>
    </form>
</div>