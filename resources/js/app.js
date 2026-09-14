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

    const photoCountEl = document.querySelector('[data-photo-count]');
    let photoCount = parseInt(photoCountEl?.dataset.photoCount || '3', 10);
    if (!photoCount || photoCount < 1) photoCount = 3;

    let stream = null;
    const photos = new Array(photoCount).fill(null);
    let shooting = false;

    const stopStream = () => {
        if (stream) stream.getTracks().forEach((t) => t.stop());
    };

    navigator.mediaDevices
        .getUserMedia({ video: { width: { ideal: 1024 }, height: { ideal: 768 } }, audio: false })
        .then((s) => {
            stream = s;
            video.srcObject = s;
            if (loading) loading.classList.add('hidden');
        })
        .catch(() => {
            if (loading) loading.classList.add('hidden');
            if (errorBox) {
                errorBox.classList.remove('hidden');
                errorBox.classList.add('flex');
            }
            if (shootBtn) shootBtn.disabled = true;
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
        const icon = item.querySelector('.text-3xl');
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

        for (let n = 3; n >= 1; n--) {
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
        updateNext();
    };

    const runShoot = async () => {
        if (shooting) return;
        shooting = true;
        if (shootBtn) shootBtn.disabled = true;

        for (let i = 0; i < photoCount; i++) {
            if (photos[i]) continue;
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

    document.querySelectorAll('[data-retake]').forEach((btn) => {
        btn.addEventListener('click', () => {
            if (shooting) return;
            takePhoto(parseInt(btn.dataset.retake, 10));
        });
    });

    updateNext();
}

/* ---------------- Filter selection ---------------- */

function initFilterSelect() {
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

/* ---------------- Frame selection ---------------- */

function initFrameSelect() {
    const form = document.querySelector('[data-frame-form]');
    if (!form) return;
    const chips = document.querySelectorAll('[data-category-chip]');
    const input = document.getElementById('frame-input');
    const options = form.querySelectorAll('[data-frame-option]');
    const cats = document.querySelectorAll('[data-category-chip]');

    const selectOption = (opt) => {
        const slug = opt.dataset.frameOption;
        const picks = document.getElementById('frame-picks');
        const isPicked = opt.classList.contains('ring-2');

        opt.classList.toggle('ring-2', !isPicked);
        opt.classList.toggle('ring-rose-500', !isPicked);
        opt.classList.toggle('shadow-lg', !isPicked);
        opt.classList.toggle('shadow-rose-100', !isPicked);

        let hidden = picks.querySelector('input[value="'+slug+'"]');
        if (isPicked) {
            if (hidden) hidden.remove();
        } else {
            if (!hidden) {
                hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'frames[]';
                hidden.value = slug;
                picks.appendChild(hidden);
            }
        }
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

        if (slug !== 'semua') {
            const selected = [...options].find((o) => o.dataset.frameOption === input.value);
            if (selected && selected.classList.contains('hidden')) {
                selectOption([...options].find((o) => !o.classList.contains('hidden')));
            }
        }
    };

    cats.forEach((cat) => cat.addEventListener('click', () => applyFilter(cat.dataset.category)));
    options.forEach((opt) => opt.addEventListener('click', () => selectOption(opt)));
}

/* ---------------- Print count selection ---------------- */

function initPrintSelect() {
    const form = document.querySelector('[data-print-form]');
    if (!form) return;

    const input = document.getElementById('copy-input');
    const options = form.querySelectorAll('[data-copy-option]');
    const totalEl = form.querySelector('[data-total]');

    const fmt = (n) => 'Rp. ' + n.toLocaleString('id-ID');

    options.forEach((opt) => {
        opt.addEventListener('click', () => {
            const price = parseInt(opt.dataset.priceLabel ? opt.querySelector('[data-price-label]').dataset.basePrice : opt.dataset.copyOption, 10);
            input.value = opt.dataset.copyOption;
            options.forEach((o) => {
                o.classList.remove('ring-2', 'ring-rose-500', 'shadow-lg', 'shadow-rose-100');
            });
            opt.classList.add('ring-2', 'ring-rose-500', 'shadow-lg', 'shadow-rose-100');
            if (totalEl && opt.querySelector('[data-price-label]')) {
                totalEl.textContent = fmt(parseInt(opt.querySelector('[data-price-label]').dataset.basePrice, 10));
            } else if (totalEl) {
                totalEl.textContent = fmt(price);
            }
        });
    });
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
    const payload = `QRIS|PERKAKASKU|102020034073193|${price}|ANTRIAN${queue.replace('#', '')}`;

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
    const filter = FILTERS[filterKey] ?? 'none';
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
            ctx.filter = filter;
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
            ctx.filter = filter;
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
    document.querySelectorAll('[data-strip-canvas]').forEach((canvas) => {
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
            return;
        }
        buildStripFrame(
            canvas,
            photos,
            canvas.dataset.filter,
            canvas.dataset.frameImage,
            slots
        ).then(() => {
            const downloadLink = document.querySelector('[data-strip-download]');
            if (downloadLink) {
                downloadLink.addEventListener('click', (e) => {
                    e.preventDefault();
                    const a = document.createElement('a');
                    a.href = canvas.toDataURL('image/jpeg', 0.92);
                    a.download = 'photobooth-' + Date.now() + '.jpg';
                    a.click();
                });
            }
        });
    });
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
    initFrameSelect();
    initPrintSelect();
    initMetodeSelect();
    initQr();
    initStrip();
    initEmailForm();
});