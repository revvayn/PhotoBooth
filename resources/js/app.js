import './bootstrap';
import QRCode from 'qrcode';

const FILTERS = {
    asli: 'none',
    bw: 'grayscale(1)',
    vintage: 'sepia(0.55) contrast(1.05) brightness(0.95)',
    warm: 'sepia(0.35) saturate(1.4) brightness(1.05)',
    cool: 'hue-rotate(180deg) saturate(0.75)',
    fade: 'contrast(0.9) brightness(1.12) saturate(0.7)',
    contrast: 'contrast(1.5) saturate(1.2)',
    neon: 'saturate(1.8) contrast(1.2) hue-rotate(-8deg)',
};

/* ---------------- Toast kecil untuk pesan error/info ---------------- */

let toastTimer = null;

function showToast(message, tone = 'error') {
    const box = document.querySelector('[data-toast]');
    if (!box) return;

    box.textContent = message;
    box.classList.remove('bg-slate-900', 'bg-emerald-600', 'bg-amber-600', 'bg-red-600');
    const cls = tone === 'success' ? 'bg-emerald-600'
        : (tone === 'warning' ? 'bg-amber-600'
        : (tone === 'error' ? 'bg-red-600' : 'bg-slate-900'));
    box.classList.add(cls);
    box.classList.remove('hidden');
    box.classList.add('flex', 'items-center');
    requestAnimationFrame(() => {
        box.classList.remove('opacity-0', '-translate-y-3');
        box.classList.add('opacity-100', 'translate-y-0');
    });

    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
        box.classList.add('opacity-0', '-translate-y-3');
        box.classList.remove('opacity-100', 'translate-y-0');
        setTimeout(() => {
            box.classList.add('hidden');
            box.classList.remove('flex', 'items-center');
        }, 300);
    }, 4000);
}
window.showToast = showToast;

/* ---------------- Session Timer ---------------- */

function initTimer() {
    const el = document.querySelector('[data-session-timer]');
    if (!el) return;

    let remaining = parseInt(el.dataset.total || '300', 10);
    const minEl = el.querySelector('[data-session-timer-min]');
    const secEl = el.querySelector('[data-session-timer-sec]');
    const overlay = document.querySelector('[data-timeout-overlay]');
    const note = overlay?.querySelector('[data-timeout-note]');
    const pill = el.closest('[data-timer-pill]');

    let expired = false;
    let interval = null;

    const paint = (s) => {
        const m = Math.floor(s / 60).toString().padStart(2, '0');
        const r = (s % 60).toString().padStart(2, '0');
        if (minEl) minEl.textContent = m;
        if (secEl) secEl.textContent = r;

        // Peringatan visual saat sisa waktu <= 1 menit.
        if (pill) {
            const warn = s <= 60;
            pill.classList.toggle('bg-red-50', warn);
            pill.classList.toggle('ring-red-300', warn);
            pill.classList.toggle('animate-pulse', warn && s > 0);
            el.classList.toggle('text-red-600', warn);
            el.classList.toggle('text-slate-700', !warn);
        }
    };

    const expire = () => {
        if (expired) return;
        expired = true;
        clearInterval(interval);
        paint(0);

        if (!overlay) {
            window.location.href = document.body.dataset.homeUrl || '/';
            return;
        }

        overlay.classList.remove('hidden');
        overlay.classList.add('flex');

        // Beri waktu singkat untuk membaca, lalu kembali ke layar awal supaya
        // kiosk tidak tertinggal di sesi yang sudah mati.
        let seconds = 10;
        if (note) note.textContent = `Kembali dalam ${seconds}…`;
        const redirectTimer = setInterval(() => {
            seconds -= 1;
            if (note) note.textContent = seconds > 0
                ? `Kembali dalam ${seconds}…`
                : 'Kembali ke awal…';
            if (seconds <= 0) {
                clearInterval(redirectTimer);
                window.location.href = document.body.dataset.homeUrl || '/';
            }
        }, 1000);
    };

    paint(remaining);
    if (remaining <= 0) {
        expire();
        return;
    }

    interval = setInterval(() => {
        remaining -= 1;
        paint(Math.max(0, remaining));
        if (remaining <= 0) expire();
    }, 1000);
}
/* ---------------- Camera / Photo Session ---------------- */

