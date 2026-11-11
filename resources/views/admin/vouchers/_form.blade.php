@php
    $isEdit = isset($voucher);
    $selectedFrames = old('frames', $isEdit ? ($voucher->items ?? []) : []);
    $voucherType = old('type', $isEdit ? $voucher->type : ($type ?? 'cash'));
    $isEvent = $voucherType === 'event';

    $codeValue = old('code', $isEdit && ! $isEvent ? $voucher->code : '');
    $eventCodeValue = old('event_code', $isEdit && $isEvent ? $voucher->code : '');
    $expiresValue = old('expires_at', $isEdit && $voucher->expires_at ? $voucher->expires_at->format('Y-m-d') : '');
    $eventNameValue = old('event_name', $isEdit ? $voucher->event_name : '');
    $eventDateValue = old('event_date', $isEdit && $voucher->event_date ? $voucher->event_date->format('Y-m-d') : '');
    $peopleValue = old('number_of_people', $isEdit ? $voucher->number_of_people : '');
    $pricingModeValue = old('pricing_mode', $isEdit ? ($voucher->pricing_mode ?: 'people') : 'people');
    $startTimeValue = old('start_time', $isEdit ? $voucher->start_time : '');
    $endTimeValue = old('end_time', $isEdit ? $voucher->end_time : '');
    $activeValue = old('is_active', $isEdit ? $voucher->is_active : true);
    $totalCostValue = old('total_cost', $isEdit ? $voucher->total_cost : '');
    $maxUsesValue = old('max_uses', $isEdit ? $voucher->max_uses : 1);

    $frameStep = $isEvent ? 3 : 2;
    $summaryStep = $isEvent ? 4 : 3;
    $steps = $isEvent
        ? [1 => 'Informasi', 2 => 'Event', 3 => 'Frame', 4 => 'Ringkasan']
        : [1 => 'Informasi', 2 => 'Frame', 3 => 'Ringkasan'];

    $initialStep = 1;
    if ($isEvent && ($errors->has('event_code') || $errors->has('event_name') || $errors->has('event_date') || $errors->has('number_of_people') || $errors->has('total_cost'))) {
        $initialStep = 2;
    }
    if ($errors->has('frames')) {
        $initialStep = $frameStep;
    }
@endphp

