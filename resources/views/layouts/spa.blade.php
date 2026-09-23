<!DOCTYPE html>
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

    <div class="h-dvh overflow-hidden flex flex-col px-4 sm:px-6 py-2 max-w-5xl mx-auto w-full min-h-0">

        <header class="flex items-center justify-between gap-4 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-rose-400 to-violet-500 flex items-center justify-center shadow-md shadow-rose-200">
                    <span class="text-white text-lg">📸</span>
                </div>
                <div class="leading-tight">
                    <h1 class="font-display font-bold text-slate-800">Photobooth</h1>
                    <p class="text-xs text-slate-500" data-session-timer>
                        <span class="font-bold text-rose-500" data-session-timer-min>00</span>:<span data-session-timer-sec>00</span>
                    </p>
                </div>
            </div>
        </header>

        <nav class="flex items-center gap-2 sm:gap-3 my-3 shrink-0" data-spa-stepper>
            @foreach ([
                ['key' => 'frame', 'label' => 'Pilih Frame'],
                ['key' => 'foto', 'label' => 'Sesi Foto'],
                ['key' => 'filter', 'label' => 'Filter'],
                ['key' => 'metode', 'label' => 'Metode'],
                ['key' => 'pembayaran', 'label' => 'Pembayaran'],
                ['key' => 'review', 'label' => 'Review'],
            ] as $s)
                <span data-spa-step-dot="{{ $s['key'] }}" class="w-8 h-8 rounded-full text-xs font-bold flex items-center justify-center ring-2 ring-rose-200 bg-white/80 text-slate-400">{{ $loop->iteration }}</span>
            @endforeach
        </nav>

        <main class="flex-1 min-h-0 flex flex-col" data-spa-main>
            <section data-spa-panel="frame" class="min-h-0 flex-1 flex flex-col hidden">
                @include('partials.frame', ['step' => 1])
            </section>
            <section data-spa-panel="foto" class="min-h-0 flex-1 flex flex-col hidden">
                @include('partials.foto', ['step' => 2])
            </section>
            <section data-spa-panel="filter" class="min-h-0 flex-1 flex flex-col hidden">
                @include('partials.filter', ['step' => 3])
            </section>
            <section data-spa-panel="metode" class="min-h-0 flex-1 flex flex-col hidden">
                @include('partials.metode', ['step' => 4])
            </section>
            <section data-spa-panel="pembayaran" class="min-h-0 flex-1 flex flex-col hidden">
                @include('partials.pembayaran', ['step' => 5])
            </section>
            <section data-spa-panel="review" class="min-h-0 flex-1 flex flex-col hidden">
                @include('partials.review', ['step' => 6])
            </section>
        </main>

        <div class="fixed inset-0 z-[60] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm" data-timeout-overlay>
            <div class="bg-white rounded-3xl p-8 text-center max-w-sm mx-4 shadow-2xl">
                <div class="text-5xl mb-3">⏰</div>
                <h2 class="font-display font-bold text-xl mb-1">Waktu Sesi Habis</h2>
                <p class="text-sm text-slate-500 mb-5">Silakan ulangi sesi dari awal ya.</p>
                <a href="{{ route('home') }}" class="inline-block rounded-full bg-rose-500 text-white font-semibold px-8 py-3 hover:bg-rose-600">Kembali ke Awal</a>
            </div>
        </div>

        <div class="fixed inset-0 z-[70] pointer-events-none hidden items-center justify-center" data-flash>
            <div class="w-full h-full bg-white"></div>
        </div>
    </div>
</body>
</html>