function initPhotoSession() {
    const video = document.querySelector('[data-camera]') ?? document.getElementById('camera');
    if (!video) return;

    // Dokumen SPA memuat semua panel sekaligus; kamera hanya boleh menyala
    // saat panel foto yang sedang tampil (bukan di tahap frame/bayar/dst).
    const fotoPanel = document.querySelector('[data-spa-panel="foto"]');
    if (fotoPanel && fotoPanel.classList.contains('hidden')) return;

    // Video display:none sampai benar-benar mengalir (event playing):
    // elemen video mati tidak boleh punya layer komposit sama sekali.
    video.addEventListener('playing', () => video.classList.remove('hidden'));

    const loading = document.querySelector('[data-camera-loading]');
    const errorBox = document.querySelector('[data-camera-error]');
    const shootBtn = document.querySelector('[data-shoot-btn]');
    const countdown = document.querySelector('[data-countdown-overlay]');
    const countdownNum = document.querySelector('[data-countdown-num]');
    const flashOverlay = document.querySelector('[data-flash-overlay]');
    const nextBtn = document.querySelector('[data-next-btn]');
    const progressEl = document.querySelector('[data-photo-progress]');
    const photoError = document.querySelector('[data-photo-error]');

    // Global index foto tersusun berurutan sesuai urutan frame di cart.
    // Batasi ke panel foto: kanvas di panel filter/review juga punya
    // atribut data-frame-slots (berisi koordinat), jangan sampai terhitung.
    const fotoRoot = document.querySelector('[data-spa-panel="foto"]') || document;
    const slotGroups = [...fotoRoot.querySelectorAll('[data-frame-slots]')]
        .filter((el) => el.querySelector('[data-shot-item]'));
    const tabs = [...fotoRoot.querySelectorAll('[data-frame-tab]')];

    const frames = [];
    slotGroups.forEach((group, fIdx) => {
        const count = group.querySelectorAll('[data-shot-item]').length;
        frames.push({ index: fIdx, start: frames.length ? frames[frames.length - 1].start + frames[frames.length - 1].count : 0, count });
    });

    if (!frames.length) return;

    // Total = jumlah slot SEMUA frame (wadah thumbs), bukan tab pertama.
    const photoCountEl = document.querySelector('[data-shot-thumbs][data-photo-count]');
    let photoCount = parseInt(photoCountEl?.dataset.photoCount || '0', 10);
    if (!photoCount || photoCount < 1) photoCount = frames.reduce((s, f) => s + f.count, 0);

    const frameOf = (i) => frames.find((f) => i >= f.start && i < f.start + f.count)?.index ?? 0;
    const frameDone = (f) => photos.slice(f.start, f.start + f.count).every(Boolean);

    let stream = null;
    const photos = new Array(photoCount).fill(null);
    let shooting = false;
    let activeFrame = 0;

    const stopStream = () => {
        if (stream) stream.getTracks().forEach((t) => t.stop());
    };

    const showError = (msg) => {
        if (!errorBox) return;
        const title = errorBox.querySelector('[data-error-title]');
        if (title) title.textContent = msg || 'Kamera tidak ditemukan';
        errorBox.classList.remove('hidden');
        errorBox.classList.add('flex');
    };

    const hideError = () => {
        if (!errorBox) return;
        errorBox.classList.add('hidden');
        errorBox.classList.remove('flex');
    };

    const showPhotoError = (msg) => {
        if (!photoError) {
            if (msg) showToast(msg);
            return;
        }
        photoError.textContent = msg || '';
        photoError.hidden = !msg;
        if (msg) showToast(msg);
    };

    const activateTab = (fIdx) => {
        activeFrame = fIdx;
        slotGroups.forEach((g, i) => { g.hidden = i !== fIdx; });
        paintTabs();
        renderFotoPreview();
    };

    const paintTabs = () => {
        tabs.forEach((t, i) => {
            const done = frameDone(frames[i]);
            const isActive = i === activeFrame;
            t.setAttribute('aria-selected', isActive ? 'true' : 'false');
            t.dataset.done = done ? 'true' : 'false';
            const badge = t.querySelector('[data-tab-status]');
            if (badge) badge.textContent = done ? '✓ Selesai' : (frames[i].count + ' foto');
        });

        const doneCount = photos.filter(Boolean).length;
        if (progressEl) progressEl.textContent = doneCount + '/' + photoCount;

        const bar = document.querySelector('[data-photo-progress-bar]');
        if (bar) bar.style.width = Math.round((doneCount / Math.max(photoCount, 1)) * 100) + '%';
        const track = document.querySelector('[data-photo-progress-track]');
        if (track) track.setAttribute('aria-valuenow', String(doneCount));
    };

    // Pengaturan kamera dari admin (via panel foto SPA); default aman bila absen.
    const spaFoto = document.querySelector('[data-spa-panel="foto"]');
    const camW = parseInt(spaFoto?.dataset.camWidth || '1024', 10) || 1024;
    const camH = parseInt(spaFoto?.dataset.camHeight || '768', 10) || 768;
    const countFrom = Math.min(10, Math.max(1, parseInt(spaFoto?.dataset.camCountdown || '3', 10) || 3));

    const camSwitch = document.querySelector('[data-camera-switch]');
    let currentDeviceId = null;
    let autoTried = false;

    const withTimeout = (p, ms, label) => Promise.race([
        p,
        new Promise((_, rej) => setTimeout(() => rej(new Error(label || 'timeout')), ms)),
    ]);

    const startStream = async (deviceId) => {
        if (shooting) return false;
        stopStream();
        video.classList.add('hidden');
        if (loading) loading.classList.remove('hidden');
        hideError();
        try {
            const s = await withTimeout(navigator.mediaDevices.getUserMedia({
                video: {
                    width: { ideal: camW },
                    height: { ideal: camH },
                    ...(deviceId ? { deviceId: { exact: deviceId } } : {}),
                },
                audio: false,
            }), 10000, 'camera-timeout');
            stream = s;
            video.srcObject = s;
            try {
                await withTimeout(video.play(), 4000, 'play-timeout');
            } catch (e) {
                // autoplay ditolak browser; user menekan Mulai Foto untuk memicu play ulang
            }
            currentDeviceId = deviceId ?? null;
            if (loading) loading.classList.add('hidden');
            if (shootBtn) shootBtn.disabled = false;
            return true;
        } catch (err) {
            if (loading) loading.classList.add('hidden');
            showError();
            if (shootBtn) shootBtn.disabled = true;
            return false;
        }
    };

    // Tombol coba lagi di overlay error kamera — jangan biarkan pelanggan
    // tertahan tanpa cara memulihkan kamera.
    errorBox?.querySelector('[data-camera-retry]')?.addEventListener('click', async () => {
        if (shootBtn) shootBtn.disabled = true;
        const ok = await startStream(currentDeviceId);
        if (ok) hideError();
    });

    const CAM_SVG = '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="7" width="18" height="13" rx="2"/><circle cx="12" cy="13" r="3.5"/><path d="M9 7l1.5-2.5h5L17 7"/></svg>';

    const paintCamSwitch = (devices) => {
        if (!camSwitch) return;
        if (devices.length < 2) {
            camSwitch.remove();
            return;
        }
        camSwitch.innerHTML = '';
        devices.forEach((d, i) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            const active = (d.deviceId || null) === (currentDeviceId || null);
            btn.className = 'k-segment-item ring-1 ring-slate-200 min-w-0 max-w-[240px] bg-white';
            btn.setAttribute('aria-pressed', active ? 'true' : 'false');
            btn.innerHTML = CAM_SVG;
            const lb = document.createElement('span');
            lb.className = 'truncate';
            lb.textContent = d.label || 'Kamera ' + (i + 1);
            btn.appendChild(lb);
            btn.addEventListener('click', async () => {
                if ((d.deviceId || null) === (currentDeviceId || null)) return;
                const ok = await startStream(d.deviceId || null);
                if (ok) {
                    currentDeviceId = d.deviceId || null;
                    paintCamSwitch(devices);
                }
            });
            camSwitch.appendChild(btn);
        });
    };

    startStream(null).then(() => {
        if (!navigator.mediaDevices?.enumerateDevices) {
            if (camSwitch) camSwitch.remove();
            return;
        }
        navigator.mediaDevices.enumerateDevices()
            .then(async (all) => {
                const cams = all.filter((d) => d.kind === 'videoinput');
                paintCamSwitch(cams);
                // Jangan default ke kamera virtual (OBS/dll yang output-nya hitam):
                // bila masih di default browser, pindah ke kamera fisik pertama.
                if (currentDeviceId === null) {
                    const physical = cams.find((d) => !/virtual|obs|manycam|snap|xsplit|droidcam|ivcam|vcam/i.test(d.label || ''));
                    if (physical && cams.length > 1) {
                        const ok = await startStream(physical.deviceId || null);
                        if (ok) {
                            currentDeviceId = physical.deviceId || null;
                            paintCamSwitch(cams);
                        }
                    }
                }
                // Bila stream masih mati (menggantung/tanpa dimensi), coba semua
                // kamera fisik-dulu satu per satu; jangan diam tanpa kabar.
                const hasVideo = () => !!video.videoWidth;
                if (!hasVideo() && !autoTried) {
                    autoTried = true;
                    const score = (d) => (/virtual|obs|manycam|snap|xsplit|droidcam|ivcam|vcam/i.test(d.label || '') ? 1 : 0);
                    const ordered = [...cams].sort((a, b) => score(a) - score(b));
                    for (const d of ordered) {
                        const ok = await startStream(d.deviceId || null);
                        if (ok && hasVideo()) {
                            currentDeviceId = d.deviceId || null;
                            paintCamSwitch(cams);
                            break;
                        }
                    }
                    if (!hasVideo()) showError('Kamera tidak merespons, coba pilih kamera lain.');
                }
            })
            .catch(() => { if (camSwitch) camSwitch.remove(); });
    });

    const wait = (ms) => new Promise((r) => setTimeout(r, ms));

    const snapshot = () => {
        // Video belum siap (lebar 0) => jangan hasilkan frame hitam.
        if (!video.videoWidth || !video.videoHeight) return null;
        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth || 640;
        canvas.height = video.videoHeight || 480;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        return canvas.toDataURL('image/jpeg', 0.92);
    };

    const showFlash = async () => {
        if (flashOverlay) {
            flashOverlay.classList.remove('hidden');
            await wait(110);
            flashOverlay.classList.add('hidden');
        }
    };

    const updateSlot = (i, dataUrl) => {
        const item = document.querySelector(`[data-shot-item="${i}"]`);
        if (!item) return;
        const img = item.querySelector('[data-shot-img]');
        const status = item.querySelector('[data-shot-status]');
        const icon = item.querySelector('[data-shot-icon]');
        const retake = item.querySelector('[data-retake]');
        if (icon) icon.hidden = true;
        if (status) status.textContent = 'Selesai ✓';
        item.dataset.done = 'true';
        if (img) {
            img.src = dataUrl;
            img.classList.remove('hidden');
        }
        if (retake) retake.classList.remove('hidden');
    };

    const updateNext = () => {
        const done = photos.every(Boolean);
        // Gunakan atribut `hidden`, bukan class: class display (inline-flex)
        // menang atas .hidden pada urutan CSS Tailwind.
        if (nextBtn) {
            nextBtn.hidden = !done;
            nextBtn.setAttribute('aria-disabled', done ? 'false' : 'true');
        }
        if (shootBtn) {
            shootBtn.hidden = done || shooting;
        }
    };

    const activeFrameIndex = () => {
        const tab = document.querySelector('[data-frame-tab][aria-selected="true"]');
        return tab ? parseInt(tab.dataset.frameTab || '0', 10) : 0;
    };

    const renderFotoPreview = async () => {
        const canvas = document.querySelector('[data-foto-preview]');
        if (!canvas) return;
        const placeholder = document.querySelector('[data-preview-placeholder]');
        const idx = activeFrame;
        const container = document.querySelector(`[data-frame-slots="${idx}"]`);
        if (!container) { canvas.classList.add('hidden'); if (placeholder) placeholder.hidden = false; return; }
        const frameImage = container.dataset.frameImage || null;
        let slots = [];
        try { slots = JSON.parse(container.dataset.frameSlotsJson || '[]'); } catch (e) { slots = []; }
        // collect photos for this frame's slots
        const allShots = [...document.querySelectorAll('[data-shot-item]')];
        // map global shot index -> photo dataUrl already in photos[]
        // slots correspond to first N photos of this frame; find their global indices
        const frameShotItems = [...container.querySelectorAll('[data-shot-item]')];
        const framePhotos = frameShotItems.map((it) => {
            const gidx = parseInt(it.dataset.shotItem, 10);
            return photos[gidx] || null;
        });
        const hasAny = framePhotos.some(Boolean);
        const hint = document.querySelector('[data-foto-preview-hint]');
        if (!hasAny) {
            canvas.classList.add('hidden');
            if (placeholder) placeholder.hidden = false;
            if (hint) hint.classList.remove('hidden');
            return;
        }
        canvas.classList.remove('hidden');
        if (placeholder) placeholder.hidden = true;
        if (hint) hint.classList.add('hidden');
        try {
            await buildStripFrame(canvas, framePhotos, 'asli', frameImage, slots && slots.length ? slots : null);
        } catch (e) {
            canvas.classList.add('hidden');
            if (placeholder) placeholder.hidden = false;
        }
    };

    const setCountdownVisible = (visible) => {
        if (!countdown) return;
        countdown.classList.toggle('hidden', !visible);
        countdown.classList.toggle('flex', visible);
    };

    const takePhoto = async (i) => {
        if (shootBtn) shootBtn.hidden = true;
        if (nextBtn) nextBtn.hidden = true;

        if (countdownNum) countdownNum.textContent = String(countFrom);
        setCountdownVisible(true);

        for (let n = countFrom; n >= 1; n--) {
            if (countdownNum) countdownNum.textContent = String(n);
            await wait(1000);
        }

        setCountdownVisible(false);

        await showFlash();

        const dataUrl = snapshot();
        if (!dataUrl) {
            showPhotoError('Kamera belum siap. Tunggu sebentar lalu coba lagi.');
            updateNext();
            return;
        }
        showPhotoError('');
        photos[i] = dataUrl;
        updateSlot(i, dataUrl);
        paintTabs();
        updateNext();
        renderFotoPreview();
    };

    const runShoot = async () => {
        if (shooting) return;
        shooting = true;
        if (shootBtn) shootBtn.disabled = true;
        try {
            // Pastikan video benar-benar berjalan sebelum memotret.
            try {
                await video.play();
            } catch (e) {
                // abaikan, lanjut cek dimensi
            }
            await new Promise((res) => {
                if (video.videoWidth) return res();
                video.addEventListener('loadedmetadata', res, { once: true });
                setTimeout(res, 2500);
            });
            if (!video.videoWidth) {
                showError();
                showPhotoError('Kamera belum siap. Tunggu hingga gambar muncul lalu coba lagi.');
                return;
            }
            hideError();

            for (let i = 0; i < photoCount; i++) {
                if (photos[i]) continue;
                activateTab(frameOf(i));
                await takePhoto(i);
                if (i < photoCount - 1) await wait(450);
            }
        } catch (err) {
            showError('Gagal memotret, coba lagi.');
            showPhotoError('Gagal memotret. Periksa kamera lalu coba lagi.');
        } finally {
            shooting = false;
            if (shootBtn) shootBtn.disabled = false;
            updateNext();
        }
    };

    const submitPhotos = async () => {
        if (!photos.every(Boolean)) {
            showPhotoError('Masih ada foto yang belum diambil. Selesaikan semua foto dulu ya.');
            return;
        }
        if (shootBtn) shootBtn.disabled = true;
        if (nextBtn) nextBtn.disabled = true;
        showPhotoError('');

        try {
            const res = await fetch('/foto', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
                body: JSON.stringify({
                    // Kirim semua slot sesuai urutan; jangan difilter agar
                    // indeks foto tetap cocok dengan frame di server.
                    photos,
                    from_spa: !!document.querySelector('[data-spa-main]'),
                }),
            });

            if (res.ok) {
                stopStream();
                const data = await res.json();
                window.location.href = data.redirect || '/spa?step=filter';
                return;
            }

            const data = await res.json().catch(() => ({}));
            showPhotoError(data.message || 'Foto gagal disimpan. Coba ambil ulang.');
        } catch (err) {
            showPhotoError('Koneksi bermasalah. Periksa jaringan lalu coba lagi.');
        }

        if (shootBtn) shootBtn.disabled = false;
        if (nextBtn) nextBtn.disabled = false;
    };

    if (shootBtn) shootBtn.addEventListener('click', runShoot);
    if (nextBtn) nextBtn.addEventListener('click', (e) => {
        e.preventDefault();
        submitPhotos();
    });

    tabs.forEach((t, i) => t.addEventListener('click', () => activateTab(i)));

    document.querySelectorAll('[data-retake]').forEach((btn) => {
        btn.addEventListener('click', () => {
            if (shooting) return;
            const i = parseInt(btn.dataset.retake, 10);
            activateTab(frameOf(i));
            takePhoto(i);
        });
    });

    updateNext();
    paintTabs();
}

/* ---------------- Filter selection ---------------- */

function initFilterSelect() {
    // Panel filter SPA punya editor per-foto sendiri (initFilterEditor);
    // jalur lama di bawah hanya untuk halaman /filter non-SPA.
    if (document.querySelector('[data-spa-panel="filter"]')) return;

    const form = document.querySelector('[data-filter-form]');
    if (!form) return;

    const input = document.getElementById('filter-input');
    const options = form.querySelectorAll('[data-filter-option]');

    options.forEach((opt) => {
        opt.addEventListener('click', () => {
            input.value = opt.dataset.filterOption;
            options.forEach((o) => {
                o.classList.remove('ring-2', 'ring-rose-500', 'shadow-lg', 'shadow-rose-100');
            });
            opt.classList.add('ring-2', 'ring-rose-500', 'shadow-lg', 'shadow-rose-100');
        });
    });
}

/* ---------------- Filter per-foto (panel SPA) ---------------- */

