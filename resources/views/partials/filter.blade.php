<div class="flex-1 min-h-0 flex flex-col">
    <div class="text-center mb-2">
        <div class="inline-flex items-center justify-center gap-2">
            <span class="w-8 h-8 rounded-xl bg-gradient-to-br from-rose-400 to-violet-500 text-white flex items-center justify-center shadow-md shadow-rose-200">
                <x-icon name="sliders" class="w-4 h-4 text-white" />
            </span>
            <h2 class="font-display font-bold text-2xl">Pilih Filter</h2>
        </div>
        <p class="text-slate-500 text-sm mt-1">Ketuk pilihan di tiap foto untuk mengganti tampilannya</p>
    </div>

    <form method="POST" action="{{ route('filter') }}" data-filter-form class="flex flex-col flex-1 min-h-0">
        @csrf
        <input type="hidden" name="from_spa" value="1">
        @php($multi = count($items) > 1)
        {{-- Chip di-grid agar sejajar & tanpa "yatim". Kartu sempit (multi) pakai 2
             kolom; kartu tunggal yang lega pakai 4 kolom di layar sedang+. --}}
        @php($chipCols = $multi ? 'grid-cols-2' : 'grid-cols-2 sm:grid-cols-4')

        <div class="flex-1 min-h-0 overflow-y-auto grid gap-3 pb-2 pr-0.5 content-start {{ $multi ? 'sm:grid-cols-2' : 'sm:grid-cols-1 sm:max-w-2xl sm:mx-auto sm:w-full' }}">
            @forelse ($items as $item)
                @php($slug = $item['slug'])
                @php($photoList = array_values($item['photos'] ?? []))
                <div class="rounded-3xl bg-white/90 backdrop-blur ring-1 ring-rose-100 p-4 shadow-sm flex flex-col gap-3" data-filter-item="{{ $slug }}">
                    <div class="flex items-center justify-between gap-2">
                        <h3 class="flex items-center gap-2 text-sm font-display font-bold text-slate-700 truncate min-w-0">
                            <span class="w-6 h-6 rounded-lg bg-rose-50 ring-1 ring-rose-100 flex items-center justify-center text-rose-500 shrink-0">
                                <x-icon name="photo" class="w-3.5 h-3.5" />
                            </span>
                            <span class="truncate">{{ $item['name'] }}</span>
                        </h3>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-50 text-rose-600 ring-1 ring-rose-100">{{ count($photoList) }} foto</span>
                            @if (count($photoList) > 1)
                                <button type="button" data-filter-same
                                        class="text-[10px] font-bold px-2 py-1 rounded-full bg-white text-rose-600 ring-1 ring-rose-200 hover:bg-rose-50 transition"
                                        title="Pakai filter Foto 1 untuk semua foto">
                                    Samakan
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="flex gap-3 items-start">
                        <div class="shrink-0 w-[96px] sm:w-[108px]">
                            <div class="overflow-hidden rounded-xl ring-1 ring-slate-100 bg-gradient-to-b from-slate-50 to-white" data-strip-container>
                                <canvas class="w-full h-auto block" data-strip-canvas
                                        data-photos='@json($photoList)'
                                        data-filters='@json($item['filter_map'] ?? [])'
                                        data-filter="asli"
                                        data-frame="{{ $slug }}"
                                        data-frame-image="{{ $item['image_url'] }}"
                                        data-frame-slots='@json($item['slots'] ?? [])'></canvas>
                            </div>
                            <p class="mt-1 text-center text-[10px] font-medium text-slate-400">Pratinjau</p>
                        </div>
                        <div class="flex-1 min-w-0 space-y-3">
                            @foreach ($photoList as $local => $photo)
                                <div data-filter-photo="{{ $slug }}:{{ $local }}">
                                    <div class="flex items-center gap-2 mb-1.5">
                                        <img src="{{ $photo }}" class="w-7 h-7 rounded-lg object-cover ring-1 ring-rose-100 shrink-0" alt="Foto {{ $local + 1 }}">
                                        <span class="text-xs font-bold text-slate-600">Foto {{ $local + 1 }}</span>
                                    </div>
                                    <input type="hidden" name="filters[{{ $slug }}:{{ $local }}]" value="{{ ($item['filter_map'] ?? [])[$local] ?? 'asli' }}" data-filter-value>
                                    <div class="grid {{ $chipCols }} gap-1">
                                        @foreach ($filters as $key => $label)
                                            @php($activeChip = (($item['filter_map'] ?? [])[$local] ?? 'asli') === $key)
                                            <button type="button" data-filter-chip="{{ $key }}" aria-pressed="{{ $activeChip ? 'true' : 'false' }}"
                                                    class="w-full justify-center px-2 py-2 rounded-full text-xs font-bold ring-1 transition-all hover:scale-[1.03] active:scale-95 {{ $activeChip ? 'bg-rose-500 text-white ring-rose-500' : 'bg-white text-slate-500 ring-rose-100 hover:ring-rose-200' }}">{{ $label }}</button>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @empty
                <div class="sm:col-span-full flex flex-col items-center gap-1.5 py-6 text-center">
                    <span class="w-10 h-10 rounded-full bg-white ring-1 ring-rose-100 flex items-center justify-center text-rose-300">
                        <x-icon name="photo" class="w-5 h-5" />
                    </span>
                    <p class="text-xs text-slate-400 leading-snug">Belum ada foto.<br>Selesaikan sesi foto terlebih dulu.</p>
                </div>
            @endforelse
        </div>

        <div class="pt-2 flex justify-center items-center gap-3 shrink-0">
            <a href="{{ route('spa', ['step' => 'foto']) }}" data-back-btn
               class="inline-flex items-center gap-1.5 rounded-full bg-white/80 ring-1 ring-rose-100 px-4 sm:px-5 py-3 text-xs sm:text-sm font-semibold text-slate-600 hover:bg-white transition">
                <x-icon name="arrow-left" class="w-4 h-4" /> Foto Ulang
            </a>
            <button type="submit"
                    class="w-full max-w-xs inline-flex items-center justify-center gap-1.5 px-5 sm:px-6 py-3.5 rounded-full bg-gradient-to-r from-rose-500 to-violet-600 text-white font-display font-bold text-sm sm:text-base shadow-lg shadow-rose-200 hover:scale-[1.02] active:scale-95 transition-transform focus:outline-none focus-visible:ring-4 focus-visible:ring-rose-300">
                Lanjut ke Pembayaran
                <x-icon name="arrow-right" class="w-4 h-4 text-white" />
            </button>
        </div>
    </form>
</div>
