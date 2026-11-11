@extends('layouts.admin')

@section('title', 'Log Pembayaran - '.\App\Support\Brand::get()['name'].' Admin')
@section('page-title', 'Log Pembayaran')

@php
    $rp = fn ($n) => 'Rp. '.number_format((int) $n, 0, ',', '.');
    $qs = fn (array $extra = []) => array_filter(array_merge(request()->query(), $extra), fn ($v) => $v !== null && $v !== '');
    // Header kolom yang bisa diurutkan; klik lagi untuk membalik arah.
    $sortLink = function (string $col) use ($filters, $qs) {
        $dir = ($filters['sort'] === $col && $filters['dir'] === 'asc') ? 'desc' : 'asc';
        return route('admin.logs.index', $qs(['sort' => $col, 'dir' => $dir, 'page' => null]));
    };
    $arrow = fn (string $col) => $filters['sort'] === $col ? ($filters['dir'] === 'asc' ? '↑' : '↓') : '';
    $methodBadge = fn (string $method) => match ($method) {
        'qris' => 'bg-violet-100 text-violet-700 ring-violet-200',
        'voucher' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
        'cash' => 'bg-amber-100 text-amber-700 ring-amber-200',
        default => 'bg-slate-100 text-slate-600 ring-slate-200',
    };
    $dateLabel = 'Semua tanggal';
    if ($filters['from'] !== '' || $filters['to'] !== '') {
        $fromLabel = $filters['from'] !== '' ? \Carbon\Carbon::parse($filters['from'])->format('d M Y') : null;
        $toLabel = $filters['to'] !== '' ? \Carbon\Carbon::parse($filters['to'])->format('d M Y') : null;
        $dateLabel = match (true) {
            $fromLabel && $toLabel => $fromLabel.' – '.$toLabel,
            (bool) $fromLabel => 'Dari '.$fromLabel,
            default => 'Sampai '.$toLabel,
        };
    }
    $hasActiveFilters = $filters['q'] !== '' || $filters['metode'] !== '' || $filters['from'] !== '' || $filters['to'] !== '';
@endphp