function initFilterEditor() {
    const panel = document.querySelector('[data-spa-panel="filter"]');
    if (!panel) return;
    const form = panel.querySelector('[data-filter-form]');
    if (!form) return;

    const blocks = [...panel.querySelectorAll('[data-filter-item]')];
    if (!blocks.length) return;

    const stickersInput = form.querySelector('[data-stickers-input]');
    const items = [];

    const clampSticker = (value, min, max, fallback) => {
        const n = Number(value);
        return Number.isFinite(n) ? Math.max(min, Math.min(max, n)) : fallback;
    };

    const readJson = (raw, fallback) => {
        try {
            const value = JSON.parse(raw || 'null');
            return value ?? fallback;
        } catch (e) {
            return fallback;
        }
    };

    // Hanya kirim id + koordinat; server mengisi ulang nilai emoji/gambar dari
    // database agar klien tidak bisa menyisipkan konten sembarangan.
    const syncStickers = () => {
        if (!stickersInput) return;
        const payload = {};
        items.forEach((item) => {
            if (!item.placements.length) return;
            payload[item.slug] = item.placements.map((p) => ({
                id: p.id,
                x: Number(p.x.toFixed(4)),
                y: Number(p.y.toFixed(4)),
                size: Number(p.size.toFixed(4)),
                rotation: Number((p.rotation || 0).toFixed(2)),
            }));
        });
        stickersInput.value = JSON.stringify(payload);
    };

    const setRowState = (row, value) => {
        const input = row.querySelector('[data-filter-value]');
        if (input) input.value = value;

        const active = [...row.querySelectorAll('[data-filter-chip]')]
            .find((chip) => chip.dataset.filterChip === value);
        const label = row.querySelector('[data-filter-label]');
        if (label && active) label.textContent = active.textContent.trim();

        row.querySelectorAll('[data-filter-chip]').forEach((chip) => {
            const on = chip.dataset.filterChip === value;
            chip.setAttribute('aria-pressed', on ? 'true' : 'false');
            chip.classList.toggle('bg-rose-500', on);
            chip.classList.toggle('text-white', on);
            chip.classList.toggle('ring-rose-500', on);
            chip.classList.toggle('bg-white', !on);
            chip.classList.toggle('text-slate-500', !on);
            chip.classList.toggle('ring-rose-100', !on);
        });
    };

    const rowIndex = (row) => {
        const parts = (row.dataset.filterPhoto || '').split(':');
        return parseInt(parts.pop(), 10);
    };

    const activeRow = (item) => item.block.querySelector(
        `[data-filter-photo="${item.slug}:${item.activePhoto}"]`
    ) || item.block.querySelector('[data-filter-photo]');

    const layoutItem = (item) => {
        const width = item.stage.clientWidth;
        const height = item.stage.clientHeight;
        if (!width || !height) return;

        item.placements.forEach((placement, index) => {
            const node = item.nodes[index];
            if (!node) return;

            const side = Math.max(1, placement.size * width);
            node.style.width = `${side}px`;
            node.style.height = `${side}px`;
            node.style.left = `${placement.x * width}px`;
            node.style.top = `${placement.y * height}px`;

            const emoji = node.querySelector('[data-sticker-body]');
            if (emoji) emoji.style.fontSize = `${side * 0.92}px`;
            node.style.transform = `translate(-50%, -50%) rotate(${Number(placement.rotation) || 0}deg)`;
        });
    };

    const bindDrag = (item, node) => {
        let dragging = false;

        const move = (event) => {
            if (!dragging) return;
            const rect = item.stage.getBoundingClientRect();
            if (!rect.width || !rect.height) return;

            const index = Number(node.dataset.stickerIndex);
            const placement = item.placements[index];
            if (!placement) return;

            placement.x = clampSticker((event.clientX - rect.left) / rect.width, 0.02, 0.98, placement.x);
            placement.y = clampSticker((event.clientY - rect.top) / rect.height, 0.02, 0.98, placement.y);
            node.style.left = `${placement.x * rect.width}px`;
            node.style.top = `${placement.y * rect.height}px`;
        };

        const stop = () => {
            if (!dragging) return;
            dragging = false;
            node.classList.remove('is-dragging');
            window.removeEventListener('pointermove', move);
            window.removeEventListener('pointerup', stop);
            window.removeEventListener('pointercancel', stop);
            syncStickers();
        };

        node.addEventListener('pointerdown', (event) => {
            if (event.target.closest('[data-sticker-remove], [data-sticker-resize], [data-sticker-rotate]')) return;
            event.preventDefault();
            dragging = true;
            node.classList.add('is-dragging');
            try {
                node.setPointerCapture(event.pointerId);
            } catch (e) {
                // abaikan; pointer capture tidak wajib
            }
            window.addEventListener('pointermove', move);
            window.addEventListener('pointerup', stop);
            window.addEventListener('pointercancel', stop);
        });
    };

    // Geser pojok kanan-bawah untuk mengubah ukuran stiker.
    const bindResize = (item, node) => {
        const handle = node.querySelector('[data-sticker-resize]');
        if (!handle) return;

        let resizing = false;

        const move = (event) => {
            if (!resizing) return;
            const rect = item.stage.getBoundingClientRect();
            if (!rect.width || !rect.height) return;

            const index = Number(node.dataset.stickerIndex);
            const placement = item.placements[index];
            if (!placement) return;

            const cx = placement.x * rect.width;
            const cy = placement.y * rect.height;
            const dx = event.clientX - rect.left - cx;
            const dy = event.clientY - rect.top - cy;
            const dist = Math.hypot(dx, dy);
            // Ukuran adalah sisi kotak; pegangan berada di sudut (~sisi/√2).
            const side = (dist * Math.SQRT2);
            placement.size = clampSticker(side / rect.width, 0.05, 0.5, placement.size);
            layoutItem(item);
        };

        const stop = () => {
            if (!resizing) return;
            resizing = false;
            node.classList.remove('is-resizing');
            window.removeEventListener('pointermove', move);
            window.removeEventListener('pointerup', stop);
            window.removeEventListener('pointercancel', stop);
            syncStickers();
        };

        handle.addEventListener('pointerdown', (event) => {
            event.preventDefault();
            event.stopPropagation();
            resizing = true;
            node.classList.add('is-resizing');
            try {
                handle.setPointerCapture(event.pointerId);
            } catch (e) { /* abaikan */ }
            window.addEventListener('pointermove', move);
            window.addEventListener('pointerup', stop);
            window.addEventListener('pointercancel', stop);
        });
    };

    // Pegangan di atas untuk memutar stiker.
    const bindRotate = (item, node) => {
        const handle = node.querySelector('[data-sticker-rotate]');
        if (!handle) return;

        let rotating = false;

        const move = (event) => {
            if (!rotating) return;
            const rect = item.stage.getBoundingClientRect();
            if (!rect.width || !rect.height) return;

            const index = Number(node.dataset.stickerIndex);
            const placement = item.placements[index];
            if (!placement) return;

            const cx = placement.x * rect.width;
            const cy = placement.y * rect.height;
            const dx = event.clientX - rect.left - cx;
            const dy = event.clientY - rect.top - cy;
            // Pegangan awal berada di atas (sudut -90°), jadi tambahkan 90.
            let deg = (Math.atan2(dy, dx) * 180) / Math.PI + 90;
            if (deg > 180) deg -= 360;
            placement.rotation = Math.max(-180, Math.min(180, deg));
            layoutItem(item);
        };

        const stop = () => {
            if (!rotating) return;
            rotating = false;
            node.classList.remove('is-rotating');
            window.removeEventListener('pointermove', move);
            window.removeEventListener('pointerup', stop);
            window.removeEventListener('pointercancel', stop);
            syncStickers();
        };

        handle.addEventListener('pointerdown', (event) => {
            event.preventDefault();
            event.stopPropagation();
            rotating = true;
            node.classList.add('is-rotating');
            try {
                handle.setPointerCapture(event.pointerId);
            } catch (e) { /* abaikan */ }
            window.addEventListener('pointermove', move);
            window.addEventListener('pointerup', stop);
            window.addEventListener('pointercancel', stop);
        });
    };

    const createNode = (item, placement, index) => {
        const node = document.createElement('div');
        node.className = 'k-sticker';
        node.dataset.stickerIndex = String(index);
        node.dataset.type = placement.type;

        if (placement.type === 'image') {
            const img = document.createElement('img');
            img.src = placement.value;
            img.alt = '';
            img.draggable = false;
            img.className = 'k-sticker-img';
            node.appendChild(img);
        } else {
            const emoji = document.createElement('span');
            emoji.className = 'k-sticker-emoji';
            emoji.dataset.stickerBody = '';
            emoji.textContent = placement.value;
            node.appendChild(emoji);
        }

        const remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'k-sticker-remove';
        remove.dataset.stickerRemove = '';
        remove.setAttribute('aria-label', 'Hapus stiker');
        remove.textContent = '×';
        node.appendChild(remove);

        const resize = document.createElement('div');
        resize.className = 'k-sticker-resize';
        resize.dataset.stickerResize = '';
        resize.setAttribute('aria-label', 'Ubah ukuran');
        node.appendChild(resize);

        const rotate = document.createElement('div');
        rotate.className = 'k-sticker-rotate';
        rotate.dataset.stickerRotate = '';
        rotate.setAttribute('aria-label', 'Putar stiker');
        node.appendChild(rotate);

        bindDrag(item, node);
        bindResize(item, node);
        bindRotate(item, node);
        item.layer.appendChild(node);
        return node;
    };

    const rebuildNodes = (item) => {
        item.layer.innerHTML = '';
        item.nodes = item.placements.map((placement, index) => createNode(item, placement, index));
        layoutItem(item);
    };

    const paint = async (item) => {
        const token = ++item.paintToken;
        const filters = item.photos.map((_, index) => item.filters[index] ?? 'asli');
        try {
            // Stiker digambar sebagai lapisan HTML di atas kanvas agar bisa
            // digeser halus; kanvas pratinjau cukup menampilkan foto + filter.
            await buildStripFrame(item.canvas, item.photos, filters, item.frameImage, item.slots);
        } catch (e) {
            // biarkan pratinjau kosong bila gambar gagal dimuat
        }
        if (token !== item.paintToken) return;
        requestAnimationFrame(() => layoutItem(item));
    };

    blocks.forEach((block) => {
        const canvas = block.querySelector('[data-strip-canvas]');
        const stage = block.querySelector('[data-sticker-stage]');
        const layer = block.querySelector('[data-sticker-layer]');
        if (!canvas || !stage || !layer) return;

        const item = {
            block,
            canvas,
            stage,
            layer,
            slug: canvas.dataset.item || block.dataset.filterItem || '',
            photos: readJson(canvas.dataset.photos, []),
            slots: readJson(canvas.dataset.frameSlots, []),
            frameImage: canvas.dataset.frameImage || null,
            filters: {},
            placements: [],
            nodes: [],
            activePhoto: 0,
            paintToken: 0,
        };
        if (!item.slug) return;

        block.querySelectorAll('[data-filter-photo]').forEach((row) => {
            const local = rowIndex(row);
            const value = row.querySelector('[data-filter-value]')?.value || 'asli';
            if (!Number.isNaN(local)) item.filters[local] = value;
            setRowState(row, value);
        });

        const stored = readJson(canvas.dataset.stickers, []);
        item.placements = (Array.isArray(stored) ? stored : [])
            .map((placement) => ({
                id: Number(placement?.id),
                type: placement?.type === 'image' ? 'image' : 'emoji',
                value: typeof placement?.value === 'string' ? placement.value : '',
                x: clampSticker(placement?.x, 0.02, 0.98, 0.5),
                y: clampSticker(placement?.y, 0.02, 0.98, 0.5),
                size: clampSticker(placement?.size, 0.05, 0.5, 0.16),
                rotation: clampSticker(placement?.rotation, -180, 180, 0),
            }))
            .filter((placement) => placement.value !== '' && Number.isFinite(placement.id));

        items.push(item);

        // Filter per foto: klik chip langsung memperbarui pratinjau.
        block.querySelectorAll('[data-filter-chip]').forEach((chip) => {
            chip.addEventListener('click', () => {
                const row = chip.closest('[data-filter-photo]');
                if (!row) return;
                const local = rowIndex(row);
                const value = chip.dataset.filterChip || 'asli';
                setRowState(row, value);
                if (!Number.isNaN(local)) item.filters[local] = value;
                paint(item);
            });
        });

        // Tab foto memilih baris filter yang ditampilkan.
        block.querySelectorAll('[data-photo-tab]').forEach((tab) => {
            tab.addEventListener('click', () => {
                const local = Number(tab.dataset.photoTab);
                if (!Number.isFinite(local)) return;
                item.activePhoto = local;
                block.querySelectorAll('[data-photo-tab]').forEach((other) => {
                    other.setAttribute('aria-selected', other === tab ? 'true' : 'false');
                });
                block.querySelectorAll('[data-filter-photo]').forEach((row) => {
                    row.hidden = rowIndex(row) !== local;
                });
            });
        });

        // Terapkan filter foto yang sedang dipilih ke semua foto frame ini.
        const sameBtn = block.querySelector('[data-filter-same]');
        if (sameBtn) {
            sameBtn.addEventListener('click', () => {
                const rows = [...block.querySelectorAll('[data-filter-photo]')];
                if (!rows.length) return;
                const value = activeRow(item)?.querySelector('[data-filter-value]')?.value || 'asli';
                rows.forEach((row) => {
                    const local = rowIndex(row);
                    setRowState(row, value);
                    if (!Number.isNaN(local)) item.filters[local] = value;
                });
                paint(item);
                showToast('Filter diterapkan ke semua foto.', 'success');
            });
        }

        // Tambah stiker dari palet admin.
        block.querySelectorAll('[data-sticker-add]').forEach((button) => {
            button.addEventListener('click', () => {
                if (item.placements.length >= 30) {
                    showToast('Maksimal 30 stiker per frame.', 'warning');
                    return;
                }

                const index = item.placements.length;
                item.placements.push({
                    id: Number(button.dataset.stickerAdd),
                    type: button.dataset.stickerType === 'image' ? 'image' : 'emoji',
                    value: button.dataset.stickerValue || '',
                    x: clampSticker(0.5 + ((index % 3) - 1) * 0.07, 0.02, 0.98, 0.5),
                    y: clampSticker(0.5 + ((Math.floor(index / 3) % 3) - 1) * 0.07, 0.02, 0.98, 0.5),
                    size: 0.16,
                });
                rebuildNodes(item);
                syncStickers();
            });
        });

        // Hapus satu stiker.
        layer.addEventListener('click', (event) => {
            const button = event.target.closest('[data-sticker-remove]');
            if (!button) return;
            const node = button.closest('[data-sticker-index]');
            const index = Number(node?.dataset.stickerIndex);
            if (!Number.isFinite(index)) return;
            item.placements.splice(index, 1);
            rebuildNodes(item);
            syncStickers();
        });

        const clearBtn = block.querySelector('[data-sticker-clear]');
        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                if (!item.placements.length) return;
                item.placements = [];
                rebuildNodes(item);
                syncStickers();
                showToast('Semua stiker dihapus.', 'success');
            });
        }

        rebuildNodes(item);
        paint(item);

        if (typeof ResizeObserver !== 'undefined') {
            new ResizeObserver(() => layoutItem(item)).observe(stage);
        } else {
            window.addEventListener('resize', () => layoutItem(item));
        }
    });

    // Pindah tab frame/keranjang.
    panel.querySelectorAll('[data-filter-tab]').forEach((tab) => {
        tab.addEventListener('click', () => {
            const index = Number(tab.dataset.filterTab);
            if (!Number.isFinite(index)) return;

            panel.querySelectorAll('[data-filter-tab]').forEach((other) => {
                other.setAttribute('aria-selected', other === tab ? 'true' : 'false');
            });
            items.forEach((item, itemIndex) => {
                item.block.hidden = itemIndex !== index;
            });

            const item = items[index];
            if (item) requestAnimationFrame(() => layoutItem(item));
        });
    });

    syncStickers();
    form.addEventListener('submit', syncStickers);
}

