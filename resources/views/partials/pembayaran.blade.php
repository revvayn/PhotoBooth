<div class="flex-1 min-h-0 flex flex-col">
    @php($brand = $brand ?? \App\Support\Brand::get())
    <div class="k-step-head mb-3 shrink-0">
        <a href="{{ ($fromSpa ?? true) ? route('spa', ['step' => 'frame']) : route('frame') }}" data-back-btn aria-label="Ubah pilihan frame"
           class="k-btn k-btn-ghost justify-self-start">
            <x-icon name="arrow-left" class="w-4 h-4" />
            <span class="hidden sm:inline">Ubah frame</span>
        </a>
        <div class="k-step-head-title">
            <h2 class="k-title">
                <span class="k-icon-soft w-9 h-9 rounded-xl">
                    <x-icon name="qr" class="w-4 h-4" />
                </span>
                {{ $brand['payment_title'] }}
            </h2>
            <p class="text-slate-500 text-xs sm:text-sm">{{ $brand['payment_subtitle'] }}</p>
        </div>
        <span class="k-pill justify-self-end !text-xs">
            <x-icon name="card" class="w-4 h-4 text-rose-500" />
            QRIS
        </span>
    </div>

    <div class="flex-1 min-h-0 flex items-center justify-center">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full max-w-4xl items-stretch">

            {{-- Kartu QR --}}
            <div class="k-card flex flex-col items-center justify-center p-5 sm:p-6 text-center">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400 mb-3">Scan QRIS</p>

                @if (!empty($qris_image))
                    <div class="w-44 h-44 sm:w-52 sm:h-52 flex items-center justify-center rounded-2xl bg-white p-2 ring-1 ring-black/5">
                        <img src="{{ asset('storage/' . $qris_image) }}" alt="QRIS" class="w-full h-full object-contain">
                    </div>
                @else
                    <div class="w-44 h-44 sm:w-52 sm:h-52 bg-white rounded-2xl ring-1 ring-black/5 flex items-center justify-center" id="qrcode" data-nmid="{{ $nmid }}" data-merchant="{{ $merchant }}" data-queue="{{ $queue }}"></div>
                @endif

                <p class="mt-3 text-[11px] font-medium text-slate-400">{{ $merchant ?: 'QRIS' }}</p>
                <p class="mt-1 text-3xl sm:text-4xl font-display font-extrabold tracking-tight text-slate-900 tabular-nums" data-qris-price>Rp {{ number_format($total, 0, ',', '.') }}</p>
            </div>

            {{-- Ringkasan & konfirmasi --}}
            <div class="k-card flex flex-col p-5 sm:p-6">
                <div class="flex items-center justify-between gap-2 mb-4">
                    <h3 class="font-display font-bold text-base text-slate-900">Ringkasan Pembayaran</h3>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-bold text-amber-700 ring-1 ring-amber-100">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                        Menunggu
                    </span>
                </div>

                <div class="space-y-2.5 text-sm">
                    <p class="flex items-center justify-between gap-3">
                        <span class="text-slate-500">Nomor antrian</span>
                        <span class="font-display font-extrabold text-rose-500">{{ $queue }}</span>
                    </p>
                    <p class="flex items-center justify-between gap-3">
                        <span class="text-slate-500">Metode</span>
                        <span class="font-semibold text-slate-800">QRIS</span>
                    </p>
                </div>
                @if (!empty($items))
                    {{-- Daftar item bisa discroll bila frame yang dipilih banyak. --}}
                    <div class="mt-2.5 max-h-28 overflow-y-auto pr-1 space-y-2 text-xs">
                        @foreach ($items as $item)
                            <p class="flex items-center justify-between gap-3">
                                <span class="text-slate-500 truncate">
                                    {{ $item['name'] }}@if (($item['qty'] ?? 1) > 1) x{{ $item['qty'] }}@endif
                                    @if (!empty($item['size_name']))
                                        <span class="text-slate-400">· {{ $item['size_name'] }}</span>
                                    @endif
                                </span>
                                <span class="font-semibold text-slate-700 tabular-nums shrink-0">Rp {{ number_format(($item['price'] ?? 0) * ($item['qty'] ?? 1), 0, ',', '.') }}</span>
                            </p>
                        @endforeach
                    </div>
                @endif
                <p class="mt-2.5 flex items-center justify-between gap-3 pt-2.5 border-t border-dashed border-slate-200">
                    <span class="font-semibold text-slate-700">Total</span>
                    <span class="font-display font-extrabold text-xl text-slate-900 tabular-nums">Rp {{ number_format($total, 0, ',', '.') }}</span>
                </p>

                <div class="k-inset mt-4 px-3.5 py-3 text-xs text-slate-500 leading-relaxed">
                    {{ $brand['payment_note'] }}
                </div>

                <form method="POST" action="{{ route('pembayaran') }}" data-pay-form class="mt-4 space-y-3">
                    @csrf
                    <input type="hidden" name="from_spa" value="{{ ($fromSpa ?? true) ? '1' : '0' }}">

                    @if (!empty($needs_pin))
                        <div>
                            <label for="pin-input" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                PIN Kasir <span class="font-normal text-slate-400">&mdash; diisi kasir setelah pembayaran diterima</span>
                            </label>
                            <input type="password" id="pin-input" name="pin" inputmode="numeric" pattern="[0-9]*" autocomplete="off" maxlength="8" required autofocus
                                   class="k-field text-center tracking-[0.5em] text-xl font-bold @error('pin') !border-red-400 @enderror"
                                   placeholder="&bull;&bull;&bull;&bull;">
                            @error('pin')
                                <p class="mt-1.5 text-sm text-red-600 text-center">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <button type="submit" data-pay-submit class="k-btn k-btn-success w-full">
                        {{ !empty($needs_pin) ? 'Konfirmasi Pembayaran' : 'Sudah Bayar, Lanjut Foto' }}
                        <x-icon name="arrow-right" class="w-5 h-5 text-white" />
                    </button>
                </form>

                <p class="mt-auto pt-3 text-center text-[11px] text-slate-400">
                    Pembayaran diverifikasi oleh kasir sebelum sesi foto dimulai.
                </p>
            </div>
        </div>
    </div>
</div>
