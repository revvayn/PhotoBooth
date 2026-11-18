@extends('layouts.public')

@section('content')
    <div data-public-download-root
         data-queue="{{ $log['queue'] }}"
         class="space-y-6">

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <p class="text-[11px] font-bold uppercase tracking-wider text-rose-500">Hasil photobooth</p>
            <h1 class="mt-1 font-heading text-2xl font-extrabold text-slate-900">Antrian {{ $log['queue'] }}</h1>
            <p class="mt-1 text-xs text-slate-400">{{ $log['time'] }}</p>
            <p class="mt-4 text-sm text-slate-600">
                Berikut hasil foto beserta <strong>frame, filter, dan stiker/emoji</strong> yang kamu pilih.
                Tekan tombol unduh pada setiap hasil.
            </p>
        </div>

        @forelse ($log['groups'] as $group)
            @php
                $filters = array_values(array_unique(array_filter((array) ($group['filters'] ?? []))));
                $stickers = collect($group['stickers'] ?? [])
                    ->map(fn ($sticker) => is_array($sticker) ? ($sticker['value'] ?? null) : $sticker)
                    ->filter()
                    ->values()
                    ->all();
            @endphp

            <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="truncate font-heading text-base font-bold text-slate-800">{{ $group['name'] }}</h2>
                        <p class="mt-0.5 text-[11px] text-slate-400">{{ $group['photo_total'] }} foto · {{ $group['qty'] }} lembar</p>
                    </div>
                    <span class="shrink-0 rounded-full bg-rose-50 px-2.5 py-1 text-[11px] font-bold text-rose-600">&times;{{ $group['qty'] }}</span>
                </div>

                @if ($filters || $stickers)
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        @foreach ($filters as $filter)
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-500">Filter: {{ $filter }}</span>
                        @endforeach
                        @foreach ($stickers as $sticker)
                            <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-700">{{ $sticker }}</span>
                        @endforeach
                    </div>
                @endif

                <div class="mt-4 flex min-h-[180px] items-center justify-center rounded-2xl bg-slate-50 p-3">
                    @if (! empty($group['photos']))
                        <canvas class="max-h-[420px] w-auto max-w-full rounded-xl bg-white shadow-sm"
                                data-public-canvas
                                data-photos='@json($group['photos'])'
                                data-filters='@json($group['filters'])'
                                data-frame="{{ $group['frame_url'] ?? '' }}"
                                data-slots='@json($group['slots'])'
                                data-stickers='@json($group['stickers'])'></canvas>
                    @else
                        <p class="text-center text-xs text-slate-400">Foto arsip tidak tersedia.</p>
                    @endif
                </div>

                @if (! empty($group['photos']))
                    <button type="button" data-public-download
                            class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-rose-50 px-4 py-2 text-xs font-bold text-rose-600 transition hover:bg-rose-100" disabled>
                        <x-icon name="download" class="h-4 w-4" /> Unduh hasil ini
                    </button>
                @endif
            </article>
        @empty
            <div class="rounded-3xl border border-slate-200 bg-white p-10 text-center text-sm text-slate-400 shadow-sm">
                Belum ada hasil foto untuk token ini.
            </div>
        @endforelse
    </div>
@endsection
