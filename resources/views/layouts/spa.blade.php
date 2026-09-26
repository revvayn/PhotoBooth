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

    <div class="h-dvh overflow-hidden flex flex-col px-4 sm:px-6 py-2 max-w-5xl mx-auto w-full min-h-0">

        <header class="flex items-center justify-between gap-4 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-rose-400 to-violet-500 flex items-center justify-center shadow-md shadow-rose-200">
                    <span class="text-white"><x-icon name="camera" class="w-5 h-5 text-white" /></span>
                </div>
                <div class="leading-tight">
                    <h1 class="font-display font-bold text-slate-800">Photobooth</h1>
                    <p class="text-xs text-slate-500" data-session-timer data-total="{{ (int) session('total_seconds', 300) }}">
                        <span class="font-bold text-rose-500" data-session-timer-min>00</span>:<span data-session-timer-sec>00</span>
                    </p>
                </div>
            </div>
        </header>

        <nav class="flex items-start justify-center gap-1 sm:gap-2 my-2 shrink-0" data-spa-stepper>
            @foreach ([
                ['key' => 'frame', 'label' => 'Frame'],
                ['key' => 'foto', 'label' => 'Foto'],
                ['key' => 'filter', 'label' => 'Filter'],
                ['key' => 'metode', 'label' => 'Metode'],
                ['key' => 'pembayaran', 'label' => 'Bayar'],
                ['key' => 'review', 'label' => 'Review'],
            ] as $s)
                <a href="{{ route('spa', ['step' => $s['key']]) }}" class="flex flex-col items-center gap-0.5 w-10 sm:w-12 hover:opacity-80 transition">
                    <span data-spa-step-dot="{{ $s['key'] }}" class="w-7 h-7 rounded-full text-[11px] font-bold flex items-center justify-center ring-2 {{ ($step ?? 'frame') === $s['key'] ? 'ring-rose-500 bg-rose-500 text-white shadow-md shadow-rose-200' : 'ring-rose-200 bg-white/80 text-slate-400' }}">{{ $loop->iteration }}</span>
                    <span class="text-[9px] sm:text-[10px] font-semibold {{ ($step ?? 'frame') === $s['key'] ? 'text-rose-600' : 'text-slate-400' }}">{{ $s['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <main class="flex-1 min-h-0 flex flex-col overflow-hidden" data-spa-main>
            <section data-spa-panel="frame" class="min-h-0 flex-1 flex flex-col overflow-y-auto overflow-x-hidden {{ ($step ?? 'frame') === 'frame' ? '' : 'hidden' }}">
                @include('partials.frame', ['step' => 1])
            </section>
            <section data-spa-panel="foto" data-cam-width="{{ $cam_width ?? 1024 }}" data-cam-height="{{ $cam_height ?? 768 }}" data-countdown="{{ $countdown ?? 3 }}" class="min-h-0 flex-1 flex flex-col overflow-y-auto overflow-x-hidden {{ ($step ?? 'frame') === 'foto' ? '' : 'hidden' }}">
                @include('partials.foto', ['step' => 2])
            </section>
            <section data-spa-panel="filter" class="min-h-0 flex-1 flex flex-col overflow-y-auto overflow-x-hidden {{ ($step ?? 'frame') === 'filter' ? '' : 'hidden' }}">
                @include('partials.filter', ['step' => 3])
            </section>
            <section data-spa-panel="metode" class="min-h-0 flex-1 flex flex-col overflow-y-auto overflow-x-hidden {{ ($step ?? 'frame') === 'metode' ? '' : 'hidden' }}">
                @include('partials.metode', ['step' => 4])
            </section>
            <section data-spa-panel="pembayaran" class="min-h-0 flex-1 flex flex-col overflow-y-auto overflow-x-hidden {{ ($step ?? 'frame') === 'pembayaran' ? '' : 'hidden' }}">
                @include('partials.pembayaran', ['step' => 5])
            </section>
            <section data-spa-panel="review" class="min-h-0 flex-1 flex flex-col overflow-y-auto overflow-x-hidden {{ ($step ?? 'frame') === 'review' ? '' : 'hidden' }}">
                @include('partials.review', ['step' => 6])
            </section>
        </main>

        <div class="fixed inset-0 z-[60] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm" data-timeout-overlay>
            <div class="bg-white rounded-3xl p-8 text-center max-w-sm mx-4 shadow-2xl">
                <div class="mb-3 text-slate-400"><x-icon name="clock" class="w-12 h-12" /></div>
                <h2 class="font-display font-bold text-xl mb-1">Waktu Sesi Habis</h2>
                <p class="text-sm text-slate-500 mb-5">Silakan ulangi sesi dari awal ya.</p>
                <a href="{{ route('home') }}" class="inline-block rounded-full bg-rose-500 text-white font-semibold px-8 py-3 hover:bg-rose-600">Kembali ke Awal</a>
            </div>
        </div>

        <div class="fixed inset-0 z-[70] pointer-events-none hidden items-center justify-center" data-flash>
            <div class="w-full h-full bg-white"></div>
        </div>
    </div>

    <script>
        // Jaring pengaman atomik: dokumen ini selalu me-render tepat satu panel
        // tanpa 'hidden'. Bila nol panel tampil (dokumen basi/inkonsisten),
        // tampilkan panel sesuai dot aktif, jatuh ke frame.
        document.addEventListener('DOMContentLoaded', () => {
            const panels = [...document.querySelectorAll('[data-spa-panel]')];
            if (!panels.length) return;
            if (panels.some((p) => !p.classList.contains('hidden'))) return;
            const active = document.querySelector('[data-spa-step-dot].bg-rose-500');
            const key = (active && active.dataset.spaStepDot) || 'frame';
            const target = panels.find((p) => p.dataset.spaPanel === key) || panels[0];
            target.classList.remove('hidden');
        });
    </script>
    <div id="spa-diag" style="position:fixed;left:8px;bottom:8px;z-index:9999;background:#111;color:#0f0;font:11px/1.5 monospace;padding:6px 8px;border-radius:8px;max-width:92vw;white-space:pre-wrap;">DIAG: menunggu…</div>
    <script>
        // Diagnostik sementara: laporkan fakta render apa adanya.
        (function () {
            function report() {
                try {
                    const panels = [...document.querySelectorAll('[data-spa-panel]')];
                    const vis = panels.filter((p) => !p.classList.contains('hidden'));
                    const foto = document.querySelector('[data-spa-panel="foto"]');
                    const main = document.querySelector('[data-spa-main]');
                    const el = document.getElementById('spa-diag');
                    if (!el) return;
                    el.textContent = 'DIAG panels=' + panels.length + ' vis=' + vis.length
                        + ' | fotoH=' + (foto ? foto.offsetHeight : -1)
                        + ' | mainH=' + (main ? main.offsetHeight : -1)
                        + ' | vw=' + window.innerWidth + 'x' + window.innerHeight
                        + ' | js=jalan';
                    // Putaran 2: isi DOM hidup (anak, h2, video, display, scroll).
                    const kids = foto ? foto.children.length : -1;
                    const htmllen = foto ? (foto.innerHTML || '').length : -1;
                    const h2 = foto && foto.querySelector('h2') ? foto.querySelector('h2').textContent.trim().slice(0, 12) : 'TIDAK-ADA';
                    const vid = foto && foto.querySelector('#camera') ? 'ada' : 'TIDAK-ADA';
                    const disp = foto ? getComputedStyle(foto).display : '-';
                    const soli = foto ? (foto.scrollHeight + '/' + foto.clientHeight) : '-';
                    el.textContent += ' || DOM anak=' + kids + ' html=' + htmllen + ' h2=[' + h2 + '] video=' + vid + ' disp=' + disp + ' scroll=' + soli;
                    // Putaran 3: status kamera hidup (video, stream, overlay).
                    const vid2 = foto ? foto.querySelector('#camera') : null;
                    const vs = vid2 && vid2.srcObject ? vid2.srcObject.getVideoTracks() : [];
                    const cdo = foto ? foto.querySelector('[data-countdown]') : null;
                    el.textContent += ' || CAM vw=' + (vid2 ? (vid2.videoWidth + 'x' + vid2.videoHeight) : 'NOVIDEO')
                        + ' rs=' + (vid2 ? vid2.readyState : '-')
                        + ' tracks=' + vs.length + (vs.length ? ':' + vs.map((t) => t.readyState + '/' + (t.enabled ? 'on' : 'off')).join(',') : '')
                        + ' cd=' + (cdo ? (cdo.classList.contains('hidden') ? 'hidden' : 'TAMPIL') : '-');
                } catch (e) {
                    const el = document.getElementById('spa-diag');
                    if (el) el.textContent = 'DIAG error: ' + e;
                }
            }
            document.addEventListener('DOMContentLoaded', () => { setTimeout(report, 800); setTimeout(report, 3000); });
        })();
    </script>
</body>
</html>
