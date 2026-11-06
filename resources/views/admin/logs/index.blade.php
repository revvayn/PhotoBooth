@extends('layouts.admin')

@section('title', 'Log Pembayaran - Photobooth Admin')
@section('page-title', 'Log Pembayaran')

@php
    $rp = fn ($n) => 'Rp. '.number_format($n, 0, ',', '.');
    $qs = fn (array $extra = []) => array_filter(array_merge(request()->query(), $extra), fn ($v) => $v !== null && $v !== '');
    // Header kolom yang bisa diurutkan; klik lagi untuk membalik arah.
    $sortLink = function (string $col) use ($filters, $qs) {
        $dir = ($filters['sort'] === $col && $filters['dir'] === 'asc') ? 'desc' : 'asc';
        return route('admin.logs.index', $qs(['sort' => $col, 'dir' => $dir, 'page' => null]));
    };
    $arrow = fn (string $col) => $filters['sort'] === $col ? ($filters['dir'] === 'asc' ? '↑' : '↓') : '';
@endphp

@section('content')
<div class="space-y-6">
    {{-- Ringkasan sesuai filter --}}
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-xs font-medium text-slate-500">Transaksi</p>
            <p class="text-2xl font-bold text-slate-800 mt-1">{{ $summary['count'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-xs font-medium text-slate-500">Total Pendapatan</p>
            <p class="text-2xl font-bold text-rose-600 mt-1 tabular-nums">{{ $rp($summary['revenue']) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-xs font-medium text-slate-500">QRIS</p>
            <p class="text-2xl font-bold text-slate-800 mt-1 tabular-nums">{{ $rp($summary['qris']) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-xs font-medium text-slate-500">Voucher</p>
            <p class="text-2xl font-bold text-slate-800 mt-1 tabular-nums">{{ $rp($summary['voucher']) }}</p>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('admin.logs.index') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex flex-wrap items-end gap-3">
        <input type="hidden" name="sort" value="{{ $filters['sort'] }}">
        <input type="hidden" name="dir" value="{{ $filters['dir'] }}">
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Cari antrian</label>
            <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="#1234"
                   class="w-32 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-rose-400">
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Metode</label>
            <select name="metode" class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-rose-400">
                <option value="">Semua</option>
                <option value="qris" @selected($filters['metode'] === 'qris')>QRIS</option>
                <option value="voucher" @selected($filters['metode'] === 'voucher')>Voucher</option>
                <option value="cash" @selected($filters['metode'] === 'cash')>Tunai (lama)</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Dari tanggal</label>
            <input type="date" name="from" value="{{ $filters['from'] }}" class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-rose-400">
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Sampai tanggal</label>
            <input type="date" name="to" value="{{ $filters['to'] }}" class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-rose-400">
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="px-4 py-2 rounded-lg bg-rose-500 text-white text-sm font-semibold hover:bg-rose-600 transition">Terapkan</button>
            <a href="{{ route('admin.logs.index') }}" class="px-4 py-2 rounded-lg text-sm font-medium text-slate-600 hover:bg-gray-100 transition">Reset</a>
        </div>
        <a href="{{ route('admin.logs.export', $qs(['page' => null])) }}"
           class="ml-auto px-4 py-2 rounded-lg ring-1 ring-emerald-200 bg-emerald-50 text-emerald-700 text-sm font-semibold hover:bg-emerald-100 transition">
            Ekspor CSV
        </a>
    </form>

    {{-- Tabel --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="text-left px-6 py-3 font-semibold text-slate-600"><a href="{{ $sortLink('created_at') }}" class="hover:text-rose-600">Waktu {{ $arrow('created_at') }}</a></th>
                        <th class="text-left px-6 py-3 font-semibold text-slate-600"><a href="{{ $sortLink('queue_number') }}" class="hover:text-rose-600">Antrian {{ $arrow('queue_number') }}</a></th>
                        <th class="text-left px-6 py-3 font-semibold text-slate-600"><a href="{{ $sortLink('metode') }}" class="hover:text-rose-600">Metode {{ $arrow('metode') }}</a></th>
                        <th class="text-left px-6 py-3 font-semibold text-slate-600">Frame</th>
                        <th class="text-right px-6 py-3 font-semibold text-slate-600"><a href="{{ $sortLink('total') }}" class="hover:text-rose-600">Total {{ $arrow('total') }}</a></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($logs as $p)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-3 text-slate-600 whitespace-nowrap">
                                <div>{{ $p->created_at->format('d M Y') }}</div>
                                <div class="text-xs text-slate-400">{{ $p->created_at->format('H:i:s') }}</div>
                            </td>
                            <td class="px-6 py-3 font-semibold text-slate-700">{{ $p->queue_number ?? '-' }}</td>
                            <td class="px-6 py-3">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $p->payment_method === 'qris' ? 'bg-violet-100 text-violet-700' : ($p->payment_method === 'voucher' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700') }}">{{ strtoupper($p->payment_method) }}</span>
                            </td>
                            <td class="px-6 py-3 text-slate-500 text-xs">
                                {{ $p->payment_items_label }}
                                @if ($p->payment_photo_count)
                                    <span class="text-slate-400">&middot; {{ $p->payment_photo_count }} foto</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-right font-semibold text-slate-800 tabular-nums whitespace-nowrap">{{ $rp($p->payment_total) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400">Tidak ada pembayaran yang cocok dengan filter</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
