<div class="flex-1 min-h-0 flex flex-col">
    <div class="mb-1">
        <a href="{{ route('spa', ['step' => 'foto']) }}" class="text-xs font-semibold text-slate-400 hover:text-rose-500 transition">← Kembali</a>
    </div>
    <div class="text-center mb-3">
        <h2 class="font-display font-bold text-2xl">Pilih Filter</h2>
        <p class="text-slate-500 text-sm mt-1">Foto sudah masuk frame — ketuk filter di tiap foto, boleh beda-beda</p>
    </div>

    <form method="POST" action="{{ route('filter') }}" data-filter-form class="flex flex-col flex-1 min-h-0">
        @csrf
        <input type="hidden" name="from_spa" value="1">

        <div class="flex-1 min-h-0 overflow-y-auto space-y-3 pb-2 pr-0.5">
            @forelse ($items as $item)
                @php($slug = $item['slug'])
                <div class="rounded-3xl bg-white/80 ring-1 ring-rose-100 p-3" data-filter-item="{{ $slug }}">
                    <p class="text-xs font-bold text-slate-600 mb-2">{{ $item['name'] }}</p>
                    <div class="mx-auto max-w-[210px] overflow-hidden rounded-2xl ring-1 ring-rose-100 bg-white" data-strip-container>
                        <canvas class="w-full h-auto block" data-strip-canvas
                                data-photos='@json(array_values($item['photos'] ?? []))'
                                data-filters='@json($item['filter_map'] ?? [])'
                                data-filter="asli"
                                data-frame="{{ $slug }}"
                                data-frame-image="{{ $item['image_url'] }}"
                                data-frame-slots='@json($item['slots'] ?? [])'></canvas>
                    </div>
                    @foreach (array_values($item['photos'] ?? []) as $local => $photo)
                        <div class="mt-2 flex items-center gap-2" data-filter-photo="{{ $slug }}:{{ $local }}">
                            <img src="{{ $photo }}" class="w-9 h-9 rounded-lg object-cover ring-1 ring-rose-100 shrink-0" alt="Foto {{ $local + 1 }}">
                            <span class="text-xs font-bold text-slate-500 shrink-0">Foto {{ $local + 1 }}</span>
                            <input type="hidden" name="filters[{{ $slug }}:{{ $local }}]" value="{{ ($item['filter_map'] ?? [])[$local] ?? 'asli' }}" data-filter-value>
                            <div class="flex flex-wrap gap-1">
                                @foreach ($filters as $key => $label)
                                    <button type="button" data-filter-chip="{{ $key }}"
                                            class="px-2 py-1 rounded-full text-[10px] font-bold ring-1 transition {{ (($item['filter_map'] ?? [])[$local] ?? 'asli') === $key ? 'bg-rose-500 text-white ring-rose-500' : 'bg-white text-slate-500 ring-rose-100' }}">{{ $label }}</button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @empty
                <p class="text-center text-slate-400">Belum ada foto. Kembali dan selesaikan sesi foto dulu.</p>
            @endforelse
        </div>

        <div class="pt-3 flex justify-end shrink-0">
            <button type="submit" class="px-10 py-3 rounded-full bg-gradient-to-r from-rose-500 to-violet-600 text-white font-display font-bold text-lg shadow-lg shadow-rose-200 hover:scale-105 transition-transform">
                Lanjut →
            </button>
        </div>
    </form>
</div>
