@extends('layouts.admin')

@section('title', 'Kelola Frame - Photobooth Admin')
@section('page-title', 'Kelola Frame')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <p class="text-slate-500 text-sm">Kelola semua frame photobooth</p>
        <a href="{{ route('admin.frames.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-rose-500 to-pink-500 text-white text-sm font-semibold rounded-xl shadow-lg shadow-rose-500/25 hover:shadow-rose-500/40 hover:from-rose-600 hover:to-pink-600 transition-all duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Frame
        </a>
    </div>

    {{-- Frames Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="text-left px-6 py-3 font-semibold text-slate-600 w-16">No</th>
                        <th class="text-left px-6 py-3 font-semibold text-slate-600">Nama</th>
                        <th class="text-left px-6 py-3 font-semibold text-slate-600">Kategori</th>
                        <th class="text-left px-6 py-3 font-semibold text-slate-600">Warna</th>
                        <th class="text-center px-6 py-3 font-semibold text-slate-600">Status</th>
                        <th class="text-center px-6 py-3 font-semibold text-slate-600 w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($frames as $frame)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-3 text-slate-500">{{ $loop->iteration }}</td>
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-rose-300 to-violet-300 flex items-center justify-center text-white text-xs font-bold">
                                        {{ strtoupper(substr($frame->name, 0, 2)) }}
                                    </div>
                                    <span class="font-medium text-slate-800">{{ $frame->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-slate-600">{{ $frame->category ?? 'Tanpa kategori' }}</td>
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="w-4 h-4 rounded-full bg-gradient-to-br from-rose-200 to-slate-300 border border-gray-200"></span>
                                    <span class="text-slate-600 text-xs">{{ $frame->color_class ?? 'default' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-center">
                                @if($frame->is_active)
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.frames.edit', $frame) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-50 text-blue-600 text-xs font-medium rounded-lg hover:bg-blue-100 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.frames.destroy', $frame) }}" method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus frame ini?')">
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p class="mb-3">Belum ada frame</p>
                                <a href="{{ route('admin.frames.create') }}" class="text-rose-500 hover:text-rose-600 font-medium text-sm">
                                    Tambah frame pertama &rarr;
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($frames, 'links'))
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $frames->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