/* ---------------- Frame cart selection ---------------- */

function initFrameCart() {
    const form = document.querySelector('[data-frame-form]');
    if (!form) return;

    const cats = document.querySelectorAll('[data-category-chip]');
    const options = form.querySelectorAll('[data-frame-option]');
    const picks = document.getElementById('frame-picks');
    const cartList = form.querySelector('[data-cart-list]');
    const cartEmpty = form.querySelector('[data-cart-empty]');
    const cartCount = form.querySelector('[data-cart-count]');
    const totalEl = form.querySelector('[data-cart-total]');
    const submitBtn = form.querySelector('[data-cart-submit]');
    const clearBtn = form.querySelector('[data-cart-clear]');

    const fmt = (n) => 'Rp ' + n.toLocaleString('id-ID');

    const FRAME_INFO = {};
    options.forEach((opt) => {
        FRAME_INFO[opt.dataset.frameOption] = {
            name: opt.dataset.name || opt.dataset.frameOption,
            price: parseInt(opt.dataset.price || '0', 10),
            size: opt.dataset.sizeName || '',
            img: opt.querySelector('img')?.src || '',
        };
    });

    const cart = {};
    options.forEach((opt) => {
        const qty = parseInt(opt.dataset.qty || '0', 10);
        if (qty > 0) cart[opt.dataset.frameOption] = qty;
    });

    const render = () => {
        options.forEach((opt) => {
            const slug = opt.dataset.frameOption;
            const inCart = cart[slug] > 0;
            const badge = opt.querySelector('[data-add-badge]');
            opt.setAttribute('aria-pressed', inCart ? 'true' : 'false');
            if (badge) badge.hidden = !inCart;
            const qtyEl = opt.querySelector('[data-add-qty]');
            if (qtyEl) qtyEl.textContent = cart[slug] || 0;
        });

        const entries = Object.entries(cart).filter(([, q]) => q > 0);

        if (clearBtn) clearBtn.hidden = entries.length === 0;

        cartList.innerHTML = '';
        if (entries.length === 0 && cartEmpty) {
            // pesan kosong ikut terhapus innerHTML='' di atas — pasang ulang
            cartEmpty.classList.remove('hidden');
            cartList.appendChild(cartEmpty);
        }
        if (cartCount) cartCount.textContent = String(entries.length);

        entries.forEach(([slug, qty]) => {
            const info = FRAME_INFO[slug] || { name: slug, price: 0, size: '', img: '' };
            const row = document.createElement('div');
            // Dua baris: info + tombol qty agar nama tidak terpotong.
            row.className = 'k-cart-row';
            row.innerHTML = `
                <div class="flex items-center gap-2.5">
                    <img src="${info.img}" alt="" class="w-9 h-11 rounded-lg object-contain bg-slate-50 shrink-0">
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-bold text-slate-800 truncate leading-tight">${info.name}</p>
                        <p class="text-[11px] text-slate-400 truncate">${info.size ? info.size + ' · ' : ''}${fmt(info.price)}</p>
                    </div>
                    <button type="button" data-cart-remove="${slug}" class="text-slate-300 hover:text-rose-500 text-xl leading-none shrink-0" aria-label="Hapus" title="Hapus">×</button>
                </div>
                <div class="flex items-center justify-end gap-2">
                    <button type="button" data-cart-dec="${slug}" aria-label="Kurangi" class="k-cart-qty">−</button>
                    <span class="text-sm font-extrabold w-6 text-center tabular-nums">${qty}</span>
                    <button type="button" data-cart-inc="${slug}" aria-label="Tambah" class="k-cart-qty">+</button>
                </div>
            `;
            cartList.appendChild(row);
        });

        picks.innerHTML = '';
        entries.forEach(([slug, qty]) => {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'cart[' + slug + ']';
            hidden.value = qty;
            picks.appendChild(hidden);
        });

        const total = entries.reduce((sum, [s, q]) => sum + (FRAME_INFO[s]?.price ?? 0) * q, 0);
        if (totalEl) totalEl.textContent = fmt(total);
        if (submitBtn) submitBtn.disabled = entries.length === 0;
    };

    const add = (slug) => {
        cart[slug] = (cart[slug] || 0) + 1;
        render();
    };

    const dec = (slug) => {
        cart[slug] = Math.max(0, (cart[slug] || 0) - 1);
        render();
    };

    const remove = (slug) => {
        delete cart[slug];
        render();
    };

    const applyFilter = (slug) => {
        cats.forEach((cat) => {
            cat.setAttribute('aria-pressed', cat.dataset.category === slug ? 'true' : 'false');
        });

        options.forEach((opt) => {
            opt.hidden = slug !== 'semua' && opt.dataset.category !== slug;
        });
    };

    options.forEach((opt) => opt.addEventListener('click', () => add(opt.dataset.frameOption)));
    cats.forEach((cat) => cat.addEventListener('click', () => applyFilter(cat.dataset.category)));

    cartList.addEventListener('click', (e) => {
        const inc = e.target.closest('[data-cart-inc]');
        const decBtn = e.target.closest('[data-cart-dec]');
        const rm = e.target.closest('[data-cart-remove]');
        if (inc) add(inc.dataset.cartInc);
        else if (decBtn) dec(decBtn.dataset.cartDec);
        else if (rm) remove(rm.dataset.cartRemove);
    });

    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            Object.keys(cart).forEach((key) => delete cart[key]);
            render();
            showToast('Keranjang dikosongkan.', 'success');
        });
    }

    form.addEventListener('submit', () => {
        if (!submitBtn || submitBtn.disabled) return;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Menyiapkan kamera…';
    });

    render();
}

/* ---------------- Payment method selection ---------------- */

function initMetodeSelect() {
    const form = document.querySelector('form[action*="metode"]');
    if (!form) return;

    const input = document.getElementById('metode-input');
    const options = form.querySelectorAll('[data-metode-option]');
    const voucherBox = form.querySelector('[data-voucher-box]');
    const voucherInput = document.getElementById('voucher-code');
    const submitBtn = document.getElementById('metode-submit');
    if (!input) return;

    const paint = () => {
        options.forEach((opt) => {
            const active = opt.dataset.metodeOption === input.value;
            opt.setAttribute('aria-pressed', active ? 'true' : 'false');
        });

        const isVoucher = input.value === 'voucher';
        if (voucherBox) voucherBox.hidden = !isVoucher;
        if (submitBtn) {
            const label = isVoucher ? 'Gunakan Voucher & Mulai Foto' : 'Lanjut Pilih Frame';
            const textNode = [...submitBtn.childNodes].find((n) => n.nodeType === 3 && n.textContent.trim() !== '');
            if (textNode) textNode.textContent = '\n            ' + label + '\n            ';
            else submitBtn.prepend(document.createTextNode(label));
        }
    };

    options.forEach((opt) => {
        opt.addEventListener('click', () => {
            input.value = opt.dataset.metodeOption;
            paint();
            if (input.value === 'voucher') {
                setTimeout(() => voucherInput?.focus(), 50);
            } else {
                voucherInput?.blur();
            }
        });
    });

    // Setelah error validasi, server mengembalikan pilihan terakhir.
    paint();
}

/* ---------------- QRIS QR code ---------------- */

function initQr() {
    const box = document.getElementById('qrcode');
    if (!box) return;

    const queue = box.dataset.queue ?? '';
    const priceEl = document.querySelector('[data-qris-price]');
    const price = (priceEl?.textContent ?? 'Rp 30.000').replace(/\D/g, '') || '30000';
    const nmid = box.dataset.nmid || '102020034073193';
    const merchant = box.dataset.merchant || 'PERKAKASKU';
    const payload = `QRIS|${merchant}|${nmid}|${price}|ANTRIAN${queue.replace('#', '')}`;

    QRCode.toCanvas(box, payload, { width: 160, margin: 2, color: { dark: '#18181b', light: '#fafafa' } })
        .catch((err) => {
            box.textContent = 'QR Invoice';
        });
}

/* ---------------- Frame strip composition ---------------- */

async function loadImages(urls) {
    return Promise.all(
        urls.map(
            (url) =>
                new Promise((resolve) => {
                    if (!url) { resolve(null); return; }
                    const img = new Image();
                    img.onload = () => resolve(img);
                    img.onerror = () => resolve(null);
                    try {
                        const u = new URL(url, window.location.href);
                        if (u.origin !== window.location.origin) {
                            img.crossOrigin = 'anonymous';
                        }
                    } catch (e) {
                        img.crossOrigin = 'anonymous';
                    }
                    img.src = url;
                })
        )
    );
}

async function buildStripFrame(canvas, photoUrls, filterKey, frameImageUrl, frameSlots, stickerPlacements, targetWidth) {
    // filterKey: string (satu filter utk semua, kompatibel lama) atau
    // array per-index foto (jalur per-foto).
    const isPerPhoto = Array.isArray(filterKey);
    const filter = isPerPhoto ? 'none' : (FILTERS[filterKey] ?? 'none');
    const filterAt = (i) => (isPerPhoto ? (FILTERS[filterKey[i]] ?? 'none') : filter);
    const slots = Array.isArray(frameSlots) && frameSlots.length ? frameSlots : null;

    const baseW = 360;
    const photos = await loadImages(photoUrls);
    const frameImg = frameImageUrl ? (await loadImages([frameImageUrl]))[0] : null;

    // Resolusi ekspor mengikuti lebar kertas (px), supaya hasil cetak/unduh
    // cukup tajam untuk ukuran besar tanpa mengubah tata letak.
    const exportWidth = Number(targetWidth) > 0
        ? Math.max(720, Math.min(2480, Math.round(Number(targetWidth))))
        : 0;
    const scale = exportWidth ? Math.max(2, exportWidth / baseW) : 2;
    let baseH;
    if (frameImg) {
        baseH = Math.round((baseW * frameImg.naturalHeight) / frameImg.naturalWidth);
    } else if (slots && slots.length) {
        const top = Math.min(...slots.map((s) => s.y));
        const bottom = Math.max(...slots.map((s) => s.y + s.h));
        baseH = Math.round(baseW / (Math.max(slots[0].w, 0.001) / Math.max(bottom - top, 0.001)));
    } else {
        baseH = Math.round((baseW * 4) / 3);
    }

    canvas.width = baseW * scale;
    canvas.height = baseH * scale;
    const ctx = canvas.getContext('2d');
    ctx.scale(scale, scale);

    ctx.save();
    ctx.filter = 'none';
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, baseW, baseH);
    ctx.restore();

    if (slots) {
        // Foto dipotong mengikuti bentuk lubang pada PNG frame (bisa melengkung /
        // tidak beraturan), bukan sekadar kotak yang ditimpa gambar frame.
        let photoCanvas = null;
        let photoCtx = null;
        let maskCanvas = null;
        let maskCtx = null;

        photos.forEach((img, i) => {
            if (!img || !slots[i]) return;
            const slot = slots[i];
            const x = slot.x * baseW;
            const y = slot.y * baseH;
            const w = slot.w * baseW;
            const h = slot.h * baseH;
            if (w <= 0 || h <= 0) return;
            const srcAspect = img.naturalWidth / img.naturalHeight;
            const dstAspect = w / h;

            let sx = 0, sy = 0, sw = img.naturalWidth, sh = img.naturalHeight;
            if (srcAspect > dstAspect) {
                sw = img.naturalHeight * dstAspect;
                sx = (img.naturalWidth - sw) / 2;
            } else {
                sh = img.naturalWidth / dstAspect;
                sy = (img.naturalHeight - sh) / 2;
            }

            ctx.save();
            ctx.filter = 'none';

            if (frameImg) {
                const mw = Math.max(1, Math.round(w * scale));
                const mh = Math.max(1, Math.round(h * scale));
                if (!photoCanvas) {
                    photoCanvas = document.createElement('canvas');
                    photoCtx = photoCanvas.getContext('2d', { willReadFrequently: true });
                    maskCanvas = document.createElement('canvas');
                    maskCtx = maskCanvas.getContext('2d', { willReadFrequently: true });
                }
                if (photoCanvas.width !== mw) photoCanvas.width = mw;
                if (photoCanvas.height !== mh) photoCanvas.height = mh;
                if (maskCanvas.width !== mw) maskCanvas.width = mw;
                if (maskCanvas.height !== mh) maskCanvas.height = mh;

                // 1) Foto (dengan filter) ke kanvas sementara.
                photoCtx.setTransform(1, 0, 0, 1, 0, 0);
                photoCtx.globalCompositeOperation = 'source-over';
                photoCtx.clearRect(0, 0, mw, mh);
                photoCtx.filter = filterAt(i);
                photoCtx.drawImage(img, sx, sy, sw, sh, 0, 0, mw, mh);
                photoCtx.filter = 'none';

                // 2) Ambil area frame untuk slot ini, lalu jadikan mask biner:
                //    piksel transparan = lubang (foto dipertahankan), sisanya
                //    dihapus. Alpha frame yang semi-transparan tetap dianggap
                //    bagian frame agar foto tidak "membocor" sebagai kotak.
                maskCtx.setTransform(1, 0, 0, 1, 0, 0);
                maskCtx.globalCompositeOperation = 'source-over';
                maskCtx.clearRect(0, 0, mw, mh);
                maskCtx.drawImage(
                    frameImg,
                    slot.x * frameImg.naturalWidth,
                    slot.y * frameImg.naturalHeight,
                    slot.w * frameImg.naturalWidth,
                    slot.h * frameImg.naturalHeight,
                    0, 0, mw, mh
                );

                const maskData = maskCtx.getImageData(0, 0, mw, mh);
                const px = maskData.data;
                for (let p = 0; p < px.length; p += 4) {
                    const isHole = px[p + 3] <= 40;
                    px[p] = 0;
                    px[p + 1] = 0;
                    px[p + 2] = 0;
                    px[p + 3] = isHole ? 255 : 0;
                }
                maskCtx.putImageData(maskData, 0, 0);

                // 3) Potong foto dengan mask lubang.
                photoCtx.globalCompositeOperation = 'destination-in';
                photoCtx.drawImage(maskCanvas, 0, 0);
                photoCtx.globalCompositeOperation = 'source-over';

                ctx.drawImage(photoCanvas, 0, 0, mw, mh, x, y, w, h);
            } else {
                ctx.filter = filterAt(i);
                ctx.drawImage(img, sx, sy, sw, sh, x, y, w, h);
            }
            ctx.restore();
        });
    } else {
        // fallback: tanpa slots, susun foto vertikal
        const padding = 24;
        const spacing = 6;
        const captionH = 46;
        const photoW = baseW - padding * 2;
        const photoH = Math.round((photoW * 4) / 3);

        ctx.save();
        ctx.fillStyle = '#f4f4f5';
        ctx.fillRect(0, 0, baseW, baseH);
        photos.forEach((img, i) => {
            if (!img) return;
            const y = padding + i * (photoH + spacing);
            const srcAspect = img.naturalWidth / img.naturalHeight;
            const dstAspect = photoW / photoH;
            let sx = 0, sy = 0, sw = img.naturalWidth, sh = img.naturalHeight;
            if (srcAspect > dstAspect) {
                sw = img.naturalHeight * dstAspect;
                sx = (img.naturalWidth - sw) / 2;
            } else {
                sh = img.naturalWidth / dstAspect;
                sy = (img.naturalHeight - sh) / 2;
            }
            ctx.save();
            ctx.filter = filterAt(i);
            ctx.drawImage(img, sx, sy, sw, sh, padding, y, photoW, photoH);
            ctx.restore();
        });
        ctx.restore();
    }

    if (frameImg) {
        ctx.save();
        ctx.filter = 'none';
        ctx.drawImage(frameImg, 0, 0, baseW, baseH);
        ctx.restore();
    }

    // Stiker/emoji ditempel di atas frame. Koordinat & ukuran dinormalisasi
    // terhadap lebar/tinggi strip agar hasil konsisten di layar mana pun.
    const placements = Array.isArray(stickerPlacements) ? stickerPlacements : [];
    if (placements.length) {
        const imageUrls = placements.map((sticker) => (
            sticker && sticker.type === 'image' ? sticker.value : null
        ));
        const stickerImages = imageUrls.some(Boolean) ? await loadImages(imageUrls) : [];
        const clampUnit = (value, min, max, fallback) => {
            const n = Number(value);
            return Number.isFinite(n) ? Math.max(min, Math.min(max, n)) : fallback;
        };

        placements.forEach((sticker, index) => {
            if (!sticker || typeof sticker.value !== 'string' || sticker.value === '') return;

            const cx = clampUnit(sticker.x, 0.02, 0.98, 0.5) * baseW;
            const cy = clampUnit(sticker.y, 0.02, 0.98, 0.5) * baseH;
            const side = clampUnit(sticker.size, 0.05, 0.5, 0.16) * baseW;
            const rot = (clampUnit(sticker.rotation, -180, 180, 0) * Math.PI) / 180;
            if (side <= 0) return;

            ctx.save();
            ctx.filter = 'none';
            ctx.translate(cx, cy);
            ctx.rotate(rot);

            if (sticker.type === 'image') {
                const img = stickerImages[index];
                if (img) {
                    const aspect = (img.naturalWidth || 1) / (img.naturalHeight || 1);
                    const w = aspect > 1 ? side : side * aspect;
                    const h = aspect > 1 ? side / aspect : side;
                    ctx.drawImage(img, -w / 2, -h / 2, w, h);
                }
            } else {
                ctx.font = `${Math.round(side * 0.92)}px "Apple Color Emoji", "Segoe UI Emoji", "Noto Color Emoji", sans-serif`;
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(sticker.value, 0, 0);
            }

            ctx.restore();
        });
    }
}

function initStrip() {
    const downloadLink = document.querySelector('[data-strip-download]');

    // Dokumen SPA memuat semua panel; hanya kanvas di panel yang tampil yang dipakai
    // (agar cetak/unduh tidak ikut menyertakan kanvas panel filter yang tersembunyi).
    const canvases = [...document.querySelectorAll('[data-strip-canvas]')].filter((c) => {
        const panel = c.closest('[data-spa-panel]');
        if (!panel) return true;
        // Panel edit punya pratinjau sendiri dengan lapisan stiker interaktif;
        // jangan pernah ikut diekspor sebagai strip final.
        return panel.dataset.spaPanel !== 'filter' && !panel.classList.contains('hidden');
    });
    const ready = canvases.map((canvas) => {
        let photos = [];
        let slots = [];
        let stickers = [];
        try {
            photos = JSON.parse(canvas.dataset.photos || '[]');
        } catch (e) {
            photos = [];
        }
        try {
            slots = JSON.parse(canvas.dataset.frameSlots || '[]');
        } catch (e) {
            slots = [];
        }
        try {
            stickers = JSON.parse(canvas.dataset.stickers || '[]');
        } catch (e) {
            stickers = [];
        }
        if (!photos.length) {
            canvas.style.display = 'none';
            const container = canvas.closest('[data-strip-container]');
            if (container) {
                container.classList.add('hidden');
            }
            return Promise.resolve(null);
        }
        // Prioritas: peta per-foto (data-filters, JSON {index: key}),
        // jatuh kembali ke filter tunggal (data-filter, kompatibel lama).
        let filterArg = canvas.dataset.filter;
        try {
            const parsed = JSON.parse(canvas.dataset.filters || 'null');
            if (parsed && typeof parsed === 'object') {
                filterArg = photos.map((_, i) => parsed[i] ?? parsed[String(i)] ?? 'asli');
            }
        } catch (e) {
            // abaikan, pakai filter tunggal
        }
        const printWidth = parseFloat(canvas.dataset.printWidth || '0');
        return buildStripFrame(
            canvas,
            photos,
            filterArg,
            canvas.dataset.frameImage,
            slots,
            stickers,
            printWidth > 0 ? printWidth * 100 : 0
        ).then(() => canvas);
    });

    window.__stripsReady = Promise.all(ready);

    if (downloadLink) {
        downloadLink.addEventListener('click', (e) => {
            e.preventDefault();
            Promise.all(ready).then((built) => {
                const strips = built.filter(Boolean);
                if (!strips.length) {
                    showToast('Belum ada foto untuk diunduh.');
                    return;
                }
                strips.forEach((canvas, i) => {
                    setTimeout(() => {
                        const a = document.createElement('a');
                        a.href = canvas.toDataURL('image/jpeg', 0.92);
                        a.download = 'photobooth-' + (strips.length > 1 ? i + 1 + '-' : '') + Date.now() + '.jpg';
                        a.click();
                    }, i * 400);
                });
                showToast('Menyiapkan unduhan ' + strips.length + ' file…', 'success');
            });
        });
    }
}

/* ---------------- Modal (cetak / kirim email) ---------------- */

function showModal(title, text) {
    const modal = document.querySelector('[data-printing-modal]');
    if (!modal) return;
    const t = modal.querySelector('[data-modal-title]');
    const p = modal.querySelector('[data-modal-text]');
    if (t) t.textContent = title;
    if (p) p.textContent = text;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function hideModal() {
    const modal = document.querySelector('[data-printing-modal]');
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

/* ---------------- QR download hasil ---------------- */

function renderDownloadQr(canvas, url, size = 160) {
    if (!canvas || !url) return Promise.resolve(false);
    return QRCode.toCanvas(canvas, url, {
        width: size,
        margin: 1,
        color: { dark: '#0f172a', light: '#ffffff' },
        errorCorrectionLevel: 'M',
    }).then(() => true).catch(() => false);
}

function initDownloadQr() {
    document.querySelectorAll('[data-download-qr]').forEach((canvas) => {
        renderDownloadQr(canvas, canvas.dataset.downloadQrUrl || '', 160);
    });
}

/**
 * Halaman unduh publik: gambar ulang strip memakai frame, filter, dan
 * stiker/emoji yang tersimpan di metadata log pembayaran.
 */
function initPublicDownload() {
    const root = document.querySelector('[data-public-download-root]');
    if (!root) return;

    const queue = (root.dataset.queue || 'hasil').replace(/[^a-z0-9_-]+/gi, '-');

    root.querySelectorAll('[data-public-canvas]').forEach((canvas) => {
        const parse = (key) => {
            try {
                return JSON.parse(canvas.dataset[key] || '[]');
            } catch (e) {
                return [];
            }
        };

        const photos = parse('photos');
        const filters = parse('filters');
        const slots = parse('slots');
        const stickers = parse('stickers');

        if (!photos.length) return;

        const filterArg = filters.length === photos.length ? filters : (filters[0] || 'asli');
        const article = canvas.closest('article');
        const button = article ? article.querySelector('[data-public-download]') : null;

        buildStripFrame(canvas, photos, filterArg, canvas.dataset.frame || '', slots, stickers, 1200)
            .then(() => {
                if (button) button.disabled = false;
            })
            .catch(() => {
                if (button) button.textContent = 'Gagal menyiapkan hasil';
            });

        if (button) {
            button.addEventListener('click', () => {
                const link = document.createElement('a');
                link.download = `photobooth-${queue}-${Date.now()}.jpg`;
                link.href = canvas.toDataURL('image/jpeg', 0.92);
                link.click();
            });
        }
    });
}

/* ---------------- Cetak ---------------- */

function initPrint() {
    const btn = document.querySelector('[data-print-btn]');
    const sheet = document.getElementById('print-sheet');
    if (!btn || !sheet) return;
    const pageStyle = document.getElementById('print-page-style');

    const printGroup = (groups, index) => {
        if (index >= groups.length) {
            sheet.innerHTML = '';
            if (pageStyle) pageStyle.textContent = '';
            return;
        }

        const group = groups[index];
        if (pageStyle) {
            pageStyle.textContent = group.width && group.height
                ? '@page { size: ' + group.width + 'mm ' + group.height + 'mm; margin: 0; }'
                : '@page { margin: 0; }';
        }

        sheet.innerHTML = '';
        group.pages.forEach((src) => {
            const page = document.createElement('div');
            page.className = 'print-page';
            if (group.width && group.height) {
                page.style.width = group.width + 'mm';
                page.style.height = group.height + 'mm';
            }
            const img = new Image();
            img.src = src;
            page.appendChild(img);
            sheet.appendChild(page);
        });

        Promise.all([...sheet.querySelectorAll('img')].map((img) => (
            img.decode ? img.decode().catch(() => {}) : Promise.resolve()
        ))).then(() => {
            let done = false;
            const next = () => {
                if (done) return;
                done = true;
                window.removeEventListener('afterprint', next);
                if (groups.length > 1) {
                    setTimeout(() => printGroup(groups, index + 1), 250);
                } else {
                    sheet.innerHTML = '';
                    if (pageStyle) pageStyle.textContent = '';
                }
            };
            window.addEventListener('afterprint', next);
            window.print();
            // Jaring pengaman bila browser tidak memicu afterprint.
            setTimeout(next, 60000);
        });
    };

    btn.addEventListener('click', async () => {
        showModal('Menyiapkan cetakan…', '');
        try {
            await (window.__stripsReady || Promise.resolve());
            sheet.innerHTML = '';

            // Setiap frame dicetak sebanyak jumlah (qty) yang dibayar.
            // Hanya ambil kartu di panel yang tampil; panel filter yang
            // tersembunyi juga menyimpan kanvas pratinjau.
            const groups = [];
            const bySize = new Map();

            document.querySelectorAll('[data-strip-container]').forEach((box) => {
                const canvas = box.querySelector('[data-strip-canvas]');
                if (!canvas || !canvas.width || box.classList.contains('hidden')) return;
                if (box.closest('[data-spa-panel].hidden')) return;
                const qty = Math.max(1, parseInt(box.dataset.qty || '1', 10) || 1);
                const width = parseFloat(box.dataset.printWidth || '0');
                const height = parseFloat(box.dataset.printHeight || '0');
                const key = width && height ? width + 'x' + height : 'auto';
                const src = canvas.toDataURL('image/png');

                if (!bySize.has(key)) {
                    bySize.set(key, { width, height, pages: [] });
                    groups.push(bySize.get(key));
                }

                const group = bySize.get(key);
                for (let i = 0; i < qty; i++) {
                    group.pages.push(src);
                }
            });

            hideModal();

            if (groups.length === 0) {
                showToast('Belum ada hasil foto untuk dicetak.');
                return;
            }

            printGroup(groups, 0);
        } catch (err) {
            hideModal();
        }
    });
}

/* ---------------- Konfirmasi bayar: cegah klik ganda ---------------- */

function initPayForm() {
    const form = document.querySelector('[data-pay-form]');
    if (!form) return;
    form.addEventListener('submit', () => {
        const btn = form.querySelector('[data-pay-submit]');
        if (btn) {
            btn.disabled = true;
            btn.textContent = 'Memproses...';
        }
    });
}

/* ---------------- Selesai: cegah klik ganda ---------------- */

function initFinishForm() {
    const form = document.querySelector('[data-finish-form]');
    if (!form) return;
    form.addEventListener('submit', () => {
        const btn = form.querySelector('button[type="submit"]');
        if (btn) {
            btn.disabled = true;
            btn.textContent = 'Menyiapkan sesi baru…';
        }
    });
}

/* ---------------- Filter tanggal (log & voucher) ---------------- */

function initDateRangeFilter() {
    const picker = document.querySelector('[data-date-picker]');
    if (!picker) return;

    const form = picker.closest('form');
    const fromInput = picker.querySelector('[data-date-from]');
    const toInput = picker.querySelector('[data-date-to]');
    const trigger = picker.querySelector('[data-date-trigger]');
    const popover = picker.querySelector('[data-date-popover]');
    const label = picker.querySelector('[data-date-label]');
    const title = picker.querySelector('[data-date-title]');
    const daysEl = picker.querySelector('[data-date-days]');
    const weekdaysEl = picker.querySelector('[data-date-weekdays]');
    const hint = picker.querySelector('[data-date-hint]');

    if (!fromInput || !toInput || !trigger || !popover || !title || !daysEl) return;

    const pad = (n) => String(n).padStart(2, '0');
    const keyOf = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
    const sameDay = (a, b) => a && b && a.getFullYear() === b.getFullYear()
        && a.getMonth() === b.getMonth() && a.getDate() === b.getDate();
    const parseKey = (value) => {
        const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(value || '');
        return match ? new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3])) : null;
    };
    const displayDate = (d) => d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
    const addDays = (date, amount) => {
        const copy = new Date(date);
        copy.setDate(copy.getDate() + amount);
        return copy;
    };

    let start = parseKey(fromInput.value);
    let end = parseKey(toInput.value);
    let view = start ? new Date(start) : new Date();
    let isOpen = false;

    const updateLabel = () => {
        if (!label) return;
        if (!start) {
            label.textContent = 'Semua tanggal';
        } else if (!end || sameDay(start, end)) {
            label.textContent = displayDate(start);
        } else {
            label.textContent = displayDate(start) + ' – ' + displayDate(end);
        }
    };

    const setOpen = (open) => {
        isOpen = open;
        popover.classList.toggle('hidden', !open);
        trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
    };

    const render = () => {
        title.textContent = view.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });

        if (weekdaysEl) {
            weekdaysEl.innerHTML = '';
            ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'].forEach((day) => {
                const cell = document.createElement('span');
                cell.textContent = day;
                weekdaysEl.appendChild(cell);
            });
        }

        const year = view.getFullYear();
        const month = view.getMonth();
        const firstOffset = (new Date(year, month, 1).getDay() + 6) % 7;
        const totalDays = new Date(year, month + 1, 0).getDate();
        const today = new Date();

        daysEl.innerHTML = '';

        for (let i = 0; i < firstOffset; i++) {
            const blank = document.createElement('span');
            blank.className = 'h-8';
            daysEl.appendChild(blank);
        }

        for (let day = 1; day <= totalDays; day++) {
            const date = new Date(year, month, day);
            const inRange = start && end && date >= start && date <= end;
            const isEdge = sameDay(date, start) || sameDay(date, end);
            const isToday = sameDay(date, today);

            const button = document.createElement('button');
            button.type = 'button';
            button.textContent = String(day);
            button.setAttribute('aria-selected', isEdge ? 'true' : 'false');
            button.className = 'h-8 rounded-lg text-xs font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-400 '
                + (isEdge
                    ? 'bg-rose-500 text-white shadow-sm'
                    : (inRange
                        ? 'bg-rose-50 text-rose-700'
                        : 'text-slate-600 hover:bg-slate-100'))
                + (isToday && !isEdge ? ' ring-1 ring-rose-100' : '');

            button.addEventListener('click', (event) => {
                // Jangan biarkan klik tanggal menutup popover. Tombol hari
                // diganti saat render(), jadi target klik sudah lepas dari DOM.
                event.stopPropagation();
                if (!start || (start && end)) {
                    start = date;
                    end = null;
                } else if (date < start) {
                    start = date;
                    end = null;
                } else {
                    end = date;
                }
                render();
            });

            daysEl.appendChild(button);
        }

        if (hint) {
            hint.textContent = !start
                ? 'Pilih tanggal mulai'
                : (!end ? 'Pilih tanggal akhir' : 'Rentang dipilih');
        }
    };

    const apply = () => {
        if (start && !end) {
            end = new Date(start);
        }
        if (start && end) {
            if (start > end) {
                [start, end] = [end, start];
            }
            fromInput.value = keyOf(start);
            toInput.value = keyOf(end);
        } else {
            fromInput.value = '';
            toInput.value = '';
        }
        updateLabel();
    };

    trigger.addEventListener('click', () => {
        setOpen(!isOpen);
        if (isOpen) {
            view = start ? new Date(start) : new Date();
            render();
        }
    });

    picker.querySelector('[data-date-prev]')?.addEventListener('click', () => {
        view = new Date(view.getFullYear(), view.getMonth() - 1, 1);
        render();
    });
    picker.querySelector('[data-date-next]')?.addEventListener('click', () => {
        view = new Date(view.getFullYear(), view.getMonth() + 1, 1);
        render();
    });

    picker.querySelectorAll('[data-date-preset]').forEach((button) => {
        button.addEventListener('click', () => {
            const today = new Date();
            const preset = button.dataset.datePreset;

            if (preset === 'clear') {
                start = null;
                end = null;
                view = new Date();
            } else if (preset === 'today') {
                start = new Date(today);
                end = new Date(today);
                view = new Date(today);
            } else if (preset === '7' || preset === '30') {
                const amount = preset === '7' ? 6 : 29;
                end = new Date(today);
                start = addDays(today, -amount);
                view = new Date(start);
            } else if (preset === 'month') {
                start = new Date(today.getFullYear(), today.getMonth(), 1);
                end = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                view = new Date(start);
            }

            render();
        });
    });

    picker.querySelector('[data-date-apply]')?.addEventListener('click', () => {
        apply();
        setOpen(false);
        if (form) form.requestSubmit();
    });

    // Tombol "Terapkan" di luar popover juga harus memakai pilihan tanggal
    // yang sedang aktif, meski pengguna belum menekan tombol di dalam popover.
    form?.addEventListener('submit', () => apply());

    document.addEventListener('click', (event) => {
        if (!isOpen) return;
        const path = typeof event.composedPath === 'function' ? event.composedPath() : [];
        if (path.includes(picker) || picker.contains(event.target)) return;
        setOpen(false);
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && isOpen) setOpen(false);
    });

    updateLabel();
}

