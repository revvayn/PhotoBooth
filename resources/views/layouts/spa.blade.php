<!DOCTYPE html>
<!-- SPA-KANVAS build 74652ed+ : bila tulisan ini tak ada di view-source, tab Anda basi -->
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $brand = \App\Support\Brand::get();
    @endphp
    <title>{{ $brand['name'] }} — {{ ucfirst($step ?? 'metode') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.branding', ['brand' => $brand])
</head>
<body data-home-url="{{ route('home') }}"
      class="kiosk-theme {{ $brand['bg_image_url'] ? 'has-brand-bg' : '' }} h-dvh overflow-hidden text-slate-800 font-sans antialiased"
      @if ($brand['bg_image_url']) style="--brand-bg-image: url('{{ $brand['bg_image_url'] }}')" @endif>
@php
    // Sisa waktu dihitung dari server agar tidak ter-reset di tiap pergantian panel.
    $remaining = max(0, (int) session('total_seconds', 600) - (now()->timestamp - (int) session('started_at', now()->timestamp)));
    // Controller mengirim $flowSteps agar urutan langkah mengikuti metode
    // pembayaran (voucher melewati pemilihan frame).
    $steps = $flowSteps ?? [
        ['key' => 'metode', 'label' => 'Bayar'],
        ['key' => 'frame', 'label' => 'Frame'],
        ['key' => 'pembayaran', 'label' => 'Konfirmasi'],
        ['key' => 'foto', 'label' => 'Foto'],
        ['key' => 'review', 'label' => 'Selesai'],
    ];
    $activeKey = $step ?? 'metode';
    $activeIdx = (int) collect($steps)->search(fn ($s) => $s['key'] === $activeKey);
    $activeIdx = max(0, $activeIdx);
    $progress = (int) round((($activeIdx + 1) / count($steps)) * 100);
@endphp

    {{-- Dekorasi latar; tidak mengganggu interaksi. --}}
    <div aria-hidden="true" class="pointer-events-none fixed inset-0 overflow-hidden">
        <div class="absolute -top-24 -left-24 w-80 h-80 rounded-full blur-3xl" style="background: color-mix(in srgb, {{ $brand['primary'] }} 30%, transparent);"></div>
        <div class="absolute -bottom-32 -right-16 w-96 h-96 rounded-full blur-3xl" style="background: color-mix(in srgb, {{ $brand['secondary'] }} 30%, transparent);"></div>
        <div class="absolute top-1/3 right-1/4 w-56 h-56 rounded-full blur-3xl" style="background: color-mix(in srgb, {{ $brand['accent'] }} 20%, transparent);"></div>
    </div>

    <div class="relative z-10 h-dvh overflow-hidden flex flex-col px-3 sm:px-8 py-2.5 sm:py-3 max-w-6xl mx-auto w-full min-h-0">

        <header class="flex items-center justify-between gap-3 shrink-0">
            <div class="flex items-center gap-2.5 min-w-0">
                <x-brand-mark size="w-10 h-10 sm:w-11 sm:h-11" rounded="rounded-2xl" icon="w-5 h-5 sm:w-6 sm:h-6" />
                <div class="min-w-0">
                    <h1 class="font-display font-bold text-lg sm:text-xl text-slate-800 leading-none truncate">{{ $brand['name'] }}</h1>
                    <p class="text-[11px] sm:text-xs text-slate-500 leading-tight truncate">{{ $brand['tagline'] }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-2.5 shrink-0">
                @if (!empty($queue))
                    <span class="inline-flex items-center rounded-full bg-white/80 backdrop-blur px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm font-semibold text-slate-600 shadow-sm ring-1 ring-rose-100">
                        <span class="hidden sm:inline">Antrian&nbsp;</span>
                        <span class="font-bold text-rose-500">{{ $queue }}</span>
                    </span>
                @endif
                <div data-timer-pill class="flex items-center gap-1.5 sm:gap-2 rounded-full bg-white/80 backdrop-blur px-3 sm:px-4 py-1.5 sm:py-2 shadow-sm ring-1 ring-rose-100 transition-colors">
                    <x-icon name="clock" class="w-4 h-4 text-rose-500" />
                    <span class="text-xs sm:text-sm font-bold tabular-nums text-slate-700" data-session-timer data-total="{{ $remaining }}" aria-live="polite" aria-label="Sisa waktu sesi">
                        <span data-session-timer-min>00</span>:<span data-session-timer-sec>00</span>
                    </span>
                </div>
            </div>
        </header>

        <nav class="flex items-start justify-center my-2 sm:my-3 shrink-0 overflow-x-auto scrollbar-none" data-spa-stepper aria-label="Langkah sesi">
            @foreach ($steps as $i => $s)
                @php($state = $i < $activeIdx ? 'done' : ($i === $activeIdx ? 'active' : 'todo'))
                @if ($i > 0)
                    <span class="h-0.5 w-3 sm:w-10 rounded-full mt-[18px] sm:mt-5 shrink-0 {{ $i <= $activeIdx ? 'bg-rose-400' : 'bg-rose-200' }}"></span>
                @endif
                <div class="flex flex-col items-center gap-1 w-12 sm:w-16 shrink-0">
                    <span data-spa-step-dot="{{ $s['key'] }}" data-state="{{ $state }}"
                          class="w-8 h-8 sm:w-9 sm:h-9 rounded-full text-xs sm:text-sm font-bold flex items-center justify-center ring-2 transition-all
                                 {{ $state === 'done' ? 'bg-emerald-500 ring-emerald-500 text-white' : ($state === 'active' ? 'bg-rose-500 ring-rose-500 text-white shadow-md shadow-rose-200 scale-110' : 'bg-white/80 ring-rose-200 text-slate-400') }}">
                        @if ($state === 'done')
                            <x-icon name="check" class="w-3.5 h-3.5 text-white" />
                        @else
                            {{ $loop->iteration }}
                        @endif
                    </span>
                    <span class="text-[10px] sm:text-xs font-semibold truncate max-w-full {{ $state === 'active' ? 'text-rose-600' : ($state === 'done' ? 'text-emerald-600' : 'text-slate-400') }}">{{ $s['label'] }}</span>
                </div>
            @endforeach
        </nav>
        <div class="sr-only" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $progress }}">Langkah {{ $activeIdx + 1 }} dari {{ count($steps) }}</div>

        <main class="flex-1 min-h-0 flex flex-col overflow-hidden" data-spa-main>
            <section data-spa-panel="metode" class="min-h-0 flex-1 flex flex-col overflow-y-auto overflow-x-hidden {{ $activeKey === 'metode' ? '' : 'hidden' }}">
                @include('partials.metode', ['step' => 1])
            </section>
            <section data-spa-panel="frame" class="min-h-0 flex-1 flex flex-col overflow-y-auto overflow-x-hidden {{ $activeKey === 'frame' ? '' : 'hidden' }}">
                @include('partials.frame', ['step' => 2])
            </section>
            <section data-spa-panel="pembayaran" class="min-h-0 flex-1 flex flex-col overflow-y-auto overflow-x-hidden {{ $activeKey === 'pembayaran' ? '' : 'hidden' }}">
                @include('partials.pembayaran', ['step' => 3])
            </section>
            <section data-spa-panel="foto" data-cam-width="{{ $cam_width ?? 1024 }}" data-cam-height="{{ $cam_height ?? 768 }}" data-cam-countdown="{{ $countdown ?? 3 }}" class="min-h-0 flex-1 flex flex-col overflow-y-auto overflow-x-hidden {{ $activeKey === 'foto' ? '' : 'hidden' }}">
                @include('partials.foto', ['step' => 4])
            </section>
            <section data-spa-panel="review" class="min-h-0 flex-1 flex flex-col overflow-y-auto overflow-x-hidden {{ $activeKey === 'review' ? '' : 'hidden' }}">
                @include('partials.review', ['step' => 5])
            </section>
        </main>

        {{-- Notifikasi ringan untuk error/bantuan singkat dari JS. --}}
        <div class="pointer-events-none fixed inset-x-0 top-3 z-[90] flex justify-center px-4" data-toast-region aria-live="assertive">
            <div data-toast class="hidden pointer-events-auto max-w-md w-full rounded-2xl bg-slate-900 text-white text-sm font-medium px-4 py-3 shadow-2xl ring-1 ring-white/10"></div>
        </div>

        <div class="fixed inset-0 z-[60] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm" data-timeout-overlay>
            <div class="bg-white rounded-3xl p-8 text-center max-w-sm mx-4 shadow-2xl">
                <div class="mb-3 text-rose-400 flex justify-center"><x-icon name="clock" class="w-12 h-12" /></div>
                <h2 class="font-display font-bold text-xl mb-1">Waktu Sesi Habis</h2>
                <p class="text-sm text-slate-500 mb-2">Jangan khawatir, sesi baru bisa dimulai kapan saja.</p>
                <p class="text-xs text-slate-400 mb-5" data-timeout-note>Mengalihkan ke layar awal…</p>
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
        // langkah aktif di stepper, jatuh ke pemilihan metode pembayaran.
        document.addEventListener('DOMContentLoaded', () => {
            const panels = [...document.querySelectorAll('[data-spa-panel]')];
            if (!panels.length) return;
            if (panels.some((p) => !p.classList.contains('hidden'))) return;
            const active = document.querySelector('[data-spa-step-dot][data-state="active"]');
            const key = (active && active.dataset.spaStepDot) || 'metode';
            const target = panels.find((p) => p.dataset.spaPanel === key) || panels[0];
            target.classList.remove('hidden');
        });
    </script>
</body>
</html>
