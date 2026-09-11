@extends('layouts.admin')

@section('title', 'Tambah Frame - Photobooth Admin')
@section('page-title', 'Tambah Frame')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.frames.index') }}"
           class="inline-flex items-center gap-1 px-3 py-2 text-slate-600 text-sm font-medium rounded-lg hover:bg-gray-100 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali
        </a>
    </div>

    {{-- Form Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="font-heading text-lg font-bold text-slate-800">Form Tambah Frame</h2>
        </div>
        <div class="p-6">
            <form action="{{ route('admin.frames.store') }}" method="POST" class="space-y-5">
                @csrf

                {{-- Name --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 mb-1.5">Nama Frame <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-transparent transition-all duration-200 @error('name') border-red-300 focus:ring-red-400 @enderror"
                           placeholder="Contoh: Sunset Vibes">
                    @error('name')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Category --}}
                <div>
                    <label for="category" class="block text-sm font-medium text-slate-700 mb-1.5">Kategori</label>
                    <input type="text" id="category" name="category" value="{{ old('category') }}"
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-transparent transition-all duration-200 @error('category') border-red-300 focus:ring-red-400 @enderror"
                           placeholder="Contoh: Casual, Formal, Fun">
                    @error('category')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Color Class --}}
                <div>
                    <label for="color_class" class="block text-sm font-medium text-slate-700 mb-1.5">Kelas Warna (Tailwind gradient)</label>
                    <input type="text" id="color_class" name="color_class" value="{{ old('color_class') }}"
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-slate-800 placeholder-slate-400 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-transparent transition-all duration-200 @error('color_class') border-red-300 focus:ring-red-400 @enderror"
                           placeholder="contoh: from-pink-200 via-rose-100 to-violet-200">
                    @error('color_class')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Border Class --}}
                <div>
                    <label for="border_class" class="block text-sm font-medium text-slate-700 mb-1.5">Kelas Border</label>
                    <input type="text" id="border_class" name="border_class" value="{{ old('border_class') }}"
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-slate-800 placeholder-slate-400 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-transparent transition-all duration-200 @error('border_class') border-red-300 focus:ring-red-400 @enderror"
                           placeholder="contoh: border-rose-300">
                    @error('border_class')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Accent Class --}}
                <div>
                    <label for="accent_class" class="block text-sm font-medium text-slate-700 mb-1.5">Kelas Aksen (warna teks)</label>
                    <input type="text" id="accent_class" name="accent_class" value="{{ old('accent_class') }}"
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-slate-800 placeholder-slate-400 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-transparent transition-all duration-200 @error('accent_class') border-red-300 focus:ring-red-400 @enderror"
                           placeholder="contoh: text-rose-500">
                    @error('accent_class')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Caption --}}
                <div>
                    <label for="caption" class="block text-sm font-medium text-slate-700 mb-1.5">Caption</label>
                    <textarea id="caption" name="caption" rows="3"
                              class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-transparent transition-all duration-200 resize-none @error('caption') border-red-300 focus:ring-red-400 @enderror"
                              placeholder="Teks caption pada frame">{{ old('caption') }}</textarea>
                    @error('caption')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    {{-- Sort Order --}}
                    <div>
                        <label for="sort_order" class="block text-sm font-medium text-slate-700 mb-1.5">Urutan</label>
                        <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-transparent transition-all duration-200 @error('sort_order') border-red-300 focus:ring-red-400 @enderror">
                        @error('sort_order')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Is Active --}}
                    <div class="flex items-end pb-1">
                        <label class="flex items-center gap-3 cursor-pointer select-none">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                                   class="w-5 h-5 text-rose-500 bg-gray-100 border-gray-300 rounded focus:ring-rose-400 focus:ring-2 cursor-pointer">
                            <span class="text-sm font-medium text-slate-700">Aktif</span>
                        </label>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="flex items-center gap-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('admin.frames.index') }}"
                       class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                            class="px-6 py-2.5 bg-gradient-to-r from-rose-500 to-pink-500 text-white text-sm font-semibold rounded-xl shadow-lg shadow-rose-500/25 hover:shadow-rose-500/40 hover:from-rose-600 hover:to-pink-600 transition-all duration-200">
                        Simpan Frame
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
