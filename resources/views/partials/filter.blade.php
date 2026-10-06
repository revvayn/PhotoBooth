<div class="flex-1 min-h-0 flex flex-col">
    <div class="text-center mb-2">
        <h2 class="font-display font-bold text-xl">Pilih Filter</h2>
    </div>

    <form method="POST" action="{{ route('filter') }}" data-filter-form class="flex flex-col flex-1 min-h-0">
        @csrf
        <input type="hidden" name="from_spa" value="1">

        <div class="flex-1 min-h-0 overflow-y-auto grid gap-2 pb-2 pr-0.5 content-start {{ count($items) > 1 ? 'sm:grid-cols-2' : 'sm:grid-cols-1' }}">
            @forelse ($items as $item)
                @php($slug = $item['slug'])
                <div class="rounded-2xl bg-white/80 ring-1 ring-rose-100 p-2.5 flex gap-3 items-start" data-filter-item="{{ $slug }}">
                    <div class="shrink-0">
                        <p class="text-xs font-bold text-slate-600 mb-1.5">{{ $item['name'] }}</p>
                        <div class="max-w-[110px] overflow-hidden rounded-xl ring-1 ring-rose-100 bg-white" data-strip-container>
                            <canvas class="w-full h-auto block" data-strip-canvas
                                    data-photos='@json(array_values($item['photos'] ?? []))'
                                    data-filters='@json($item['filter_map'] ?? [])'
                                    data-filter="asli"
                                    data-frame="{{ $slug }}"
                                    data-frame-image="{{ $item['image_url'] }}"
                                    data-frame-slots='@json($item['slots'] ?? [])'></canvas>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0 space-y-1.5">
                        @foreach (array_values($item['photos'] ?? []) as $local => $photo)
                            <div class="flex items-center gap-2" data-filter-photo="{{ $slug }}:{{ $local }}">
                                <img src="{{ $photo }}" class="w-7 h-7 rounded-lg object-cover ring-1 ring-rose-100 shrink-0" alt="Foto {{ $local + 1 }}">
                                <span class="text-[11px] font-bold text-slate-500 shrink-0">Foto {{ $local + 1 }}</span>
                                <input type="hidden" name="filters[{{ $slug }}:{{ $local }}]" value="{{ ($item['filter_map'] ?? [])[$local] ?? 'asli' }}" data-filter-value>
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($filters as $key => $label)
                                        <button type="button" data-filter-chip="{{ $key }}"
                                                class="px-1.5 py-0.5 rounded-full text-[10px] font-bold ring-1 transition {{ (($item['filter_map'] ?? [])[$local] ?? 'asli') === $key ? 'bg-rose-500 text-white ring-rose-500' : 'bg-white text-slate-500 ring-rose-100' }}">{{ $label }}</button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-center text-slate-400">Belum ada foto. Kembali dan selesaikan sesi foto dulu.</p>
            @endforelse
        </div>

        <div class="pt-2 flex justify-end shrink-0">
            <button type="submit" class="px-9 py-2.5 rounded-full bg-gradient-to-r from-rose-500 to-violet-600 text-white font-display font-bold text-base shadow-lg shadow-rose-200 hover:scale-105 transition-transform">
                Lanjut →
            </button>
        </div>
    </form>
</div>
