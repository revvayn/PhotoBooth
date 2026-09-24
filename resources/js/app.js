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

/* ---------------- Session Timer ---------------- */

function initTimer() {
    const el = document.querySelector('[data-session-timer]');
    if (!el) return;

    let remaining = parseInt(el.dataset.total || '300', 10);
    const minEl = el.querySelector('[data-session-timer-min]');
    const secEl = el.querySelector('[data-session-timer-sec]');
    const overlay = document.querySelector('[data-timeout-overlay]');

    const paint = (s) => {
        const m = Math.floor(s / 60).toString().padStart(2, '0');
        const r = (s % 60).toString().padStart(2, '0');
        if (minEl) minEl.textContent = m;
        if (secEl) secEl.textContent = r;
    };

    paint(remaining);

    const tick = () => {
        if (remaining <= 0) {
            clearInterval(interval);
            if (overlay) {
                overlay.classList.remove('hidden');
                overlay.classList.add('flex');
            }
            return;
        }
        remaining -= 1;
        paint(remaining);
    };

    const interval = setInterval(tick, 1000);
}

/* ---------------- Camera / Photo Session ---------------- */

function initPhotoSession() {
    const video = document.querySelector('[data-camera]') ?? document.getElementById('camera');
    if (!video) return;

    const loading = document.querySelector('[data-camera-loading]');
    const errorBox = document.querySelector('[data-camera-error]');
    const shootBtn = document.querySelector('[data-shoot-btn]');
    const countdown = document.querySelector('[data-countdown]');
    const countdownNum = document.querySelector('[data-countdown-num]');
    const flashOverlay = document.querySelector('[data-flash-overlay]');
    const nextBtn = document.querySelector('[data-next-btn]');

    // Global index foto tersusun berurutan sesuai urutan frame di cart.
    const slotGroups = [...document.querySelectorAll('[data-frame-slots]')];
    const tabs = [...document.querySelectorAll('[data-frame-tab]')];

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

    const activateTab = (fIdx) => {
        activeFrame = fIdx;
        slotGroups.forEach((g, i) => g.classList.toggle('hidden', i !== fIdx));
        paintTabs();
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
            t.className = 'px-4 py-2 rounded-full text-sm font-semibold ring-1 transition-all ' + clazz;
            const badge = t.querySelector('[data-tab-status]');
            if (badge) badge.textContent = done ? '✓ Selesai' : (frames[i].count + ' foto');
        });
    };

    // Pengaturan kamera dari admin (via panel foto SPA); default aman bila absen.
    const spaFoto = document.querySelector('[data-spa-panel="foto"]');
    const camW = parseInt(spaFoto?.dataset.camWidth || '1024', 10) || 1024;
    const camH = parseInt(spaFoto?.dataset.camHeight || '768', 10) || 768;
    const countFrom = Math.min(10, Math.max(1, parseInt(spaFoto?.dataset.countdown || '3', 10) || 3));

    const camSwitch = document.querySelector('[data-camera-switch]');
    let currentDeviceId = null;

    const startStream = async (deviceId) => {
        if (shooting) return false;
        stopStream();
        if (loading) loading.classList.remove('hidden');
        if (errorBox) {
            errorBox.classList.add('hidden');
            errorBox.classList.remove('flex');
        }
        try {
            const s = await navigator.mediaDevices.getUserMedia({
                video: {
                    width: { ideal: camW },
                    height: { ideal: camH },
                    ...(deviceId ? { deviceId: { exact: deviceId } } : {}),
                },
                audio: false,
            });
            stream = s;
            video.srcObject = s;
            currentDeviceId = deviceId ?? null;
            if (loading) loading.classList.add('hidden');
            return true;
        } catch (err) {
            if (loading) loading.classList.add('hidden');
            if (errorBox) {
                errorBox.classList.remove('hidden');
                errorBox.classList.add('flex');
            }
            if (shootBtn) shootBtn.disabled = true;
            return false;
        }
    };

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
            btn.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold ring-1 transition ' + (active ? 'bg-rose-500 text-white ring-rose-500 shadow-md shadow-rose-200' : 'bg-white/80 text-rose-600 ring-rose-200 hover:bg-white');
            btn.innerHTML = CAM_SVG;
            const lb = document.createElement('span');
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
            .then((all) => paintCamSwitch(all.filter((d) => d.kind === 'videoinput')))
            .catch(() => { if (camSwitch) camSwitch.remove(); });
    });

    const wait = (ms) => new Promise((r) => setTimeout(r, ms));

    const snapshot = () => {
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
        if (!nextBtn) return;
        const done = photos.every(Boolean);
        nextBtn.classList.toggle('hidden', !done);
    };

    const takePhoto = async (i) => {
        if (countdown) {
            countdown.classList.remove('hidden');
            countdown.classList.add('flex');
        }

        for (let n = countFrom; n >= 1; n--) {
            if (countdownNum) countdownNum.textContent = n;
            await wait(700);
        }

        if (countdown) {
            countdown.classList.add('hidden');
            countdown.classList.remove('flex');
        }

        await showFlash();

        const dataUrl = snapshot();
        photos[i] = dataUrl;
        updateSlot(i, dataUrl);
        paintTabs();
        updateNext();
    };

    const runShoot = async () => {
        if (shooting) return;
        shooting = true;
        if (shootBtn) shootBtn.disabled = true;

        for (let i = 0; i < photoCount; i++) {
            if (photos[i]) continue;
            activateTab(frameOf(i));
            await takePhoto(i);
            if (i < photoCount - 1) await wait(450);
        }

        shooting = false;
        if (shootBtn) shootBtn.disabled = false;
    };

    const submitPhotos = async () => {
        const valid = photos.filter(Boolean);
        if (!valid.length) return;
        if (shootBtn) shootBtn.disabled = true;
        if (nextBtn) nextBtn.disabled = true;

        try {
            const res = await fetch('/foto', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
                body: JSON.stringify({ photos: valid }),
            });

            if (res.ok) {
                stopStream();
                const data = await res.json();
                // Di dalam kanvas SPA (/spa): tetap di satu halaman, pindah ke panel filter.
                if (document.querySelector('[data-spa-main]')) {
                    window.location.href = '/spa?step=filter';
                    return;
                }
                window.location.href = data.redirect;
                return;
            }
        } catch (err) {
            // fall through
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
            if (badge) badge.classList.toggle('hidden', !inCart);
        });

        const entries = Object.entries(cart).filter(([, q]) => q > 0);

        cartList.innerHTML = '';
        if (cartEmpty) cartEmpty.classList.toggle('hidden', entries.length > 0);
        if (cartCount) cartCount.textContent = entries.length + (entries.length === 1 ? ' item' : ' item');

        entries.forEach(([slug, qty]) => {
            const info = FRAME_INFO[slug] || { name: slug, price: 0, img: '' };
            const row = document.createElement('div');
            row.className = 'flex items-center gap-2.5';
            row.innerHTML = `
                <img src="${info.img}" alt="" class="w-10 h-12 rounded-lg object-contain bg-slate-50 ring-1 ring-slate-100 shrink-0">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold truncate">${info.name}</p>
                    <p class="text-xs text-slate-400">@ ${fmt(info.price)}</p>
                </div>
                <div class="flex items-center gap-1.5">
                    <button type="button" data-cart-dec="${slug}" class="w-7 h-7 rounded-full bg-slate-100 text-slate-600 font-bold hover:bg-rose-50 hover:text-rose-600">−</button>
                    <span class="text-sm font-bold w-4 text-center">${qty}</span>
                    <button type="button" data-cart-inc="${slug}" class="w-7 h-7 rounded-full bg-slate-100 text-slate-600 font-bold hover:bg-rose-50 hover:text-rose-600">+</button>
                </div>
                <button type="button" data-cart-remove="${slug}" class="text-slate-300 hover:text-rose-500 text-xl leading-none shrink-0" title="Hapus">×</button>
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

    render();
}

/* ---------------- Payment method selection ---------------- */

function initMetodeSelect() {
    const form = document.querySelector('form[action*="metode"]');
    if (!form) return;

    const input = document.getElementById('metode-input');
    const options = form.querySelectorAll('[data-metode-option]');

    options.forEach((opt) => {
        opt.addEventListener('click', () => {
            input.value = opt.dataset.metodeOption;
            options.forEach((o) => {
                o.classList.remove('ring-2', 'ring-rose-500', 'shadow-lg', 'shadow-rose-100');
                o.classList.add('ring-1', 'ring-rose-100');
            });
            opt.classList.remove('ring-1', 'ring-rose-100');
            opt.classList.add('ring-2', 'ring-rose-500', 'shadow-lg', 'shadow-rose-100');
        });
    });
}

/* ---------------- QRIS QR code ---------------- */

function initQr() {
    const box = document.getElementById('qrcode');
    if (!box) return;

    const queue = document.querySelector('.text-rose-500')?.textContent ?? '';
    const priceEl = document.querySelector('[data-qris-price]');
    const price = (priceEl?.textContent ?? 'Rp. 30.000').replace(/\D/g, '') || '30000';
    const nmid = box.dataset.nmid || '102020034073193';
    const merchant = box.dataset.merchant || 'PERKAKASKU';
    const payload = `QRIS|${merchant}|${nmid}|${price}|ANTRIAN${queue.replace('#', '')}`;

    QRCode.toCanvas(box, payload, { width: 208, margin: 2, color: { dark: '#18181b', light: '#fafafa' } })
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
                    img.crossOrigin = 'anonymous';
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

    const canvases = [...document.querySelectorAll('[data-strip-canvas]')];
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

    if (downloadLink) {
        downloadLink.addEventListener('click', (e) => {
            e.preventDefault();
            Promise.all(ready).then((built) => {
                const strips = built.filter(Boolean);
                strips.forEach((canvas, i) => {
                    setTimeout(() => {
                        const a = document.createElement('a');
                        a.href = canvas.toDataURL('image/jpeg', 0.92);
                        a.download = 'photobooth-' + (strips.length > 1 ? i + 1 + '-' : '') + Date.now() + '.jpg';
                        a.click();
                    }, i * 400);
                });
            });
        });
    }
}

/* ---------------- Email form + printing modal ---------------- */

function initEmailForm() {
    const form = document.querySelector('[data-email-form]');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = document.getElementById('email-input');
        if (!email.value || !email.checkValidity()) {
            email.focus();
            return;
        }

        const modal = document.querySelector('[data-printing-modal]');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                },
                body: JSON.stringify({ email: email.value }),
            });
            const data = await res.json();
            window.location.href = data.redirect;
        } catch (err) {
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
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
});
