@extends('layouts.print')

@php
    $brand = \App\Support\Brand::get();
@endphp

@section('title', 'Cetak Invoice - '.$brand['name'].' Admin')

@section('content')
<div class="flex items-center justify-between mb-6 no-page-break">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">INVOICE</h1>
        <p class="text-sm text-slate-500">{{ $voucher->name }} &middot; {{ $brand['name'] }} Kiosk</p>
    </div>
    <div class="text-right">
        <p class="text-sm font-semibold text-slate-700">No. Invoice</p>
        <p class="text-mono font-bold" style="color: var(--brand-primary);">{{ $voucher->code }}</p>
        <p class="text-xs text-slate-500">{{ now()->format('d M Y H:i') }}</p>
    </div>
</div>

<!-- Info Event/Voucher -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6 no-page-break">
    <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
        <p class="text-xs font-bold uppercase text-slate-400 mb-2">Tipe Voucher</p>
        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full {{ $voucher->type_badge_class }} text-xs font-semibold">
            {{ $voucher->type_label }}
        </span>
    </div>
    @if ($voucher->isEvent())
        <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
            <p class="text-xs font-bold uppercase text-slate-400 mb-2">Nama Event</p>
            <p class="text-sm font-semibold text-slate-800">{{ $voucher->event_name }}</p>
        </div>
        <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
            <p class="text-xs font-bold uppercase text-slate-400 mb-2">Tanggal Event</p>
            <p class="text-sm font-semibold text-slate-800">{{ $voucher->event_date ? $voucher->event_date->format('d M Y') : '-' }}</p>
        </div>
        <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
            <p class="text-xs font-bold uppercase text-slate-400 mb-2">Jumlah Orang</p>
            <p class="text-sm font-semibold text-slate-800">{{ $voucher->number_of_people }} orang</p>
        </div>
        <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
            <p class="text-xs font-bold uppercase text-slate-400 mb-2">Foto per Voucher</p>
            <p class="text-sm font-semibold text-slate-800">{{ $voucher->photos_per_voucher }} foto</p>
        </div>
    @endif
</div>

<!-- Tabel Items -->
<div class="overflow-x-auto mb-6 no-page-break">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="border-b border-slate-200">
                <th class="px-4 py-3 text-xs font-bold uppercase text-slate-400">Frame</th>
                <th class="px-4 py-3 text-xs font-bold uppercase text-slate-400 text-right">Qty</th>
                <th class="px-4 py-3 text-xs font-bold uppercase text-slate-400 text-right">Harga</th>
                <th class="px-4 py-3 text-xs font-bold uppercase text-slate-400 text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
                <tr class="border-b border-slate-100">
                    <td class="px-4 py-3 text-sm text-slate-700">{{ $item['name'] }}</td>
                    <td class="px-4 py-3 text-sm text-slate-700 text-right">{{ $item['qty'] }}</td>
                    <td class="px-4 py-3 text-sm text-slate-700 text-right">Rp. {{ number_format($item['price'], 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-sm font-semibold text-slate-800 text-right">Rp. {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Total -->
<div class="flex justify-end no-page-break">
    <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 inline-block">
        <table class="text-left">
            <tr>
                <td class="px-4 py-2 text-sm text-slate-500">Biaya per Orang</td>
                <td class="px-4 py-2 text-sm font-semibold text-slate-800 text-right">Rp. {{ number_format($costPerPerson, 0, ',', '.') }}</td>
            </tr>
            @if ($voucher->isEvent())
                <tr>
                    <td class="px-4 py-2 text-sm text-slate-500">Jumlah Orang</td>
                    <td class="px-4 py-2 text-sm font-semibold text-slate-800 text-right">{{ $voucher->number_of_people }} orang</td>
                </tr>
            @endif
            @if (($voucher->discount ?? 0) > 0)
                <tr>
                    <td class="px-4 py-2 text-sm text-slate-500">Diskon</td>
                    <td class="px-4 py-2 text-sm font-semibold text-emerald-600 text-right">{{ $voucher->discount }}%</td>
                </tr>
            @endif
            <tr class="border-t border-slate-200 pt-2">
                <td class="px-4 py-2 text-sm font-bold text-slate-800">Total Biaya</td>
                <td class="px-4 py-2 text-sm font-bold text-right" style="color: var(--brand-primary);">Rp. {{ number_format($voucher->final_cost, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>
</div>

<div class="mt-8 text-center no-page-break">
    <p class="text-xs text-slate-400 uppercase tracking-wider">Ini adalah dokumen resmi dari {{ $brand['name'] }} Kiosk</p>
</div>
@endsection
