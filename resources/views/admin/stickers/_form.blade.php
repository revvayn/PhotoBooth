@php($type = old('type', $sticker?->type ?? 'emoji'))
@php($imageUrl = $sticker?->image_url)
<div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
    <div>
        <label for="name" class="block text-sm font-medium text-slate-700 mb-1.5">Nama Stiker <span class="text-red-500">*</span></label>
        <input type="text" id="name" name="name" value="{{ old('name', $sticker->name ?? '') }}" required
               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-transparent transition-all duration-200 @error('name') border-red-300 focus:ring-red-400 @enderror"
               placeholder="Contoh: Bunga">
        @error('name')
            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="type" class="block text-sm font-medium text-slate-700 mb-1.5">Tipe</label>
        <select id="type" name="type" data-sticker-type
                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-transparent transition-all duration-200">
            <option value="emoji" @selected($type === 'emoji')>Emoji / karakter</option>
            <option value="image" @selected($type === 'image')>Gambar (PNG/JPG/WEBP)</option>
        </select>
        @error('type')
            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>
</div>

<div data-sticker-field="emoji" @if ($type !== 'emoji') hidden @endif>
    <label for="emoji" class="block text-sm font-medium text-slate-700 mb-1.5">Emoji <span class="text-red-500">*</span></label>
    <div class="flex items-center gap-3">
        <input type="text" id="emoji" name="emoji" value="{{ old('emoji', $sticker?->emoji ?? '😀') }}" maxlength="32"
               class="w-24 px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-2xl text-center text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-transparent transition-all duration-200 @error('emoji') border-red-300 focus:ring-red-400 @enderror">
        <p class="text-xs text-slate-400">Tempel satu karakter emoji, mis. 😍 atau 🎉.</p>
    </div>
    @error('emoji')
        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>

<div data-sticker-field="image" @if ($type !== 'image') hidden @endif>
    <label class="block text-sm font-medium text-slate-700 mb-1.5">Gambar Stiker</label>
    <div class="mt-1 flex items-start gap-4">
        <div class="w-24 h-24 bg-white ring-1 ring-slate-200 rounded-2xl overflow-hidden flex items-center justify-center shrink-0">
            <img id="sticker-preview" src="{{ $imageUrl ?? '' }}" alt="Pratinjau stiker"
                 class="{{ $imageUrl ? 'w-full h-full object-contain p-1' : 'hidden' }}">
            <span id="sticker-preview-empty" class="text-slate-300 text-xs text-center px-2 {{ $imageUrl ? 'hidden' : '' }}">Belum ada gambar</span>
        </div>
        <div class="flex-1">
            <input type="file" id="sticker_image" name="sticker_image" accept="image/png,image/jpeg,image/webp"
                   class="w-full text-sm text-slate-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-2xl file:border-0 file:text-sm file:font-semibold file:bg-rose-50 file:text-rose-600 hover:file:bg-rose-100 transition">
            <p class="mt-2 text-xs text-slate-400">Gunakan PNG transparan agar stiker menyatu dengan foto. Maks. 4 MB.</p>
            @error('sticker_image')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
    <div>
        <label for="sort_order" class="block text-sm font-medium text-slate-700 mb-1.5">Urutan</label>
        <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $sticker?->sort_order ?? 0) }}" min="0"
               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-transparent transition-all duration-200">
        @error('sort_order')
            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-end pb-1">
        <label class="flex items-center gap-3 cursor-pointer select-none">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $sticker?->is_active ?? true) ? 'checked' : '' }}
                   class="w-5 h-5 text-rose-500 bg-gray-100 border-gray-300 rounded focus:ring-rose-400 focus:ring-2 cursor-pointer">
            <span class="text-sm font-medium text-slate-700">Aktif</span>
        </label>
    </div>
</div>

<script>
    (() => {
        const form = document.currentScript.closest('form');
        const type = form.querySelector('[data-sticker-type]');
        const emojiField = form.querySelector('[data-sticker-field="emoji"]');
        const imageField = form.querySelector('[data-sticker-field="image"]');
        const input = form.querySelector('#sticker_image');
        const preview = form.querySelector('#sticker-preview');
        const empty = form.querySelector('#sticker-preview-empty');

        const paintType = () => {
            const isEmoji = type.value === 'emoji';
            emojiField.hidden = !isEmoji;
            imageField.hidden = isEmoji;
        };

        type.addEventListener('change', paintType);
        paintType();

        input?.addEventListener('change', () => {
            const file = input.files[0];
            if (!file) return;
            preview.src = URL.createObjectURL(file);
            preview.classList.remove('hidden');
            empty.classList.add('hidden');
        });
    })();
</script>
