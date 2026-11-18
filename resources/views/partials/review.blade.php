<div class="flex-1 min-h-0 flex flex-col items-center">
    @php($brand = $brand ?? \App\Support\Brand::get())
    <div class="text-center mb-2 shrink-0">
        <div class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-bold text-emerald-700 ring-1 ring-emerald-100 mb-1.5">
            <x-icon name="check" class="w-3.5 h-3.5" />
            {{ ($metode ?? '') === 'voucher' ? 'Voucher terverifikasi' : 'Pembayaran diterima' }}
        </div>
        <h2 class="k-title">{{ $brand['review_title'] }}</h2>
        <p class="text-slate-500 text-xs sm:text-sm mt-1">{{ $brand['review_subtitle'] }}</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-[minmax(0,1fr)_340px] gap-3 sm:gap-4 w-full max-w-6xl items-start">
        <div class="k-card w-full p-3.5 sm:p-4">
            <div class="k-inset flex min-h-[200px] flex-wrap justify-center gap-4 p-3">
                @forelse ($items as $item)
                    <div class="w-[168px] sm:w-[204px] overflow-hidden rounded-2xl bg-white shadow-xl shadow-slate-900/10 ring-1 ring-black/5" data-strip-container data-qty="{{ $item['qty'] }}"
                         data-print-width="{{ $item['size_width_cm'] ?? '' }}" data-print-height="{{ $item['size_height_cm'] ?? '' }}">
                        <canvas class="w-full h-auto block" data-strip-canvas
                                data-photos='@json($item['photos'] ?? [])'
                                data-filters='@json($item['filter_map'] ?? [])'
                                data-filter="{{ $filter }}"
                                data-frame="{{ $item['slug'] }}"
                                data-frame-image="{{ $item['image_url'] }}"
                                data-frame-slots='@json($item['slots'] ?? [])'
                                data-print-width="{{ $item['size_width_cm'] ?? '' }}"
                                data-stickers='@json($item['sticker_map'] ?? [])'></canvas>
                        <div class="px-3 py-2 flex items-center justify-between gap-2 text-xs border-t border-black/5">
                            <div class="min-w-0">
                                <span class="block font-semibold text-slate-600 truncate">{{ $item['name'] }}</span>
                                @if (!empty($item['size_name']))
                                    <span class="block text-[10px] text-slate-400 truncate">
                                        {{ $item['size_name'] }}@if (!empty($item['size_label'])) &middot; {{ $item['size_label'] }}@endif
                                    </span>
                                @endif
                            </div>
                            @if ($item['qty'] > 1)
                                <span class="font-bold text-rose-600 shrink-0">{{ $item['qty'] }} lembar</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-center text-slate-400 self-center">Belum ada frame yang dipilih.</p>
                @endforelse
            </div>
        </div>

        <div class="flex flex-col gap-2 w-full md:sticky md:top-2">
            <div class="k-card p-3.5">
                <div class="flex items-center justify-between gap-2 mb-2">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Ringkasan</p>
                    <span class="k-pill !py-1 !px-2.5 !text-[11px]">
                        <x-icon name="receipt" class="w-3.5 h-3.5 text-rose-500" />
                        {{ $queue }}
                    </span>
                </div>

                <div class="text-sm text-slate-600 space-y-1.5">
                    <p class="flex justify-between gap-2">
                        <span>Metode</span>
                        <span class="font-semibold text-slate-800">{{ ($metode ?? '') === 'voucher' ? 'Voucher' : 'QRIS' }}</span>
                    </p>
                    @if (($metode ?? '') === 'voucher')
                        <p class="flex justify-between gap-2">
                            <span>Kode voucher</span>
                            <span class="font-mono font-semibold text-slate-800">{{ $voucher['code'] ?? '-' }}</span>
                        </p>
                    @endif
                    @if ($items)
                        {{-- Daftar item dibatasi agar tombol aksi tetap terlihat. --}}
                        <div class="max-h-24 overflow-y-auto pr-1 space-y-1.5">
                            @foreach ($items as $item)
                                <p class="flex justify-between gap-2">
                                    <span class="truncate">{{ $item['name'] }}@if ($item['qty'] > 1) x{{ $item['qty'] }}@endif
                                        @if (!empty($item['size_name']))
                                            <span class="text-slate-400">· {{ $item['size_name'] }}</span>
                                        @endif
                                    </span>
                                    <span class="tabular-nums shrink-0">Rp {{ number_format($item['price'] * $item['qty'], 0, ',', '.') }}</span>
                                </p>
                            @endforeach
                        </div>
                    @endif
                    <p class="flex justify-between pt-2 mt-1 border-t border-dashed border-slate-200">
                        <span class="font-bold text-slate-800">Total</span>
                        <span class="font-display font-extrabold text-rose-600 tabular-nums">Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </p>
                    @if (($metode ?? '') === 'voucher')
                        <p class="text-right text-[11px] font-semibold text-emerald-600">Sudah dibayar dengan voucher</p>
                    @endif
                </div>
            </div>

            @if (! empty($downloadToken))
                <div class="k-card p-3 flex items-center gap-3">
                    <div class="shrink-0 rounded-xl bg-white p-1.5 ring-1 ring-slate-200">
                        <canvas data-download-qr
                                data-download-qr-url="{{ \App\Support\DownloadLink::show($downloadToken) }}"
                                class="h-20 w-20"></canvas>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-slate-800">Scan untuk unduh di HP</p>
                        <p class="mt-0.5 text-[10px] leading-tight text-slate-500">Pindai QR ini sebelum menekan Selesai. Token: <span class="font-mono font-semibold text-slate-700">{{ $downloadToken }}</span></p>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-2 gap-3">
                <button type="button" data-print-btn class="k-btn !px-4">
                    <x-icon name="printer" class="w-5 h-5 text-white" /> Cetak
                </button>
                <button type="button" data-strip-download class="k-btn k-btn-ghost !px-4 !py-3.5 !text-base">
                    <x-icon name="photo" class="w-5 h-5" /> Unduh
                </button>
            </div>

            <form method="POST" action="{{ route('selesai.store') }}" data-finish-form>
                @csrf
                <button type="submit" class="w-full text-center text-sm font-semibold text-slate-500 hover:text-rose-600 py-0.5 transition focus:outline-none focus-visible:underline">
                    {{ $brand['finish_label'] }}
                </button>
            </form>
        </div>
    </div>

    <div class="fixed inset-0 z-[80] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm" data-printing-modal>
        <div class="k-card-solid p-10 text-center max-w-sm mx-4 shadow-2xl">
            <div class="relative w-16 h-16 mx-auto mb-4">
                <div class="animate-spin w-16 h-16 rounded-full border-4 border-rose-100 border-t-rose-500"></div>
                <span class="absolute inset-0 flex items-center justify-center text-slate-600"><x-icon name="printer" class="w-8 h-8" /></span>
            </div>
            <h2 class="font-display font-bold text-xl" data-modal-title>Mohon tunggu</h2>
            <p class="text-sm text-slate-500 mt-1" data-modal-text>Sebentar ya...</p>
        </div>
    </div>
</div>
