<div class="flex-1 min-h-0 flex flex-col gap-3">
    @php($brand = $brand ?? \App\Support\Brand::get())
    {{-- Header --}}
    <div class="shrink-0 grid grid-cols-[1fr_auto_1fr] items-center gap-3">
        <div class="justify-self-start">
            @if (($metode ?? '') === 'voucher')
                <span class="k-pill !text-emerald-700 !bg-emerald-50">
                    <x-icon name="check" class="w-4 h-4" /> Voucher {{ $voucher['code'] ?? '' }}
                </span>
            @else
                <a href="{{ ($fromSpa ?? true) ? route('spa', ['step' => 'frame']) : route('frame') }}" data-back-btn aria-label="Ganti frame"
                   class="k-btn k-btn-ghost !px-3.5 !text-xs">
                    <x-icon name="arrow-left" class="w-4 h-4" />
                    <span class="hidden sm:inline">Ganti Frame</span>
                </a>
            @endif
        </div>

        <div class="text-center">
            <h2 class="k-title">{{ $brand['photo_title'] }}</h2>
            <p class="text-sm text-slate-500 mt-1">Ambil <strong>{{ $photoCount }} foto</strong> untuk {{ count($items) }} frame</p>
        </div>

        <span class="k-pill justify-self-end tabular-nums" data-photo-progress-wrap>
            <x-icon name="camera" class="w-4 h-4 text-rose-500" />
            <span data-photo-progress>0/{{ $photoCount }}</span>
        </span>
    </div>

    @if (count($items) > 1)
        <div class="shrink-0 flex flex-wrap justify-center gap-1.5 sm:gap-2" data-frame-tabs role="tablist" aria-label="Pilih frame">
            @foreach ($items as $index => $item)
                <button type="button" role="tab" data-frame-tab="{{ $index }}" data-frame-photo-count="{{ $item['photo_count'] }}"
                        aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                        class="px-3 py-1.5 rounded-full text-xs font-semibold ring-1 transition-all {{ $index === 0 ? 'bg-rose-500 text-white ring-rose-500 shadow-md shadow-rose-200' : 'text-rose-600 bg-white/80 ring-rose-200' }}">
                    {{ $item['name'] }}@if ($item['qty'] > 1) <span class="opacity-80">x{{ $item['qty'] }}</span>@endif
                    <span class="ml-1 text-xs font-bold opacity-90" data-tab-status>{{ $item['photo_count'] }} foto</span>
                </button>
            @endforeach
        </div>
    @endif

    <div class="flex-1 min-h-0 grid grid-cols-1 lg:grid-cols-[192px_minmax(0,1fr)_192px] gap-3 items-stretch">
        {{-- Thumbnail slot (kiri) --}}
        <div class="order-2 lg:order-1 flex lg:flex-col gap-2 overflow-x-auto lg:overflow-y-auto shrink-0 w-full lg:w-auto min-h-0 pb-1 lg:pb-0" data-shot-thumbs data-photo-count="{{ $photoCount }}">
            @php($gi = 0)
            @foreach ($items as $index => $item)
                <div class="flex gap-2 flex-1 min-w-0 lg:flex-col lg:w-full lg:flex-1 lg:min-h-0 shrink-0 {{ count($items) > 1 && $index !== 0 ? 'hidden' : '' }}" data-frame-slots="{{ $index }}" data-frame-image="{{ $item['image_url'] }}" data-frame-slots-json='@json($item['slots'] ?? [])'>
                    @foreach (range(1, $item['photo_count']) as $local)
                        @php($gidx = $gi++)
                        <div class="relative rounded-xl overflow-hidden ring-1 ring-black/10 bg-white shadow-sm h-16 flex-1 min-w-0 lg:h-auto lg:w-full lg:flex-1 lg:min-h-0" data-shot-item="{{ $gidx }}">
                            <div class="absolute inset-0 flex flex-col items-center justify-center text-center px-1">
                                <span class="text-xl leading-none text-rose-300" data-shot-icon><x-icon name="film" class="w-6 h-6" /></span>
                                <span class="text-[10px] font-semibold text-slate-500 leading-tight truncate max-w-full">{{ $item['name'] }} {{ $local }}</span>
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
            <div class="relative isolate rounded-3xl overflow-hidden bg-slate-900 shadow-2xl shadow-slate-900/30 w-full max-w-[560px] aspect-square lg:h-full lg:w-auto lg:max-w-full lg:max-h-full ring-1 ring-white/20">
                <video id="camera" class="w-full h-full object-cover hidden" autoplay playsinline muted></video>

                <div class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-slate-900/70 text-white hidden" data-camera-loading>
                    <span class="animate-spin w-10 h-10 border-4 border-white/30 border-t-white rounded-full mb-3"></span>
                    <p class="text-sm font-medium">Menyiapkan kamera…</p>
                </div>

                <div class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-slate-900/85 text-white hidden" data-camera-error>
                    <span class="mb-3 text-slate-500"><x-icon name="camera" class="w-12 h-12" /></span>
                    <p class="font-semibold px-6 text-center" data-error-title>Kamera tidak ditemukan</p>
                    <p class="text-sm text-slate-300 mt-1 px-8 text-center">Izinkan akses kamera atau pastikan perangkatmu memiliki webcam.</p>
                    <button type="button" data-camera-retry class="mt-3 rounded-full bg-white/15 hover:bg-white/25 ring-1 ring-white/25 px-4 py-2 text-xs font-semibold transition">
                        Coba Lagi
                    </button>
                </div>

                <div class="absolute inset-0 z-20 flex items-center justify-center bg-slate-900/40 hidden" data-countdown-overlay>
                    <span class="font-display font-extrabold text-[6rem] sm:text-[9rem] leading-none text-white drop-shadow-[0_4px_20px_rgba(0,0,0,0.6)] tabular-nums" data-countdown-num>3</span>
                </div>

                <div class="absolute inset-0 z-10 bg-white hidden" data-flash-overlay></div>

                <div class="pointer-events-none absolute inset-x-0 top-0 z-10 flex justify-center p-3">
                    <span class="rounded-full bg-slate-900/45 backdrop-blur px-3 py-1 text-[11px] sm:text-xs font-medium text-white/90">
                        {{ $brand['photo_hint'] }}
                    </span>
                </div>

                <div class="absolute inset-x-0 bottom-0 p-3 flex flex-col items-center gap-2">
                    <button type="button" data-shoot-btn class="px-9 sm:px-12 py-3.5 rounded-full bg-white text-slate-900 font-display font-extrabold text-lg shadow-2xl hover:scale-105 active:scale-95 transition-transform disabled:opacity-50 focus:outline-none focus-visible:ring-4 focus-visible:ring-white/50">
                        <x-icon name="camera" class="w-5 h-5 inline-block align-middle" /> Mulai Foto
                    </button>

                    <a href="{{ route('spa', ['step' => 'review']) }}" data-next-btn class="k-btn hidden w-full max-w-sm">
                        Lanjut ke Hasil Foto
                        <x-icon name="arrow-right" class="w-5 h-5 text-white" />
                    </a>
                </div>
            </div>
        </div>

        {{-- Pratinjau frame (kanan, lebar sama dengan rail kiri) --}}
        <div class="order-3 flex items-center justify-center w-full lg:w-auto min-h-0">
            <div class="k-card flex w-full flex-col items-center gap-2 p-3">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Pratinjau</p>
                <canvas data-foto-preview class="hidden w-full rounded-xl shadow-sm ring-1 ring-rose-100 bg-white" style="max-height: 420px; width: auto; max-width: 100%;"></canvas>
                <p class="text-[10px] text-slate-400 text-center" data-foto-preview-hint>Belum ada foto</p>
            </div>
        </div>
    </div>

    <div class="flex flex-col items-center gap-1.5 pt-1 shrink-0">
        <p class="hidden text-sm font-medium text-red-600 text-center" data-photo-error aria-live="assertive"></p>
        <div class="flex flex-wrap justify-center gap-2" data-camera-switch></div>
    </div>
</div>