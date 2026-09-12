@extends('layouts.admin')

@section('title', 'Edit Frame - Photobooth Admin')
@section('page-title', 'Edit Frame')

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

    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="font-heading text-lg font-bold text-slate-800">Edit: {{ $frame->name }}</h2>
        </div>
        <div class="p-6">
            <form action="{{ route('admin.frames.update', $frame) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700 mb-1.5">Nama Frame <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name', $frame->name) }}" required
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-transparent transition-all duration-200 @error('name') border-red-300 focus:ring-red-400 @enderror"
                               placeholder="Contoh: Sunset Vibes">
                        @error('name')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="category" class="block text-sm font-medium text-slate-700 mb-1.5">Kategori</label>
                        <input type="text" id="category" name="category" value="{{ old('category', $frame->category) }}"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-transparent transition-all duration-200 @error('category') border-red-300 focus:ring-red-400 @enderror"
                               placeholder="Contoh: Casual, Formal, Fun">
                        @error('category')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Gambar Frame (PNG)</label>
                    <div class="mt-1 flex items-start gap-4">
                        <div class="w-28 h-36 bg-white ring-1 ring-slate-200 rounded-xl overflow-hidden flex items-center justify-center shrink-0">
                            <img src="{{ $frame->image_url ?: '' }}" class="{{ $frame->image_url ? 'w-full h-full object-contain' : 'hidden' }}" id="frame-preview" alt="Pratinjau frame">
                            <span class="text-slate-300 text-xs text-center px-2 {{ $frame->image_url ? 'hidden' : '' }}" id="frame-preview-empty">Belum ada gambar</span>
                        </div>
                        <div class="flex-1">
                            @if ($frame->photo_count)
                                <p class="text-sm text-emerald-600 font-medium mb-2">✓ {{ $frame->photo_count }} area foto terdeteksi pada frame ini</p>
                            @endif
                            <input type="file" id="frame_image" name="frame_image" accept="image/png"
                                   class="w-full text-sm text-slate-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-rose-50 file:text-rose-600 hover:file:bg-rose-100 transition">
                            <p class="mt-2 text-xs text-slate-400">
                                Kosongkan jika tidak ingin mengubah gambar. Upload PNG baru akan menghitung ulang area foto transparan.
                            </p>
                            @error('frame_image')
                                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div>
                    <label for="caption" class="block text-sm font-medium text-slate-700 mb-1.5">Caption</label>
                    <input type="text" id="caption" name="caption" value="{{ old('caption', $frame->caption) }}"
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-transparent transition-all duration-200"
                           placeholder="Teks opsional pada frame">
                    @error('caption')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="sort_order" class="block text-sm font-medium text-slate-700 mb-1.5">Urutan</label>
                        <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $frame->sort_order) }}" min="0"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-transparent transition-all duration-200">
                        @error('sort_order')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-end pb-1">
                        <label class="flex items-center gap-3 cursor-pointer select-none">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $frame->is_active) ? 'checked' : '' }}
                                   class="w-5 h-5 text-rose-500 bg-gray-100 border-gray-300 rounded focus:ring-rose-400 focus:ring-2 cursor-pointer">
                            <span class="text-sm font-medium text-slate-700">Aktif</span>
                        </label>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('admin.frames.index') }}"
                       class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors">Batal</a>
                    <button type="submit"
                            class="px-6 py-2.5 bg-gradient-to-r from-rose-500 to-pink-500 text-white text-sm font-semibold rounded-xl shadow-lg shadow-rose-500/25 hover:shadow-rose-500/40 hover:from-rose-600 hover:to-pink-600 transition-all duration-200">
                        Update Frame
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
</script>
@endsection