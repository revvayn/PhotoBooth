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
    {{-- Ukuran kertas cetak diisi JS sesuai ukuran item yang dicetak. --}}
    <style id="print-page-style"></style>
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
        ['key' => 'filter', 'label' => 'Edit'],
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

    <div class="relative z-10 h-dvh overflow-hidden flex flex-col justify-center px-3 sm:px-7 py-2 sm:py-3 {{ in_array($activeKey, ['foto', 'frame'], true) ? 'max-w-7xl' : 'max-w-6xl' }} mx-auto w-full min-h-0">

        <header class="k-topbar shrink-0">
            <div class="flex items-center gap-2.5 min-w-0">
                <x-brand-mark size="w-10 h-10" rounded="rounded-xl" icon="w-5 h-5" />
                <div class="min-w-0">
                    <h1 class="font-display font-extrabold text-lg tracking-tight text-slate-900 leading-none truncate">{{ $brand['name'] }}</h1>
                    <p class="text-[11px] text-slate-500 leading-tight truncate mt-0.5">{{ $brand['tagline'] }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                @if (!empty($queue))
                    <span class="k-pill !py-1.5 !px-3 !text-xs" aria-label="Nomor antrian">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Antrian</span>
                        <span class="font-display font-extrabold text-rose-500 tabular-nums">{{ $queue }}</span>
                    </span>
                @endif
                <div data-timer-pill class="k-pill !py-1.5 !px-3 !text-xs shadow-sm transition-colors">
                    <x-icon name="clock" class="w-4 h-4 text-rose-500" />
                    <span class="tabular-nums font-semibold" data-session-timer data-total="{{ $remaining }}" aria-live="polite" aria-label="Sisa waktu sesi">
                        <span data-session-timer-min>00</span>:<span data-session-timer-sec>00</span>
                    </span>
                </div>
            </div>
        </header>

        {{-- Progres langkah: satu pil tersegmentasi agar ringkas dan mudah dibaca.
             Pada tahap foto, ruang vertikal diprioritaskan untuk kamera. --}}
        <nav class="flex justify-center my-1.5 sm:my-2 shrink-0 {{ $activeKey === 'foto' ? 'hidden' : '' }}" data-spa-stepper aria-label="Langkah sesi">
            <div class="k-stepper">
                @foreach ($steps as $i => $s)
                    @php($state = $i < $activeIdx ? 'done' : ($i === $activeIdx ? 'active' : 'todo'))
                    <div class="k-stepper-item" data-state="{{ $state }}">
                        <span data-spa-step-dot="{{ $s['key'] }}" data-state="{{ $state }}" class="k-stepper-dot">
                            @if ($state === 'done')
                                <x-icon name="check" class="w-3 h-3 text-white" />
                            @else
                                {{ $loop->iteration }}
                            @endif
                        </span>
                        <span class="hidden sm:inline">{{ $s['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </nav>
        <div class="sr-only" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $progress }}">Langkah {{ $activeIdx + 1 }} dari {{ count($steps) }}</div>

        <main class="flex-1 min-h-0 flex flex-col justify-center overflow-hidden" data-spa-main>
            <section data-spa-panel="metode" class="min-h-0 flex-1 flex flex-col overflow-y-auto overflow-x-hidden {{ $activeKey === 'metode' ? '' : 'hidden' }}">
                @include('partials.metode', ['step' => 1, 'brand' => $brand])
            </section>
            <section data-spa-panel="frame" class="min-h-0 flex-1 flex flex-col overflow-y-auto overflow-x-hidden {{ $activeKey === 'frame' ? '' : 'hidden' }}">
                @if (($metode ?? '') === 'voucher' && ! empty($voucherQuotas ?? []))
                    @include('partials.voucher-frame', ['step' => 2, 'brand' => $brand, 'voucherQuotas' => $voucherQuotas ?? []])
                @else
                    @include('partials.frame', ['step' => 2, 'brand' => $brand])
                @endif
            </section>
            <section data-spa-panel="pembayaran" class="min-h-0 flex-1 flex flex-col overflow-y-auto overflow-x-hidden {{ $activeKey === 'pembayaran' ? '' : 'hidden' }}">
                @include('partials.pembayaran', ['step' => 3, 'brand' => $brand])
            </section>
            <section data-spa-panel="foto" data-cam-width="{{ $cam_width ?? 1024 }}" data-cam-height="{{ $cam_height ?? 768 }}" data-cam-countdown="{{ $countdown ?? 3 }}" class="min-h-0 flex-1 flex flex-col overflow-y-auto overflow-x-hidden {{ $activeKey === 'foto' ? '' : 'hidden' }}">
                @include('partials.foto', ['step' => 4, 'brand' => $brand])
            </section>
            <section data-spa-panel="filter" class="min-h-0 flex-1 flex flex-col overflow-y-auto overflow-x-hidden {{ $activeKey === 'filter' ? '' : 'hidden' }}">
                @include('partials.filter', ['step' => 5, 'brand' => $brand])
            </section>
            <section data-spa-panel="review" class="min-h-0 flex-1 flex flex-col overflow-y-auto overflow-x-hidden {{ $activeKey === 'review' ? '' : 'hidden' }}">
                @include('partials.review', ['step' => 6, 'brand' => $brand])
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
