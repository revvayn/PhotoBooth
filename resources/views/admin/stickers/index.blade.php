@extends('layouts.admin')

@section('title', 'Kelola Stiker - '.\App\Support\Brand::get()['name'].' Admin')
@section('page-title', 'Kelola Stiker')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="font-heading text-xl font-bold text-slate-800">Kelola Stiker</h2>
            <p class="text-sm text-slate-500 mt-0.5">Stiker dan emoji ini muncul di editor foto pelanggan setelah sesi foto.</p>
        </div>
        <a href="{{ route('admin.stickers.create') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-rose-500 text-white text-sm font-semibold rounded-2xl shadow-sm hover:bg-rose-600 transition-colors self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Stiker
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="text-left px-6 py-3 font-semibold text-slate-600 w-16">No</th>
                        <th class="text-left px-6 py-3 font-semibold text-slate-600">Stiker</th>
                        <th class="text-left px-6 py-3 font-semibold text-slate-600">Tipe</th>
                        <th class="text-center px-6 py-3 font-semibold text-slate-600">Urutan</th>
                        <th class="text-center px-6 py-3 font-semibold text-slate-600">Status</th>
                        <th class="text-center px-6 py-3 font-semibold text-slate-600 w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($stickers as $sticker)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-3 text-slate-500">{{ $loop->iteration }}</td>
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-slate-50 ring-1 ring-slate-200 rounded-xl overflow-hidden shrink-0 flex items-center justify-center text-2xl">
                                        @if ($sticker->type === 'image' && $sticker->image_url)
                                            <img src="{{ $sticker->image_url }}" alt="{{ $sticker->name }}" class="w-full h-full object-contain p-1">
                                        @else
                                            <span>{{ $sticker->emoji ?: '—' }}</span>
                                        @endif
                                    </div>
                                    <span class="font-medium text-slate-800">{{ $sticker->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-slate-600">{{ $sticker->type === 'image' ? 'Gambar' : 'Emoji' }}</td>
                            <td class="px-6 py-3 text-center text-slate-500">{{ $sticker->sort_order }}</td>
                            <td class="px-6 py-3 text-center">
                                @if($sticker->is_active)
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Aktif</span>
                                @else
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.stickers.edit', $sticker) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-50 text-blue-600 text-xs font-medium rounded-lg hover:bg-blue-100 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.stickers.destroy', $sticker) }}" method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus stiker ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-50 text-red-600 text-xs font-medium rounded-lg hover:bg-red-100 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="mb-3">Belum ada stiker</p>
                                <a href="{{ route('admin.stickers.create') }}" class="text-rose-500 hover:text-rose-600 font-medium text-sm">
                                    Tambah stiker pertama &rarr;
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
