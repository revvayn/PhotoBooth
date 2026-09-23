<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Photobooth')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-dvh overflow-hidden bg-gradient-to-br from-rose-100 via-fuchsia-50 to-violet-100 text-slate-800 font-sans antialiased">

    <div class="h-dvh overflow-hidden flex flex-col px-4 sm:px-6 py-2 max-w-5xl mx-auto w-full min-h-0">

        @if (isset($step))
            <header class="flex items-center justify-between gap-3 mb-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-rose-400 to-violet-500 flex items-center justify-center shadow-md shadow-rose-200">
                        <span class="text-white text-lg">📸</span>
                    </div>
                    <div>
                        <h1 class="font-display font-bold text-slate-800 leading-none">Photobooth</h1>
                        <p class="text-xs text-slate-500">{{ $queue ?? '' }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="rounded-full bg-white/80 backdrop-blur px-4 py-2 shadow-sm ring-1 ring-rose-100">
                        <span class="text-sm font-semibold tabular-nums" data-session-timer data-total="{{ session('total_seconds', 300) }}">
                            <span class="text-rose-500 font-bold" data-session-timer-min>00</span>:<span data-session-timer-sec>00</span>
                        </span>
                    </div>
                </div>
            </header>

            <nav class="grid grid-cols-3 gap-2 sm:gap-3 mb-8" aria-label="Langkah">
                @foreach ([
                    ['n' => 1, 'label' => 'Cek Pembayaran'],
                    ['n' => 2, 'label' => 'Sesi Foto'],
                    ['n' => 3, 'label' => 'Print & Download'],
                ] as $s)
                    @php($active = $step === $s['n'])
                    @php($done = $step > $s['n'])
                    <div class="flex items-center gap-2 sm:gap-3">
                        @if (!$loop->first)
                            <div class="h-0.5 flex-1 max-w-6 {{ $done ? 'bg-rose-400' : 'bg-slate-200' }} rounded-full"></div>
                        @endif
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="shrink-0 w-7 h-7 rounded-full text-xs font-bold flex items-center justify-center {{ $done ? 'bg-emerald-400 text-white' : ($active ? 'bg-rose-500 text-white shadow-md shadow-rose-200' : 'bg-white text-slate-400 ring-1 ring-slate-200') }}">
                                {{ $done ? '✓' : $s['n'] }}
                            </span>
                            <span class="hidden sm:block text-xs font-medium {{ $active ? 'text-rose-600' : 'text-slate-400' }}">{{ $s['label'] }}</span>
                        </div>
                    </div>
                @endforeach
            </nav>
        @endif

        <main class="flex-1 flex flex-col">
            @yield('content')

            <div class="fixed inset-0 z-[60] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm" data-timeout-overlay>
                <div class="bg-white rounded-3xl p-8 text-center max-w-sm mx-4 shadow-2xl">
                    <div class="text-5xl mb-3">⏰</div>
                    <h2 class="font-display font-bold text-xl mb-1">Waktu Sesi Habis</h2>
                    <p class="text-sm text-slate-500 mb-5">Silakan ulangi sesi dari awal ya.</p>
                    <a href="{{ route('home') }}" class="inline-block rounded-full bg-rose-500 text-white font-semibold px-8 py-3 hover:bg-rose-600">Kembali ke Awal</a>
                </div>
            </div>
        </main>

        <footer class="text-center text-xs text-slate-400 mt-8">
            © {{ date('Y') }} Photobooth · PERKAKASKU
        </footer>
    </div>

    <div class="fixed inset-0 z-[70] pointer-events-none hidden items-center justify-center" data-flash>
        <div class="w-full h-full bg-white"></div>
    </div>
</body>
</html>