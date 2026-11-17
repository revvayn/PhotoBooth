<div class="flex-1 min-h-0 flex flex-col gap-2">
    @php($brand = $brand ?? \App\Support\Brand::get())
    {{-- Header ringkas & simetris: 3 kolom setara agar judul benar-benar di tengah. --}}
    <div class="k-step-head shrink-0">
        <div class="justify-self-start flex items-center gap-2 min-w-0">
            @if (($metode ?? '') === 'voucher')
                <span class="k-pill !text-emerald-700 !bg-emerald-50 !text-xs">
                    <x-icon name="check" class="w-4 h-4" />
                    <span class="truncate">Voucher {{ $voucher['code'] ?? '' }}</span>
                </span>
            @else
                <a href="{{ ($fromSpa ?? true) ? route('spa', ['step' => 'frame']) : route('frame') }}" data-back-btn aria-label="Ganti frame"
                   class="k-btn k-btn-ghost">
                    <x-icon name="arrow-left" class="w-4 h-4" />
                    <span class="hidden sm:inline">Ganti Frame</span>
                </a>
            @endif
        </div>

        <div class="k-step-head-title">
            <h2 class="k-title !text-lg sm:!text-xl leading-tight truncate">{{ $brand['photo_title'] }}</h2>
            <p class="text-[11px] text-slate-500 leading-tight">{{ $photoCount }} foto &middot; ikuti hitungan mundur</p>
        </div>

        <div class="justify-self-end flex items-center gap-2 shrink-0">
            <span class="k-pill tabular-nums !text-xs" data-photo-progress-wrap>
                <x-icon name="camera" class="w-4 h-4 text-rose-500" />
                <span data-photo-progress>0/{{ $photoCount }}</span>
            </span>
        </div>
    </div>

    {{-- Progres pengambilan foto: umpan balik cepat tanpa harus menghitung thumbnail. --}}
    <div class="shrink-0 k-progress" role="progressbar" aria-label="Progres foto" aria-valuemin="0" aria-valuemax="{{ $photoCount }}" aria-valuenow="0" data-photo-progress-track>
        <span data-photo-progress-bar></span>
    </div>

    @if (count($items) > 1)
        <div class="shrink-0 flex justify-center" data-frame-tabs role="tablist" aria-label="Pilih frame">
            <div class="k-segment">
                @foreach ($items as $index => $item)
                    <button type="button" role="tab" data-frame-tab="{{ $index }}" data-frame-photo-count="{{ $item['photo_count'] }}"
                            aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                            class="k-segment-item max-w-[220px]">
                        <span class="truncate">{{ $item['name'] }}@if ($item['qty'] > 1) <span class="opacity-80">x{{ $item['qty'] }}</span>@endif</span>
                        <span class="k-segment-count" data-tab-status>{{ $item['photo_count'] }} foto</span>
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    <div class="flex-1 min-h-0 grid grid-cols-1 lg:grid-cols-[180px_minmax(0,1fr)_180px] gap-2 items-stretch">
        {{-- Thumbnail slot (kiri) --}}
        <div class="order-2 lg:order-1 flex lg:flex-col gap-2 overflow-x-auto lg:overflow-y-auto shrink-0 w-full lg:w-auto min-h-0 pb-1 lg:pb-0" data-shot-thumbs data-photo-count="{{ $photoCount }}">
            @php($gi = 0)
            @foreach ($items as $index => $item)
                <div class="flex gap-2 flex-1 min-w-0 lg:flex-col lg:w-full lg:flex-1 lg:min-h-0 shrink-0" @if (count($items) > 1 && $index !== 0) hidden @endif data-frame-slots="{{ $index }}" data-frame-image="{{ $item['image_url'] }}" data-frame-slots-json='@json($item['slots'] ?? [])'>
                    @foreach (range(1, $item['photo_count']) as $local)
                        @php($gidx = $gi++)
                        <div class="k-shot relative rounded-xl overflow-hidden ring-1 ring-black/10 bg-white shadow-sm h-28 flex-1 min-w-0 lg:h-auto lg:w-full lg:flex-1 lg:min-h-[132px]" data-shot-item="{{ $gidx }}" data-done="false">
                            <div class="absolute inset-0 flex flex-col items-center justify-center text-center px-1 gap-0.5">
                                <span class="text-2xl leading-none text-rose-300" data-shot-icon><x-icon name="film" class="w-7 h-7" /></span>
                                <span class="text-[11px] font-semibold text-slate-500 leading-tight truncate max-w-full">{{ $item['name'] }} {{ $local }}</span>
                                <span class="text-[10px] text-slate-400 leading-tight" data-shot-status>Menunggu</span>
                            </div>
                            <img class="hidden w-full h-full object-cover" data-shot-img alt="Foto {{ $local }}">
                            <button type="button" data-retake="{{ $gidx }}" class="hidden absolute bottom-1 inset-x-0 mx-auto w-fit text-[10px] font-semibold bg-slate-800/80 text-white backdrop-blur px-2 py-1 rounded-full hover:bg-slate-800 transition">
                                Ulangi
                            </button>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>

        {{-- Kamera (tengah, seimbang dengan rail kiri & kanan) --}}
        <div class="order-1 lg:order-2 flex items-center justify-center min-h-0 min-w-0">
            <div class="relative isolate rounded-3xl overflow-hidden bg-slate-900 shadow-2xl shadow-slate-900/30 w-full max-w-[820px] aspect-square lg:h-full lg:w-auto lg:max-w-full lg:max-h-full ring-1 ring-white/20">
                <video id="camera" class="w-full h-full object-cover hidden" autoplay playsinline muted></video>

                {{-- Panduan bingkai: sudut lembut agar wajah berada di tengah. --}}
                <div class="pointer-events-none absolute inset-3 z-10" aria-hidden="true">
                    <span class="absolute left-0 top-0 h-7 w-7 rounded-tl-xl border-l-2 border-t-2 border-white/55"></span>
                    <span class="absolute right-0 top-0 h-7 w-7 rounded-tr-xl border-r-2 border-t-2 border-white/55"></span>
                    <span class="absolute bottom-0 left-0 h-7 w-7 rounded-bl-xl border-b-2 border-l-2 border-white/55"></span>
                    <span class="absolute bottom-0 right-0 h-7 w-7 rounded-br-xl border-b-2 border-r-2 border-white/55"></span>
                </div>

                <div class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-slate-900/70 text-white hidden" data-camera-loading>
                    <span class="animate-spin w-10 h-10 border-4 border-white/30 border-t-white rounded-full mb-3"></span>
                    <p class="text-sm font-medium">Kamera...</p>
                </div>

                <div class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-slate-900/85 text-white hidden" data-camera-error>
                    <span class="mb-3 text-slate-500"><x-icon name="camera" class="w-12 h-12" /></span>
                    <p class="font-semibold px-6 text-center" data-error-title>Kamera tidak ditemukan</p>
                    <p class="text-sm text-slate-300 mt-1 px-8 text-center">Izinkan akses kamera.</p>
                    <button type="button" data-camera-retry class="mt-3 rounded-full bg-white/15 hover:bg-white/25 ring-1 ring-white/25 px-4 py-2 text-xs font-semibold transition">
                        Coba Lagi
                    </button>
                </div>

                <div class="absolute inset-0 z-20 flex items-center justify-center bg-slate-900/40 hidden" data-countdown-overlay>
                    <span class="font-display font-extrabold text-[6rem] sm:text-[9rem] leading-none text-white drop-shadow-[0_4px_20px_rgba(0,0,0,0.6)] tabular-nums" data-countdown-num>3</span>
                </div>

                <div class="absolute inset-0 z-10 bg-white hidden" data-flash-overlay></div>

                <div class="pointer-events-none absolute inset-x-0 bottom-0 z-10 flex justify-center p-3">
                    <span class="rounded-full bg-slate-900/50 backdrop-blur px-3 py-1 text-[11px] font-medium text-white/85">
                        Posisikan wajah di tengah bingkai
                    </span>
                </div>
            </div>
        </div>

        {{-- Pratinjau frame (kanan) — tinggi penuh agar seimbang dengan rail kiri. --}}
        <div class="order-3 flex items-stretch justify-center w-full lg:w-auto min-h-0">
            <div class="k-card flex w-full flex-col items-center gap-2 p-3 lg:h-full lg:min-h-0">
                <p class="shrink-0 text-[10px] font-bold uppercase tracking-wider text-slate-400">Pratinjau</p>
                <div class="flex flex-1 min-h-0 w-full items-center justify-center">
                    <button type="button" data-preview-open class="w-full rounded-xl overflow-hidden ring-1 ring-slate-200 bg-white hover:ring-rose-300 transition" title="Klik untuk perbesar">
                        <span data-preview-placeholder class="flex aspect-[4/5] w-full flex-col items-center justify-center gap-1.5 bg-slate-50 text-slate-300">
                            <x-icon name="photo" class="w-9 h-9" />
                            <span class="text-[10px] font-semibold text-slate-400">Belum ada foto</span>
                        </span>
                        <canvas data-foto-preview class="hidden w-full rounded-xl shadow-sm bg-white" style="max-height: 100%; width: auto; max-width: 100%;"></canvas>
                    </button>
                </div>
                <p class="shrink-0 text-[10px] text-slate-400 text-center leading-snug" data-foto-preview-hint>Belum ada foto</p>
            </div>
        </div>

        {{-- Modal pratinjau besar --}}
        <div class="fixed inset-0 z-[80] hidden items-center justify-center bg-slate-900/70 backdrop-blur-sm p-4" data-preview-modal>
            <div class="k-card-solid p-4 sm:p-6 max-w-4xl w-full max-h-[90vh] overflow-auto text-center shadow-2xl">
                <div class="flex items-center justify-between gap-3 mb-3">
                    <h3 class="font-display font-bold text-lg">Pratinjau Frame</h3>
                    <button type="button" data-preview-close class="w-9 h-9 rounded-full bg-slate-100 text-slate-600 hover:bg-slate-200 flex items-center justify-center">
                        <x-icon name="x" class="w-5 h-5" />
                    </button>
                </div>
                <canvas data-foto-preview class="hidden mx-auto rounded-xl shadow ring-1 ring-black/10 bg-white" style="max-width: 100%; max-height: 70vh;"></canvas>
                <p class="text-sm text-slate-400 mt-2" data-foto-preview-hint>Belum ada foto</p>
            </div>
        </div>
    </div>

    {{-- Aksi utama: selalu menempel di bawah area foto agar tidak perlu scroll. --}}
    <div class="sticky bottom-0 z-20 flex flex-col items-center gap-2 pt-1 pb-1 shrink-0 w-full">
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 w-full max-w-lg">
            <button type="button" data-shoot-btn class="k-btn k-btn-dark w-full sm:w-auto sm:min-w-[240px] !py-3.5">
                <x-icon name="camera" class="w-5 h-5 text-white" /> Mulai Foto
            </button>

            {{-- Pakai atribut `hidden` (bukan class) karena class display seperti
                 inline-flex menang atas class .hidden pada urutan CSS Tailwind. --}}
            <a href="{{ route('spa', ['step' => 'review']) }}" data-next-btn hidden
               class="k-btn w-full sm:w-auto sm:min-w-[240px] !py-3.5 cursor-pointer">
                Lanjut ke Hasil
                <x-icon name="arrow-right" class="w-5 h-5 text-white" />
            </a>
        </div>

        <p hidden class="text-sm font-medium text-red-600 text-center" data-photo-error aria-live="assertive"></p>
        <div class="flex flex-wrap justify-center gap-2" data-camera-switch></div>
    </div>

    <script>
        (function () {
            const modal = document.querySelector('[data-preview-modal]');
            const closeBtn = document.querySelector('[data-preview-close]');
            const openBtn = document.querySelector('[data-preview-open]');
            if (!modal || !openBtn) return;

            const close = () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.style.overflow = '';
            };

            const open = () => {
                const src = document.querySelector('[data-foto-preview]');
                const dst = modal.querySelector('[data-foto-preview]');
                if (src && dst && !src.classList.contains('hidden')) {
                    dst.width = src.width || dst.width;
                    dst.height = src.height || dst.height;
                    const ctx = dst.getContext('2d');
                    ctx.clearRect(0, 0, dst.width, dst.height);
                    ctx.drawImage(src, 0, 0);
                    dst.classList.remove('hidden');
                } else if (dst) {
                    dst.classList.add('hidden');
                }
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.style.overflow = 'hidden';
            };

            openBtn.addEventListener('click', open);
            closeBtn?.addEventListener('click', close);
            modal.addEventListener('click', (event) => {
                if (event.target === modal) close();
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !modal.classList.contains('hidden')) close();
            });
        })();
    </script>
</div>
