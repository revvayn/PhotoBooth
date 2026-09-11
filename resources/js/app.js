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

    let stream = null;
    const photos = [];

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

    const runShoot = async () => {
        if (shootBtn) shootBtn.disabled = true;

        for (let i = 0; i < 3; i++) {
            if (countdown) countdown.classList.remove('hidden');
            countdown.classList.add('flex');

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
            photos.push(dataUrl);

            const thumb = document.querySelector(`[data-shot-item="${i}"]`);
            if (thumb) {
                const img = thumb.querySelector('[data-shot-img]');
                const status = thumb.querySelector('[data-shot-status]');
                const icon = thumb.querySelector('span:first-child');
                if (icon) icon.classList.add('hidden');
                if (status) status.textContent = 'Selesai ✓';
                if (img) {
                    img.src = dataUrl;
                    img.classList.remove('hidden');
                }
            }

            if (i < 2) await wait(1200);
        }

        const res = await fetch('/foto', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            },
            body: JSON.stringify({ photos }),
        });

        if (res.ok) {
            stopStream();
            const data = await res.json();
            if (nextBtn) {
                nextBtn.classList.remove('hidden');
                nextBtn.classList.add('flex');
                nextBtn.href = data.redirect;
            }
        }
    };

    if (shootBtn) shootBtn.addEventListener('click', runShoot);
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

    const input = document.getElementById('frame-input');
    const options = form.querySelectorAll('[data-frame-option]');
    const cats = form.querySelectorAll('[data-category]');

    cats.forEach((cat) => {
        cat.addEventListener('click', () => {
            cats.forEach((c) => {
                c.classList.remove('bg-rose-500', 'text-white', 'ring-rose-500', 'shadow-md', 'shadow-rose-200');
                c.classList.add('text-rose-600', 'bg-white/80');
            });
            cat.classList.remove('text-rose-600', 'bg-white/80');
            cat.classList.add('bg-rose-500', 'text-white', 'ring-rose-500', 'shadow-md', 'shadow-rose-200');

            const slug = cat.dataset.category;
            options.forEach((o) => {
                const show = o.dataset.cat === slug;
                o.classList.toggle('hidden', !show);
                if (!show) o.classList.remove('ring-2', 'ring-rose-500', 'shadow-lg', 'shadow-rose-100');
            });

            const firstVisible = [...options].find((o) => o.dataset.cat === slug);
            if (firstVisible) {
                input.value = firstVisible.dataset.frameOption;
                firstVisible.classList.add('ring-2', 'ring-rose-500', 'shadow-lg', 'shadow-rose-100');
            }
        });
    });

    options.forEach((opt) => {
        opt.addEventListener('click', () => {
            input.value = opt.dataset.frameOption;
            options.forEach((o) => {
                o.classList.remove('ring-2', 'ring-rose-500', 'shadow-lg', 'shadow-rose-100');
            });
            opt.classList.add('ring-2', 'ring-rose-500', 'shadow-lg', 'shadow-rose-100');
        });
    });
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

function drawFrames(ctx, frame, W, padding, spacing, photoW, photoH, captionH) {
    const H = padding + photoH * 3 + spacing * 2 + captionH;
    ctx.save();
    ctx.filter = 'none';

    const frameDef = {
        kpop: {
            colors: ['#fecdd3', '#f0abfc', '#c4b5fd'],
            accent: '#fb7185',
            caption: 'K-POP',
        },
        classic: {
            colors: ['#1c1917', '#d4a017'],
            accent: '#b45309',
            caption: 'CLASSIC MOMENTS',
        },
        minimalis: {
            colors: ['#f8fafc'],
            accent: '#64748b',
            caption: 'MINIMALIS',
        },
        neon: {
            colors: ['#e879f9', '#22d3ee'],
            accent: '#d946ef',
            caption: 'NEON',
        },
        estetik: {
            colors: ['#a7f3d0', '#99f6e4'],
            accent: '#14b8a6',
            caption: 'ESTETIK',
        },
    }[frame] ?? { colors: ['#fecdd3'], accent: '#fb7185', caption: 'PHOTOBOOTH' };

    // background
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, W, H);

    // outer frame band
    ctx.fillStyle = frameDef.colors[0];
    ctx.fillRect(0, 0, W, H);

    // accent band
    if (frameDef.colors[1]) {
        ctx.fillStyle = frameDef.colors[1];
        ctx.fillRect(padding - 6, padding - 6, W - (padding - 6) * 2, H - (padding - 6) * 2);
    }

    // photo area background
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(padding, padding, photoW, photoH * 3 + spacing * 2);

    // neon glow decor
    if (frame === 'neon') {
        ctx.shadowColor = frameDef.accent;
        ctx.shadowBlur = 14;
        ctx.strokeStyle = frameDef.accent;
        ctx.lineWidth = 4;
        ctx.strokeRect(padding - 10, padding - 10, photoW + 20, H - (padding - 10) * 2 - captionH + 20);
        ctx.shadowBlur = 0;
    }

    // minimalis: thin line
    if (frame === 'minimalis') {
        ctx.strokeStyle = '#cbd5e1';
        ctx.lineWidth = 2;
        ctx.strokeRect(padding - 8, padding - 8, photoW + 16, H - (padding - 8) * 2 - captionH + 16);
    }

    // kpop sparkles
    if (frame === 'kpop') {
        ctx.fillStyle = '#ffffff';
        ctx.font = '22px sans-serif';
        ctx.fillText('✦', padding + 8, H - captionH / 2);
        ctx.fillText('♥', W - padding - 30, H - captionH / 2);
        ctx.fillText('➶', W - padding - 60, padding + 14);
    }

    // caption
    ctx.font = `bold ${Math.round(captionH * 0.34)}px "Poppins", sans-serif`;
    ctx.fillStyle = frameDef.accent;
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(frameDef.caption, W / 2, H - captionH / 2 - Math.round(captionH * 0.1));

    ctx.restore();
}

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

async function drawStrip(canvas, photoUrls, filterKey, frameKey) {
    const filter = FILTERS[filterKey] ?? 'none';
    const frame = frameKey ?? 'kpop';

    const W = 360;
    const padding = { kpop: 26, classic: 30, minimalis: 18, neon: 28, estetik: 24 }[frame] ?? 24;
    const spacing = 6;
    const photoW = W - padding * 2;
    const photoH = Math.round((photoW * 4) / 3);
    const captionH = 46;
    const H = padding + photoH * 3 + spacing * 2 + captionH;

    canvas.width = W * 2;
    canvas.height = H * 2;
    const ctx = canvas.getContext('2d');
    ctx.scale(2, 2);

    const imgs = await loadImages(photoUrls);

    drawFrames(ctx, frame, W, padding, spacing, photoW, photoH, captionH);

    ctx.save();
    imgs.forEach((img, i) => {
        if (!img) return;
        const x = padding;
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

        ctx.filter = filter;
        ctx.drawImage(img, sx, sy, sw, sh, x, y, photoW, photoH);
    });
    ctx.restore();
}

function initStrip() {
    document.querySelectorAll('[data-strip-canvas]').forEach((canvas) => {
        let photos = [];
        try {
            photos = JSON.parse(canvas.dataset.photos || '[]');
        } catch (e) {
            photos = [];
        }
        if (!photos.length) {
            canvas.style.display = 'none';
            const container = canvas.closest('[data-strip-container]');
            if (container) {
                container.classList.add('hidden');
            }
            return;
        }
        drawStrip(canvas, photos, canvas.dataset.filter, canvas.dataset.frame).then(() => {
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