@extends('layouts.admin')

@section('title', 'Dashboard - Photobooth Admin')
@section('page-title', 'Dashboard')

@php
    $rp = fn ($n) => 'Rp. '.number_format($n, 0, ',', '.');
@endphp

@section('content')
<div class="space-y-6">
    {{-- Kartu statistik --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm font-medium text-slate-500">Pendapatan Hari Ini</p>
            <p class="text-3xl font-bold text-slate-800 mt-1 tabular-nums">{{ $rp($stats['revenue_today']) }}</p>
            <p class="text-xs text-slate-400 mt-1">{{ $stats['count_today'] }} transaksi</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm font-medium text-slate-500">Total Pendapatan</p>
            <p class="text-3xl font-bold text-slate-800 mt-1 tabular-nums">{{ $rp($stats['revenue_total']) }}</p>
            <p class="text-xs text-slate-400 mt-1">{{ $stats['count_total'] }} transaksi &middot; {{ $stats['photos_total'] }} foto</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm font-medium text-slate-500">Per Metode</p>
            <div class="mt-2 space-y-1.5 text-sm">
                <p class="flex justify-between"><span class="text-slate-500">QRIS <span class="text-xs text-slate-400">({{ $stats['qris_count'] }})</span></span><span class="font-semibold text-slate-800 tabular-nums">{{ $rp($stats['qris_total']) }}</span></p>
                <p class="flex justify-between"><span class="text-slate-500">Voucher <span class="text-xs text-slate-400">({{ $stats['voucher_count'] }})</span></span><span class="font-semibold text-slate-800 tabular-nums">{{ $rp($stats['voucher_total']) }}</span></p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm font-medium text-slate-500">Frame Aktif</p>
            <p class="text-3xl font-bold text-slate-800 mt-1">{{ $framesActive }}<span class="text-lg font-semibold text-slate-400"> / {{ $framesTotal }}</span></p>
            <a href="{{ route('admin.frames.index') }}" class="text-xs text-rose-500 hover:text-rose-600 font-medium">Kelola frame &rarr;</a>
        </div>
    </div>

    {{-- Grafik 7 hari --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="font-heading text-lg font-bold text-slate-800">Pendapatan 7 Hari Terakhir</h2>
            <span class="text-xs text-slate-400">{{ $rp($week->sum('revenue')) }} total</span>
        </div>
        <div class="p-6">
            <div class="flex items-end gap-3 h-44">
                @foreach ($week as $d)
                    <div class="flex-1 flex flex-col items-center justify-end h-full gap-1.5 min-w-0" title="{{ $d['date'] }}: {{ $rp($d['revenue']) }} ({{ $d['count'] }} transaksi)">
                        <span class="text-[11px] font-semibold text-slate-500 tabular-nums truncate max-w-full">{{ $d['revenue'] > 0 ? number_format($d['revenue'] / 1000, 0, ',', '.').'rb' : '' }}</span>
                        <div class="w-full rounded-t-lg {{ $d['is_today'] ? 'bg-rose-500' : 'bg-rose-200' }}"
                             style="height: {{ $d['revenue'] > 0 ? max(4, round($d['revenue'] / $weekMax * 100)) : 2 }}%"></div>
                    </div>
                @endforeach
            </div>
            <div class="flex gap-3 mt-2">
                @foreach ($week as $d)
                    <div class="flex-1 text-center min-w-0">
                        <p class="text-xs font-semibold {{ $d['is_today'] ? 'text-rose-600' : 'text-slate-500' }}">{{ $d['label'] }}</p>
                        <p class="text-[11px] text-slate-400">{{ $d['date'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Pembayaran terbaru --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="font-heading text-lg font-bold text-slate-800">Pembayaran Terbaru</h2>
            <a href="{{ route('admin.logs.index') }}" class="text-sm text-rose-500 hover:text-rose-600 font-medium">Lihat semua &rarr;</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="text-left px-6 py-3 font-semibold text-slate-600">Waktu</th>
                        <th class="text-left px-6 py-3 font-semibold text-slate-600">Antrian</th>
                        <th class="text-left px-6 py-3 font-semibold text-slate-600">Metode</th>
                        <th class="text-left px-6 py-3 font-semibold text-slate-600">Frame</th>
                        <th class="text-right px-6 py-3 font-semibold text-slate-600">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($recent as $p)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-3 text-slate-600 whitespace-nowrap">{{ $p->created_at->format('d M Y H:i') }}</td>
                            <td class="px-6 py-3 font-semibold text-slate-700">{{ $p->queue_number ?? '-' }}</td>
                            <td class="px-6 py-3">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $p->payment_method === 'qris' ? 'bg-violet-100 text-violet-700' : ($p->payment_method === 'voucher' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700') }}">{{ strtoupper($p->payment_method) }}</span>
                            </td>
                            <td class="px-6 py-3 text-slate-500 text-xs max-w-xs truncate">{{ $p->payment_items_label }}</td>
                            <td class="px-6 py-3 text-right font-semibold text-slate-800 tabular-nums whitespace-nowrap">{{ $rp($p->payment_total) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400">Belum ada pembayaran tercatat</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
