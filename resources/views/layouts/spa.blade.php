<!DOCTYPE html>
<!-- SPA-KANVAS build 74652ed+ : bila tulisan ini tak ada di view-source, tab Anda basi -->
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Photobooth</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-dvh overflow-hidden bg-gradient-to-br from-rose-100 via-fuchsia-50 to-violet-100 text-slate-800 font-sans antialiased">
@php
    // Sisa waktu dihitung dari server agar tidak ter-reset di tiap pergantian panel.
    $remaining = max(0, (int) session('total_seconds', 600) - (now()->timestamp - (int) session('started_at', now()->timestamp)));
    $steps = [
        ['key' => 'frame', 'label' => 'Frame'],
        ['key' => 'foto', 'label' => 'Foto'],
        ['key' => 'filter', 'label' => 'Filter'],
        ['key' => 'metode', 'label' => 'Metode'],
        ['key' => 'pembayaran', 'label' => 'Bayar'],
        ['key' => 'review', 'label' => 'Selesai'],
    ];
    $activeKey = $step ?? 'frame';
    $activeIdx = (int) collect($steps)->search(fn ($s) => $s['key'] === $activeKey);
@endphp

    <div class="h-dvh overflow-hidden flex flex-col px-4 sm:px-8 py-3 max-w-6xl mx-auto w-full min-h-0">

        <header class="flex items-center justify-between gap-4 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-rose-400 to-violet-500 flex items-center justify-center shadow-md shadow-rose-200">
                    <x-icon name="camera" class="w-6 h-6 text-white" />
                </div>
                <h1 class="font-display font-bold text-xl text-slate-800 leading-none">Photobooth</h1>
            </div>

            <div class="flex items-center gap-2.5">
                @if (!empty($queue))
                    <span class="hidden sm:inline-flex items-center rounded-full bg-white/80 backdrop-blur px-4 py-2 text-sm font-semibold text-slate-600 shadow-sm ring-1 ring-rose-100">
                        Antrian <span class="ml-1.5 font-bold text-rose-500">{{ $queue }}</span>
                    </span>
                @endif
                <div data-timer-pill class="flex items-center gap-2 rounded-full bg-white/80 backdrop-blur px-4 py-2 shadow-sm ring-1 ring-rose-100 transition-colors">
                    <x-icon name="clock" class="w-4 h-4 text-rose-500" />
                    <span class="text-sm font-bold tabular-nums text-slate-700" data-session-timer data-total="{{ $remaining }}">
                        <span data-session-timer-min>00</span>:<span data-session-timer-sec>00</span>
                    </span>
                </div>
            </div>
        </header>

        <nav class="flex items-center justify-center my-3 shrink-0" data-spa-stepper aria-label="Langkah">
            @foreach ($steps as $i => $s)
                @php($state = $i < $activeIdx ? 'done' : ($i === $activeIdx ? 'active' : 'todo'))
                @if ($i > 0)
                    <span class="h-0.5 w-4 sm:w-10 rounded-full mb-5 {{ $i <= $activeIdx ? 'bg-rose-400' : 'bg-rose-200' }}"></span>
                @endif
                <div class="flex flex-col items-center gap-1 w-14 sm:w-16">
                    <span data-spa-step-dot="{{ $s['key'] }}" data-state="{{ $state }}"
                          class="w-9 h-9 rounded-full text-sm font-bold flex items-center justify-center ring-2 transition-all
                                 {{ $state === 'done' ? 'bg-emerald-500 ring-emerald-500 text-white' : ($state === 'active' ? 'bg-rose-500 ring-rose-500 text-white shadow-md shadow-rose-200 scale-110' : 'bg-white/80 ring-rose-200 text-slate-400') }}">
                        @if ($state === 'done')
                            <x-icon name="check" class="w-4 h-4 text-white" />
                        @else
                            {{ $loop->iteration }}
                        @endif
                    </span>
                    <span class="text-xs font-semibold {{ $state === 'active' ? 'text-rose-600' : ($state === 'done' ? 'text-emerald-600' : 'text-slate-400') }}">{{ $s['label'] }}</span>
                </div>
            @endforeach
        </nav>

        <main class="flex-1 min-h-0 flex flex-col overflow-hidden" data-spa-main>
            <section data-spa-panel="frame" class="min-h-0 flex-1 flex flex-col overflow-y-auto overflow-x-hidden {{ $activeKey === 'frame' ? '' : 'hidden' }}">
                @include('partials.frame', ['step' => 1])
            </section>
            <section data-spa-panel="foto" data-cam-width="{{ $cam_width ?? 1024 }}" data-cam-height="{{ $cam_height ?? 768 }}" data-cam-countdown="{{ $countdown ?? 3 }}" class="min-h-0 flex-1 flex flex-col overflow-y-auto overflow-x-hidden {{ $activeKey === 'foto' ? '' : 'hidden' }}">
                @include('partials.foto', ['step' => 2])
            </section>
            <section data-spa-panel="filter" class="min-h-0 flex-1 flex flex-col overflow-y-auto overflow-x-hidden {{ $activeKey === 'filter' ? '' : 'hidden' }}">
                @include('partials.filter', ['step' => 3])
            </section>
            <section data-spa-panel="metode" class="min-h-0 flex-1 flex flex-col overflow-y-auto overflow-x-hidden {{ $activeKey === 'metode' ? '' : 'hidden' }}">
                @include('partials.metode', ['step' => 4])
            </section>
            <section data-spa-panel="pembayaran" class="min-h-0 flex-1 flex flex-col overflow-y-auto overflow-x-hidden {{ $activeKey === 'pembayaran' ? '' : 'hidden' }}">
                @include('partials.pembayaran', ['step' => 5])
            </section>
            <section data-spa-panel="review" class="min-h-0 flex-1 flex flex-col overflow-y-auto overflow-x-hidden {{ $activeKey === 'review' ? '' : 'hidden' }}">
                @include('partials.review', ['step' => 6])
            </section>
        </main>

        <div class="fixed inset-0 z-[60] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm" data-timeout-overlay>
            <div class="bg-white rounded-3xl p-8 text-center max-w-sm mx-4 shadow-2xl">
                <div class="mb-3 text-slate-400 flex justify-center"><x-icon name="clock" class="w-12 h-12" /></div>
                <h2 class="font-display font-bold text-xl mb-1">Waktu Sesi Habis</h2>
                <p class="text-sm text-slate-500 mb-5">Silakan ulangi sesi dari awal ya.</p>
                <a href="{{ route('home') }}" class="inline-block rounded-full bg-rose-500 text-white font-semibold px-8 py-3 hover:bg-rose-600">Kembali ke Awal</a>
            </div>
        </div>

        <div class="fixed inset-0 z-[70] pointer-events-none hidden items-center justify-center" data-flash>
            <div class="w-full h-full bg-white"></div>
        </div>
    </div>

    {{-- Lembar cetak: diisi JS saat tombol Cetak ditekan, hanya tampil di mode print. --}}
    <div id="print-sheet" class="hidden"></div>

    <script>
        // Jaring pengaman: dokumen ini selalu me-render tepat satu panel tanpa 'hidden'.
        // Bila nol panel tampil (dokumen basi/inkonsisten), tampilkan panel sesuai
        // langkah aktif di stepper, jatuh ke frame.
        document.addEventListener('DOMContentLoaded', () => {
            const panels = [...document.querySelectorAll('[data-spa-panel]')];
            if (!panels.length) return;
            if (panels.some((p) => !p.classList.contains('hidden'))) return;
            const active = document.querySelector('[data-spa-step-dot][data-state="active"]');
            const key = (active && active.dataset.spaStepDot) || 'frame';
            const target = panels.find((p) => p.dataset.spaPanel === key) || panels[0];
            target.classList.remove('hidden');
        });
    </script>
</body>
</html>
