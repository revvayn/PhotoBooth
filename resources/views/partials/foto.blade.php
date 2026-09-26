<div class="flex-1 min-h-0 flex flex-col">
    <div class="mb-1">
        <a href="{{ route('spa', ['step' => 'frame']) }}" class="text-xs font-semibold text-slate-400 hover:text-rose-500 transition">← Kembali</a>
    </div>
    <div class="pb-2 flex items-center justify-between">
        <h2 class="font-display font-bold text-xl">Sesi Foto</h2>
        <span class="text-xs text-slate-500">Ambil <strong>{{ $photoCount }} foto</strong> untuk {{ count($items) }} frame</span>
    </div>

    @if (count($items) > 1)
        <div class="flex flex-wrap gap-2 mb-2" data-frame-tabs role="tablist" aria-label="Pilih frame">
            @foreach ($items as $index => $item)
                <button type="button" role="tab" data-frame-tab="{{ $index }}" data-frame-photo-count="{{ $item['photo_count'] }}"
                        class="px-3 py-1.5 rounded-full text-xs font-semibold ring-1 ring-rose-200 transition-all {{ $index === 0 ? 'bg-rose-500 text-white ring-rose-500 shadow-md shadow-rose-200' : 'text-rose-600 bg-white/80' }}">
                    {{ $item['name'] }}@if ($item['qty'] > 1) <span class="opacity-80">x{{ $item['qty'] }}</span>@endif
                    <span class="ml-1 text-[10px] font-bold opacity-90" data-tab-status>{{ $item['photo_count'] }} foto</span>
                </button>
            @endforeach
        </div>
    @endif

    <div class="flex-1 min-h-0 flex flex-col sm:flex-row gap-2 items-center sm:items-start sm:justify-center">
    <div class="order-2 sm:order-1 flex sm:flex-col gap-2 overflow-x-auto sm:overflow-y-auto sm:overflow-x-hidden shrink-0 w-full sm:w-24 min-h-0 sm:max-h-[calc(100dvh_-_260px)] pb-1 sm:pb-0" data-shot-thumbs data-photo-count="{{ $photoCount }}">
        @php($gi = 0)
        @foreach ($items as $index => $item)
            <div class="flex gap-2 sm:flex-col sm:w-full shrink-0 {{ count($items) > 1 && $index !== 0 ? 'hidden' : '' }}" data-frame-slots="{{ $index }}">
                @foreach (range(1, $item['photo_count']) as $local)
                    @php($gidx = $gi++)
                    <div class="relative rounded-xl overflow-hidden border border-rose-200 h-16 w-16 sm:h-auto sm:w-full sm:aspect-square bg-white shadow-sm shrink-0" data-shot-item="{{ $gidx }}">
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                            <span class="text-xl leading-none text-rose-300" data-shot-icon><x-icon name="film" class="w-6 h-6" /></span>
                            <span class="text-[10px] font-semibold text-slate-500 leading-tight">{{ $item['name'] }} {{ $local }}</span>
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

    <div class="order-1 sm:order-2 relative rounded-[2rem] overflow-hidden bg-slate-900 shadow-2xl shrink-0 max-w-full w-[min(100%,calc(100dvh_-_260px),720px)] aspect-square min-h-0">
        <video id="camera" class="w-full h-full object-cover" autoplay playsinline muted></video>

        <div class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-slate-900/70 text-white hidden" data-camera-loading>
            <span class="animate-spin w-10 h-10 border-4 border-white/30 border-t-white rounded-full mb-3"></span>
            <p class="text-sm font-medium">Mempersiapkan kamera...</p>
        </div>

        <div class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-slate-900/85 text-white hidden" data-camera-error>
            <span class="mb-3 text-slate-500"><x-icon name="camera" class="w-12 h-12" /></span>
            <p class="font-semibold">Kamera tidak ditemukan</p>
            <p class="text-sm text-slate-300 mt-1 px-8 text-center">Izinkan akses kamera atau pastikan perangkatmu memiliki webcam.</p>
        </div>

        <div class="absolute inset-0 z-10 flex items-center justify-center hidden" data-countdown>
            <span class="font-display font-extrabold text-[9rem] sm:text-[12rem] leading-none text-white drop-shadow-[0_4px_20px_rgba(0,0,0,0.6)] animate-bounce" data-countdown-num>3</span>
        </div>

        <div class="absolute inset-0 z-10 bg-white hidden" data-flash-overlay></div>

        <div class="absolute inset-x-0 bottom-0 p-3 flex flex-col items-center gap-2">
            <button type="button" data-shoot-btn class="px-10 py-3 rounded-full bg-white text-slate-900 font-display font-bold text-lg shadow-xl hover:scale-105 transition-transform">
                <x-icon name="camera" class="w-5 h-5 inline-block align-middle" /> Mulai Foto
            </button>

            <a href="{{ route('spa', ['step' => 'filter']) }}" data-next-btn class="hidden w-full max-w-sm text-center rounded-full bg-gradient-to-r from-rose-500 to-violet-600 text-white font-display font-bold px-6 py-3 shadow-lg hover:scale-105 transition-transform">
                Lanjut
            </a>
        </div>
    </div>
    </div>
    <div class="flex flex-wrap justify-center gap-2 pt-1 shrink-0" data-camera-switch></div>
</div>