<form method="POST" action="{{ $isEdit ? route('admin.vouchers.update', $voucher) : route('admin.vouchers.store') }}"
      class="space-y-6" data-voucher-form data-initial-step="{{ $initialStep }}" novalidate>
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="grid items-start gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
        {{-- Langkah pembuatan --}}
        <aside class="lg:sticky lg:top-24 space-y-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Alur pembuatan</p>
                <ol class="mt-3 grid grid-cols-2 gap-2 lg:grid-cols-1">
                    @foreach ($steps as $number => $label)
                        <li>
                            <button type="button" data-step-target="{{ $number }}"
                                    class="voucher-step group flex w-full items-start gap-3 rounded-xl border border-transparent px-3 py-2.5 text-left text-slate-500 transition hover:bg-slate-50">
                                <span data-step-dot class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-[11px] font-bold text-slate-500 transition">{{ $number }}</span>
                                <span class="min-w-0">
                                    <span class="block text-sm font-semibold">{{ $label }}</span>
                                    <span class="block text-[11px] text-slate-400">
                                        {{ $label === 'Informasi' ? 'Nama & masa berlaku' : ($label === 'Event' ? 'Peserta & kode' : ($label === 'Frame' ? 'Isi voucher' : 'Cek & simpan')) }}
                                    </span>
                                </span>
                            </button>
                        </li>
                    @endforeach
                </ol>
            </div>

            <div class="rounded-2xl border border-slate-800 bg-slate-900 p-4 text-white shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Tipe voucher</p>
                <div class="mt-2 flex items-center gap-2">
                    <span class="inline-flex rounded-full {{ $isEvent ? 'bg-sky-400/15 text-sky-200' : 'bg-emerald-400/15 text-emerald-200' }} px-2.5 py-1 text-xs font-bold">
                        {{ $isEvent ? 'Event' : 'Cash' }}
                    </span>
                    <span class="text-xs text-slate-400">{{ $isEvent ? 'Kode otomatis per orang' : 'Satu kode tunggal' }}</span>
                </div>
                <p class="mt-3 text-[11px] leading-relaxed text-slate-400">
                    {{ $isEvent ? 'Isi frame dan jumlah orang, sistem membuat kode untuk setiap peserta.' : 'Satu kode untuk satu pelanggan dengan frame yang sudah ditentukan.' }}
                </p>
            </div>
        </aside>

        <div class="min-w-0 space-y-5">
            {{-- Langkah 1: informasi --}}
            <section data-step-panel="1" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5">
                    <div class="flex items-start gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-sm font-bold text-rose-600">1</span>
                        <div>
                            <h2 class="font-heading text-lg font-bold text-slate-800">Informasi voucher</h2>
                            <p class="mt-0.5 text-xs text-slate-400">Isi identitas voucher. Tipe sudah mengikuti menu yang dipilih.</p>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-5 p-6 sm:grid-cols-2">
                    <input type="hidden" name="type" value="{{ $voucherType }}">

                    <div class="sm:col-span-2 flex flex-wrap items-center gap-2 rounded-xl bg-slate-50 px-3 py-2.5">
                        <span class="text-xs font-semibold text-slate-500">Tipe:</span>
                        <span class="inline-flex items-center gap-1 rounded-full {{ $isEvent ? 'bg-sky-100 text-sky-700' : 'bg-emerald-100 text-emerald-700' }} px-2.5 py-0.5 text-xs font-bold">
                            {{ $isEvent ? 'Event' : 'Cash' }}
                        </span>
                        <span class="text-xs text-slate-400">{{ $isEvent ? 'Kode dibuat otomatis untuk setiap orang.' : 'Satu kode untuk satu pelanggan.' }}</span>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="name" class="mb-1.5 block text-sm font-medium text-slate-700">Nama Voucher <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name', $isEdit ? $voucher->name : '') }}" required maxlength="100"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-800 focus:border-rose-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-400 @error('name') border-red-300 @enderror"
                               placeholder="Contoh: Voucher Opening Store">
                        @error('name')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    @if (! $isEvent)
                        <div>
                            <label for="code" class="mb-1.5 block text-sm font-medium text-slate-700">Kode Voucher</label>
                            <input type="text" id="code" name="code" value="{{ $codeValue }}" maxlength="30"
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 uppercase tracking-wider text-slate-800 focus:border-rose-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-400 @error('code') border-red-300 @enderror"
                                   placeholder="Kosongkan untuk otomatis">
                            <p class="mt-1.5 text-xs text-slate-400">Contoh: PB-OPENING. Kosongkan agar dibuat acak.</p>
                            @error('code')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="max_uses" class="mb-1.5 block text-sm font-medium text-slate-700">Batas Pemakaian <span class="text-red-500">*</span></label>
                            <input type="number" id="max_uses" name="max_uses" min="1" max="100000" required
                                   value="{{ $maxUsesValue }}"
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-800 focus:border-rose-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-400 @error('max_uses') border-red-300 @enderror">
                            <p class="mt-1.5 text-xs text-slate-400">1 = sekali pakai.</p>
                            @error('max_uses')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                    @endif

                    <div class="sm:col-span-2">
                        <label for="description" class="mb-1.5 block text-sm font-medium text-slate-700">Keterangan</label>
                        <input type="text" id="description" name="description" value="{{ old('description', $isEdit ? $voucher->description : '') }}" maxlength="255"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-800 focus:border-rose-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-400 @error('description') border-red-300 @enderror"
                               placeholder="Opsional, misalnya digunakan untuk event tertentu">
                        @error('description')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="discount" class="mb-1.5 block text-sm font-medium text-slate-700">Diskon (%)</label>
                        <input type="number" id="discount" name="discount" min="0" max="100"
                               value="{{ old('discount', $isEdit ? $voucher->discount : 0) }}"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-800 focus:border-rose-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-400 @error('discount') border-red-300 @enderror">
                        <p class="mt-1.5 text-xs text-slate-400">Harga akhir dibulatkan ke bawah ke ribuan. 0 = tanpa diskon.</p>
                        @error('discount')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="expires_at" class="mb-1.5 block text-sm font-medium text-slate-700">Berlaku Sampai</label>
                        <input type="date" id="expires_at" name="expires_at" value="{{ $expiresValue }}"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-800 focus:border-rose-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-400 @error('expires_at') border-red-300 @enderror">
                        <p class="mt-1.5 text-xs text-slate-400">Kosongkan bila tanpa batas waktu.</p>
                        @error('expires_at')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <label class="sm:col-span-2 flex cursor-pointer items-center gap-2.5 rounded-xl bg-slate-50 px-3 py-2.5 text-sm text-slate-600">
                        <input type="checkbox" name="is_active" value="1" @checked($activeValue) class="rounded border-slate-300 text-rose-500 focus:ring-rose-400">
                        Aktifkan voucher ini untuk pelanggan
                    </label>

                    @if ($isEvent)
                        <div class="sm:col-span-2 rounded-xl border border-sky-100 bg-sky-50 px-4 py-3 text-xs leading-relaxed text-sky-700">
                            <p class="font-bold">Kode event dibuat otomatis</p>
                            <p class="mt-1">Kode dasar dan jumlah peserta diatur di langkah Event. Setiap peserta mendapat satu kode sekali pakai.</p>
                        </div>
                    @endif
                </div>
            </section>

            {{-- Langkah 2: event --}}
            @if ($isEvent)
                <section data-step-panel="2" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-6 py-5">
                        <div class="flex items-start gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sm font-bold text-sky-600">2</span>
                            <div>
                                <h2 class="font-heading text-lg font-bold text-slate-800">Pengaturan event</h2>
                                <p class="mt-0.5 text-xs text-slate-400">Tentukan detail acara dan jumlah peserta yang akan mendapat kode.</p>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-5 p-6 sm:grid-cols-2">
                        <div>
                            <label for="event_code" class="mb-1.5 block text-sm font-medium text-slate-700">Kode Dasar Event</label>
                            <input type="text" id="event_code" name="event_code" value="{{ $eventCodeValue }}" maxlength="30"
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 uppercase tracking-wider text-slate-800 focus:border-sky-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-400 @error('event_code') border-red-300 @enderror"
                                   placeholder="Contoh: WEDDING">
                            <p class="mt-1.5 text-xs text-slate-400">Digabung dengan nomor urut: WEDDING-001, WEDDING-002, dst.</p>
                            @error('event_code')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="event_name" class="mb-1.5 block text-sm font-medium text-slate-700">Nama Event <span class="text-red-500">*</span></label>
                            <input type="text" id="event_name" name="event_name" value="{{ $eventNameValue }}" required maxlength="100"
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-800 focus:border-sky-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-400 @error('event_name') border-red-300 @enderror"
                                   placeholder="Contoh: Pernikahan Budi & Siti">
                            @error('event_name')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="event_date" class="mb-1.5 block text-sm font-medium text-slate-700">Tanggal Event <span class="text-red-500">*</span></label>
                            <input type="date" id="event_date" name="event_date" value="{{ $eventDateValue }}" required
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-800 focus:border-sky-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-400 @error('event_date') border-red-300 @enderror">
                            @error('event_date')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Penetapan Voucher Event</label>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                                    <input type="radio" name="pricing_mode" value="people" @checked($pricingModeValue === 'people') class="text-sky-500 focus:ring-sky-400">
                                    Berdasarkan jumlah orang
                                </label>
                                <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                                    <input type="radio" name="pricing_mode" value="duration" @checked($pricingModeValue === 'duration') class="text-sky-500 focus:ring-sky-400">
                                    Berdasarkan durasi (jam)
                                </label>
                            </div>
                            <p class="mt-1.5 text-xs text-slate-400">Pilih salah satu. Jumlah orang menentukan pemakaian voucher; durasi menentukan jam voucher bisa dipakai.</p>
                        </div>

                        <div data-people-fields>
                            <label for="number_of_people" class="mb-1.5 block text-sm font-medium text-slate-700">Jumlah Orang <span class="text-red-500">*</span></label>
                            <input type="number" id="number_of_people" name="number_of_people" min="1" max="10000"
                                   value="{{ $peopleValue }}" {{ $isEdit ? 'readonly' : '' }}
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-800 focus:border-sky-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-400 @error('number_of_people') border-red-300 @enderror {{ $isEdit ? 'cursor-not-allowed bg-slate-100' : '' }}">
                            <p class="mt-1.5 text-xs text-slate-400">Jumlah pemakaian voucher.{{ $isEdit ? ' Jumlah orang tidak dapat diubah setelah voucher dibuat.' : '' }}</p>
                            @error('number_of_people')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>

                        <div data-duration-fields class="sm:col-span-2 grid grid-cols-1 gap-5">
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div>
                                    <label for="duration_hours" class="mb-1.5 block text-sm font-medium text-slate-700">Durasi (jam) <span class="text-red-500">*</span></label>
                                    <input type="number" id="duration_hours" name="duration_hours" min="1" max="1000"
                                           value="{{ old('duration_hours', $isEdit ? $voucher->duration_hours : '') }}"
                                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-800 focus:border-sky-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-400 @error('duration_hours') border-red-300 @enderror">
                                </div>
                                <div>
                                    <label for="cost_per_hour" class="mb-1.5 block text-sm font-medium text-slate-700">Biaya per Jam (Rp) <span class="text-red-500">*</span></label>
                                    <input type="text" inputmode="numeric" id="cost_per_hour" name="cost_per_hour" data-rupiah
                                           value="{{ old('cost_per_hour', $isEdit ? number_format($voucher->cost_per_hour, 0, ',', '.') : '') }}"
                                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-800 focus:border-sky-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-400 @error('cost_per_hour') border-red-300 @enderror"
                                           placeholder="0">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div>
                                    <label for="start_time" class="mb-1.5 block text-sm font-medium text-slate-700">Dari Jam <span class="text-red-500">*</span></label>
                                    <input type="time" id="start_time" name="start_time" value="{{ $startTimeValue }}"
                                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-800 focus:border-sky-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-400 @error('start_time') border-red-300 @enderror">
                                </div>
                                <div>
                                    <label for="end_time" class="mb-1.5 block text-sm font-medium text-slate-700">Sampai Jam <span class="text-red-500">*</span></label>
                                    <input type="time" id="end_time" name="end_time" value="{{ $endTimeValue }}"
                                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-800 focus:border-sky-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-400 @error('end_time') border-red-300 @enderror">
                                </div>
                            </div>
                            <p class="text-xs text-slate-400">Voucher durasi hanya bisa dipakai pada rentang jam tersebut di tanggal event.</p>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="total_cost" class="mb-1.5 block text-sm font-medium text-slate-700">Total Biaya Estimasi (Rp)</label>
                            <input type="text" id="total_cost" name="total_cost" value="{{ number_format((int) $totalCostValue, 0, ',', '.') }}" readonly inputmode="numeric" data-rupiah
                                   class="w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-100 px-4 py-2.5 text-slate-600 focus:outline-none">
                            <p class="mt-1.5 text-xs text-slate-400">Dihitung otomatis dari harga frame, jumlah, dan jumlah orang.</p>
                            @error('total_cost')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>
            @endif

            {{-- Langkah frame --}}
            <section data-step-panel="{{ $frameStep }}" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5">
                    <div class="flex items-start gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-sm font-bold text-rose-600">{{ $frameStep }}</span>
                        <div>
                            <h2 class="font-heading text-lg font-bold text-slate-800">Frame yang didapat</h2>
                            <p class="mt-0.5 text-xs text-slate-400">Tentukan frame dan jumlah cetak. Pelanggan tidak perlu memilih frame lagi.</p>
                        </div>
                    </div>
                </div>
                <div class="space-y-3 p-6">
                    @error('frames')
                        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</div>
                    @enderror

                    @forelse ($frames as $frame)
                        <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50/70 p-3">
                            <div class="flex h-14 w-12 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-white ring-1 ring-slate-200">
                                @if ($frame->image_url)
                                    <img src="{{ $frame->image_url }}" alt="{{ $frame->name }}" class="h-full w-full object-contain p-1">
                                @else
                                    <x-icon name="photo" class="h-5 w-5 text-slate-300" />
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-slate-800">{{ $frame->name }}</p>
                                <p class="text-xs text-slate-400">
                                    {{ $frame->photo_count }} foto/frame
                                    &middot; Rp. {{ number_format($frame->price, 0, ',', '.') }}
                                    @if (! $frame->is_active) &middot; <span class="text-amber-600">nonaktif</span>@endif
                                </p>
                            </div>
                            <div class="w-24 shrink-0">
                                <label for="frame-{{ $frame->id }}" class="mb-1 block text-[10px] font-semibold uppercase tracking-wider text-slate-400">Jumlah</label>
                                <input type="number" id="frame-{{ $frame->id }}" name="frames[{{ $frame->slug }}]" min="0" max="50"
                                       value="{{ (int) ($selectedFrames[$frame->slug] ?? 0) }}"
                                       data-price="{{ $frame->price }}" data-frame-name="{{ $frame->name }}"
                                       class="frame-qty w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-center font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-400">
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                            Belum ada frame. Tambahkan frame terlebih dahulu di menu Frames.
                        </div>
                    @endforelse

                    <p class="text-xs text-slate-400">Isi 0 untuk frame yang tidak termasuk dalam voucher.</p>
                </div>
            </section>

            {{-- Langkah ringkasan --}}
            <section data-step-panel="{{ $summaryStep }}" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5">
                    <div class="flex items-start gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-sm font-bold text-emerald-600">{{ $summaryStep }}</span>
                        <div>
                            <h2 class="font-heading text-lg font-bold text-slate-800">Ringkasan &amp; simpan</h2>
                            <p class="mt-0.5 text-xs text-slate-400">Periksa kembali isi voucher dan harga akhir sebelum disimpan.</p>
                        </div>
                    </div>
                </div>
                <div class="space-y-4 p-6">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Ringkasan voucher</p>
                        <dl class="mt-3 grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                            <div>
                                <dt class="text-xs text-slate-400">Tipe</dt>
                                <dd class="mt-0.5 font-semibold text-slate-700" data-summary-type>{{ $isEvent ? 'Event' : 'Cash' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-slate-400">Kode</dt>
                                <dd class="mt-0.5 font-semibold text-slate-700" data-summary-code>{{ $isEvent ? ($eventCodeValue ?: 'Otomatis per orang') : ($codeValue ?: 'Otomatis') }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-xs text-slate-400">Nama voucher</dt>
                                <dd class="mt-0.5 font-semibold text-slate-700" data-summary-name>{{ old('name', $isEdit ? $voucher->name : '') ?: 'Belum diisi' }}</dd>
                            </div>
                            @if ($isEvent)
                                <div>
                                    <dt class="text-xs text-slate-400">Event</dt>
                                    <dd class="mt-0.5 font-semibold text-slate-700" data-summary-event>{{ $eventNameValue ?: 'Belum diisi' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-slate-400">Jumlah orang</dt>
                                    <dd class="mt-0.5 font-semibold text-slate-700" data-summary-people>{{ $peopleValue ?: 'Belum diisi' }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Perhitungan harga</p>
                        <div id="price-breakdown" class="mt-2 space-y-1 text-sm text-slate-600"></div>
                        <div class="mt-2 flex items-center justify-between border-t border-slate-200 pt-2">
                            <span class="text-sm font-bold text-slate-800">Total</span>
                            <span id="price-total" class="text-sm font-bold" style="color: var(--brand-primary);">Rp. 0</span>
                        </div>
                        <p class="mt-1 text-[11px] text-slate-400" id="price-note"></p>
                    </div>

                    <p class="text-xs text-slate-400">Harga akhir sudah dibulatkan ke bawah ke ribuan. Pastikan frame dan jumlah sudah sesuai.</p>
                </div>
            </section>

            {{-- Navigasi langkah --}}
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <a href="{{ route('admin.vouchers.index') }}" class="px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 rounded-xl">Batal</a>
                <div class="flex items-center gap-2">
                    <button type="button" data-step-prev class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40">
                        <x-icon name="arrow-left" class="w-4 h-4" />
                        Kembali
                    </button>
                    {{-- Navigasi dilakukan lewat menu langkah di samping; tidak perlu urut. --}}
                    <button type="submit" data-step-submit class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-500 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-600">
                        <x-icon name="check" class="w-4 h-4" />
                        {{ $isEdit ? 'Simpan Perubahan' : 'Buat Voucher' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    (function () {
        const form = document.querySelector('[data-voucher-form]');
        if (!form) return;

        const isEvent = @json($isEvent);
        const frameStep = Number(@json($frameStep));
        const panels = Array.from(form.querySelectorAll('[data-step-panel]'));
        const stepOrder = panels.map((panel) => Number(panel.dataset.stepPanel));
        const buttons = Array.from(form.querySelectorAll('[data-step-target]'));
        const prevButton = form.querySelector('[data-step-prev]');
        const nextButton = form.querySelector('[data-step-next]');
        const submitButton = form.querySelector('[data-step-submit]');
        const firstStep = stepOrder[0];
        const lastStep = stepOrder[stepOrder.length - 1];
        let currentStep = Number(form.dataset.initialStep || firstStep);

        if (!stepOrder.includes(currentStep)) currentStep = firstStep;

        function togglePricingMode() {
            if (!isEvent) return;
            const mode = document.querySelector('input[name=\"pricing_mode\"]:checked')?.value || 'people';
            const peopleFields = document.querySelector('[data-people-fields]');
            const durationFields = document.querySelector('[data-duration-fields]');
            if (peopleFields) peopleFields.classList.toggle('hidden', mode !== 'people');
            if (durationFields) durationFields.classList.toggle('hidden', mode !== 'duration');
            ['duration_hours', 'cost_per_hour', 'start_time', 'end_time'].forEach((id) => {
                const el = document.getElementById(id);
                if (el) el.required = mode === 'duration';
            });
            const peopleEl = document.getElementById('number_of_people');
            if (peopleEl) peopleEl.required = mode === 'people';
        }

        const panelFor = (step) => panels.find((panel) => Number(panel.dataset.stepPanel) === step);

        function showStep(step, focus) {
            currentStep = step;
            panels.forEach((panel) => {
                panel.classList.toggle('hidden', Number(panel.dataset.stepPanel) !== step);
            });

            buttons.forEach((button) => {
                const active = Number(button.dataset.stepTarget) === step;
                button.classList.toggle('border-rose-200', active);
                button.classList.toggle('bg-rose-50', active);
                button.classList.toggle('text-rose-700', active);
                button.classList.toggle('border-transparent', !active);
                button.classList.toggle('text-slate-500', !active);
                button.classList.toggle('hover:bg-slate-50', !active);
                if (active) {
                    button.setAttribute('aria-current', 'step');
                } else {
                    button.removeAttribute('aria-current');
                }

                const dot = button.querySelector('[data-step-dot]');
                if (dot) {
                    dot.classList.toggle('bg-rose-500', active);
                    dot.classList.toggle('text-white', active);
                    dot.classList.toggle('bg-slate-100', !active);
                    dot.classList.toggle('text-slate-500', !active);
                }
            });

            if (prevButton) prevButton.disabled = step === firstStep;
            if (nextButton) nextButton.classList.toggle('hidden', step === lastStep);
            if (submitButton) submitButton.classList.toggle('hidden', step !== lastStep);

            if (focus) {
                panelFor(step)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        function validateStep(step) {
            const panel = panelFor(step);
            if (!panel) return true;

            const fields = Array.from(panel.querySelectorAll('input, select, textarea'))
                .filter((field) => !field.disabled && field.type !== 'hidden' && field.type !== 'button');

            for (const field of fields) {
                if (!field.checkValidity()) {
                    field.reportValidity();
                    return false;
                }
            }

            return true;
        }

        function validateThrough(step) {
            for (const candidate of stepOrder) {
                if (candidate >= step) break;
                if (!validateStep(candidate)) {
                    showStep(candidate, true);
                    return false;
                }
            }
            return true;
        }

        function nextStep() {
            const index = stepOrder.indexOf(currentStep);
            if (index === -1 || index >= stepOrder.length - 1) return;
            if (!validateStep(currentStep)) return;
            showStep(stepOrder[index + 1], true);
        }

        function previousStep() {
            const index = stepOrder.indexOf(currentStep);
            if (index > 0) showStep(stepOrder[index - 1], true);
        }

        prevButton?.addEventListener('click', previousStep);
        nextButton?.addEventListener('click', nextStep);
        buttons.forEach((button) => {
            button.addEventListener('click', () => {
                const target = Number(button.dataset.stepTarget);
                if (target === currentStep) return;
                // Admin boleh pindah langkah bebas tanpa harus urut.
                showStep(target, true);
            });
        });

        function peopleCount() {
            if (!isEvent) return 1;
            const el = document.getElementById('number_of_people');
            if (!el) return 1;
            const value = parseInt(el.value || '1', 10);
            return value > 0 ? value : 1;
        }

        function durationHours() {
            if (!isEvent) return 0;
            const el = document.getElementById('duration_hours');
            if (!el) return 0;
            const value = parseInt(el.value || '0', 10);
            return value > 0 ? value : 0;
        }

        function formatRupiah(value) {
            const digits = String(value ?? '').replace(/\\D/g, '');
            if (!digits) return '';
            return digits.replace(/\\B(?=(\\d{3})+(?!\\d))/g, '.');
        }

        function costPerHour() {
            if (!isEvent) return 0;
            const el = document.getElementById('cost_per_hour');
            if (!el) return 0;
            const value = parseInt(String(el.value || '0').replace(/\\D/g, ''), 10);
            return value > 0 ? value : 0;
        }

        function discountPct() {
            const el = document.getElementById('discount');
            if (!el) return 0;
            const value = parseInt(el.value || '0', 10);
            return Math.min(100, Math.max(0, isNaN(value) ? 0 : value));
        }

        function floor1000(value) {
            if (value >= 1000) return Math.floor(value / 1000) * 1000;
            return Math.floor(value);
        }

        function hasSelectedFrame() {
            return Array.from(document.querySelectorAll('.frame-qty')).some((input) => parseInt(input.value || '0', 10) > 0);
        }

        function updateSummary() {
            const name = document.getElementById('name');
            const code = document.getElementById('code');
            const eventCode = document.getElementById('event_code');
            const eventName = document.getElementById('event_name');
            const people = document.getElementById('number_of_people');
            const codeValue = isEvent
                ? (eventCode ? eventCode.value.trim() : '')
                : (code ? code.value.trim() : '');

            const codeEl = document.querySelector('[data-summary-code]');
            const nameEl = document.querySelector('[data-summary-name]');
            const eventEl = document.querySelector('[data-summary-event]');
            const peopleEl = document.querySelector('[data-summary-people]');

            if (codeEl) codeEl.textContent = codeValue || (isEvent ? 'Otomatis per orang' : 'Otomatis');
            if (nameEl) nameEl.textContent = name?.value.trim() || 'Belum diisi';
            if (eventEl) eventEl.textContent = eventName?.value.trim() || 'Belum diisi';
            if (peopleEl) peopleEl.textContent = people?.value || 'Belum diisi';
        }

        function updatePrice() {
            const people = peopleCount();
            const hours = durationHours();
            const hourlyRate = costPerHour();
            const discount = discountPct();
            const breakdown = document.getElementById('price-breakdown');
            const totalEl = document.getElementById('price-total');
            const noteEl = document.getElementById('price-note');
            const totalCostEl = document.getElementById('total_cost');

            let subtotal = 0;
            let html = '';

            document.querySelectorAll('.frame-qty').forEach((input) => {
                const qty = parseInt(input.value || '0', 10);
                if (qty <= 0) return;
                const price = parseInt(input.getAttribute('data-price') || '0', 10);
                const line = price * qty;
                subtotal += line;
                const name = input.dataset.frameName || 'Frame';
                html += '<div class="flex justify-between gap-3"><span>' + name + ' x' + qty + '</span><span>' + 'Rp. ' + line.toLocaleString('id-ID') + '</span></div>';
            });

            const hourlyTotal = hours * hourlyRate;
            const originalTotal = subtotal * people + hourlyTotal;
            let finalTotal = originalTotal;
            if (discount > 0) {
                finalTotal = floor1000(Math.floor(originalTotal * (1 - discount / 100)));
            }

            if (breakdown) breakdown.innerHTML = html || '<span class="text-slate-400">Belum ada frame dipilih.</span>';
            if (totalEl) totalEl.textContent = 'Rp. ' + finalTotal.toLocaleString('id-ID');
            if (noteEl) {
                if (isEvent) {
                    const parts = [];
                    if (subtotal > 0) parts.push('Frame: Rp. ' + subtotal.toLocaleString('id-ID') + ' x ' + people + ' orang');
                    if (hourlyTotal > 0) parts.push('Durasi: ' + hours + ' jam x Rp. ' + hourlyRate.toLocaleString('id-ID'));
                    noteEl.textContent = parts.join(' + ') + (discount > 0 ? ' - diskon ' + discount + '%' : '');
                } else {
                    noteEl.textContent = discount > 0 ? 'Harga asli Rp. ' + originalTotal.toLocaleString('id-ID') + ' - diskon ' + discount + '%' : '';
                }
            }
            if (totalCostEl) totalCostEl.value = formatRupiah(finalTotal);
        }

        function refresh() {
            updatePrice();
            updateSummary();
        }

        document.querySelectorAll('.frame-qty, #discount, #number_of_people, #duration_hours, #cost_per_hour, #start_time, #end_time, #name, #code, #event_code, #event_name').forEach((input) => {
            input.addEventListener('input', refresh);
        });

        document.querySelectorAll('input[name=\"pricing_mode\"]').forEach((input) => {
            input.addEventListener('change', () => {
                togglePricingMode();
                refresh();
            });
        });

        togglePricingMode();

        document.querySelectorAll('[data-rupiah]').forEach((input) => {
            input.addEventListener('input', () => {
                const cursor = input.selectionStart || 0;
                const beforeLength = input.value.length;
                input.value = formatRupiah(input.value);
                const afterLength = input.value.length;
                const nextCursor = Math.max(0, cursor + (afterLength - beforeLength));
                input.selectionStart = input.selectionEnd = nextCursor;
            });
        });

        form.addEventListener('submit', (event) => {
            if (!validateThrough(lastStep)) {
                event.preventDefault();
                return;
            }
            if (!hasSelectedFrame()) {
                event.preventDefault();
                showStep(frameStep, true);
                if (typeof window.showToast === 'function') {
                    window.showToast('Pilih minimal satu frame dan tentukan jumlah cetaknya.', 'warning');
                }
                return;
            }
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Menyimpan...';
            }
        });

        showStep(currentStep);
        refresh();
    })();
</script>
