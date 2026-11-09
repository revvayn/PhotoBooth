@extends('layouts.admin')

@section('title', 'Dashboard - '.\App\Support\Brand::get()['name'].' Admin')
@section('page-title', 'Dashboard')

@php
    $rp = fn ($n) => 'Rp. '.number_format($n, 0, ',', '.');
    $pct = fn ($part, $total) => $total > 0 ? round($part / $total * 100) : 0;
@endphp

@section('content')
<div class="space-y-6">
    {{-- Kartu statistik utama --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
        <div class="group bg-white rounded-2xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Pendapatan Hari Ini</p>
                    <p class="text-2xl xl:text-3xl font-bold text-slate-800 mt-2 tabular-nums">{{ $rp($stats['revenue_today']) }}</p>
                    <p class="text-xs text-slate-400 mt-1">{{ $stats['count_today'] }} transaksi &middot; {{ number_format($stats['revenue_today'] > 0 ? $stats['revenue_today'] / max(1,$stats['count_today']) : 0, 0, ',', '.') }} rata-rata</p>
                </div>
                <span class="w-11 h-11 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center">
                    <x-icon name="card" class="w-5 h-5" />
                </span>
            </div>
        </div>

        <div class="group bg-white rounded-2xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Pendapatan</p>
                    <p class="text-2xl xl:text-3xl font-bold text-slate-800 mt-2 tabular-nums">{{ $rp($stats['revenue_total']) }}</p>
                    <p class="text-xs text-slate-400 mt-1">{{ $stats['count_total'] }} transaksi &middot; {{ $stats['photos_total'] }} foto</p>
                </div>
                <span class="w-11 h-11 rounded-xl bg-violet-50 text-violet-500 flex items-center justify-center">
                    <x-icon name="bag" class="w-5 h-5" />
                </span>
            </div>
        </div>

        <div class="group bg-white rounded-2xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Rata-rata Transaksi</p>
                    <p class="text-2xl xl:text-3xl font-bold text-slate-800 mt-2 tabular-nums">{{ $rp($stats['avg_ticket']) }}</p>
                    <p class="text-xs text-slate-400 mt-1">{{ $stats['avg_photos'] }} foto/transaksi &middot; puncak {{ $stats['peak_day'] }}</p>
                </div>
                <span class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center">
                    <x-icon name="photo" class="w-5 h-5" />
                </span>
            </div>
        </div>

        <div class="group bg-white rounded-2xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Frame Aktif</p>
                    <p class="text-2xl xl:text-3xl font-bold text-slate-800 mt-2">{{ $framesActive }}<span class="text-lg font-semibold text-slate-400"> / {{ $framesTotal }}</span></p>
                    <a href="{{ route('admin.frames.index') }}" class="text-xs text-rose-500 hover:text-rose-600 font-medium">Kelola frame &rarr;</a>
                </div>
                <span class="w-11 h-11 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center">
                    <x-icon name="photo" class="w-5 h-5" />
                </span>
            </div>
        </div>
    </div>

    {{-- Baris kedua: metode + voucher + chart --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Metode pembayaran --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-heading text-sm font-bold text-slate-800 mb-4">Per Metode Pembayaran</h3>
            @php
                $methods = [
                    ['label' => 'QRIS', 'count' => $stats['qris_count'], 'total' => $stats['qris_total'], 'color' => 'violet', 'icon' => 'card'],
                    ['label' => 'Voucher', 'count' => $stats['voucher_count'], 'total' => $stats['voucher_total'], 'color' => 'emerald', 'icon' => 'bag'],
                    ['label' => 'Cash', 'count' => $stats['cash_count'], 'total' => $stats['cash_total'], 'color' => 'amber', 'icon' => 'cash'],
                ];
            @endphp
            <div class="space-y-4">
                @foreach ($methods as $m)
                    @php($share = $pct($m['total'], $stats['revenue_total']))
                    <div>
                        <div class="flex items-center justify-between text-sm mb-1.5">
                            <span class="flex items-center gap-2 font-medium text-slate-700">
                                <span class="w-7 h-7 rounded-lg bg-{{ $m['color'] }}-50 text-{{ $m['color'] }}-500 flex items-center justify-center">
                                    <x-icon name="{{ $m['icon'] }}" class="w-4 h-4" />
                                </span>
                                {{ $m['label'] }}
                                <span class="text-xs text-slate-400">({{ $m['count'] }})</span>
                            </span>
                            <span class="font-semibold text-slate-800 tabular-nums">{{ $rp($m['total']) }}</span>
                        </div>
                        <div class="h-2 rounded-full bg-gray-100 overflow-hidden">
                            <div class="h-full rounded-full bg-{{ $m['color'] }}-500 transition-all" style="width: {{ $share }}%"></div>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">{{ $share }}% dari total</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Voucher summary --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-heading text-sm font-bold text-slate-800">Voucher</h3>
                <a href="{{ route('admin.vouchers.index') }}" class="text-xs text-rose-500 hover:text-rose-600 font-medium">Kelola &rarr;</a>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-xl bg-gray-50 p-3">
                    <p class="text-[11px] text-slate-500">Total</p>
                    <p class="text-xl font-bold text-slate-800 mt-0.5">{{ $voucherStats['total'] }}</p>
                </div>
                <div class="rounded-xl bg-emerald-50 p-3">
                    <p class="text-[11px] text-emerald-600">Aktif</p>
                    <p class="text-xl font-bold text-emerald-700 mt-0.5">{{ $voucherStats['active'] }}</p>
                </div>
                <div class="rounded-xl bg-blue-50 p-3">
                    <p class="text-[11px] text-blue-600">Cash</p>
                    <p class="text-xl font-bold text-blue-700 mt-0.5">{{ $voucherStats['cash'] }}</p>
                </div>
                <div class="rounded-xl bg-violet-50 p-3">
                    <p class="text-[11px] text-violet-600">Event</p>
                    <p class="text-xl font-bold text-violet-700 mt-0.5">{{ $voucherStats['event'] }}</p>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-100 text-xs text-slate-500 flex justify-between">
                <span>Total pemakaian</span>
                <span class="font-semibold text-slate-700">{{ $voucherStats['used'] }} kali</span>
            </div>
        </div>

        {{-- Grafik 7 hari --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-heading text-sm font-bold text-slate-800">7 Hari Terakhir</h3>
                <span class="text-xs font-semibold text-slate-500 tabular-nums">{{ $rp($week->sum('revenue')) }}</span>
            </div>
            <div class="flex items-end gap-2 h-32">
                @foreach ($week as $d)
                    <div class="flex-1 flex flex-col items-center justify-end h-full gap-1 min-w-0 group" title="{{ $d['date'] }}: {{ $rp($d['revenue']) }} ({{ $d['count'] }} trx)">
                        <div class="w-full rounded-t-lg {{ $d['is_today'] ? 'bg-rose-500' : 'bg-rose-200' }} group-hover:bg-rose-400 transition-colors"
                             style="height: {{ $d['revenue'] > 0 ? max(6, round($d['revenue'] / $weekMax * 100)) : 3 }}%"></div>
                    </div>
                @endforeach
            </div>
            <div class="flex gap-2 mt-2">
                @foreach ($week as $d)
                    <div class="flex-1 text-center min-w-0">
                        <p class="text-[10px] font-semibold {{ $d['is_today'] ? 'text-rose-600' : 'text-slate-400' }}">{{ $d['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Pembayaran terbaru --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="font-heading text-lg font-bold text-slate-800">Pembayaran Terbaru</h2>
            <a href="{{ route('admin.logs.index') }}" class="text-sm text-rose-500 hover:text-rose-600 font-medium">Lihat semua &rarr;</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/80">
                        <th class="text-left px-6 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Waktu</th>
                        <th class="text-left px-6 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Antrian</th>
                        <th class="text-left px-6 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Metode</th>
                        <th class="text-left px-6 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Frame</th>
                        <th class="text-right px-6 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($recent as $p)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-6 py-3 text-slate-600 whitespace-nowrap text-xs">{{ $p->created_at->format('d M Y H:i') }}</td>
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