function initAdminLogs() {
    const root = document.querySelector('[data-log-root]');
    const dataEl = document.getElementById('log-data');
    if (!root || !dataEl) return;

    let logs = {};
    try {
        logs = JSON.parse(dataEl.textContent || '{}') || {};
    } catch (error) {
        logs = {};
    }

    const modal = document.querySelector('[data-log-modal]');
    const body = document.querySelector('[data-log-modal-body]');
    const queueEl = document.querySelector('[data-log-modal-queue]');
    const metaEl = document.querySelector('[data-log-modal-meta]');
    const totalEl = document.querySelector('[data-log-modal-total]');
    const zipLink = document.querySelector('[data-log-modal-zip]');
    const deleteModal = document.querySelector('[data-delete-modal]');
    const deleteForm = document.querySelector('[data-delete-form]');
    const deleteLabel = document.querySelector('[data-delete-label]');
    const template = root.dataset.downloadTemplate || '';
    const hasilTemplate = root.dataset.hasilTemplate || '';
    const qrWrap = document.querySelector('[data-log-qr-wrap]');
    const qrCanvas = document.querySelector('[data-log-qr]');

    const esc = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[char]));
    const rp = (value) => 'Rp. ' + Number(value || 0).toLocaleString('id-ID');
    const methodLabel = (method) => {
        if (method === 'qris') return 'QRIS';
        if (method === 'voucher') return 'Voucher';
        if (method === 'cash') return 'Tunai';
        return String(method || '-').toUpperCase();
    };

    const closeModal = () => {
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        if (body) body.innerHTML = '';
        document.body.style.overflow = '';
    };

    const openModal = (id) => {
        const log = logs[String(id)];
        if (!log || !modal || !body) return;

        if (queueEl) queueEl.textContent = 'Antrian ' + log.queue;
        if (metaEl) {
            metaEl.textContent = [
                log.time,
                methodLabel(log.method),
                log.voucher_code ? 'Voucher ' + log.voucher_code : null,
            ].filter(Boolean).join(' · ');
        }
        if (totalEl) totalEl.textContent = rp(log.total);
        if (zipLink) {
            zipLink.href = template.replace('__ID__', String(log.id));
            zipLink.style.display = log.has_photos ? '' : 'none';
        }

        if (qrWrap && qrCanvas) {
            if (log.download_token && hasilTemplate) {
                qrWrap.hidden = false;
                renderDownloadQr(qrCanvas, hasilTemplate.replace('__TOKEN__', log.download_token), 80);
            } else {
                qrWrap.hidden = true;
            }
        }

        body.innerHTML = '';
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';

        const groups = Array.isArray(log.groups) ? log.groups : [];
        if (!groups.length) {
            body.innerHTML = '<div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white p-10 text-center text-sm text-slate-400">Tidak ada detail frame untuk transaksi ini.</div>';
            return;
        }

        groups.forEach((group, index) => {
            const card = document.createElement('article');
            card.className = 'rounded-2xl border border-slate-200 bg-white p-4 shadow-sm'
                + (groups.length === 1 ? ' lg:col-span-2' : '');
            card.dataset.logGroup = String(index);
            const photos = Array.isArray(group.photos) ? group.photos.filter(Boolean) : [];
            const hasPhotos = photos.length > 0;

            card.innerHTML = `
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h3 class="font-heading text-sm font-bold text-slate-800 truncate">${esc(group.name || 'Frame')}</h3>
                        <p class="text-[11px] text-slate-400 mt-0.5">${esc(group.photo_total || 0)} foto${Number(group.qty || 1) > 1 ? ' · ' + esc(group.qty) + ' lembar' : ''}</p>
                    </div>
                    <span class="inline-flex shrink-0 rounded-full bg-rose-50 px-2.5 py-1 text-[11px] font-bold text-rose-600">×${esc(group.qty || 1)}</span>
                </div>
                <div class="mt-3 flex min-h-[190px] items-center justify-center rounded-xl bg-slate-50 p-3">
                    ${hasPhotos
                        ? '<canvas class="max-h-[340px] w-auto max-w-full rounded-lg bg-white shadow-sm" data-log-canvas></canvas>'
                        : '<div class="text-center text-slate-400"><svg class="w-9 h-9 mx-auto mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="1.5"/><path d="m21 15-4.5-4.5L6 21"/></svg><p class="text-xs font-medium">Foto sudah tidak tersimpan</p></div>'}
                </div>
                <div class="mt-3 flex items-center justify-between gap-2">
                    <span class="text-[11px] font-medium text-slate-400">${hasPhotos ? 'Hasil foto + frame' : 'Arsip tidak tersedia'}</span>
                    ${hasPhotos
                        ? '<button type="button" data-log-download-item class="inline-flex items-center gap-1.5 rounded-lg bg-rose-50 px-3 py-1.5 text-[11px] font-bold text-rose-600 hover:bg-rose-100 transition" disabled><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M4 21h16"/></svg> Unduh hasil</button>'
                        : ''}
                </div>
            `;

            body.appendChild(card);

            if (!hasPhotos) return;

            const canvas = card.querySelector('[data-log-canvas]');
            const button = card.querySelector('[data-log-download-item]');

            buildStripFrame(canvas, photos, group.filters || [], group.frame_url || null, group.slots || [], group.stickers || [])
                .then(() => {
                    if (button) button.disabled = false;
                })
                .catch(() => {
                    if (button) {
                        button.disabled = true;
                        button.textContent = 'Gagal menyiapkan';
                    }
                });
        });
    };

    document.querySelectorAll('[data-log-open]').forEach((button) => {
        button.addEventListener('click', () => openModal(button.dataset.logOpen));
    });

    document.querySelectorAll('[data-log-close]').forEach((button) => {
        button.addEventListener('click', closeModal);
    });

    modal?.addEventListener('click', (event) => {
        if (event.target === modal) closeModal();
    });

    body?.addEventListener('click', (event) => {
        const button = event.target.closest('[data-log-download-item]');
        if (!button) return;

        const card = button.closest('[data-log-group]');
        const canvas = card?.querySelector('[data-log-canvas]');
        if (!canvas || !canvas.width) {
            showToast('Hasil foto masih disiapkan. Coba lagi sebentar.', 'warning');
            return;
        }

        const logId = document.querySelector('[data-log-modal-queue]')?.textContent || '';
        const index = Number(card?.dataset.logGroup || 0) + 1;
        const link = document.createElement('a');
        link.href = canvas.toDataURL('image/jpeg', 0.92);
        link.download = 'photobooth-' + logId.replace(/[^0-9a-z]+/gi, '-').replace(/^-|-$/g, '') + '-' + index + '.jpg';
        link.click();
        showToast('Hasil foto berhasil diunduh.', 'success');
    });

    // Hapus log dengan konfirmasi modal (bukan confirm bawaan browser).
    const closeDelete = () => {
        if (!deleteModal) return;
        deleteModal.classList.add('hidden');
        deleteModal.classList.remove('flex');
        document.body.style.overflow = '';
    };

    document.querySelectorAll('[data-log-delete]').forEach((button) => {
        button.addEventListener('click', () => {
            if (!deleteModal || !deleteForm) return;
            deleteForm.action = button.dataset.deleteUrl || '#';
            if (deleteLabel) deleteLabel.textContent = button.dataset.deleteLabel || 'Transaksi ini';
            deleteModal.classList.remove('hidden');
            deleteModal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        });
    });

    document.querySelector('[data-delete-cancel]')?.addEventListener('click', closeDelete);
    deleteModal?.addEventListener('click', (event) => {
        if (event.target === deleteModal) closeDelete();
    });

    deleteForm?.addEventListener('submit', () => {
        const button = deleteForm.querySelector('button[type="submit"]');
        if (button) {
            button.disabled = true;
            button.textContent = 'Menghapus…';
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        if (deleteModal && !deleteModal.classList.contains('hidden')) {
            closeDelete();
        } else if (modal && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });
}

/* ---------------- Bulk delete modal (log & voucher) ---------------- */
function initBulkDeleteModal() {
    const modal = document.querySelector('[data-bulk-delete-modal]');
    if (!modal) return;

    const form = document.querySelector('[data-bulk-delete-form]');
    const openBtn = document.querySelector('[data-bulk-delete-open]');
    const cancelBtn = document.querySelector('[data-bulk-delete-cancel]');

    const close = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    };

    const open = () => {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    };

    openBtn?.addEventListener('click', open);
    cancelBtn?.addEventListener('click', close);
    modal.addEventListener('click', (event) => {
        if (event.target === modal) close();
    });

    form?.addEventListener('submit', () => {
        const button = form.querySelector('[data-bulk-delete-confirm]');
        if (button) {
            button.disabled = true;
            button.textContent = 'Menghapus...';
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            close();
        }
    });
}

