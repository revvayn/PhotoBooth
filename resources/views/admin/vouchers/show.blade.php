@extends('layouts.admin')

@section('title', 'Detail Voucher - '.\App\Support\Brand::get()['name'].' Admin')
@section('page-title', 'Detail Voucher')

@section('content')
@php
    $isEvent = $voucher->isEvent();
    $frames = $frames ?? collect();
@endphp
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="font-heading text-lg font-bold text-slate-800">{{ $voucher->name }}</h2>
            <p class="text-sm text-slate-500">{{ $isEvent ? 'Event voucher dengan '.$vouchers->count().' kode.' : 'Voucher cash satuan.' }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if ($isEvent)
                <a href="{{ route('admin.vouchers.edit', $voucher) }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold hover:bg-slate-200 transition">
                    <x-icon name="pencil" class="w-4 h-4" />
                    Edit Event
                </a>
            @else
                <a href="{{ route('admin.vouchers.edit', $voucher) }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold hover:bg-slate-200 transition">
                    <x-icon name="pencil" class="w-4 h-4" />
                    Edit Voucher
                </a>
            @endif
            <a href="{{ route('admin.vouchers.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-100 transition">Kembali</a>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="font-heading text-base font-bold text-slate-800">Informasi {{ $isEvent ? 'Event' : 'Voucher' }}</h3>
            <dl class="mt-4 grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-xs text-slate-400">Tipe</dt>
                    <dd class="mt-0.5 font-semibold text-slate-700">{{ $isEvent ? 'Event' : 'Cash' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-400">Kode dasar</dt>
                    <dd class="mt-0.5 font-semibold text-slate-700">{{ $isEvent ? ($voucher->event_code ?: $voucher->code) : $voucher->code }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs text-slate-400">Nama</dt>
                    <dd class="mt-0.5 font-semibold text-slate-700">{{ $voucher->name }}</dd>
                </div>
                @if ($isEvent)
                    <div>
                        <dt class="text-xs text-slate-400">Nama event</dt>
                        <dd class="mt-0.5 font-semibold text-slate-700">{{ $voucher->event_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-400">Tanggal event</dt>
                        <dd class="mt-0.5 font-semibold text-slate-700">{{ $voucher->event_date ? $voucher->event_date->format('d M Y') : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-400">Jumlah orang</dt>
                        <dd class="mt-0.5 font-semibold text-slate-700">{{ $voucher->number_of_people }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-400">Durasi</dt>
                        <dd class="mt-0.5 font-semibold text-slate-700">{{ (int) $voucher->duration_hours > 0 ? $voucher->duration_hours.' jam' : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-400">Jam berlaku</dt>
                        <dd class="mt-0.5 font-semibold text-slate-700">
                            @if ($voucher->start_time || $voucher->end_time)
                                {{ $voucher->start_time ?: '00:00' }} - {{ $voucher->end_time ?: '23:59' }}
                            @else
                                -
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-400">Biaya per jam</dt>
                        <dd class="mt-0.5 font-semibold text-slate-700">{{ (int) $voucher->cost_per_hour > 0 ? 'Rp. '.number_format($voucher->cost_per_hour, 0, ',', '.') : '-' }}</dd>
                    </div>
                @endif
                <div>
                    <dt class="text-xs text-slate-400">Diskon</dt>
                    <dd class="mt-0.5 font-semibold text-slate-700">{{ (int) $voucher->discount }}%</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-400">Berlaku sampai</dt>
                    <dd class="mt-0.5 font-semibold text-slate-700">{{ $voucher->expires_at ? $voucher->expires_at->format('d M Y') : 'Tanpa batas' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-400">Status</dt>
                    <dd class="mt-0.5 font-semibold text-slate-700">{{ $voucher->isUsable() ? 'Aktif' : ($voucher->isExpired() ? 'Kedaluwarsa' : ($voucher->isExhausted() ? 'Habis' : (($voucher->isEvent() && $voucher->eventWindow()[0] && now()->lt($voucher->eventWindow()[0])) ? 'Terjadwal' : 'Nonaktif'))) }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-400">Total biaya</dt>
                    <dd class="mt-0.5 font-semibold text-slate-700">Rp. {{ number_format($voucher->total_cost, 0, ',', '.') }}</dd>
                </div>
            </dl>

            @if ($voucher->description)
                <p class="mt-4 text-sm text-slate-500">{{ $voucher->description }}</p>
            @endif
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="font-heading text-base font-bold text-slate-800">Frame</h3>
            <div class="mt-4 space-y-2">
                @foreach ($voucher->items ?? [] as $slug => $qty)
                    @php $frame = $frames->get($slug); @endphp
                    <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3 text-sm">
                        <span class="font-semibold text-slate-700">{{ $frame?->name ?? $slug }}</span>
                        <span class="text-slate-500">x{{ (int) $qty }} &middot; {{ $frame?->photo_count ?? 0 }} foto</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-6 py-4">
            <div>
                <h3 class="font-heading text-base font-bold text-slate-800">{{ $isEvent ? 'Daftar Kode Voucher' : 'Voucher' }}</h3>
                <p class="text-xs text-slate-400">{{ $vouchers->count() }} kode.</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200">
                        <th class="text-left px-6 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wider">Kode</th>
                        <th class="text-left px-4 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wider">Status</th>
                        <th class="text-left px-4 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wider">Pemakaian</th>
                        <th class="text-right px-6 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($vouchers as $item)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex rounded-lg bg-slate-900 text-white px-2.5 py-1 text-xs font-bold font-mono tracking-wider">{{ $item->code }}</span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                @if ($item->isUsable())
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Aktif</span>
                                @elseif (! $item->is_active)
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">Nonaktif</span>
                                @elseif ($item->isExpired())
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">Kedaluwarsa</span>
                                @else
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-700">Habis</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-slate-600">
                                {{ $item->used_count }} / {{ $item->max_uses }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-1.5 whitespace-nowrap">
                                    <a href="{{ route('admin.vouchers.print-voucher', $item) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-sky-50 text-sky-600 text-xs font-semibold hover:bg-sky-100 transition" title="Cetak Voucher" target="_blank">
                                        <x-icon name="printer" class="w-3.5 h-3.5" />
                                        Cetak
                                    </a>
                                    <a href="{{ route('admin.vouchers.print-invoice', $item) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-600 text-xs font-semibold hover:bg-emerald-100 transition" title="Cetak Invoice" target="_blank">
                                        <x-icon name="receipt" class="w-3.5 h-3.5" />
                                        Invoice
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection