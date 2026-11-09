@php
    $isEdit = isset($voucher);
    $selectedFrames = old('frames', $isEdit ? ($voucher->items ?? []) : []);
    $voucherType = old('type', $isEdit ? $voucher->type : ($type ?? 'cash'));
    $codeValue = old('code', $isEdit ? $voucher->code : '');
    $eventCodeValue = old('event_code', $isEdit ? $voucher->code : '');
    $expiresValue = old('expires_at', $isEdit && $voucher->expires_at ? $voucher->expires_at->format('Y-m-d') : '');
    $eventNameValue = old('event_name', $isEdit ? $voucher->event_name : '');
    $eventDateValue = old('event_date', $isEdit && $voucher->event_date ? $voucher->event_date->format('Y-m-d') : '');

    $peopleValue = old('number_of_people', $isEdit ? $voucher->number_of_people : '');
    $activeValue = old('is_active', $isEdit ? $voucher->is_active : true);
    $totalCostValue = old('total_cost', $isEdit ? $voucher->total_cost : '');
@endphp

<form method="POST" action="{{ $isEdit ? route('admin.vouchers.update', $voucher) : route('admin.vouchers.store') }}" class="space-y-6">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="font-heading text-lg font-bold text-slate-800">Informasi Voucher</h2>
            <p class="text-xs text-slate-400 mt-0.5">Pilih tipe: Cash untuk voucher tunggal, Event untuk acara dengan banyak peserta.</p>
        </div>
        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
            <!-- Tipe Voucher (ditentukan dari menu sidebar) -->
            <input type="hidden" name="type" value="{{ $voucherType }}">
            <div class="sm:col-span-2 flex items-center gap-2">
                <span class="text-sm font-medium text-slate-700">Tipe Voucher:</span>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full {{ $voucherType === 'event' ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700' }} text-xs font-semibold">
                    {{ $voucherType === 'event' ? 'Event' : 'Cash' }}
                </span>
                <p class="text-xs text-slate-400 ml-2">({{ $voucherType === 'event' ? 'banyak kode otomatis per orang' : 'satu kode tunggal' }})</p>
            </div>

            <div>
                <label for="name" class="block text-sm font-medium text-slate-700 mb-1.5">Nama Voucher <span class="text-red-500">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name', $isEdit ? $voucher->name : '') }}" required maxlength="100"
                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-400 @error('name') border-red-300 @enderror"
                       placeholder="Contoh: Voucher Opening Store">
                @error('name')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="code" class="block text-sm font-medium text-slate-700 mb-1.5">Kode Voucher</label>
                <input type="text" id="code" name="code" value="{{ $codeValue }}" maxlength="30"
                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-slate-800 uppercase tracking-wider focus:outline-none focus:ring-2 focus:ring-rose-400 @error('code') border-red-300 @enderror"
                       placeholder="Kosongkan untuk dibuat otomatis">
                <p class="mt-1.5 text-xs text-slate-400">Contoh: PB-OPENING. Kosongkan agar sistem membuat kode acak.</p>
                @error('code')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
                <label for="description" class="block text-sm font-medium text-slate-700 mb-1.5">Keterangan</label>
                <input type="text" id="description" name="description" value="{{ old('description', $isEdit ? $voucher->description : '') }}" maxlength="255"
                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-400 @error('description') border-red-300 @enderror"
                       placeholder="Opsional, misalnya digunakan untuk event tertentu">
                @error('description')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="max_uses" class="block text-sm font-medium text-slate-700 mb-1.5">Batas Pemakaian <span class="text-red-500">*</span></label>
                <input type="number" id="max_uses" name="max_uses" min="1" max="100000" required
                       value="{{ old('max_uses', $isEdit ? $voucher->max_uses : 1) }}"
                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-400 @error('max_uses') border-red-300 @enderror">
                <p class="mt-1.5 text-xs text-slate-400">Berapa kali kode ini boleh dipakai. 1 = sekali pakai.</p>
                @error('max_uses')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="discount" class="block text-sm font-medium text-slate-700 mb-1.5">Diskon (%)</label>
                <input type="number" id="discount" name="discount" min="0" max="100"
                       value="{{ old('discount', $isEdit ? $voucher->discount : 0) }}"
                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-400 @error('discount') border-red-300 @enderror">
                <p class="mt-1.5 text-xs text-slate-400">Diskon persen untuk voucher ini. Harga akhir dibulatkan ke bawah ke ribuan (contoh: 55.500 → 55.000). 0 = tanpa diskon.</p>
                @error('discount')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="expires_at" class="block text-sm font-medium text-slate-700 mb-1.5">Berlaku Sampai</label>
                <input type="date" id="expires_at" name="expires_at" value="{{ $expiresValue }}"
                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-400 @error('expires_at') border-red-300 @enderror">
                <p class="mt-1.5 text-xs text-slate-400">Kosongkan bila voucher berlaku tanpa batas waktu.</p>
                @error('expires_at')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <label class="sm:col-span-2 flex items-center gap-2.5 text-sm text-slate-600 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" @checked($activeValue) class="rounded border-gray-300 text-rose-500 focus:ring-rose-400">
                Aktifkan voucher ini untuk pelanggan
            </label>
        </div>
    </div>

    <!-- Event Fields (hanya muncul saat tipe = event) -->
    <div data-event-fields class="{{ $voucherType === 'event' ? '' : 'hidden' }} bg-blue-50 rounded-2xl shadow-sm border border-blue-100">
        <div class="px-6 py-4 border-b border-blue-100">
            <h2 class="font-heading text-lg font-bold text-blue-800">Pengaturan Event</h2>
            <p class="text-xs text-blue-500 mt-0.5">Atur detail acara dan sistem akan otomatis membuat kode voucher untuk setiap orang.</p>
        </div>
        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label for="event_code" class="block text-sm font-medium text-blue-700 mb-1.5">Kode Dasar Event</label>
                <input type="text" id="event_code" name="event_code" value="{{ $eventCodeValue }}" maxlength="30"
                       class="w-full px-4 py-2.5 bg-gray-50 border border-blue-100 rounded-2xl text-slate-800 uppercase tracking-wider focus:outline-none focus:ring-2 focus:ring-blue-400 @error('event_code') border-red-300 @enderror"
                       placeholder="Contoh: WEDDING">
                <p class="mt-1.5 text-xs text-blue-500">Kode dasar akan digabungkan dengan nomor urut (WEDDING-001, WEDDING-002, dst).</p>
                @error('event_code')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="event_name" class="block text-sm font-medium text-blue-700 mb-1.5">Nama Event <span class="text-blue-600">*</span></label>
                <input type="text" id="event_name" name="event_name" value="{{ $eventNameValue }}" required maxlength="100"
                       class="w-full px-4 py-2.5 bg-gray-50 border border-blue-100 rounded-2xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-400 @error('event_name') border-red-300 @enderror"
                       placeholder="Contoh: Pernikahan Budi & Siti">
                @error('event_name')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="event_date" class="block text-sm font-medium text-blue-700 mb-1.5">Tanggal Event <span class="text-blue-600">*</span></label>
                <input type="date" id="event_date" name="event_date" value="{{ $eventDateValue }}" required
                       class="w-full px-4 py-2.5 bg-gray-50 border border-blue-100 rounded-2xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-400 @error('event_date') border-red-300 @enderror">
                @error('event_date')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>


            <div>
                <label for="number_of_people" class="block text-sm font-medium text-blue-700 mb-1.5">Jumlah Orang <span class="text-blue-600">*</span></label>
                <input type="number" id="number_of_people" name="number_of_people" min="1" max="10000" required
                       value="{{ $peopleValue }}"
                       class="w-full px-4 py-2.5 bg-gray-50 border border-blue-100 rounded-2xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-400 @error('number_of_people') border-red-300 @enderror">
                <p class="mt-1.5 text-xs text-blue-500">Sistem akan otomatis membuat kode voucher sebanyak orang ini.</p>
                @error('number_of_people')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
                <label for="total_cost" class="block text-sm font-medium text-blue-700 mb-1.5">Total Biaya Estimasi (Rp)</label>
                <input type="number" id="total_cost" name="total_cost" min="0" value="{{ $totalCostValue }}"
                       class="w-full px-4 py-2.5 bg-gray-50 border border-blue-100 rounded-2xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-400 @error('total_cost') border-red-300 @enderror">
                <p class="mt-1.5 text-xs text-blue-500">Total biaya seluruh event (dihitung otomatis dari frame × qty × orang).</p>
                @error('total_cost')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    <!-- Frame Selection -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="font-heading text-lg font-bold text-slate-800">Frame yang Didapat</h2>
            <p class="text-xs text-slate-400 mt-0.5">Tentukan frame dan jumlah cetak yang otomatis dipakai pelanggan. Pelanggan tidak perlu memilih frame lagi.</p>
        </div>
        <div class="p-6 space-y-3">
            @error('frames')
                <div class="rounded-2xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">{{ $message }}</div>
            @enderror

            @forelse ($frames as $frame)
                <div class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-gray-50/70 p-3">
                    <div class="w-12 h-14 rounded-lg bg-white ring-1 ring-gray-200 overflow-hidden flex items-center justify-center shrink-0">
                        @if ($frame->image_url)
                            <img src="{{ $frame->image_url }}" alt="{{ $frame->name }}" class="w-full h-full object-contain p-1">
                        @else
                            <x-icon name="photo" class="w-5 h-5 text-slate-300" />
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-slate-800 truncate">{{ $frame->name }}</p>
                        <p class="text-xs text-slate-400">
                            {{ $frame->photo_count }} foto/frame
                            &middot; Rp. {{ number_format($frame->price, 0, ',', '.') }}
                            @if (! $frame->is_active) &middot; <span class="text-amber-600">nonaktif</span>@endif
                        </p>
                    </div>
                    <div class="w-24 shrink-0">
                        <label for="frame-{{ $frame->id }}" class="block text-[10px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Jumlah</label>
                        <input type="number" id="frame-{{ $frame->id }}" name="frames[{{ $frame->slug }}]" min="0" max="50"
                               value="{{ (int) ($selectedFrames[$frame->slug] ?? 0) }}"
                               data-price="{{ $frame->price }}" class="w-full px-3 py-2 text-center rounded-lg bg-white border border-gray-200 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-400 frame-qty">
                    </div>
                </div>
            @empty
                <div class="rounded-2xl bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">
                    Belum ada frame. Tambahkan frame terlebih dahulu di menu Frames.
                </div>
            @endforelse

            <p class="text-xs text-slate-400">Isi 0 untuk frame yang tidak termasuk dalam voucher.</p>
        </div>
    </div>

    <!-- Ringkasan harga -->
    <div class="bg-slate-50 rounded-2xl border border-slate-200 p-4">
        <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Perhitungan Harga</p>
        <div id="price-breakdown" class="space-y-1 text-sm text-slate-600"></div>
        <div class="mt-2 pt-2 border-t border-slate-200 flex items-center justify-between">
            <span class="text-sm font-bold text-slate-800">Total</span>
            <span id="price-total" class="text-sm font-bold" style="color: var(--brand-primary);">Rp. 0</span>
        </div>
        <p class="mt-1 text-[11px] text-slate-400" id="price-note"></p>
    </div>

    <div class="flex items-center justify-between gap-3">
        <a href="{{ route('admin.vouchers.index') }}" class="px-5 py-3 rounded-2xl text-sm font-semibold text-slate-600 hover:bg-gray-100 transition">Kembali</a>
        <button type="submit" class="px-8 py-3 rounded-2xl bg-rose-500 text-white font-semibold shadow-md shadow-rose-200 hover:bg-rose-600 transition">
            {{ $isEdit ? 'Simpan Perubahan' : 'Buat Voucher' }}
        </button>
    </div>
</form>

<script>
    (function () {
        const typeInput = document.querySelector('input[name="type"]');
        const eventFields = document.querySelector('[data-event-fields]');

        function toggle() {
            const isEvent = typeInput && typeInput.value === 'event';
            if (isEvent) {
                eventFields.classList.remove('hidden');
            } else {
                eventFields.classList.add('hidden');
            }
            updatePrice();
        }

        function peopleCount() {
            const el = document.getElementById('number_of_people');
            if (!el) return 1;
            const v = parseInt(el.value || '1', 10);
            return v > 0 ? v : 1;
        }

        function discountPct() {
            const el = document.getElementById('discount');
            if (!el) return 0;
            const v = parseInt(el.value || '0', 10);
            return Math.min(100, Math.max(0, isNaN(v) ? 0 : v));
        }

        function floor1000(n) {
            if (n >= 1000) return Math.floor(n / 1000) * 1000;
            return Math.floor(n);
        }

        function updatePrice() {
            const isEvent = typeInput && typeInput.value === 'event';
            const people = isEvent ? peopleCount() : 1;
            const discount = discountPct();

            const breakdown = document.getElementById('price-breakdown');
            const totalEl = document.getElementById('price-total');
            const noteEl = document.getElementById('price-note');

            let subtotal = 0;
            let html = '';

            document.querySelectorAll('.frame-qty').forEach(function (input) {
                const qty = parseInt(input.value || '0', 10);
                if (qty <= 0) return;
                const price = parseInt(input.getAttribute('data-price') || '0', 10);
                const line = price * qty;
                subtotal += line;
                let name = 'Frame';
                const row = input.closest('.flex');
                if (row) {
                    const p = row.querySelector('p.text-sm.font-semibold');
                    if (p) name = p.textContent.trim();
                }
                html += '<div class="flex justify-between"><span>' + name + ' x' + qty + '</span><span>Rp. ' + line.toLocaleString('id-ID') + '</span></div>';
            });

            const originalTotal = subtotal * people;
            let finalTotal = originalTotal;
            if (discount > 0) {
                finalTotal = floor1000(Math.floor(originalTotal * (1 - discount / 100)));
            }

            if (breakdown) breakdown.innerHTML = html || '<span class="text-slate-400">Belum ada frame dipilih.</span>';
            if (totalEl) totalEl.textContent = 'Rp. ' + finalTotal.toLocaleString('id-ID');
            if (noteEl) {
                if (isEvent && people > 1) {
                    noteEl.textContent = 'Subtotal per orang: Rp. ' + subtotal.toLocaleString('id-ID') + ' x ' + people + ' orang' + (discount > 0 ? ' · diskon ' + discount + '%' : '');
                } else {
                    noteEl.textContent = discount > 0 ? 'Harga asli Rp. ' + originalTotal.toLocaleString('id-ID') + ' · diskon ' + discount + '%' : '';
                }
            }
        }

        document.querySelectorAll('.frame-qty').forEach(function (input) {
            input.addEventListener('input', updatePrice);
        });
        const peopleEl = document.getElementById('number_of_people');
        if (peopleEl) peopleEl.addEventListener('input', updatePrice);
        const discountEl = document.getElementById('discount');
        if (discountEl) discountEl.addEventListener('input', updatePrice);

        toggle();
        updatePrice();
    })();
</script>
