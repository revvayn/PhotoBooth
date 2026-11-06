<div class="flex-1 min-h-0 flex flex-col items-center">
    <div class="text-center mb-3">
        <h2 class="font-display font-bold text-2xl">Fotomu Sudah Jadi!</h2>
        <p class="text-slate-500 text-sm mt-0.5">Cetak, unduh, atau kirim hasilnya ke email</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-[1fr_340px] gap-5 w-full max-w-4xl items-start">
        <div class="flex flex-wrap justify-center gap-4 w-full">
            @forelse ($items as $item)
                <div class="w-[200px] overflow-hidden rounded-2xl shadow-xl shadow-rose-100 ring-1 ring-rose-100 bg-white" data-strip-container data-qty="{{ $item['qty'] }}">
                    <canvas class="w-full h-auto block" data-strip-canvas
                            data-photos='@json($item['photos'] ?? [])'
                            data-filters='@json($item['filter_map'] ?? [])'
                            data-filter="{{ $filter }}"
                            data-frame="{{ $item['slug'] }}"
                            data-frame-image="{{ $item['image_url'] }}"
                            data-frame-slots='@json($item['slots'] ?? [])'></canvas>
                    <div class="px-3 py-2 flex items-center justify-between text-xs text-slate-500 border-t border-rose-100">
                        <span class="font-semibold text-slate-700 truncate">{{ $item['name'] }}</span>
                        @if ($item['qty'] > 1)
                            <span class="font-bold text-rose-600 shrink-0 ml-2">{{ $item['qty'] }} lembar</span>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-center text-slate-400">Belum ada frame yang dipilih.</p>
            @endforelse
        </div>

        <div class="flex flex-col gap-3 md:sticky md:top-2">
            <div class="rounded-2xl bg-white/90 backdrop-blur ring-1 ring-rose-100 p-4">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Ringkasan Pesanan</p>
                <div class="text-sm text-slate-600 space-y-1">
                    <p>Antrian: <span class="font-bold text-rose-500">{{ $queue }}</span></p>
                    @foreach ($items as $item)
                        <p class="flex justify-between gap-2">
                            <span>{{ $item['name'] }}@if ($item['qty'] > 1) x{{ $item['qty'] }}@endif</span>
                            <span class="tabular-nums">Rp. {{ number_format($item['price'] * $item['qty'], 0, ',', '.') }}</span>
                        </p>
                    @endforeach
                    <p class="flex justify-between pt-1.5 mt-1.5 border-t border-rose-100">
                        <span class="font-bold text-slate-800">Total</span>
                        <span class="font-bold text-rose-600 tabular-nums">Rp. {{ number_format($total, 0, ',', '.') }}</span>
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <button type="button" data-print-btn
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-rose-500 to-violet-600 text-white font-display font-bold py-3.5 shadow-lg shadow-rose-200 hover:scale-[1.02] active:scale-95 transition-transform">
                    <x-icon name="printer" class="w-5 h-5 text-white" /> Cetak
                </button>
                <button type="button" data-strip-download
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white ring-1 ring-rose-200 text-rose-600 font-display font-bold py-3.5 hover:bg-rose-50 active:scale-95 transition">
                    <x-icon name="photo" class="w-5 h-5" /> Unduh
                </button>
            </div>

            <form method="POST" action="{{ route('email.send') }}" data-email-form class="rounded-2xl bg-white/90 backdrop-blur ring-1 ring-rose-100 p-4 space-y-2.5">
                @csrf
                <label for="email-input" class="block text-sm font-semibold text-slate-700">Kirim ke Email</label>
                <input type="email" name="email" id="email-input" placeholder="email@contoh.com" required autocomplete="email"
                       class="w-full rounded-2xl bg-slate-50 ring-1 ring-slate-200 px-4 py-3 focus:ring-2 focus:ring-rose-400 focus:outline-none">
                <p class="hidden text-sm text-red-600" data-email-error></p>
                <button type="submit" class="w-full rounded-full bg-slate-800 text-white font-display font-bold py-3 shadow-md hover:bg-slate-900 active:scale-95 transition">
                    <x-icon name="envelope" class="w-5 h-5 inline-block align-middle" /> Kirim Softfile
                </button>
            </form>

            <a href="{{ route('selesai') }}" class="rounded-full bg-white ring-1 ring-rose-200 text-rose-600 font-semibold py-3 text-center hover:bg-rose-50 transition">
                Selesai, tanpa kirim email
            </a>
        </div>
    </div>

    <div class="fixed inset-0 z-[80] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm" data-printing-modal>
        <div class="bg-white rounded-3xl p-10 text-center max-w-sm mx-4 shadow-2xl">
            <div class="relative w-16 h-16 mx-auto mb-4">
                <div class="animate-spin w-16 h-16 rounded-full border-4 border-rose-100 border-t-rose-500"></div>
                <span class="absolute inset-0 flex items-center justify-center text-slate-600"><x-icon name="printer" class="w-8 h-8" /></span>
            </div>
            <h2 class="font-display font-bold text-xl mb-1" data-modal-title>Mohon tunggu</h2>
            <p class="text-sm text-slate-500" data-modal-text>Sebentar ya...</p>
        </div>
    </div>
</div>
