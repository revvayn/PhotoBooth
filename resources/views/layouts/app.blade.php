<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $brand = \App\Support\Brand::get();
    @endphp
    <title>@yield('title', $brand['name'])</title>

    @include('partials.fonts', ['brand' => $brand])

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.branding', ['brand' => $brand])
</head>
<body data-home-url="{{ route('home') }}"
      class="kiosk-theme {{ $brand['bg_image_url'] ? 'has-brand-bg' : '' }} h-dvh overflow-hidden text-slate-800 font-sans antialiased"
      @if ($brand['bg_image_url']) style="--brand-bg-image: url('{{ $brand['bg_image_url'] }}')" @endif>

    <div aria-hidden="true" class="pointer-events-none fixed inset-0 overflow-hidden">
        <div class="absolute -top-24 -left-24 w-80 h-80 rounded-full blur-3xl" style="background: color-mix(in srgb, {{ $brand['primary'] }} 30%, transparent);"></div>
        <div class="absolute -bottom-32 -right-16 w-96 h-96 rounded-full blur-3xl" style="background: color-mix(in srgb, {{ $brand['secondary'] }} 30%, transparent);"></div>
    </div>

    <div class="relative z-10 h-dvh overflow-hidden flex flex-col px-3 sm:px-8 py-3 max-w-6xl mx-auto w-full min-h-0">

        @if (isset($step))
            <header class="flex items-center justify-between gap-3 mb-3 shrink-0">
                <div class="flex items-center gap-3 min-w-0">
                    <x-brand-mark size="w-10 h-10" rounded="rounded-2xl" />
                    <div class="min-w-0">
                        <h1 class="font-display font-bold text-slate-800 leading-none truncate">{{ $brand['name'] }}</h1>
                        <p class="text-xs text-slate-500 truncate">{{ $brand['tagline'] }} &middot; Antrian {{ $queue ?? '-' }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    @php($remaining = max(0, (int) session('total_seconds', 600) - (now()->timestamp - (int) session('started_at', now()->timestamp))))
                    <div data-timer-pill class="flex items-center gap-2 rounded-full bg-white/80 backdrop-blur px-4 py-2 shadow-sm ring-1 ring-rose-100 transition-colors">
                        <x-icon name="clock" class="w-4 h-4 text-rose-500" />
                        <span class="text-sm font-bold tabular-nums text-slate-700" data-session-timer data-total="{{ $remaining }}" aria-live="polite">
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
                    <div class="mb-3 text-rose-400 flex justify-center"><x-icon name="clock" class="w-12 h-12" /></div>
                    <h2 class="font-display font-bold text-xl mb-1">Waktu Sesi Habis</h2>
                    <p class="text-sm text-slate-500 mb-2">Jangan khawatir, sesi baru bisa dimulai kapan saja.</p>
                    <p class="text-xs text-slate-400 mb-5" data-timeout-note>Mengalihkan ke layar awal…</p>
                    <a href="{{ route('home') }}" class="inline-block rounded-full bg-rose-500 text-white font-semibold px-8 py-3 hover:bg-rose-600">Kembali ke Awal</a>
                </div>
            </div>
        </main>

        <footer class="text-center text-xs text-slate-400 mt-3 shrink-0">
            &copy; {{ date('Y') }} {{ $brand['footer'] }}
        </footer>
    </div>

    @include('partials.toast')

    {{-- Lembar cetak: diisi JS saat tombol Cetak ditekan, hanya tampil di mode print. --}}
    <div id="print-sheet" class="hidden"></div>

    <div class="fixed inset-0 z-[70] pointer-events-none hidden items-center justify-center" data-flash>
        <div class="w-full h-full bg-white"></div>
    </div>
</body>
</html>
