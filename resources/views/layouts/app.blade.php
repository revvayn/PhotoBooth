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

    <div class="h-dvh overflow-hidden flex flex-col px-4 sm:px-8 py-3 max-w-6xl mx-auto w-full min-h-0">

        @if (isset($step))
            <header class="flex items-center justify-between gap-3 mb-3 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-rose-400 to-violet-500 flex items-center justify-center shadow-md shadow-rose-200">
                        <span class="text-white"><x-icon name="camera" class="w-5 h-5 text-white" /></span>
                    </div>
                    <div>
                        <h1 class="font-display font-bold text-slate-800 leading-none">Photobooth</h1>
                        <p class="text-xs text-slate-500">{{ $queue ?? '' }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    @php($remaining = max(0, (int) session('total_seconds', 600) - (now()->timestamp - (int) session('started_at', now()->timestamp))))
                    <div data-timer-pill class="flex items-center gap-2 rounded-full bg-white/80 backdrop-blur px-4 py-2 shadow-sm ring-1 ring-rose-100 transition-colors">
                        <x-icon name="clock" class="w-4 h-4 text-rose-500" />
                        <span class="text-sm font-bold tabular-nums text-slate-700" data-session-timer data-total="{{ $remaining }}">
                            <span data-session-timer-min>00</span>:<span data-session-timer-sec>00</span>
                        </span>
                    </div>
                </div>
            </header>
        @endif

        <main class="flex-1 min-h-0 flex flex-col overflow-y-auto">
            @yield('content')

            <div class="fixed inset-0 z-[60] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm" data-timeout-overlay>
                <div class="bg-white rounded-3xl p-8 text-center max-w-sm mx-4 shadow-2xl">
                    <div class="mb-3 text-slate-400"><x-icon name="clock" class="w-12 h-12" /></div>
                    <h2 class="font-display font-bold text-xl mb-1">Waktu Sesi Habis</h2>
                    <p class="text-sm text-slate-500 mb-5">Silakan ulangi sesi dari awal ya.</p>
                    <a href="{{ route('home') }}" class="inline-block rounded-full bg-rose-500 text-white font-semibold px-8 py-3 hover:bg-rose-600">Kembali ke Awal</a>
                </div>
            </div>
        </main>

        <footer class="text-center text-xs text-slate-400 mt-3 shrink-0">
            © {{ date('Y') }} Photobooth · PERKAKASKU
        </footer>
    </div>

    <div class="fixed inset-0 z-[70] pointer-events-none hidden items-center justify-center" data-flash>
        <div class="w-full h-full bg-white"></div>
    </div>
</body>
</html>
