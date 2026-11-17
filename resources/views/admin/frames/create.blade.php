@extends('layouts.admin')

@section('title', 'Tambah Frame - '.\App\Support\Brand::get()['name'].' Admin')
@section('page-title', 'Tambah Frame')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.frames.index') }}"
           class="inline-flex items-center gap-1 px-3 py-2 text-slate-600 text-sm font-medium rounded-lg hover:bg-gray-100 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="font-heading text-lg font-bold text-slate-800">Form Tambah Frame</h2>
        </div>
        <div class="p-6">
            <form action="{{ route('admin.frames.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700 mb-1.5">Nama Frame <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-transparent transition-all duration-200 @error('name') border-red-300 focus:ring-red-400 @enderror"
                               placeholder="Contoh: Sunset Vibes">
                        @error('name')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="size_id" class="block text-sm font-medium text-slate-700 mb-1.5">Ukuran <span class="text-red-500">*</span></label>
                        <select id="size_id" name="size_id" required
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-transparent transition-all duration-200 @error('size_id') border-red-300 focus:ring-red-400 @enderror">
                            @forelse ($sizes as $size)
                                <option value="{{ $size->id }}"
                                        data-template="{{ route('admin.sizes.template', $size) }}"
                                        data-dimension="{{ $size->dimension_label }}"
                                        data-ratio="{{ number_format($size->width_cm / max($size->height_cm, 0.01), 4, '.', '') }}"
                                        @selected((string) old('size_id', $sizes->first()?->id) === (string) $size->id)>
                                    {{ $size->name }} — {{ $size->dimension_label }}
                                </option>
                            @empty
                                <option value="">Belum ada ukuran</option>
                            @endforelse
                        </select>
                        @error('size_id')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                        <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1">
                            <a href="{{ $sizes->first() ? route('admin.sizes.template', $sizes->first()) : '#' }}" data-template-link
                               class="inline-flex items-center gap-1 text-xs font-semibold text-rose-600 hover:text-rose-700">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Unduh template ukuran ini
                            </a>
                            <span class="text-[11px] text-slate-400" data-size-dimension></span>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Gambar Frame (PNG) <span class="text-red-500">*</span></label>
                    <div class="mt-1 flex items-start gap-4">
                        <div class="w-28 h-36 bg-white ring-1 ring-slate-200 rounded-2xl overflow-hidden flex items-center justify-center shrink-0" id="frame-preview-wrap">
                            <img id="frame-preview" class="w-full h-full object-contain hidden" alt="Pratinjau frame">
                            <span class="text-slate-300 text-xs text-center px-2" id="frame-preview-empty">Belum ada gambar</span>
                        </div>
                        <div class="flex-1">
                            <input type="file" id="frame_image" name="frame_image" accept="image/png" required
                                   class="w-full text-sm text-slate-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-2xl file:border-0 file:text-sm file:font-semibold file:bg-rose-50 file:text-rose-600 hover:file:bg-rose-100 transition">
                            <p class="mt-2 text-xs text-slate-400">
                                Upload PNG dengan <strong>area transparan sebagai tempat foto</strong>. Rasio gambar wajib sama dengan ukuran yang dipilih; bila tidak sesuai, upload ditolak. Jumlah lubang transparan menentukan banyaknya foto (mis. 4 lubang = 4 foto).
                            </p>
                            @error('frame_image')
                                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div>
                    <label for="caption" class="block text-sm font-medium text-slate-700 mb-1.5">Caption</label>
                    <input type="text" id="caption" name="caption" value="{{ old('caption') }}"
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-transparent transition-all duration-200"
                           placeholder="Teks opsional pada frame">
                    @error('caption')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="price" class="block text-sm font-medium text-slate-700 mb-1.5">Harga Khusus Frame (Rp)</label>
                        <input type="number" id="price" name="price" value="{{ old('price') }}" min="0" step="500" placeholder="Ikut harga ukuran"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-transparent transition-all duration-200">
                        <p class="mt-1.5 text-xs text-slate-400">Kosongkan agar harga mengikuti ukuran.</p>
                        @error('price')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="sort_order" class="block text-sm font-medium text-slate-700 mb-1.5">Urutan</label>
                        <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-transparent transition-all duration-200">
                        @error('sort_order')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                    <div class="flex items-end pb-1">
                        <label class="flex items-center gap-3 cursor-pointer select-none">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                                   class="w-5 h-5 text-rose-500 bg-gray-100 border-gray-300 rounded focus:ring-rose-400 focus:ring-2 cursor-pointer">
                            <span class="text-sm font-medium text-slate-700">Aktif</span>
                        </label>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('admin.frames.index') }}"
                       class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-gray-100 rounded-2xl hover:bg-gray-200 transition-colors">Batal</a>
                    <button type="submit"
                            class="px-6 py-2.5 bg-gradient-to-r from-rose-500 to-pink-500 text-white text-sm font-semibold rounded-2xl shadow-lg shadow-rose-500/25 hover:shadow-rose-500/40 hover:from-rose-600 hover:to-pink-600 transition-all duration-200">
                        Simpan Frame
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const input = document.getElementById('frame_image');
    const preview = document.getElementById('frame-preview');
    const empty = document.getElementById('frame-preview-empty');
    input.addEventListener('change', () => {
        const file = input.files[0];
        if (!file) return;
        const url = URL.createObjectURL(file);
        preview.src = url;
        preview.classList.remove('hidden');
        empty.classList.add('hidden');
    });

    const sizeSelect = document.getElementById('size_id');
    const templateLink = document.querySelector('[data-template-link]');
    const dimension = document.querySelector('[data-size-dimension]');
    const syncSize = () => {
        const option = sizeSelect.options[sizeSelect.selectedIndex];
        if (!option) return;
        if (templateLink) templateLink.href = option.dataset.template || '#';
        if (dimension) dimension.textContent = option.dataset.dimension ? 'Rasio wajib: ' + option.dataset.dimension : '';
    };
    if (sizeSelect) {
        sizeSelect.addEventListener('change', syncSize);
        syncSize();
    }
</script>
@endsection