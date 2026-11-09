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
        if (note) note.textContent = `Mengalihkan ke layar awal dalam ${seconds} detik…`;
        const redirectTimer = setInterval(() => {
            seconds -= 1;
            if (note) note.textContent = seconds > 0
                ? `Mengalihkan ke layar awal dalam ${seconds} detik…`
                : 'Mengalihkan ke layar awal…';
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
        photoError.classList.toggle('hidden', !msg);
        if (msg) showToast(msg);
    };

    const activateTab = (fIdx) => {
        activeFrame = fIdx;
        slotGroups.forEach((g, i) => g.classList.toggle('hidden', i !== fIdx));
        paintTabs();
        renderFotoPreview();
    };

    const paintTabs = () => {
        tabs.forEach((t, i) => {
            const done = frameDone(frames[i]);
            const isActive = i === activeFrame;
            const clazz = done && !isActive
                ? 'bg-emerald-500 text-white ring-emerald-500 shadow-md shadow-emerald-200'
                : isActive
                    ? 'bg-rose-500 text-white ring-rose-500 shadow-md shadow-rose-200'
                    : 'bg-white/80 text-rose-600 ring-rose-200';
            t.className = 'px-3 py-1.5 rounded-full text-xs font-semibold ring-1 transition-all ' + clazz;
            t.setAttribute('aria-selected', isActive ? 'true' : 'false');
            const badge = t.querySelector('[data-tab-status]');
            if (badge) badge.textContent = done ? '✓ Selesai' : (frames[i].count + ' foto');
        });

        if (progressEl) {
            const doneCount = photos.filter(Boolean).length;
            progressEl.textContent = doneCount + '/' + photoCount;
        }
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
            btn.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold ring-1 transition min-w-0 max-w-[240px] ' + (active ? 'bg-rose-500 text-white ring-rose-500 shadow-md shadow-rose-200' : 'bg-white/80 text-rose-600 ring-rose-200 hover:bg-white');
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
        if (icon) icon.classList.add('hidden');
        if (status) status.textContent = 'Selesai ✓';
        if (img) {
            img.src = dataUrl;
            img.classList.remove('hidden');
        }
        if (retake) retake.classList.remove('hidden');
    };

    const updateNext = () => {
        const done = photos.every(Boolean);
        if (nextBtn) {
            nextBtn.classList.toggle('hidden', !done);
        }
        if (shootBtn) {
            shootBtn.classList.toggle('hidden', done);
        }
    };

    const activeFrameIndex = () => {
        const tab = document.querySelector('[data-frame-tab][aria-selected="true"]');
        return tab ? parseInt(tab.dataset.frameTab || '0', 10) : 0;
    };

    const renderFotoPreview = async () => {
        const canvas = document.querySelector('[data-foto-preview]');
        if (!canvas) return;
        const idx = activeFrame;
        const container = document.querySelector(`[data-frame-slots="${idx}"]`);
        if (!container) { canvas.classList.add('hidden'); return; }
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
        if (!hasAny) { canvas.classList.add('hidden'); if (hint) hint.classList.remove('hidden'); return; }
        canvas.classList.remove('hidden');
        if (hint) hint.classList.add('hidden');
        try {
            await buildStripFrame(canvas, framePhotos, 'asli', frameImage, slots && slots.length ? slots : null);
        } catch (e) {
            canvas.classList.add('hidden');
        }
    };

    const setCountdownVisible = (visible) => {
        if (!countdown) return;
        countdown.classList.toggle('hidden', !visible);
        countdown.classList.toggle('flex', visible);
    };

    const takePhoto = async (i) => {
        if (shootBtn) shootBtn.classList.add('hidden');
        if (nextBtn) nextBtn.classList.add('hidden');

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
                window.location.href = data.redirect || '/spa?step=review';
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

    panel.querySelectorAll('[data-filter-item]').forEach((block) => {
        const canvas = block.querySelector('[data-strip-canvas]');
        if (!canvas) return;

        let photos = [];
        let slots = [];
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
        const frameImage = canvas.dataset.frameImage;

        const paint = () => {
            const map = {};
            block.querySelectorAll('[data-filter-photo]').forEach((row) => {
                const parts = (row.dataset.filterPhoto || '').split(':');
                const local = parseInt(parts.pop(), 10);
                const input = row.querySelector('[data-filter-value]');
                const val = input ? input.value : 'asli';
                if (!Number.isNaN(local)) map[local] = val;
                row.querySelectorAll('[data-filter-chip]').forEach((chip) => {
                    const on = chip.dataset.filterChip === val;
                    chip.setAttribute('aria-pressed', on ? 'true' : 'false');
                    chip.classList.toggle('bg-rose-500', on);
                    chip.classList.toggle('text-white', on);
                    chip.classList.toggle('ring-rose-500', on);
                    chip.classList.toggle('bg-white', !on);
                    chip.classList.toggle('text-slate-500', !on);
                    chip.classList.toggle('ring-rose-100', !on);
                });
            });
            const arr = photos.map((_, i) => map[i] ?? 'asli');
            buildStripFrame(canvas, photos, arr, frameImage, slots);
        };

        block.querySelectorAll('[data-filter-chip]').forEach((chip) => {
            chip.addEventListener('click', () => {
                const row = chip.closest('[data-filter-photo]');
                const input = row ? row.querySelector('[data-filter-value]') : null;
                if (input) input.value = chip.dataset.filterChip;
                paint();
            });
        });

        // Terapkan filter Foto 1 ke seluruh foto dalam frame ini.
        const sameBtn = block.querySelector('[data-filter-same]');
        if (sameBtn) {
            sameBtn.addEventListener('click', () => {
                const rows = [...block.querySelectorAll('[data-filter-photo]')];
                if (!rows.length) return;
                const value = rows[0].querySelector('[data-filter-value]')?.value || 'asli';
                rows.forEach((row) => {
                    const input = row.querySelector('[data-filter-value]');
                    if (input) input.value = value;
                });
                paint();
                showToast('Filter Foto 1 diterapkan ke semua foto.', 'success');
            });
        }

        paint();
    });
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

    const fmt = (n) => 'Rp. ' + n.toLocaleString('id-ID');

    const FRAME_INFO = {};
    options.forEach((opt) => {
        FRAME_INFO[opt.dataset.frameOption] = {
            name: opt.dataset.name || opt.dataset.frameOption,
            price: parseInt(opt.dataset.price || '0', 10),
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
            opt.classList.toggle('ring-2', inCart);
            opt.classList.toggle('ring-rose-500', inCart);
            opt.classList.toggle('shadow-lg', inCart);
            opt.classList.toggle('shadow-rose-100', inCart);
            opt.setAttribute('aria-pressed', inCart ? 'true' : 'false');
            if (badge) badge.classList.toggle('hidden', !inCart);
            const qtyEl = opt.querySelector('[data-add-qty]');
            if (qtyEl) qtyEl.textContent = cart[slug] || 0;
        });

        const entries = Object.entries(cart).filter(([, q]) => q > 0);

        if (clearBtn) clearBtn.classList.toggle('hidden', entries.length === 0);

        cartList.innerHTML = '';
        if (entries.length === 0 && cartEmpty) {
            // pesan kosong ikut terhapus innerHTML='' di atas — pasang ulang
            cartEmpty.classList.remove('hidden');
            cartList.appendChild(cartEmpty);
        }
        if (cartCount) cartCount.textContent = entries.length + (entries.length === 1 ? ' item' : ' item');

        entries.forEach(([slug, qty]) => {
            const info = FRAME_INFO[slug] || { name: slug, price: 0, img: '' };
            const row = document.createElement('div');
            // Dua baris: info + tombol qty. Satu baris membuat nama jadi "K…"
            // karena lebar aside hanya 230px.
            row.className = 'rounded-xl bg-white/70 ring-1 ring-rose-100 p-2 flex flex-col gap-1.5';
            row.innerHTML = `
                <div class="flex items-center gap-2">
                    <img src="${info.img}" alt="" class="w-9 h-11 rounded-lg object-contain bg-slate-50 ring-1 ring-slate-100 shrink-0">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold truncate leading-tight">${info.name}</p>
                        <p class="text-[11px] text-slate-400 truncate">${fmt(info.price)}</p>
                    </div>
                    <button type="button" data-cart-remove="${slug}" class="text-slate-300 hover:text-rose-500 text-lg leading-none shrink-0" title="Hapus">×</button>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-300">Jumlah</span>
                    <div class="flex items-center gap-1.5">
                        <button type="button" data-cart-dec="${slug}" class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 font-bold leading-none hover:bg-rose-50 hover:text-rose-600">−</button>
                        <span class="text-xs font-bold w-4 text-center">${qty}</span>
                        <button type="button" data-cart-inc="${slug}" class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 font-bold leading-none hover:bg-rose-50 hover:text-rose-600">+</button>
                    </div>
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
            const active = cat.dataset.category === slug;
            cat.setAttribute('aria-pressed', active ? 'true' : 'false');
            cat.classList.toggle('bg-rose-500', active);
            cat.classList.toggle('text-white', active);
            cat.classList.toggle('ring-rose-500', active);
            cat.classList.toggle('shadow-md', active);
            cat.classList.toggle('shadow-rose-200', active);
            cat.classList.toggle('bg-white/80', !active);
            cat.classList.toggle('text-rose-600', !active);
            cat.classList.toggle('ring-rose-200', !active);
        });

        options.forEach((opt) => {
            const show = slug === 'semua' || opt.dataset.category === slug;
            opt.classList.toggle('hidden', !show);
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
            opt.classList.toggle('ring-2', active);
            opt.classList.toggle('ring-rose-500', active);
            opt.classList.toggle('shadow-lg', active);
            opt.classList.toggle('shadow-rose-100', active);
            opt.classList.toggle('ring-1', !active);
            opt.classList.toggle('ring-rose-100', !active);

            const check = opt.querySelector('[data-metode-check]');
            if (check) {
                check.classList.toggle('border-rose-500', active);
                check.classList.toggle('bg-rose-500', active);
                check.classList.toggle('border-slate-300', !active);
                const dot = check.querySelector('span');
                if (dot) dot.classList.toggle('hidden', !active);
            }
        });

        const isVoucher = input.value === 'voucher';
        voucherBox?.classList.toggle('hidden', !isVoucher);
        if (submitBtn) {
            submitBtn.textContent = isVoucher ? 'Gunakan Voucher & Mulai Foto' : 'Lanjut Pilih Frame';
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
    const price = (priceEl?.textContent ?? 'Rp. 30.000').replace(/\D/g, '') || '30000';
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

async function buildStripFrame(canvas, photoUrls, filterKey, frameImageUrl, frameSlots) {
    // filterKey: string (satu filter utk semua, kompatibel lama) atau
    // array per-index foto (jalur per-foto).
    const isPerPhoto = Array.isArray(filterKey);
    const filter = isPerPhoto ? 'none' : (FILTERS[filterKey] ?? 'none');
    const filterAt = (i) => (isPerPhoto ? (FILTERS[filterKey[i]] ?? 'none') : filter);
    const slots = Array.isArray(frameSlots) && frameSlots.length ? frameSlots : null;

    const baseW = 360;
    const photos = await loadImages(photoUrls);
    const frameImg = frameImageUrl ? (await loadImages([frameImageUrl]))[0] : null;

    const scale = 2;
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
        ctx.save();
        photos.forEach((img, i) => {
            if (!img || !slots[i]) return;
            const slot = slots[i];
            const x = slot.x * baseW;
            const y = slot.y * baseH;
            const w = slot.w * baseW;
            const h = slot.h * baseH;
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
            ctx.filter = filterAt(i);
            ctx.drawImage(img, sx, sy, sw, sh, x, y, w, h);
            ctx.restore();
        });
        ctx.restore();
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
}

function initStrip() {
    const downloadLink = document.querySelector('[data-strip-download]');

    // Dokumen SPA memuat semua panel; hanya kanvas di panel yang tampil yang dipakai
    // (agar cetak/unduh/email tidak ikut menyertakan kanvas panel filter yang tersembunyi).
    const canvases = [...document.querySelectorAll('[data-strip-canvas]')].filter((c) => {
        const panel = c.closest('[data-spa-panel]');
        return !panel || !panel.classList.contains('hidden');
    });
    const ready = canvases.map((canvas) => {
        let photos = [];
        let slots = [];
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
        return buildStripFrame(
            canvas,
            photos,
            filterArg,
            canvas.dataset.frameImage,
            slots
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

/* ---------------- Email form ---------------- */

function initEmailForm() {
    const form = document.querySelector('[data-email-form]');
    if (!form) return;

    const errorBox = form.querySelector('[data-email-error]');
    const successBox = form.querySelector('[data-email-success]');
    const submitBtn = form.querySelector('[data-email-submit]');
    const submitLabel = submitBtn ? submitBtn.innerHTML : '';

    const setError = (msg) => {
        if (!errorBox) return;
        errorBox.textContent = msg || '';
        errorBox.classList.toggle('hidden', !msg);
        if (msg) showToast(msg);
    };
    const setSuccess = (msg) => {
        if (!successBox) return;
        successBox.textContent = msg || '';
        successBox.classList.toggle('hidden', !msg);
    };

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        setError('');
        setSuccess('');
        const email = document.getElementById('email-input');
        if (!email.value || !email.checkValidity()) {
            email.focus();
            setError('Masukkan alamat email yang valid.');
            return;
        }

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Mengirim…';
        }
        showModal('Mengirim softfile...', 'Mohon tunggu sebentar');

        try {
            // Lampirkan strip yang sudah jadi (frame + filter), bukan foto mentah.
            const built = await (window.__stripsReady || Promise.resolve([]));
            const strips = built
                .filter((c) => c && c.width)
                .map((c) => c.toDataURL('image/jpeg', 0.9));

            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                },
                body: JSON.stringify({ email: email.value, strips }),
            });
            const data = await res.json().catch(() => ({}));

            if (res.ok && (data.ok || data.redirect)) {
                hideModal();
                setSuccess(data.message || `Softfile dikirim ke ${email.value}.`);
                showToast(data.message || 'Email berhasil dikirim.', 'success');
                // Tetap di halaman ini agar pelanggan masih bisa mencetak/mengunduh.
                return;
            }

            hideModal();
            setError(data.message || data.errors?.email?.[0] || 'Gagal mengirim email. Coba lagi.');
        } catch (err) {
            hideModal();
            setError('Koneksi bermasalah. Coba lagi.');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = submitLabel;
            }
        }
    });
}

/* ---------------- Cetak ---------------- */

function initPrint() {
    const btn = document.querySelector('[data-print-btn]');
    const sheet = document.getElementById('print-sheet');
    if (!btn || !sheet) return;

    btn.addEventListener('click', async () => {
        showModal('Menyiapkan cetakan...', 'Dialog cetak akan terbuka');
        try {
            await (window.__stripsReady || Promise.resolve());
            sheet.innerHTML = '';

            // Setiap frame dicetak sebanyak jumlah (qty) yang dibayar.
            // Hanya ambil kartu di panel yang tampil; panel filter yang
            // tersembunyi juga menyimpan kanvas pratinjau.
            document.querySelectorAll('[data-strip-container]').forEach((box) => {
                const canvas = box.querySelector('[data-strip-canvas]');
                if (!canvas || !canvas.width || box.classList.contains('hidden')) return;
                if (box.closest('[data-spa-panel].hidden')) return;
                const qty = Math.max(1, parseInt(box.dataset.qty || '1', 10) || 1);
                const src = canvas.toDataURL('image/png');
                for (let i = 0; i < qty; i++) {
                    const page = document.createElement('div');
                    page.className = 'print-page';
                    const img = new Image();
                    img.src = src;
                    page.appendChild(img);
                    sheet.appendChild(page);
                }
            });

            await Promise.all([...sheet.querySelectorAll('img')].map((img) => (img.decode ? img.decode().catch(() => {}) : Promise.resolve())));
            hideModal();
            window.print();
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

/* ---------------- Boot ---------------- */

document.addEventListener('DOMContentLoaded', () => {
    initTimer();
    initPhotoSession();
    initFilterSelect();
    initFilterEditor();
    initFrameCart();
    initMetodeSelect();
    initQr();
    initStrip();
    initEmailForm();
    initPrint();
    initPayForm();
    initFinishForm();
});
