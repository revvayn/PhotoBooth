<div class="flex-1 flex flex-col items-center">
    <div class="text-center mb-6">
        <h2 class="font-display font-bold text-3xl">Review Foto</h2>
        <p class="text-slate-500 text-sm mt-1">Berikut hasil frame yang sudah dibuat</p>
    </div>

    <div class="space-y-6 w-full max-w-sm">
        @forelse ($items as $item)
            <div class="mx-auto max-w-sm overflow-hidden rounded-[2rem] shadow-xl shadow-rose-100 ring-1 ring-rose-100 bg-white" data-strip-container>
                <canvas class="w-full h-auto block" data-strip-canvas
                        data-photos='@json($item['photos'] ?? [])'
                        data-filter="{{ $filter }}"
                        data-frame="{{ $item['slug'] }}"
                        data-frame-image="{{ $item['image_url'] }}"
                        data-frame-slots='@json($item['slots'] ?? [])'></canvas>
                <div class="px-5 py-3 text-center text-xs font-semibold text-slate-500 border-t border-rose-100">
                    {{ $item['name'] }}@if ($item['qty'] > 1) x{{ $item['qty'] }}@endif
                </div>
            </div>
        @empty
            <p class="text-center text-slate-400">Belum ada frame yang dipilih.</p>
        @endforelse
    </div>

    @if ($email)
        <p class="text-sm text-slate-500 mt-4">Softfile telah dikirim ke <span class="font-semibold text-slate-700">{{ $email }}</span></p>
    @endif

    <div class="mt-8 flex flex-col sm:flex-row gap-3 w-full max-w-sm">
        <a href="{{ route('softfile.download') }}" data-strip-download class="flex-1 text-center rounded-full bg-gradient-to-r from-rose-500 to-violet-600 text-white font-display font-bold px-8 py-4 shadow-lg shadow-rose-200 hover:scale-105 transition-transform">
            Download Softfile
        </a>

        <form method="POST" action="{{ route('selesai.store') }}" class="flex-1">
            @csrf
            <button type="submit" class="w-full rounded-full bg-slate-800 text-white font-display font-bold px-8 py-4 shadow-md hover:scale-105 transition-transform">
                Selesai
            </button>
        </form>
    </div>
</div>
