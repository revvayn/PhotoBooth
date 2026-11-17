@extends('layouts.admin')

@section('title', 'Ukuran & Harga - '.\App\Support\Brand::get()['name'].' Admin')
@section('page-title', 'Ukuran & Harga')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="font-heading text-xl font-bold text-slate-800">Ukuran & Harga Cetak</h2>
            <p class="text-sm text-slate-500 mt-0.5">Harga dasar mengikuti ukuran. Frame otomatis memakai harga ukurannya, dan bisa punya harga khusus bila diisi.</p>
        </div>
        <a href="{{ route('admin.sizes.create') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-rose-500 text-white text-sm font-semibold rounded-2xl shadow-sm hover:bg-rose-600 transition-colors self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Ukuran
        </a>
    </div>

    @if (session('error'))
        <div class="rounded-2xl bg-red-50 text-red-700 text-sm px-4 py-3 ring-1 ring-red-100">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="text-left px-6 py-3 font-semibold text-slate-600 w-16">No</th>
                        <th class="text-left px-6 py-3 font-semibold text-slate-600">Ukuran</th>
                        <th class="text-left px-6 py-3 font-semibold text-slate-600">Dimensi</th>
                        <th class="text-left px-6 py-3 font-semibold text-slate-600">Harga Dasar</th>
                        <th class="text-center px-6 py-3 font-semibold text-slate-600">Frame</th>
                        <th class="text-center px-6 py-3 font-semibold text-slate-600">Status</th>
                        <th class="text-center px-6 py-3 font-semibold text-slate-600 w-44">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($sizes as $size)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-3 text-slate-500">{{ $loop->iteration }}</td>
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex items-center justify-center rounded-lg bg-slate-50 ring-1 ring-slate-200 shrink-0"
                                          style="width: 36px; height: 36px;">
                                        <span class="block rounded-[3px] border-2 border-rose-300 bg-white"
                                              style="width: {{ $size->width_cm >= $size->height_cm ? 26 : max(10, (int) round(26 * $size->width_cm / max($size->height_cm, 0.1))) }}px;
                                                     height: {{ $size->height_cm > $size->width_cm ? 26 : max(10, (int) round(26 * $size->height_cm / max($size->width_cm, 0.1))) }}px;"></span>
                                    </span>
                                    <div>
                                        <p class="font-semibold text-slate-800">{{ $size->name }}</p>
                                        <p class="text-[11px] text-slate-400">Urutan {{ $size->sort_order }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-slate-600">{{ $size->dimension_label }}</td>
                            <td class="px-6 py-3 font-semibold text-rose-600 tabular-nums">Rp {{ number_format($size->price, 0, ',', '.') }}</td>
                            <td class="px-6 py-3 text-center">
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">{{ $size->frames_count }}</span>
                            </td>
                            <td class="px-6 py-3 text-center">
                                @if ($size->is_active)
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Aktif</span>
                                @else
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.sizes.template', $size) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 bg-slate-50 text-slate-600 text-xs font-medium rounded-lg hover:bg-slate-100 transition-colors"
                                       title="Unduh template PNG">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        Template
                                    </a>
                                    <a href="{{ route('admin.sizes.edit', $size) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-50 text-blue-600 text-xs font-medium rounded-lg hover:bg-blue-100 transition-colors">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.sizes.destroy', $size) }}" method="POST"
                                          onsubmit="return confirm('Hapus ukuran {{ $size->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-50 text-red-600 text-xs font-medium rounded-lg hover:bg-red-100 transition-colors">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-slate-400">
                                Belum ada ukuran. Tambahkan ukuran pertama untuk mulai mengatur harga.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-2xl bg-amber-50/70 ring-1 ring-amber-100 px-5 py-4 text-sm text-amber-800">
        <p class="font-semibold mb-1">Cara kerja harga</p>
        <p class="text-amber-700 leading-relaxed">Harga yang dibayar pelanggan = harga frame bila diisi, atau harga ukuran bila harga frame dikosongkan. Saat mengunggah frame, rasio PNG harus sama dengan rasio ukuran yang dipilih (toleransi 2,5%). Gunakan tombol <strong>Template</strong> untuk mendapatkan kanvas PNG dengan rasio yang tepat.</p>
    </div>
</div>
@endsection
