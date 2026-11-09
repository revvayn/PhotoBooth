@extends('layouts.print')

@php
    $brand = \App\Support\Brand::get();
@endphp

@section('title', 'Cetak Voucher - '.$brand['name'].' Admin')

@section('content')
<div class="flex justify-center">
    <!-- Voucher Card - Sized for printing/cutting -->
    <div class="w-full max-w-sm bg-white rounded-2xl border overflow-hidden p-6 no-page-break" style="box-shadow: 0 20px 45px -20px var(--brand-primary); border-color: color-mix(in srgb, var(--brand-primary) 20%, white);">
        <!-- Header -->
        <div class="text-white p-4 rounded-t-2xl" style="background: linear-gradient(90deg, var(--brand-primary), var(--brand-secondary));">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-white/80 mb-1">{{ $brand['name'] }} Voucher</p>
                    <h1 class="text-xl font-bold">{{ $voucher->name }}</h1>
                </div>
                <div class="text-right">
                    <p class="text-xs text-white/80 opacity-80">No. Voucher</p>
                    <p class="text-sm font-mono font-bold">{{ $voucher->code }}</p>
                </div>
            </div>
            @if ($voucher->isEvent())
                <div class="mt-3 pt-2 border-t border-white/25">
                    <p class="text-xs text-white/80 opacity-80">Event</p>
                    <p class="text-sm font-semibold">{{ $voucher->event_name }}</p>
                    @if ($voucher->event_date)
                        <p class="text-xs text-white/80 opacity-80">{{ $voucher->event_date->format('d M Y') }}</p>
                    @endif
                </div>
            @endif
        </div>

        <!-- Body -->
        <div class="p-6">
            <div class="mb-4">
                <p class="text-xs font-bold uppercase text-slate-400 mb-2">Frame yang Diperoleh</p>
                <div class="space-y-2">
                    @foreach ($items as $item)
                        <div class="flex justify-between items-center p-2 bg-slate-50 rounded-lg border border-slate-100">
                            <span class="text-sm text-slate-700">{{ $item['name'] }}</span>
                            <span class="text-sm font-semibold" style="color: var(--brand-primary);">{{ $item['qty'] }} lembar</span>
                        </div>
                    @endforeach
                </div>
            </div>

            @if ($voucher->photos_per_voucher)
                <div class="mb-4">
                    <p class="text-xs font-bold uppercase text-slate-400 mb-2">Jumlah Foto</p>
                    <p class="text-sm font-semibold text-slate-800">{{ $voucher->photos_per_voucher }} foto per sesi</p>
                </div>
            @endif

            @if (($voucher->discount ?? 0) > 0)
                <div class="mb-4 flex items-center justify-between rounded-lg p-3" style="background: color-mix(in srgb, var(--brand-success) 10%, white); border: 1px solid color-mix(in srgb, var(--brand-success) 28%, white);">
                    <span class="text-xs font-bold uppercase" style="color: var(--brand-success);">Diskon</span>
                    <span class="text-sm font-bold" style="color: var(--brand-success);">{{ $voucher->discount }}%</span>
                </div>
            @endif

            <div class="rounded-lg p-3" style="background: color-mix(in srgb, var(--brand-warning) 10%, white); border: 1px solid color-mix(in srgb, var(--brand-warning) 28%, white);">
                <p class="text-xs font-bold uppercase mb-1" style="color: var(--brand-warning);">Cara Penggunaan</p>
                <p class="text-sm" style="color: color-mix(in srgb, var(--brand-warning) 82%, black);">
                    Tunjukkan kode voucher ini pada layar photobooth. Pilih <strong>Voucher</strong>,
                    masukkan kode, lalu langsung ke sesi foto. Tidak perlu memilih frame lagi.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-slate-50 p-3 rounded-b-2xl border-t border-slate-100 text-center">
            <p class="text-[10px] text-slate-500 uppercase tracking-wider">{{ $brand['name'] }} Kiosk · {{ now()->format('d M Y') }}</p>
            <p class="text-[10px] text-slate-400">Voucher ini dapat digunakan sekali kali.</p>
        </div>
    </div>
</div>
@endsection
