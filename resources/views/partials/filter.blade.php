@php($brand = $brand ?? \App\Support\Brand::get())
@php($filterItems = collect($items ?? [])->values())
@php($palette = collect($stickerPalette ?? [])->values())
@php($multi = $filterItems->count() > 1)
<div class="flex-1 min-h-0 flex flex-col" data-filter-editor>
    <div class="k-step-head mb-2.5 shrink-0">
        <a href="{{ route('spa', ['step' => 'foto']) }}" data-back-btn aria-label="Kembali ke sesi foto"
           class="k-btn k-btn-ghost justify-self-start">
            <x-icon name="arrow-left" class="w-4 h-4" />
            <span class="hidden sm:inline">Foto</span>
        </a>
        <div class="k-step-head-title">
            <h2 class="k-title">
                <span class="k-icon-soft w-9 h-9 rounded-xl">
                    <x-icon name="sliders" class="w-4 h-4" />
                </span>
                Edit Foto
            </h2>
            <p class="text-slate-500 text-xs sm:text-sm">Pilih filter, lalu tempel emoji atau stiker favoritmu</p>
        </div>
        <span class="k-pill justify-self-end !text-xs hidden sm:inline-flex">
            <x-icon name="sparkles" class="w-4 h-4 text-rose-500" />
            Finishing
        </span>
    </div>

    <form method="POST" action="{{ route('filter.store') }}" data-filter-form class="flex flex-col flex-1 min-h-0">
        @csrf
        <input type="hidden" name="from_spa" value="1">
        <input type="hidden" name="stickers" value="{}" data-stickers-input>

        @if ($multi)
            <div class="flex justify-center mb-2.5 shrink-0">
                <div class="k-segment" data-filter-tabs>
                    @foreach ($filterItems as $index => $item)
                        <button type="button" data-filter-tab="{{ $index }}"
                                aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                                class="k-segment-item max-w-[190px]">
                            <span class="truncate">{{ $item['name'] }}</span>
                            <span class="k-segment-count">{{ count($item['photos'] ?? []) }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="flex-1 min-h-0">
            @forelse ($filterItems as $index => $item)
                @php($slug = $item['slug'])
                @php($photoList = array_values($item['photos'] ?? []))
                @php($filterMap = $item['filter_map'] ?? [])
                @php($stickerMap = $item['sticker_map'] ?? [])
                <div class="h-full min-h-0 grid lg:grid-cols-[minmax(0,1fr)_360px] gap-3 items-stretch"
                     data-filter-item="{{ $slug }}" @if ($index !== 0) hidden @endif>
                    {{-- Pratinjau besar dengan lapisan stiker yang bisa digeser. --}}
                    <div class="k-card p-3 sm:p-3.5 flex flex-col min-h-0">
                        <div class="flex items-center justify-between gap-2 mb-2 shrink-0">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="k-icon-soft w-8 h-8 rounded-lg">
                                    <x-icon name="photo" class="w-4 h-4" />
                                </span>
                                <div class="min-w-0">
                                    <p class="text-sm font-display font-bold text-slate-800 truncate">{{ $item['name'] }}</p>
                                    <p class="text-[11px] text-slate-400">{{ count($photoList) }} foto &middot; {{ $item['qty'] }} lembar</p>
                                </div>
                            </div>
                            <button type="button" data-sticker-clear
                                    class="text-[11px] font-bold px-2.5 py-1.5 rounded-full bg-white text-slate-500 ring-1 ring-slate-200 hover:text-rose-600 hover:ring-rose-200 transition shrink-0">
                                Bersihkan stiker
                            </button>
                        </div>

                        <div class="flex-1 min-h-0 flex items-center justify-center">
                            <div class="relative inline-block max-w-full" data-sticker-stage>
                                <canvas class="block w-auto h-auto max-w-full max-h-[44vh] rounded-xl bg-white ring-1 ring-black/5"
                                        data-strip-canvas
                                        data-photos='@json($photoList)'
                                        data-filters='@json($filterMap)'
                                        data-filter="asli"
                                        data-frame="{{ $slug }}"
                                        data-frame-image="{{ $item['image_url'] }}"
                                        data-frame-slots='@json($item['slots'] ?? [])'
                                        data-stickers='@json($stickerMap)'
                                        data-item="{{ $slug }}"></canvas>
                                <div class="absolute inset-0" data-sticker-layer></div>
                            </div>
                        </div>

                        <p class="mt-2 text-center text-[11px] text-slate-400 shrink-0">
                            Geser untuk memindahkan &middot; tarik sudut untuk memperbesar &middot; tarik bulatan atas untuk memutar &middot; <span class="font-bold text-slate-500">×</span> untuk menghapus
                        </p>
                    </div>

                    {{-- Kontrol filter per foto + palet stiker admin. --}}
                    <div class="k-card p-3.5 flex flex-col min-h-0 overflow-y-auto">
                        <section class="shrink-0">
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <h3 class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Filter Foto</h3>
                                @if (count($photoList) > 1)
                                    <button type="button" data-filter-same
                                            class="text-[10px] font-bold px-2 py-1 rounded-full bg-rose-50 text-rose-600 ring-1 ring-rose-100 hover:bg-rose-100 transition"
                                            title="Pakai filter foto yang dipilih untuk semua foto">
                                        Samakan semua
                                    </button>
                                @endif
                            </div>

                            @if (count($photoList) > 1)
                                <div class="k-segment w-full mb-2" data-photo-tabs>
                                    @foreach ($photoList as $local => $photo)
                                        <button type="button" data-photo-tab="{{ $local }}"
                                                aria-selected="{{ $local === 0 ? 'true' : 'false' }}"
                                                class="k-segment-item flex-1">
                                            Foto {{ $local + 1 }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif

                            <div class="space-y-2">
                                @foreach ($photoList as $local => $photo)
                                    @php($currentFilter = $filterMap[$local] ?? 'asli')
                                    <div data-filter-photo="{{ $slug }}:{{ $local }}" @if ($local !== 0) hidden @endif>
                                        <input type="hidden" name="filters[{{ $slug }}:{{ $local }}]" value="{{ $currentFilter }}" data-filter-value>
                                        <div class="flex items-center gap-2 mb-1.5">
                                            <img src="{{ $photo }}" class="w-6 h-6 rounded-md object-cover ring-1 ring-rose-100 shrink-0" alt="Foto {{ $local + 1 }}">
                                            <span class="text-[11px] font-bold text-slate-600">Foto {{ $local + 1 }}</span>
                                            <span class="text-[10px] text-slate-400 truncate" data-filter-label>{{ $filters[$currentFilter] ?? 'Asli' }}</span>
                                        </div>
                                        <div class="grid grid-cols-4 gap-1.5">
                                            @foreach ($filters as $key => $label)
                                                @php($activeChip = $currentFilter === $key)
                                                <button type="button" data-filter-chip="{{ $key }}" aria-pressed="{{ $activeChip ? 'true' : 'false' }}"
                                                        class="w-full px-1 py-1.5 rounded-full text-[11px] font-bold ring-1 leading-tight transition-all hover:scale-[1.03] active:scale-95 {{ $activeChip ? 'bg-rose-500 text-white ring-rose-500' : 'bg-white text-slate-500 ring-rose-100 hover:ring-rose-200' }}">{{ $label }}</button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </section>

                        <section class="mt-3 pt-3 border-t border-slate-100">
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <h3 class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Emoji &amp; Stiker</h3>
                                <span class="text-[10px] font-semibold text-slate-400">dari admin</span>
                            </div>

                            @if ($palette->isEmpty())
                                <div class="rounded-xl bg-slate-50 ring-1 ring-slate-100 px-3 py-4 text-center text-[11px] text-slate-400">
                                    Belum ada stiker aktif. Tambahkan dari menu <span class="font-semibold text-slate-500">Stiker</span> di admin.
                                </div>
                            @else
                                <div class="grid grid-cols-5 gap-1.5" data-sticker-palette>
                                    @foreach ($palette as $sticker)
                                        <button type="button" data-sticker-add="{{ $sticker['id'] }}"
                                                data-sticker-type="{{ $sticker['type'] }}"
                                                data-sticker-value="{{ $sticker['value'] }}"
                                                title="{{ $sticker['name'] }}" aria-label="Tambah stiker {{ $sticker['name'] }}"
                                                class="aspect-square rounded-xl bg-white ring-1 ring-slate-200 hover:ring-rose-300 hover:bg-rose-50 active:scale-95 transition flex items-center justify-center overflow-hidden p-1">
                                            @if ($sticker['type'] === 'image')
                                                <img src="{{ $sticker['value'] }}" alt="{{ $sticker['name'] }}" class="w-full h-full object-contain">
                                            @else
                                                <span class="text-2xl leading-none">{{ $sticker['value'] }}</span>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </section>
                    </div>
                </div>
            @empty
                <div class="h-full flex flex-col items-center justify-center gap-2 text-center">
                    <span class="w-12 h-12 rounded-full bg-white ring-1 ring-rose-100 flex items-center justify-center text-rose-300">
                        <x-icon name="photo" class="w-6 h-6" />
                    </span>
                    <p class="text-sm font-semibold text-slate-600">Belum ada foto untuk diedit</p>
                    <a href="{{ route('spa', ['step' => 'foto']) }}" class="k-btn k-btn-ghost mt-1">
                        <x-icon name="camera" class="w-4 h-4" /> Mulai Sesi Foto
                    </a>
                </div>
            @endforelse
        </div>

        <div class="pt-2.5 flex justify-center items-center gap-3 shrink-0">
            <a href="{{ route('spa', ['step' => 'foto']) }}" data-back-btn
               class="inline-flex items-center gap-1.5 rounded-full bg-white/80 ring-1 ring-rose-100 px-4 sm:px-5 py-3 text-xs sm:text-sm font-semibold text-slate-600 hover:bg-white transition">
                <x-icon name="camera" class="w-4 h-4" /> Foto Ulang
            </a>
            <button type="submit"
                    class="w-full max-w-xs inline-flex items-center justify-center gap-1.5 px-5 sm:px-6 py-3.5 rounded-full bg-gradient-to-r from-rose-500 to-violet-600 text-white font-display font-bold text-sm sm:text-base shadow-lg shadow-rose-200 hover:scale-[1.02] active:scale-95 transition-transform focus:outline-none focus-visible:ring-4 focus-visible:ring-rose-300">
                Simpan &amp; Lanjut Review
                <x-icon name="arrow-right" class="w-4 h-4 text-white" />
            </button>
        </div>
    </form>
</div>
