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

    @include('partials.fonts', ['brand' => $brand])

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
        <div class="absolute inset-0" style="background: radial-gradient(ellipse 80% 55% at 50% 0%, color-mix(in srgb, {{ $brand['primary'] }} 15%, transparent), transparent 72%), radial-gradient(ellipse 80% 55% at 50% 100%, color-mix(in srgb, {{ $brand['secondary'] }} 13%, transparent), transparent 72%);"></div>
        <div class="absolute inset-0 k-grid-bg opacity-60"></div>
    </div>

    <div class="relative z-10 h-dvh overflow-hidden flex flex-col px-3 sm:px-8 py-3 sm:py-4 max-w-6xl mx-auto w-full min-h-0">

        <header class="flex items-center justify-between gap-3 shrink-0">
            <div class="flex items-center gap-3 min-w-0">
                <x-brand-mark size="w-11 h-11" rounded="rounded-2xl" icon="w-6 h-6" />
                <div class="min-w-0">
                    <h1 class="font-display font-extrabold text-xl tracking-tight text-slate-900 leading-none truncate">{{ $brand['name'] }}</h1>
                    <p class="text-[11px] sm:text-xs text-slate-500 leading-tight truncate mt-0.5">{{ $brand['tagline'] }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                @if (!empty($queue))
                    <span class="k-pill shadow-sm" aria-label="Nomor antrian"><span class="text-rose-500">{{ $queue }}</span></span>
                @endif
                <div data-timer-pill class="k-pill shadow-sm transition-colors">
                    <x-icon name="clock" class="w-4 h-4 text-rose-500" />
                    <span class="tabular-nums" data-session-timer data-total="{{ $remaining }}" aria-live="polite" aria-label="Sisa waktu sesi">
                        <span data-session-timer-min>00</span>:<span data-session-timer-sec>00</span>
                    </span>
                </div>
            </div>
        </header>

        {{-- Progres langkah: label terlihat dan jarak seimbang agar pengguna tahu posisinya. --}}
        <nav class="flex items-start justify-center my-3 sm:my-4 shrink-0 overflow-x-auto scrollbar-none" data-spa-stepper aria-label="Langkah sesi">
            <div class="k-card inline-flex items-start gap-0.5 sm:gap-1.5 px-3 sm:px-5 py-2 sm:py-2.5">
                @foreach ($steps as $i => $s)
                    @php($state = $i < $activeIdx ? 'done' : ($i === $activeIdx ? 'active' : 'todo'))
                    @if ($i > 0)
                        <span class="k-stepper-line w-4 sm:w-8 lg:w-11 shrink-0 mt-4 sm:mt-[18px]" data-done="{{ $i <= $activeIdx ? 'true' : 'false' }}"></span>
                    @endif
                    <div class="flex flex-col items-center gap-1 w-12 sm:w-16 shrink-0">
                        <span data-spa-step-dot="{{ $s['key'] }}" data-state="{{ $state }}"
                              class="w-8 h-8 sm:w-9 sm:h-9 rounded-full text-xs sm:text-sm font-bold flex items-center justify-center ring-2 transition-all
                                     {{ $state === 'done' ? 'bg-emerald-500 ring-emerald-500 text-white' : ($state === 'active' ? 'bg-rose-500 ring-rose-500 text-white shadow-md shadow-rose-200 scale-110' : 'bg-white ring-rose-200 text-slate-400') }}">
                            @if ($state === 'done')
                                <x-icon name="check" class="w-3.5 h-3.5 text-white" />
                            @else
                                {{ $loop->iteration }}
                            @endif
                        </span>
                        <span class="text-[10px] sm:text-xs font-semibold truncate max-w-full {{ $state === 'active' ? 'text-rose-600' : ($state === 'done' ? 'text-emerald-600' : 'text-slate-400') }}">{{ $s['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </nav>
        <div class="sr-only" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $progress }}">Langkah {{ $activeIdx + 1 }} dari {{ count($steps) }}</div>

        <main class="flex-1 min-h-0 flex flex-col overflow-hidden" data-spa-main>
            <section data-spa-panel="metode" class="min-h-0 flex-1 flex flex-col overflow-y-auto overflow-x-hidden {{ $activeKey === 'metode' ? '' : 'hidden' }}">
                @include('partials.metode', ['step' => 1, 'brand' => $brand])
            </section>
            <section data-spa-panel="frame" class="min-h-0 flex-1 flex flex-col overflow-y-auto overflow-x-hidden {{ $activeKey === 'frame' ? '' : 'hidden' }}">
                @include('partials.frame', ['step' => 2, 'brand' => $brand])
            </section>
            <section data-spa-panel="pembayaran" class="min-h-0 flex-1 flex flex-col overflow-y-auto overflow-x-hidden {{ $activeKey === 'pembayaran' ? '' : 'hidden' }}">
                @include('partials.pembayaran', ['step' => 3, 'brand' => $brand])
            </section>
            <section data-spa-panel="foto" data-cam-width="{{ $cam_width ?? 1024 }}" data-cam-height="{{ $cam_height ?? 768 }}" data-cam-countdown="{{ $countdown ?? 3 }}" class="min-h-0 flex-1 flex flex-col overflow-y-auto overflow-x-hidden {{ $activeKey === 'foto' ? '' : 'hidden' }}">
                @include('partials.foto', ['step' => 4, 'brand' => $brand])
            </section>
            <section data-spa-panel="review" class="min-h-0 flex-1 flex flex-col overflow-y-auto overflow-x-hidden {{ $activeKey === 'review' ? '' : 'hidden' }}">
                @include('partials.review', ['step' => 5, 'brand' => $brand])
            </section>
        </main>

        {{-- Notifikasi ringan untuk error/bantuan singkat dari JS. --}}
        @include('partials.toast')

        <div class="fixed inset-0 z-[60] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm" data-timeout-overlay>
            <div class="k-card-solid p-8 text-center max-w-sm mx-4 shadow-2xl">
                <div class="mb-3 text-rose-400 flex justify-center"><x-icon name="clock" class="w-12 h-12" /></div>
                <h2 class="font-display font-extrabold text-2xl mb-1">Waktu habis</h2>
                <p class="text-sm text-slate-400 mb-5" data-timeout-note>Kembali ke awal…</p>
                <a href="{{ route('home') }}" class="k-btn">Mulai Lagi</a>
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
