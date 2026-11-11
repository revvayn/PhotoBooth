@extends('layouts.admin')

@section('title', 'Voucher - '.\App\Support\Brand::get()['name'].' Admin')
@section('page-title', 'Voucher')

@section('content')
@php
    $createType = in_array($type, ['cash', 'event'], true) ? $type : 'cash';
    $hasActiveFilters = $filters['q'] !== '' || $filters['type'] !== '' || $filters['status'] !== '' || $filters['from'] !== '' || $filters['to'] !== '';
    $statusLabels = [
        'active' => 'Aktif',
        'inactive' => 'Nonaktif',
        'expired' => 'Kedaluwarsa',
        'exhausted' => 'Habis',
    ];
@endphp

<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="font-heading text-lg font-bold text-slate-800">Voucher</h2>
            <p class="text-sm text-slate-500">Kode voucher untuk pelanggan yang sudah membayar di awal.</p>
        </div>
        <a href="{{ route('admin.vouchers.create', ['type' => $createType]) }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-rose-500 text-white text-sm font-semibold shadow-md shadow-rose-200 hover:bg-rose-600 transition">
            <x-icon name="plus" class="w-4 h-4" />
            Buat Voucher {{ ucfirst($createType) }}
        </a>
    </div>

    {{-- Ringkasan sesuai filter --}}
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <span class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-rose-500 to-violet-500"></span>
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Voucher</p>
            <p class="text-2xl font-bold text-slate-800 mt-2 tabular-nums">{{ $summary['count'] }}</p>
            <p class="text-xs text-slate-400 mt-1">sesuai filter</p>
        </div>
        <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <span class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-400"></span>
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Cash</p>
            <p class="text-2xl font-bold text-slate-800 mt-2 tabular-nums">{{ $summary['cash'] }}</p>
            <p class="text-xs text-slate-400 mt-1">voucher satuan</p>
        </div>
        <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <span class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-sky-500 to-cyan-400"></span>
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Event</p>
            <p class="text-2xl font-bold text-slate-800 mt-2 tabular-nums">{{ $summary['event'] }}</p>
            <p class="text-xs text-slate-400 mt-1">voucher per orang</p>
        </div>
        <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <span class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-amber-400 to-orange-400"></span>
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Aktif</p>
            <p class="text-2xl font-bold text-slate-800 mt-2 tabular-nums">{{ $summary['active'] }}</p>
            <p class="text-xs text-slate-400 mt-1">siap dipakai</p>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('admin.vouchers.index') }}"
          class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm" data-voucher-filter>
        <div class="flex flex-col xl:flex-row xl:items-end gap-3">
            <div class="grid flex-1 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div>
                    <label for="voucher-search" class="block text-xs font-semibold text-slate-500 mb-1.5">Cari voucher</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                            <x-icon name="search" class="w-4 h-4" />
                        </span>
                        <input id="voucher-search" type="text" name="q" value="{{ $filters['q'] }}"
                               placeholder="Kode, nama, event..."
                               class="w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-rose-300 focus:bg-white transition">
                    </div>
                </div>

                <div>
                    <label for="voucher-type" class="block text-xs font-semibold text-slate-500 mb-1.5">Tipe</label>
                    <select id="voucher-type" name="type"
                            class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-rose-300 focus:bg-white transition">
                        <option value="">Semua tipe</option>
                        <option value="cash" @selected($filters['type'] === 'cash')>Cash</option>
                        <option value="event" @selected($filters['type'] === 'event')>Event</option>
                    </select>
                </div>

                <div>
                    <label for="voucher-status" class="block text-xs font-semibold text-slate-500 mb-1.5">Status</label>
                    <select id="voucher-status" name="status"
                            class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-rose-300 focus:bg-white transition">
                        <option value="">Semua status</option>
                        @foreach ($statusLabels as $value => $label)
                            <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <span class="block text-xs font-semibold text-slate-500 mb-1.5">Tanggal dibuat</span>
                    <div class="relative" data-date-picker>
                        <input type="hidden" name="from" value="{{ $filters['from'] }}" data-date-from>
                        <input type="hidden" name="to" value="{{ $filters['to'] }}" data-date-to>

                        <button type="button" data-date-trigger
                                class="w-full inline-flex items-center gap-2.5 px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 hover:bg-white hover:border-rose-200 focus:outline-none focus:ring-2 focus:ring-rose-400 transition"
                                aria-haspopup="dialog" aria-expanded="false">
                            <x-icon name="calendar" class="w-4 h-4 text-rose-500 shrink-0" />
                            <span class="truncate" data-date-label>{{ $dateLabel }}</span>
                            <x-icon name="chevron-right" class="w-3.5 h-3.5 text-slate-400 ml-auto rotate-90 shrink-0" />
                        </button>

                        <div data-date-popover class="hidden absolute right-0 z-30 mt-2 w-[290px] rounded-2xl border border-slate-200 bg-white p-3 shadow-xl">
                            <div class="flex items-center justify-between gap-2">
                                <button type="button" data-date-prev class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition" aria-label="Bulan sebelumnya">
                                    <x-icon name="chevron-left" class="w-4 h-4" />
                                </button>
                                <p class="text-sm font-bold text-slate-700 capitalize" data-date-title></p>
                                <button type="button" data-date-next class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition" aria-label="Bulan berikutnya">
                                    <x-icon name="chevron-right" class="w-4 h-4" />
                                </button>
                            </div>

                            <div data-date-weekdays class="mt-3 grid grid-cols-7 gap-1 text-center text-[10px] font-bold uppercase tracking-wide text-slate-400"></div>
                            <div data-date-days class="mt-1 grid grid-cols-7 gap-1"></div>

                            <div class="mt-3 flex flex-wrap gap-1.5 border-t border-slate-100 pt-3">
                                <button type="button" data-date-preset="today" class="px-2.5 py-1 rounded-lg bg-slate-100 text-[11px] font-semibold text-slate-600 hover:bg-rose-50 hover:text-rose-600 transition">Hari ini</button>
                                <button type="button" data-date-preset="7" class="px-2.5 py-1 rounded-lg bg-slate-100 text-[11px] font-semibold text-slate-600 hover:bg-rose-50 hover:text-rose-600 transition">7 hari</button>
                                <button type="button" data-date-preset="30" class="px-2.5 py-1 rounded-lg bg-slate-100 text-[11px] font-semibold text-slate-600 hover:bg-rose-50 hover:text-rose-600 transition">30 hari</button>
                                <button type="button" data-date-preset="month" class="px-2.5 py-1 rounded-lg bg-slate-100 text-[11px] font-semibold text-slate-600 hover:bg-rose-50 hover:text-rose-600 transition">Bulan ini</button>
                                <button type="button" data-date-preset="clear" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold text-slate-500 hover:bg-slate-100 transition">Semua</button>
                            </div>

                            <div class="mt-3 flex items-center justify-between gap-3 border-t border-slate-100 pt-3">
                                <p class="text-[11px] text-slate-400" data-date-hint>Pilih tanggal mulai</p>
                                <button type="button" data-date-apply class="px-3.5 py-1.5 rounded-lg bg-rose-500 text-white text-xs font-bold hover:bg-rose-600 transition">Terapkan</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 xl:pb-0.5">
                <button type="submit" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-rose-500 text-white text-sm font-bold shadow-sm hover:bg-rose-600 transition">
                    <x-icon name="filter" class="w-4 h-4" />
                    Terapkan
                </button>
                <a href="{{ route('admin.vouchers.index') }}" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-100 transition">Reset</a>
                <button type="button" data-bulk-delete-open
                        class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl ring-1 ring-red-200 bg-red-50 text-red-600 text-sm font-bold hover:bg-red-100 transition disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-red-50"
                        @disabled(! $hasActiveFilters || $vouchers->total() === 0)
                        title="{{ $hasActiveFilters ? 'Hapus semua voucher yang cocok dengan filter' : 'Pilih filter terlebih dahulu' }}">
                    <x-icon name="trash" class="w-4 h-4" />
                    Hapus filter
                </button>
            </div>
        </div>

        @if ($errors->any())
            <p class="mt-3 text-xs font-medium text-red-600">{{ $errors->first() }}</p>
        @endif
    </form>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200">
                        <th class="text-left px-6 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wider">Kode</th>
                        <th class="text-left px-4 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wider">Tipe</th>
                        <th class="text-left px-4 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wider">Nama</th>
                        <th class="text-left px-4 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wider">Isi Voucher</th>
                        <th class="text-left px-4 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wider">Pemakaian</th>
                        <th class="text-left px-4 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wider">Berlaku Sampai</th>
                        <th class="text-left px-4 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wider">Status</th>
                        <th class="text-right px-6 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($vouchers as $voucher)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex rounded-lg bg-slate-900 text-white px-2.5 py-1 text-xs font-bold font-mono tracking-wider">{{ $voucher->code }}</span>
                                @if ($voucher->isEvent() && (int) $voucher->event_voucher_count > 1)
                                    <span class="ml-1 inline-flex rounded-full bg-sky-100 text-sky-700 px-2 py-0.5 text-[10px] font-bold">+{{ (int) $voucher->event_voucher_count - 1 }} kode</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                @if ($voucher->isEvent())
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-sky-100 text-sky-700">Event</span>
                                @else
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Cash</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <p class="font-semibold text-slate-800">{{ $voucher->name }}</p>
                                @if ($voucher->description)
                                    <p class="text-xs text-slate-400 mt-0.5 max-w-[220px] truncate">{{ $voucher->description }}</p>
                                @endif
                                @if ($voucher->isEvent() && $voucher->event_date)
                                    <p class="text-xs text-slate-400 mt-0.5">{{ $voucher->event_date->format('d M Y') }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-slate-500 text-xs max-w-[240px]">
                                <p>{{ $voucher->items_label }}</p>
                                <p class="text-slate-400 mt-0.5">{{ $voucher->photo_count }} foto total</p>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <p class="font-semibold text-slate-700">{{ $voucher->used_count }} / {{ $voucher->max_uses }}</p>
                                <p class="text-xs text-slate-400">{{ $voucher->remaining_uses }} tersisa</p>
                            </td>
                            <td class="px-4 py-4 text-slate-500 whitespace-nowrap">
                                {{ $voucher->expires_at ? $voucher->expires_at->format('d M Y') : 'Tanpa batas' }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                @if ($voucher->isUsable())
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Aktif</span>
                                @elseif (! $voucher->is_active)
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">Nonaktif</span>
                                @elseif ($voucher->isExpired())
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">Kedaluwarsa</span>
                                @elseif ($voucher->isEvent() && $voucher->eventWindow()[0] && now()->lt($voucher->eventWindow()[0]))
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-sky-100 text-sky-700">Terjadwal</span>
                                @else
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-700">Habis</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-1.5 whitespace-nowrap">
                                    @if ($voucher->isEvent())
                                        <a href="{{ route('admin.vouchers.show', $voucher) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 text-xs font-semibold hover:bg-slate-200 transition">
                                            <x-icon name="eye" class="w-3.5 h-3.5" />
                                            Detail
                                        </a>
                                    @else
                                        <a href="{{ route('admin.vouchers.edit', $voucher) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 text-xs font-semibold hover:bg-slate-200 transition">
                                            <x-icon name="pencil" class="w-3.5 h-3.5" />
                                            Edit
                                        </a>
                                    @endif
                                    <a href="{{ route('admin.vouchers.print-voucher', $voucher) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-sky-50 text-sky-600 text-xs font-semibold hover:bg-sky-100 transition" title="Cetak Voucher" target="_blank">
                                        <x-icon name="printer" class="w-3.5 h-3.5" />
                                        Cetak
                                    </a>
                                    <a href="{{ route('admin.vouchers.print-invoice', $voucher) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-600 text-xs font-semibold hover:bg-emerald-100 transition" title="Cetak Invoice" target="_blank">
                                        <x-icon name="receipt" class="w-3.5 h-3.5" />
                                        Invoice
                                    </a>
                                    <form method="POST" action="{{ route('admin.vouchers.destroy', $voucher) }}" onsubmit="return confirm('Hapus voucher {{ $voucher->code }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-red-50 text-red-600 text-xs font-semibold hover:bg-red-100 transition">
                                            <x-icon name="trash" class="w-3.5 h-3.5" />
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-14 text-center text-slate-400">
                                <span class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                    <x-icon name="ticket" class="w-5 h-5" />
                                </span>
                                <p class="font-semibold text-slate-500">Tidak ada voucher yang cocok</p>
                                <p class="text-xs mt-1">Coba ubah kata kunci, tipe, status, atau rentang tanggal.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($vouchers->hasPages())
            <div class="px-6 py-4 border-t border-slate-200">
                {{ $vouchers->links() }}
            </div>
        @endif
    </div>
</div>
{{-- Modal hapus berdasarkan filter --}}
<div data-bulk-delete-modal class="fixed inset-0 z-[90] hidden items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm" role="dialog" aria-modal="true" aria-label="Hapus voucher sesuai filter">
    <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl ring-1 ring-black/5">
        <div class="flex items-start gap-4">
            <span class="w-12 h-12 shrink-0 rounded-2xl bg-red-50 text-red-500 flex items-center justify-center">
                <x-icon name="trash" class="w-6 h-6" />
            </span>
            <div class="min-w-0">
                <h2 class="font-heading text-lg font-bold text-slate-800">Hapus voucher sesuai filter?</h2>
                <p class="text-sm text-slate-500 mt-1.5">
                    <strong class="font-bold text-slate-700">{{ $vouchers->total() }} voucher</strong>
                    sesuai filter akan dihapus permanen.
                </p>
                @if ($filters['q'] !== '' || $filters['type'] !== '' || $filters['status'] !== '')
                    <p class="mt-2 text-xs text-slate-400">
                        {{ $filters['q'] !== '' ? 'Pencarian: '.$filters['q'] : '' }}
                        @if ($filters['q'] !== '' && ($filters['type'] !== '' || $filters['status'] !== '')) &middot; @endif
                        {{ $filters['type'] !== '' ? 'Tipe: '.strtoupper($filters['type']) : '' }}
                        @if ($filters['type'] !== '' && $filters['status'] !== '') &middot; @endif
                        {{ $filters['status'] !== '' ? 'Status: '.$filters['status'] : '' }}
                    </p>
                @endif
                <p class="mt-2 text-xs font-medium text-red-600">Tindakan ini tidak bisa dibatalkan.</p>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.vouchers.destroy-filtered') }}" data-bulk-delete-form class="mt-5 grid grid-cols-2 gap-2">
            @csrf
            @method('DELETE')
            @foreach (['q', 'type', 'status', 'from', 'to'] as $key)
                <input type="hidden" name="{{ $key }}" value="{{ $filters[$key] }}">
            @endforeach
            <button type="button" data-bulk-delete-cancel class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-sm font-bold hover:bg-slate-200 transition">Batal</button>
            <button type="submit" class="px-4 py-2.5 rounded-xl bg-red-500 text-white text-sm font-bold hover:bg-red-600 transition disabled:opacity-70" data-bulk-delete-confirm>
                Ya, hapus
            </button>
        </form>
    </div>
</div>

@endsection
