@php($isEdit = $size->exists)
<form action="{{ $action }}" method="POST" class="space-y-5" data-size-form>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
            <label for="name" class="block text-sm font-medium text-slate-700 mb-1.5">Nama Ukuran <span class="text-red-500">*</span></label>
            <input type="text" id="name" name="name" value="{{ old('name', $size->name) }}" required
                   class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-transparent transition-all duration-200 @error('name') border-red-300 focus:ring-red-400 @enderror"
                   placeholder="Contoh: 4R, A4, Strip 2x6">
            @error('name')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="price" class="block text-sm font-medium text-slate-700 mb-1.5">Harga Dasar (Rp) <span class="text-red-500">*</span></label>
            <input type="number" id="price" name="price" value="{{ old('price', $size->price) }}" min="0" step="500" required
                   class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-transparent transition-all duration-200 @error('price') border-red-300 focus:ring-red-400 @enderror">
            @error('price')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div>
            <label for="width_cm" class="block text-sm font-medium text-slate-700 mb-1.5">Lebar (cm) <span class="text-red-500">*</span></label>
            <input type="number" id="width_cm" name="width_cm" value="{{ old('width_cm', $size->width_cm) }}" min="1" max="200" step="0.1" required
                   class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-transparent transition-all duration-200 @error('width_cm') border-red-300 focus:ring-red-400 @enderror">
            @error('width_cm')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="height_cm" class="block text-sm font-medium text-slate-700 mb-1.5">Tinggi (cm) <span class="text-red-500">*</span></label>
            <input type="number" id="height_cm" name="height_cm" value="{{ old('height_cm', $size->height_cm) }}" min="1" max="200" step="0.1" required
                   class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-transparent transition-all duration-200 @error('height_cm') border-red-300 focus:ring-red-400 @enderror">
            @error('height_cm')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="sort_order" class="block text-sm font-medium text-slate-700 mb-1.5">Urutan</label>
            <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $size->sort_order ?? 0) }}" min="0"
                   class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-transparent transition-all duration-200">
            @error('sort_order')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center gap-4 rounded-2xl bg-slate-50 ring-1 ring-slate-100 p-4">
        <div class="w-24 h-28 rounded-xl bg-white ring-1 ring-slate-200 flex items-center justify-center shrink-0 overflow-hidden">
            <div data-size-preview class="rounded border-2 border-dashed border-rose-300 bg-rose-50/40"
                 style="width: 64px; height: 84px;"></div>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-slate-700">Template frame</p>
            <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">
                Unduh PNG transparan dengan rasio ukuran ini. Desain area foto (lubang transparan) di atas template,
                lalu unggah sebagai frame. Frame dengan rasio berbeda akan ditolak.
            </p>
            <div class="mt-2 flex flex-wrap items-center gap-2">
                @if ($isEdit)
                    <a href="{{ route('admin.sizes.template', $size) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white text-slate-700 text-xs font-semibold rounded-lg ring-1 ring-slate-200 hover:bg-slate-100 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Unduh Template PNG
                    </a>
                @else
                    <span class="text-[11px] font-medium text-slate-400">Template dapat diunduh setelah ukuran disimpan.</span>
                @endif
                <span class="text-[11px] text-slate-400" data-size-ratio></span>
            </div>
        </div>
    </div>

    <div class="flex items-end pb-1">
        <label class="flex items-center gap-3 cursor-pointer select-none">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $size->is_active ?? true) ? 'checked' : '' }}
                   class="w-5 h-5 text-rose-500 bg-gray-100 border-gray-300 rounded focus:ring-rose-400 focus:ring-2 cursor-pointer">
            <span class="text-sm font-medium text-slate-700">Aktif</span>
        </label>
    </div>

    <div class="flex items-center gap-3 pt-4 border-t border-gray-200">
        <a href="{{ route('admin.sizes.index') }}"
           class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-gray-100 rounded-2xl hover:bg-gray-200 transition-colors">Batal</a>
        <button type="submit"
                class="px-6 py-2.5 bg-gradient-to-r from-rose-500 to-pink-500 text-white text-sm font-semibold rounded-2xl shadow-lg shadow-rose-500/25 hover:shadow-rose-500/40 hover:from-rose-600 hover:to-pink-600 transition-all duration-200">
            {{ $submitLabel }}
        </button>
    </div>
</form>

<script>
    (() => {
        const form = document.querySelector('[data-size-form]');
        if (!form) return;
        const width = form.querySelector('#width_cm');
        const height = form.querySelector('#height_cm');
        const preview = form.querySelector('[data-size-preview]');
        const ratio = form.querySelector('[data-size-ratio]');

        const update = () => {
            const w = parseFloat(width.value) || 1;
            const h = parseFloat(height.value) || 1;
            const maxW = 64;
            const maxH = 84;
            const scale = Math.min(maxW / w, maxH / h);
            preview.style.width = Math.max(12, Math.round(w * scale)) + 'px';
            preview.style.height = Math.max(12, Math.round(h * scale)) + 'px';
            if (ratio) {
                ratio.textContent = 'Rasio ' + w.toFixed(2).replace(/\.00$/, '') + ' : ' + h.toFixed(2).replace(/\.00$/, '');
            }
        };

        width.addEventListener('input', update);
        height.addEventListener('input', update);
        update();
    })();
</script>
