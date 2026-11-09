<div class="pointer-events-none fixed inset-x-0 top-4 z-[90] flex justify-center px-4" data-toast-region aria-live="assertive">
    <div data-toast class="hidden pointer-events-auto max-w-md w-full rounded-2xl bg-slate-900 text-white text-sm font-medium px-4 py-3 shadow-2xl ring-1 ring-white/10 transition-all duration-300 ease-out opacity-0 -translate-y-3"></div>
</div>
<script>
(function () {
    if (window.__toastInit) return;
    window.__toastInit = true;

    function box() { return document.querySelector('[data-toast]'); }

    if (!window.showToast) {
        window.showToast = function (message, tone) {
            const b = box();
            if (!b) return;
            b.textContent = message;
            b.classList.remove('bg-slate-900', 'bg-emerald-600', 'bg-amber-600', 'bg-red-600');
            const cls = tone === 'success' ? 'bg-emerald-600'
                : (tone === 'warning' ? 'bg-amber-600'
                : (tone === 'error' ? 'bg-red-600' : 'bg-slate-900'));
            b.classList.add(cls);
            b.classList.remove('hidden');
            requestAnimationFrame(function () {
                b.classList.remove('opacity-0', '-translate-y-3');
                b.classList.add('opacity-100', 'translate-y-0');
            });
            clearTimeout(window.__toastTimer);
            window.__toastTimer = setTimeout(function () {
                b.classList.add('opacity-0', '-translate-y-3');
                b.classList.remove('opacity-100', 'translate-y-0');
                setTimeout(function () { b.classList.add('hidden'); }, 300);
            }, 4000);
        };
    }

    var flashes = [];
    @if (session('success')) flashes.push({ msg: @json(session('success')), tone: 'success' }); @endif
    @if (session('error')) flashes.push({ msg: @json(session('error')), tone: 'error' }); @endif
    @if (session('expired')) flashes.push({ msg: 'Sesi sebelumnya sudah berakhir. Yuk mulai sesi baru.', tone: 'warning' }); @endif
    @if (session('finished')) flashes.push({ msg: 'Terima kasih sudah foto di {{ $brand['name'] ?? 'Photobooth' }}! Sesi baru siap dimulai.', tone: 'success' }); @endif

    if (flashes.length) {
        setTimeout(function () {
            flashes.forEach(function (f, i) {
                setTimeout(function () { window.showToast(f.msg, f.tone); }, i * 450);
            });
        }, 300);
    }
})();
</script>