@section('content')
<div class="space-y-6" data-log-root data-download-template="{{ route('admin.logs.download', ['log' => '__ID__']) }}">
    {{-- Ringkasan sesuai filter --}}
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <span class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-rose-500 to-violet-500"></span>
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Transaksi</p>
                    <p class="text-2xl font-bold text-slate-800 mt-2 tabular-nums">{{ $summary['count'] }}</p>
                </div>
                <span class="w-10 h-10 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center shrink-0">
                    <x-icon name="bag" class="w-5 h-5" />
                </span>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <span class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-emerald-400 to-emerald-500"></span>
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Pendapatan</p>
                    <p class="text-2xl font-bold text-slate-800 mt-2 tabular-nums">{{ $rp($summary['revenue']) }}</p>
                    @if ($summary['cash'] > 0)
                        <p class="text-[11px] text-slate-400 mt-1">Tunai {{ $rp($summary['cash']) }}</p>
                    @endif
                </div>
                <span class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center shrink-0">
                    <x-icon name="cash" class="w-5 h-5" />
                </span>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <span class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-violet-500 to-blue-500"></span>
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">QRIS</p>
                    <p class="text-2xl font-bold text-slate-800 mt-2 tabular-nums">{{ $rp($summary['qris']) }}</p>
                </div>
                <span class="w-10 h-10 rounded-xl bg-violet-50 text-violet-500 flex items-center justify-center shrink-0">
                    <x-icon name="qr" class="w-5 h-5" />
                </span>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <span class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-blue-500 to-cyan-500"></span>
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Voucher</p>
                    <p class="text-2xl font-bold text-slate-800 mt-2 tabular-nums">{{ $rp($summary['voucher']) }}</p>
                </div>
                <span class="w-10 h-10 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center shrink-0">
                    <x-icon name="card" class="w-5 h-5" />
                </span>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('admin.logs.index') }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm" data-log-filter>
        <input type="hidden" name="sort" value="{{ $filters['sort'] }}">
        <input type="hidden" name="dir" value="{{ $filters['dir'] }}">

        <div class="flex flex-col xl:flex-row xl:items-end gap-3">
            <div class="grid flex-1 grid-cols-1 sm:grid-cols-2 lg:grid-cols-[minmax(0,1.15fr)_minmax(0,0.85fr)_minmax(0,1fr)] gap-3">
                <div>
                    <label for="log-search" class="block text-xs font-semibold text-slate-500 mb-1.5">Cari antrian</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                            <x-icon name="search" class="w-4 h-4" />
                        </span>
                        <input id="log-search" type="text" name="q" value="{{ $filters['q'] }}" placeholder="#1234"
                               class="w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-rose-300 focus:bg-white transition">
                    </div>
                </div>

                <div>
                    <label for="log-method" class="block text-xs font-semibold text-slate-500 mb-1.5">Metode</label>
                    <select id="log-method" name="metode"
                            class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-rose-300 focus:bg-white transition">
                        <option value="">Semua metode</option>
                        <option value="qris" @selected($filters['metode'] === 'qris')>QRIS</option>
                        <option value="voucher" @selected($filters['metode'] === 'voucher')>Voucher</option>
                        <option value="cash" @selected($filters['metode'] === 'cash')>Tunai (lama)</option>
                    </select>
                </div>

                <div>
                    <span class="block text-xs font-semibold text-slate-500 mb-1.5">Rentang tanggal</span>
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

                        <div data-date-popover role="dialog" aria-label="Pilih rentang tanggal"
                             class="hidden absolute right-0 z-40 mt-2 w-[min(20rem,calc(100vw-3rem))] rounded-2xl border border-slate-200 bg-white p-4 shadow-2xl shadow-slate-900/10">
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <button type="button" data-date-prev class="w-8 h-8 rounded-lg text-slate-500 hover:bg-slate-100 flex items-center justify-center transition" aria-label="Bulan sebelumnya">
                                    <x-icon name="chevron-left" class="w-4 h-4" />
                                </button>
                                <p class="text-sm font-bold text-slate-800" data-date-title></p>
                                <button type="button" data-date-next class="w-8 h-8 rounded-lg text-slate-500 hover:bg-slate-100 flex items-center justify-center transition" aria-label="Bulan berikutnya">
                                    <x-icon name="chevron-right" class="w-4 h-4" />
                                </button>
                            </div>

                            <div class="grid grid-cols-7 gap-1 mb-1 text-center text-[10px] font-bold uppercase tracking-wide text-slate-400" data-date-weekdays></div>
                            <div class="grid grid-cols-7 gap-1" data-date-days></div>

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
                <a href="{{ route('admin.logs.index') }}" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-100 transition">Reset</a>
                <button type="button" data-bulk-delete-open
                        class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl ring-1 ring-red-200 bg-red-50 text-red-600 text-sm font-bold hover:bg-red-100 transition disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-red-50"
                        @disabled(! $hasActiveFilters || $summary['count'] === 0)
                        title="{{ $hasActiveFilters ? 'Hapus semua log yang cocok dengan filter' : 'Pilih filter terlebih dahulu' }}">
                    <x-icon name="trash" class="w-4 h-4" />
                    Hapus filter
                </button>
                <a href="{{ route('admin.logs.export', $qs(['page' => null])) }}"
                   class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl ring-1 ring-emerald-200 bg-emerald-50 text-emerald-700 text-sm font-bold hover:bg-emerald-100 transition">
                    <x-icon name="download" class="w-4 h-4" />
                    Ekspor CSV
                </a>
            </div>
        </div>

        @if ($errors->any())
            <p class="mt-3 text-xs font-medium text-red-600">{{ $errors->first() }}</p>
        @endif
    </form>

    {{-- Tabel --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-5 py-4">
            <div>
                <h2 class="font-heading text-sm font-bold text-slate-800">Riwayat transaksi</h2>
                <p class="text-xs text-slate-400 mt-0.5">{{ $summary['count'] }} transaksi &middot; {{ $dateLabel }}</p>
            </div>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-[11px] font-semibold text-slate-500">
                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                Klik ikon mata untuk melihat hasil foto
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/80">
                        <th class="text-left px-5 py-3 font-semibold text-slate-500 whitespace-nowrap"><a href="{{ $sortLink('created_at') }}" class="hover:text-rose-600 transition">Waktu {{ $arrow('created_at') }}</a></th>
                        <th class="text-left px-5 py-3 font-semibold text-slate-500"><a href="{{ $sortLink('queue_number') }}" class="hover:text-rose-600 transition">Antrian {{ $arrow('queue_number') }}</a></th>
                        <th class="text-left px-5 py-3 font-semibold text-slate-500"><a href="{{ $sortLink('metode') }}" class="hover:text-rose-600 transition">Metode {{ $arrow('metode') }}</a></th>
                        <th class="text-left px-5 py-3 font-semibold text-slate-500">Frame &amp; foto</th>
                        <th class="text-right px-5 py-3 font-semibold text-slate-500"><a href="{{ $sortLink('total') }}" class="hover:text-rose-600 transition">Total {{ $arrow('total') }}</a></th>
                        <th class="text-right px-5 py-3 font-semibold text-slate-500 w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($logs as $log)
                        @php($primary = $log['groups'][0] ?? null)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-5 py-3.5 text-slate-600 whitespace-nowrap">
                                <div class="font-medium">{{ $log['created_at']?->format('d M Y') ?? '-' }}</div>
                                <div class="text-xs text-slate-400">{{ $log['created_at']?->format('H:i:s') ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-3.5 font-bold text-slate-800 whitespace-nowrap">{{ $log['queue'] }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-bold ring-1 {{ $methodBadge($log['method']) }}">{{ strtoupper($log['method']) }}</span>
                                @if ($log['voucher_code'])
                                    <p class="text-[11px] text-slate-400 font-mono mt-1">{{ $log['voucher_code'] }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="flex -space-x-2 shrink-0">
                                        @if ($log['has_photos'])
                                            @foreach (array_slice($log['photos'], 0, 3) as $photo)
                                                <img src="{{ $photo }}" alt="" loading="lazy"
                                                     class="w-9 h-11 rounded-lg object-cover bg-slate-100 ring-2 ring-white">
                                            @endforeach
                                            @if (count($log['photos']) > 3)
                                                <span class="w-9 h-11 rounded-lg bg-slate-100 ring-2 ring-white flex items-center justify-center text-[10px] font-bold text-slate-500">+{{ count($log['photos']) - 3 }}</span>
                                            @endif
                                        @elseif ($primary && $primary['frame_url'])
                                            <img src="{{ $primary['frame_url'] }}" alt="" loading="lazy" class="w-9 h-11 rounded-lg object-contain bg-slate-50 ring-2 ring-white">
                                        @else
                                            <span class="w-9 h-11 rounded-lg bg-slate-100 ring-2 ring-white flex items-center justify-center text-slate-300">
                                                <x-icon name="photo" class="w-4 h-4" />
                                            </span>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-slate-700 truncate max-w-[220px]">
                                            {{ $primary['name'] ?? '-' }}@if (($primary['qty'] ?? 1) > 1)<span class="text-slate-400"> ×{{ $primary['qty'] }}</span>@endif
                                        </p>
                                        <p class="text-[11px] text-slate-400">
                                            @if (count($log['groups']) > 1)
                                                {{ count($log['groups']) }} frame &middot;
                                            @endif
                                            @if ($log['has_photos'])
                                                {{ $log['photo_total'] }} foto siap diunduh
                                            @else
                                                Foto tidak tersedia
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-right font-bold text-slate-800 tabular-nums whitespace-nowrap">{{ $rp($log['total']) }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" data-log-open="{{ $log['id'] }}"
                                            class="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 hover:bg-rose-50 hover:text-rose-600 flex items-center justify-center transition"
                                            title="Lihat hasil foto" aria-label="Lihat hasil foto antrian {{ $log['queue'] }}">
                                        <x-icon name="eye" class="w-4 h-4" />
                                    </button>
                                    @if ($log['has_photos'])
                                        <a href="{{ route('admin.logs.download', $log['id']) }}"
                                           class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition"
                                           title="Unduh foto & frame (ZIP)" aria-label="Unduh foto & frame antrian {{ $log['queue'] }}">
                                            <x-icon name="download" class="w-4 h-4" />
                                        </a>
                                    @else
                                        <span class="w-8 h-8 rounded-lg bg-slate-50 text-slate-300 flex items-center justify-center" title="Foto tidak tersedia">
                                            <x-icon name="download" class="w-4 h-4" />
                                        </span>
                                    @endif
                                    <button type="button" data-log-delete="{{ $log['id'] }}"
                                            data-delete-url="{{ route('admin.logs.destroy', $log['id']) }}"
                                            data-delete-label="Antrian {{ $log['queue'] }}"
                                            class="w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 flex items-center justify-center transition"
                                            title="Hapus log" aria-label="Hapus log antrian {{ $log['queue'] }}">
                                        <x-icon name="trash" class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-14 text-center">
                                <span class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-300 flex items-center justify-center mx-auto mb-3">
                                    <x-icon name="calendar" class="w-6 h-6" />
                                </span>
                                <p class="text-slate-500 font-medium">Tidak ada pembayaran yang cocok</p>
                                <p class="text-xs text-slate-400 mt-1">Coba ubah rentang tanggal atau filter metode pembayaran.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Data detail untuk modal (dibaca oleh app.js) --}}
<script type="application/json" id="log-data">@json($logData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)</script>

{{-- Modal detail transaksi --}}
<div data-log-modal class="fixed inset-0 z-[85] hidden items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm" role="dialog" aria-modal="true" aria-label="Detail transaksi">
    <div class="flex max-h-[92vh] w-full max-w-5xl flex-col overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-black/5">
        <header class="flex items-start justify-between gap-4 border-b border-slate-100 px-5 py-4 sm:px-6">
            <div class="min-w-0">
                <p class="text-[11px] font-bold uppercase tracking-wider text-rose-500">Detail transaksi</p>
                <h2 class="font-heading text-xl font-bold text-slate-800 mt-0.5" data-log-modal-queue>-</h2>
                <p class="text-xs text-slate-400 mt-1" data-log-modal-meta>-</p>
            </div>
            <button type="button" data-log-close class="w-9 h-9 rounded-xl bg-slate-100 text-slate-500 hover:bg-slate-200 flex items-center justify-center transition shrink-0" aria-label="Tutup">
                <x-icon name="x" class="w-4 h-4" />
            </button>
        </header>

        <div class="flex-1 overflow-y-auto bg-slate-50/70 p-4 sm:p-6">
            <div class="grid gap-4 lg:grid-cols-2" data-log-modal-body></div>
        </div>

        <footer class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 px-5 py-4 sm:px-6">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Total dibayar</p>
                <p class="font-heading text-lg font-bold text-slate-800 tabular-nums" data-log-modal-total>-</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="#" data-log-modal-zip class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-blue-50 text-blue-700 text-sm font-bold hover:bg-blue-100 transition">
                    <x-icon name="download" class="w-4 h-4" />
                    Unduh foto &amp; frame
                </a>
                <button type="button" data-log-close class="px-4 py-2.5 rounded-xl bg-slate-900 text-white text-sm font-bold hover:bg-slate-800 transition">Tutup</button>
            </div>
        </footer>
    </div>
</div>

{{-- Modal hapus berdasarkan filter --}}
<div data-bulk-delete-modal class="fixed inset-0 z-[90] hidden items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm" role="dialog" aria-modal="true" aria-label="Hapus log sesuai filter">
    <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl ring-1 ring-black/5">
        <div class="flex items-start gap-4">
            <span class="w-12 h-12 shrink-0 rounded-2xl bg-red-50 text-red-500 flex items-center justify-center">
                <x-icon name="trash" class="w-6 h-6" />
            </span>
            <div class="min-w-0">
                <h2 class="font-heading text-lg font-bold text-slate-800">Hapus log sesuai filter?</h2>
                <p class="text-sm text-slate-500 mt-1.5">
                    <strong class="font-bold text-slate-700">{{ $summary['count'] }} transaksi</strong>
                    pada <strong class="font-semibold text-slate-700">{{ $dateLabel }}</strong>
                    akan dihapus permanen beserta arsip fotonya.
                </p>
                @if ($filters['q'] !== '' || $filters['metode'] !== '')
                    <p class="mt-2 text-xs text-slate-400">
                        {{ $filters['q'] !== '' ? 'Pencarian: '.$filters['q'] : '' }}
                        @if ($filters['q'] !== '' && $filters['metode'] !== '') &middot; @endif
                        {{ $filters['metode'] !== '' ? 'Metode: '.strtoupper($filters['metode']) : '' }}
                    </p>
                @endif
                <p class="mt-2 text-xs font-medium text-red-600">Tindakan ini tidak bisa dibatalkan.</p>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.logs.destroy-filtered') }}" data-bulk-delete-form class="mt-5 grid grid-cols-2 gap-2">
            @csrf
            @method('DELETE')
            @foreach (['q', 'metode', 'from', 'to', 'sort', 'dir'] as $key)
                <input type="hidden" name="{{ $key }}" value="{{ $filters[$key] }}">
            @endforeach
            <button type="button" data-bulk-delete-cancel class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-sm font-bold hover:bg-slate-200 transition">Batal</button>
            <button type="submit" class="px-4 py-2.5 rounded-xl bg-red-500 text-white text-sm font-bold hover:bg-red-600 transition disabled:opacity-70" data-bulk-delete-confirm>
                Ya, hapus
            </button>
        </form>
    </div>
</div>

{{-- Modal konfirmasi hapus --}}
<div data-delete-modal class="fixed inset-0 z-[90] hidden items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm" role="dialog" aria-modal="true" aria-label="Hapus log pembayaran">
    <div class="w-full max-w-sm rounded-3xl bg-white p-6 text-center shadow-2xl ring-1 ring-black/5">
        <span class="w-14 h-14 rounded-2xl bg-red-50 text-red-500 flex items-center justify-center mx-auto mb-4">
            <x-icon name="trash" class="w-6 h-6" />
        </span>
        <h2 class="font-heading text-lg font-bold text-slate-800">Hapus log pembayaran?</h2>
        <p class="text-sm text-slate-500 mt-1.5">
            <span class="font-semibold text-slate-700" data-delete-label></span> beserta arsip fotonya akan dihapus permanen.
        </p>
        <form method="POST" action="#" data-delete-form class="mt-5 grid grid-cols-2 gap-2">
            @csrf
            @method('DELETE')
            <button type="button" data-delete-cancel class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-sm font-bold hover:bg-slate-200 transition">Batal</button>
            <button type="submit" class="px-4 py-2.5 rounded-xl bg-red-500 text-white text-sm font-bold hover:bg-red-600 transition">Hapus</button>
        </form>
    </div>
</div>
@endsection
