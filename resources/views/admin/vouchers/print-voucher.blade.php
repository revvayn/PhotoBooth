@extends('layouts.print')

@php
    $brand = \App\Support\Brand::get();
    $isEvent = $voucher->isEvent();
@endphp

@section('title', 'Cetak Voucher - '.$brand['name'].' Admin')

@section('content')
<div class="flex justify-center">
    <div class="w-full max-w-md no-page-break">
        <div class="overflow-hidden rounded-2xl bg-white shadow-lg ring-1 ring-slate-200">
            <!-- Header -->
            <div class="px-6 py-5 text-center text-white" style="background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));">
                <p class="text-[10px] font-bold uppercase tracking-[0.24em] text-white/75">{{ $brand['name'] }}</p>
                <h1 class="mt-1 text-xl font-extrabold leading-tight">{{ $voucher->name }}</h1>
                @if ($isEvent && $voucher->event_name)
                    <p class="mt-1 text-sm font-medium text-white/90">{{ $voucher->event_name }}</p>
                    @if ($voucher->event_date)
                        <p class="text-xs text-white/70">{{ $voucher->event_date->format('d M Y') }}</p>
                    @endif
                @endif
                <div class="mx-auto mt-4 w-fit rounded-xl bg-white/15 px-4 py-2">
                    <p class="text-[9px] font-bold uppercase tracking-wider text-white/70">Kode Voucher</p>
                    <p class="mt-0.5 font-mono text-base font-extrabold tracking-[0.18em]">{{ $voucher->code }}</p>
                </div>
            </div>

            <!-- Body -->
            <div class="px-6 py-5">
                <p class="mb-2 text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">
                    {{ $voucher->isFrameMode() ? 'Frame yang Diperoleh' : 'Ukuran &amp; Jumlah Cetak' }}
                </p>

                <table class="w-full border-collapse text-sm">
                    <tbody>
                        @foreach ($items as $item)
                            <tr class="border-b border-slate-100 last:border-b-0">
                                <td class="py-2.5 text-left font-medium text-slate-800">
                                    {{ $item['name'] }}
                                    @if (! empty($item['dimension_label']))
                                        <span class="block text-xs text-slate-400">{{ $item['dimension_label'] }}</span>
                                    @endif
                                </td>
                                <td class="py-2.5 text-right align-middle">
                                    <span class="inline-block rounded-md px-2 py-0.5 text-xs font-bold tabular-nums text-white" style="background: var(--brand-primary);">
                                        {{ $item['qty'] }}×
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if ($voucher->photos_per_voucher || ($voucher->discount ?? 0) > 0)
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        @if ($voucher->photos_per_voucher)
                            <div class="rounded-xl bg-slate-50 px-4 py-3 text-center">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Jumlah Cetak</p>
                                <p class="mt-1 text-base font-extrabold tabular-nums text-slate-800">
                                    {{ $voucher->photos_per_voucher }} <span class="text-xs font-semibold text-slate-400">lembar</span>
                                </p>
                            </div>
                        @endif
                        @if (($voucher->discount ?? 0) > 0)
                            <div class="rounded-xl bg-emerald-50 px-4 py-3 text-center">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-600">Diskon</p>
                                <p class="mt-1 text-base font-extrabold tabular-nums text-emerald-600">{{ $voucher->discount }}%</p>
                            </div>
                        @endif
                    </div>
                @endif

                <div class="mt-4 rounded-xl bg-amber-50 px-4 py-3 text-left">
                    <p class="mb-1 text-[10px] font-bold uppercase tracking-[0.18em] text-amber-600">Cara Penggunaan</p>
                    <p class="text-sm leading-relaxed text-slate-700">
                        Tunjukkan kode voucher ini pada layar photobooth. Pilih <strong>Voucher</strong>,
                        masukkan kode, pilih frame sesuai ukuran yang tertera, lalu lanjut ke sesi foto.
                        Tidak perlu bayar lagi.
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="border-t border-slate-100 bg-slate-50 px-6 py-3 text-center">
                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">{{ $brand['name'] }} Kiosk · {{ now()->format('d M Y') }}</p>
                <p class="mt-0.5 text-[10px] text-slate-400">Simpan voucher ini dengan baik. Berlaku sesuai ketentuan.</p>
            </div>
        </div>
    </div>
</div>
@endsection