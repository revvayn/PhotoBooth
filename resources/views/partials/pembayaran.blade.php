<div class="flex-1 min-h-0 flex flex-col">
    @php($brand = $brand ?? \App\Support\Brand::get())
    <div class="grid grid-cols-[1fr_auto_1fr] items-start gap-3 mb-2 shrink-0">
        <a href="{{ ($fromSpa ?? true) ? route('spa', ['step' => 'frame']) : route('frame') }}" data-back-btn aria-label="Ubah pilihan frame"
           class="k-btn k-btn-ghost justify-self-start !px-3.5 !text-xs">
            <x-icon name="arrow-left" class="w-4 h-4" />
            <span class="hidden sm:inline">Ubah frame</span>
        </a>
        <div class="text-center">

            <h2 class="k-title">{{ $brand['payment_title'] }}</h2>
        </div>
        <span class="k-pill justify-self-end !text-xs">
            <x-icon name="card" class="w-4 h-4 text-rose-500" />
            QRIS
        </span>
    </div>


    <div class="flex flex-col items-center justify-center flex-1 min-h-0">
        <div class="k-card w-full max-w-sm p-5 sm:p-6 text-center shadow-2xl shadow-rose-100/60">
            <div class="w-10 h-10 mx-auto rounded-2xl bg-rose-500 text-white flex items-center justify-center mb-2">
                <x-icon name="card" class="w-5 h-5 text-white" />
            </div>
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Scan QRIS</p>

            @if (!empty($qris_image))
                <div class="w-44 h-44 sm:w-52 sm:h-52 mx-auto flex items-center justify-center mb-3 rounded-2xl bg-white p-2 ring-1 ring-black/5">
                    <img src="{{ asset('storage/' . $qris_image) }}" alt="QRIS" class="w-full h-full object-contain">
                </div>
            @else
                <div class="w-44 h-44 sm:w-52 sm:h-52 mx-auto bg-white rounded-2xl ring-1 ring-black/5 flex items-center justify-center mb-3" id="qrcode" data-nmid="{{ $nmid }}" data-merchant="{{ $merchant }}" data-queue="{{ $queue }}"></div>
            @endif

            <p class="text-3xl sm:text-4xl font-display font-extrabold tracking-tight text-slate-900 tabular-nums" data-qris-price>Rp. {{ number_format($total, 0, ',', '.') }}</p>

            <p class="text-sm text-slate-400 mt-1.5">Antrian <span class="font-bold text-rose-500">{{ $queue }}</span></p>

        </div>

        <form method="POST" action="{{ route('pembayaran') }}" data-pay-form class="mt-4 w-full max-w-sm space-y-3">
            @csrf
            <input type="hidden" name="from_spa" value="{{ ($fromSpa ?? true) ? '1' : '0' }}">

            @if (!empty($needs_pin))
                <div>
                    <label for="pin-input" class="block text-sm font-semibold text-slate-700 text-center mb-1.5">
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
    </div>
</div>