/* ---------------- Voucher frame selection ---------------- */

function initVoucherFrame() {
    const form = document.querySelector('[data-voucher-frame-form]');
    if (!form) return;

    const options = form.querySelectorAll('[data-voucher-frame-option]');
    const cartList = form.querySelector('[data-voucher-cart-list]');
    const cartEmpty = form.querySelector('[data-cart-empty]');
    const cartCount = form.querySelector('[data-cart-count]');
    const clearBtn = form.querySelector('[data-cart-clear]');
    const submitBtn = form.querySelector('[data-voucher-cart-submit]');
    const printTotalEl = form.querySelector('[data-voucher-print-total]');
    const printCountEl = form.querySelector('[data-voucher-print-count]');
    const quotaChips = form.querySelectorAll('[data-voucher-quota-chip]');

    const quotasJson = form.querySelector('[data-voucher-quotas]');
    let quotas = {};
    try { quotas = JSON.parse(quotasJson?.textContent || '{}'); } catch (e) { quotas = {}; }

    const totalQuota = Object.values(quotas).reduce((a, b) => a + b, 0);

    const FRAME_INFO = {};
    options.forEach((opt) => {
        FRAME_INFO[opt.dataset.voucherFrameOption] = {
            name: opt.dataset.name || opt.dataset.voucherFrameOption,
            sizeName: opt.dataset.sizeName || '',
            sizeId: opt.dataset.sizeId || '',
            photoCount: parseInt(opt.dataset.photoCount || '1', 10),
            img: opt.querySelector('img')?.src || '',
        };
    });

    const cart = {};
    options.forEach((opt) => {
        const qty = parseInt(opt.dataset.qty || '0', 10);
        if (qty > 0) cart[opt.dataset.voucherFrameOption] = qty;
    });

    const totalsBySize = () => {
        const totals = {};
        Object.entries(cart).forEach(([slug, qty]) => {
            if (qty <= 0) return;
            const info = FRAME_INFO[slug];
            if (!info) return;
            totals[info.sizeId] = (totals[info.sizeId] || 0) + qty;
        });
        return totals;
    };

    const remainingForSize = (sizeId) => {
        const quota = quotas[sizeId] || 0;
        const totals = totalsBySize();
        return quota - (totals[sizeId] || 0);
    };

    const fmt = (n) => 'Rp ' + n.toLocaleString('id-ID');

    const render = () => {
        const totals = totalsBySize();

        options.forEach((opt) => {
            const slug = opt.dataset.voucherFrameOption;
            const inCart = (cart[slug] || 0) > 0;
            const badge = opt.querySelector('[data-add-badge]');
            opt.setAttribute('aria-pressed', inCart ? 'true' : 'false');
            if (badge) badge.hidden = !inCart;
            const qtyEl = opt.querySelector('[data-add-qty]');
            if (qtyEl) qtyEl.textContent = cart[slug] || 0;

            const sizeId = opt.dataset.sizeId;
            const remaining = remainingForSize(sizeId);
            const isFull = (quotas[sizeId] || 0) > 0 && remaining <= 0 && !inCart;
            opt.classList.toggle('opacity-40', isFull);
            opt.classList.toggle('pointer-events-none', isFull);
        });

        const entries = Object.entries(cart).filter(([, q]) => q > 0);

        if (clearBtn) clearBtn.hidden = entries.length === 0;

        cartList.innerHTML = '';
        if (entries.length === 0 && cartEmpty) {
            cartEmpty.classList.remove('hidden');
            cartList.appendChild(cartEmpty);
        }
        if (cartCount) cartCount.textContent = String(entries.length);

        entries.forEach(([slug, qty]) => {
            const info = FRAME_INFO[slug] || { name: slug, sizeName: '', img: '' };
            const row = document.createElement('div');
            row.className = 'k-cart-row';
            row.innerHTML = `
                <div class="flex items-center gap-2.5">
                    <img src="${info.img}" alt="" class="w-9 h-11 rounded-lg object-contain bg-slate-50 shrink-0">
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-bold text-slate-800 truncate leading-tight">${info.name}</p>
                        <p class="text-[11px] text-slate-400 truncate">${info.sizeName ? info.sizeName + ' · ' : ''}${qty}x</p>
                    </div>
                    <button type="button" data-cart-remove="${slug}" class="text-slate-300 hover:text-rose-500 text-xl leading-none shrink-0" aria-label="Hapus" title="Hapus">×</button>
                </div>
                <div class="flex items-center justify-end gap-2">
                    <button type="button" data-cart-dec="${slug}" aria-label="Kurangi" class="k-cart-qty">−</button>
                    <span class="text-sm font-extrabold w-6 text-center tabular-nums">${qty}</span>
                    <button type="button" data-cart-inc="${slug}" aria-label="Tambah" class="k-cart-qty">+</button>
                </div>
            `;
            cartList.appendChild(row);
        });

        // picks
        const picks = form.querySelector('#voucher-frame-picks');
        if (picks) {
            picks.innerHTML = '';
            entries.forEach(([slug, qty]) => {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'cart[' + slug + ']';
                hidden.value = qty;
                picks.appendChild(hidden);
            });
        }

        // quota bar
        quotaChips.forEach((chip) => {
            const sizeId = chip.dataset.voucherQuotaChip;
            const selectedEl = chip.querySelector('[data-voucher-quota-selected]');
            if (selectedEl) selectedEl.textContent = totals[sizeId] || 0;
        });
        const totalSelected = Object.values(totals).reduce((a, b) => a + b, 0);
        if (printTotalEl) printTotalEl.textContent = totalSelected + '/' + totalQuota;
        if (printCountEl) printCountEl.textContent = totalSelected;

        // submit enabled only if every quota matches exactly
        let complete = totalQuota > 0 && totalSelected === totalQuota;
        Object.keys(quotas).forEach((sizeId) => {
            if ((totals[sizeId] || 0) !== quotas[sizeId]) complete = false;
        });
        if (submitBtn) submitBtn.disabled = !complete;
    };

    const add = (slug) => {
        const info = FRAME_INFO[slug];
        if (!info) return;
        const sizeId = info.sizeId;
        const remaining = remainingForSize(sizeId);
        if (remaining <= 0) {
            showToast('Kuota ukuran ini sudah terpenuhi.', 'warning');
            return;
        }
        cart[slug] = (cart[slug] || 0) + 1;
        render();
    };

    const dec = (slug) => {
        cart[slug] = Math.max(0, (cart[slug] || 0) - 1);
        if (cart[slug] === 0) delete cart[slug];
        render();
    };

    const remove = (slug) => {
        delete cart[slug];
        render();
    };

    const applyFilter = (slug) => {
        form.querySelectorAll('[data-category-chip]').forEach((cat) => {
            cat.setAttribute('aria-pressed', cat.dataset.voucherCategory === slug ? 'true' : 'false');
        });
        options.forEach((opt) => {
            opt.hidden = slug !== 'semua' && opt.dataset.category !== slug;
        });
    };

    options.forEach((opt) => opt.addEventListener('click', () => add(opt.dataset.voucherFrameOption)));
    form.querySelectorAll('[data-category-chip]').forEach((cat) => cat.addEventListener('click', () => applyFilter(cat.dataset.voucherCategory)));

    if (cartList) {
        cartList.addEventListener('click', (e) => {
            const inc = e.target.closest('[data-cart-inc]');
            const decBtn = e.target.closest('[data-cart-dec]');
            const rm = e.target.closest('[data-cart-remove]');
            if (inc) add(inc.dataset.cartInc);
            else if (decBtn) dec(decBtn.dataset.cartDec);
            else if (rm) remove(rm.dataset.cartRemove);
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            Object.keys(cart).forEach((key) => delete cart[key]);
            render();
            showToast('Keranjang dikosongkan.', 'success');
        });
    }

    form.addEventListener('submit', (event) => {
        const totals = totalsBySize();
        let complete = totalQuota > 0;
        Object.keys(quotas).forEach((sizeId) => {
            if ((totals[sizeId] || 0) !== quotas[sizeId]) complete = false;
        });
        if (!complete) {
            event.preventDefault();
            showToast('Jumlah cetak per ukuran harus sesuai voucher.', 'warning');
            return;
        }
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Menyiapkan kamera…';
        }
    });

    render();
}

/* ---------------- Boot ---------------- */

document.addEventListener('DOMContentLoaded', () => {
    initTimer();
    initPhotoSession();
    initFilterSelect();
    initFilterEditor();
    initFrameCart();
    initVoucherFrame();
    initMetodeSelect();
    initQr();
    initStrip();
    initDownloadQr();
    initPublicDownload();
    initPrint();
    initPayForm();
    initFinishForm();
    initDateRangeFilter();
    initAdminLogs();
    initBulkDeleteModal();
});
