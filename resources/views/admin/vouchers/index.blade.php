@extends('layouts.admin')

@section('title', 'Voucher - '.\App\Support\Brand::get()['name'].' Admin')
@section('page-title', 'Voucher')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="font-heading text-lg font-bold text-slate-800">Voucher</h2>
            <p class="text-sm text-slate-500">Kode voucher untuk pelanggan yang sudah membayar di awal.</p>
        </div>
        <a href="{{ route('admin.vouchers.create', ['type' => $type === 'cash' || $type === 'event' ? $type : 'cash']) }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-rose-500 text-white text-sm font-semibold shadow-md shadow-rose-200 hover:bg-rose-600 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat Voucher
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="text-left px-6 py-3 font-semibold text-slate-600">Kode</th>
                        <th class="text-left px-6 py-3 font-semibold text-slate-600">Tipe</th>
                        <th class="text-left px-6 py-3 font-semibold text-slate-600">Nama</th>
                        <th class="text-left px-6 py-3 font-semibold text-slate-600">Isi Voucher</th>
                        <th class="text-left px-6 py-3 font-semibold text-slate-600">Pemakaian</th>
                        <th class="text-left px-6 py-3 font-semibold text-slate-600">Berlaku Sampai</th>
                        <th class="text-left px-6 py-3 font-semibold text-slate-600">Status</th>
                        <th class="text-right px-6 py-3 font-semibold text-slate-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($vouchers as $voucher)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-lg bg-slate-900 text-white px-2.5 py-1 text-xs font-bold tracking-wider">{{ $voucher->code }}</span>
                                @if ($voucher->isEvent())
                                    <span class="ml-2 inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">Event</span>
                                @else
                                    <span class="ml-2 inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Cash</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-semibold text-slate-800">{{ $voucher->name }}</p>
                                @if ($voucher->description)
                                    <p class="text-xs text-slate-400 mt-0.5 max-w-[220px] truncate">{{ $voucher->description }}</p>
                                @endif
                                @if ($voucher->isEvent() && $voucher->event_date)
                                    <p class="text-xs text-slate-400 mt-0.5">{{ $voucher->event_date->format('d M Y') }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-500 text-xs max-w-[260px]">
                                <p>{{ $voucher->items_label }}</p>
                                <p class="text-slate-400 mt-0.5">{{ $voucher->photo_count }} foto total</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-semibold text-slate-700">{{ $voucher->used_count }} / {{ $voucher->max_uses }}</p>
                                <p class="text-xs text-slate-400">{{ $voucher->remaining_uses }} tersisa</p>
                            </td>
                            <td class="px-6 py-4 text-slate-500">
                                {{ $voucher->expires_at ? $voucher->expires_at->format('d M Y') : 'Tanpa batas' }}
                            </td>
                            <td class="px-6 py-4">
                                @if ($voucher->isUsable())
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Aktif</span>
                                @elseif (! $voucher->is_active)
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">Nonaktif</span>
                                @elseif ($voucher->isExpired())
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">Kedaluwarsa</span>
                                @else
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-700">Habis</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.vouchers.edit', $voucher) }}" class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 text-xs font-semibold hover:bg-slate-200 transition">Edit</a>
                                    <a href="{{ route('admin.vouchers.print-voucher', $voucher) }}" class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-600 text-xs font-semibold hover:bg-blue-100 transition" title="Cetak Voucher" target="_blank">
                                        <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2z"/></svg>
                                        Cetak
                                    </a>
                                    <a href="{{ route('admin.vouchers.print-invoice', $voucher) }}" class="px-3 py-1.5 rounded-lg bg-green-50 text-green-600 text-xs font-semibold hover:bg-green-100 transition" title="Cetak Invoice" target="_blank">
                                        <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v1m-6 0a2 2 0 002-2h2a2 2 0 002 2v1m-6 0a2 2 0 012-2h2a2 2 0 012 2v1m-6 0a2 2 0 002-2h2a2 2 0 002 2v1m-6 0a2 2 0 012-2h2a2 2 0 012 2v1m-6 0a2 2 0 002-2h2a2 2 0 002 2v1"/></svg>
                                        Invoice
                                    </a>
                                    <form method="POST" action="{{ route('admin.vouchers.destroy', $voucher) }}" onsubmit="return confirm('Hapus voucher {{ $voucher->code }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-50 text-red-600 text-xs font-semibold hover:bg-red-100 transition">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-14 text-center text-slate-400">
                                <p class="font-semibold text-slate-500">Belum ada voucher{{ $type === 'event' ? ' event' : ($type === 'cash' ? ' cash' : '') }}</p>
                                <p class="text-xs mt-1">Buat voucher pertama di halaman tambah voucher.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($vouchers->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $vouchers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
