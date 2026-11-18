<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Frame;
use App\Models\Size;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VoucherController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', 'in:cash,event'],
            'status' => ['nullable', 'in:active,inactive,expired,exhausted'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $filters = array_merge([
            'q' => '',
            'type' => '',
            'status' => '',
            'from' => '',
            'to' => '',
        ], $filters);
        foreach ($filters as $key => $value) {
            $filters[$key] = $value ?? '';
        }
        $filters['q'] = trim((string) $filters['q']);

        $type = $filters['type'];
        $query = Voucher::query()
            ->when($type === Voucher::TYPE_CASH, fn ($query) => $query->cash())
            ->when($type === Voucher::TYPE_EVENT, fn ($query) => $query->event())
            ->when($filters['from'] !== '', fn ($query) => $query->whereDate('created_at', '>=', $filters['from']))
            ->when($filters['to'] !== '', fn ($query) => $query->whereDate('created_at', '<=', $filters['to']));

        $this->applyStatusFilter($query, $filters['status']);

        $all = $query->orderByDesc('created_at')->get();

        // Pencarian juga harus menemukan seluruh event jika salah satu kode
        // anggotanya cocok, bukan hanya kode pertama yang tampil di tabel.
        if ($filters['q'] !== '') {
            $term = $filters['q'];
            $matchingBatches = $all
                ->filter(fn (Voucher $voucher) => $this->voucherMatches($voucher, $term))
                ->pluck('event_batch')
                ->filter()
                ->unique()
                ->all();

            $all = $all->filter(function (Voucher $voucher) use ($term, $matchingBatches) {
                if ($this->voucherMatches($voucher, $term)) {
                    return true;
                }

                return $voucher->isEvent() && in_array($voucher->event_batch, $matchingBatches, true);
            })->values();
        }

        // Event ditampilkan satu baris per batch, bukan satu baris per orang.
        $display = collect();
        $eventBatches = [];
        foreach ($all as $voucher) {
            if ($voucher->isEvent()) {
                $batchKey = $voucher->event_batch ?: ($voucher->event_name.'|'.optional($voucher->event_date)->toDateString());
                if (! isset($eventBatches[$batchKey])) {
                    $eventBatches[$batchKey] = $voucher;
                    $voucher->setAttribute('event_voucher_count', 1);
                    $display->push($voucher);
                } else {
                    $eventBatches[$batchKey]->setAttribute(
                        'event_voucher_count',
                        (int) $eventBatches[$batchKey]->event_voucher_count + 1
                    );
                }
            } else {
                $voucher->setAttribute('event_voucher_count', 1);
                $display->push($voucher);
            }
        }

        $page = LengthAwarePaginator::resolveCurrentPage();
        $vouchers = new LengthAwarePaginator(
            $display->forPage($page, 15)->values(),
            $display->count(),
            15,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $summary = [
            'count' => $display->count(),
            'cash' => $display->where('type', Voucher::TYPE_CASH)->count(),
            'event' => $display->where('type', Voucher::TYPE_EVENT)->count(),
            'active' => $display->filter(fn (Voucher $voucher) => $voucher->isUsable())->count(),
        ];

        $dateLabel = 'Semua tanggal';
        if ($filters['from'] !== '' || $filters['to'] !== '') {
            $fromLabel = $filters['from'] !== '' ? Carbon::parse($filters['from'])->format('d M Y') : null;
            $toLabel = $filters['to'] !== '' ? Carbon::parse($filters['to'])->format('d M Y') : null;
            $dateLabel = match (true) {
                $fromLabel && $toLabel => $fromLabel.' - '.$toLabel,
                (bool) $fromLabel => 'Dari '.$fromLabel,
                default => 'Sampai '.$toLabel,
            };
        }

        return view('admin.vouchers.index', compact('vouchers', 'type', 'filters', 'summary', 'dateLabel'));
    }

    private function voucherMatches(Voucher $voucher, string $term): bool
    {
        $term = mb_strtolower($term);

        return str_contains(mb_strtolower($voucher->code), $term)
            || str_contains(mb_strtolower($voucher->name), $term)
            || str_contains(mb_strtolower((string) $voucher->description), $term)
            || str_contains(mb_strtolower((string) $voucher->event_name), $term)
            || str_contains(mb_strtolower((string) $voucher->event_code), $term);
    }

    private function applyStatusFilter($query, string $status)
    {
        return $query
            ->when($status === 'active', fn ($query) => $query
                ->where('is_active', true)
                ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>=', now()))
                ->where(fn ($query) => $query->where('max_uses', '<', 1)->orWhereColumn('used_count', '<', 'max_uses')))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->when($status === 'expired', fn ($query) => $query
                ->whereNotNull('expires_at')
                ->where('expires_at', '<', now()))
            ->when($status === 'exhausted', fn ($query) => $query
                ->where('max_uses', '>', 0)
                ->whereColumn('used_count', '>=', 'max_uses'));
    }

    public function create(Request $request)
    {
        $frames = Frame::orderBy('sort_order')->orderBy('name')->get();
        $sizes = Size::active()->orderBy('sort_order')->orderBy('name')->get();
        $type = $request->input('type') === Voucher::TYPE_EVENT ? Voucher::TYPE_EVENT : Voucher::TYPE_CASH;

        return view('admin.vouchers.create', compact('frames', 'sizes', 'type'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $type = $data['type'] ?? Voucher::TYPE_CASH;

        if ($type === Voucher::TYPE_EVENT) {
            return $this->storeEvent($request, $data);
        }

        $voucher = Voucher::create($data);

        return redirect()
            ->route('admin.vouchers.index')
            ->with('success', 'Voucher '.$voucher->code.' berhasil dibuat.');
    }

    /**
     * Buat beberapa voucher untuk event berdasarkan jumlah orang.
     * Setiap orang mendapat satu kode voucher unik dengan items yang sama.
     */
    private function storeEvent(Request $request, array $data)
    {
        $numberOfPeople = (int) ($data['number_of_people'] ?? 1);
        if ($numberOfPeople < 1) {
            throw ValidationException::withMessages([
                'number_of_people' => 'Jumlah orang minimal 1.',
            ]);
        }

        $baseCode = Str::upper(trim((string) ($data['event_code'] ?? '')));
        if ($baseCode === '') {
            $baseCode = 'EVENT';
        }

        $durationHours = (int) ($data['duration_hours'] ?? 0);
        $pricingMode = $data['pricing_mode'] ?? ($durationHours > 0 || ! empty($data['start_time']) ? 'duration' : 'people');
        // Mode durasi memakai pemakaian tak terbatas selama jam event berlangsung.
        // Mode jumlah orang memakai pemakaian sebanyak jumlah orang.
        $maxUses = $pricingMode === 'duration' ? 0 : $numberOfPeople;

        $code = $baseCode;
        $duplicate = Voucher::where('code', $code)->exists();
        if ($duplicate) {
            $code = $this->generateEventCode($baseCode, 1);
        }

        $batch = (string) Str::uuid();
        Voucher::create(array_merge($data, [
            'code' => $code,
            'type' => Voucher::TYPE_EVENT,
            'event_batch' => $batch,
            'event_code' => $baseCode,
            'number_of_people' => $numberOfPeople,
            'duration_hours' => $durationHours,
            'cost_per_hour' => (int) ($data['cost_per_hour'] ?? 0),
            'pricing_mode' => $pricingMode,
            'start_time' => $data['start_time'] ?? null,
            'end_time' => $data['end_time'] ?? null,
            'max_uses' => $maxUses,
            'used_count' => 0,
        ]));

        $totalCost = (int) ($data['total_cost'] ?? 0);

        return redirect()
            ->route('admin.vouchers.index')
            ->with('success', 'Voucher event '.$code.' berhasil dibuat. Total biaya: Rp. '.number_format($totalCost, 0, ',', '.'));
    }

    public function edit(Voucher $voucher)
    {
        $frames = Frame::orderBy('sort_order')->orderBy('name')->get();
        $sizes = Size::orderBy('sort_order')->orderBy('name')->get();

        return view('admin.vouchers.edit', compact('voucher', 'frames', 'sizes'));
    }

    public function update(Request $request, Voucher $voucher)
    {
        $data = $this->validated($request, $voucher);

        if ($voucher->isEvent() && $voucher->event_batch) {
            Voucher::where('event_batch', $voucher->event_batch)
                ->get()
                ->each(fn (Voucher $item) => $item->update(Arr::except($data, ['code', 'type', 'max_uses', 'used_count'])));

            return redirect()
                ->route('admin.vouchers.index')
                ->with('success', 'Event '.$voucher->event_name.' berhasil diperbarui.');
        }

        $voucher->update($data);

        return redirect()
            ->route('admin.vouchers.index')
            ->with('success', 'Voucher '.$voucher->code.' berhasil diperbarui.');
    }

    public function destroyFiltered(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', 'in:cash,event'],
            'status' => ['nullable', 'in:active,inactive,expired,exhausted'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $filters = array_merge(['q' => '', 'type' => '', 'status' => '', 'from' => '', 'to' => ''], $filters);
        foreach ($filters as $key => $value) {
            $filters[$key] = $value ?? '';
        }
        $filters['q'] = trim((string) $filters['q']);

        if ($filters['q'] === '' && $filters['type'] === '' && $filters['status'] === '' && $filters['from'] === '' && $filters['to'] === '') {
            return redirect()
                ->route('admin.vouchers.index', $request->only(['sort', 'dir', 'page']))
                ->with('error', 'Pilih minimal satu filter sebelum menghapus.');
        }

        $query = Voucher::query()
            ->when($filters['type'] === Voucher::TYPE_CASH, fn ($query) => $query->cash())
            ->when($filters['type'] === Voucher::TYPE_EVENT, fn ($query) => $query->event())
            ->when($filters['from'] !== '', fn ($query) => $query->whereDate('created_at', '>=', $filters['from']))
            ->when($filters['to'] !== '', fn ($query) => $query->whereDate('created_at', '<=', $filters['to']));

        $this->applyStatusFilter($query, $filters['status']);

        $all = $query->orderByDesc('created_at')->get();

        if ($filters['q'] !== '') {
            $term = $filters['q'];
            $matchingBatches = $all
                ->filter(fn (Voucher $voucher) => $this->voucherMatches($voucher, $term))
                ->pluck('event_batch')
                ->filter()
                ->unique()
                ->all();

            $all = $all->filter(function (Voucher $voucher) use ($term, $matchingBatches) {
                if ($this->voucherMatches($voucher, $term)) {
                    return true;
                }

                return $voucher->isEvent() && in_array($voucher->event_batch, $matchingBatches, true);
            })->values();
        }

        // Untuk event, hapus seluruh batch agar tidak menyisakan kode anggota.
        $toDelete = collect();
        $eventBatches = [];
        foreach ($all as $voucher) {
            if ($voucher->isEvent() && $voucher->event_batch) {
                if (! in_array($voucher->event_batch, $eventBatches, true)) {
                    $eventBatches[] = $voucher->event_batch;
                    $toDelete = $toDelete->merge(Voucher::where('event_batch', $voucher->event_batch)->get());
                }
            } else {
                $toDelete->push($voucher);
            }
        }

        $count = $toDelete->count();
        foreach ($toDelete as $voucher) {
            $voucher->delete();
        }

        return redirect()
            ->route('admin.vouchers.index', $request->only(['q', 'type', 'status', 'from', 'to']))
            ->with('success', $count.' voucher sesuai filter berhasil dihapus.');
    }

    public function show(Voucher $voucher)
    {
        if ($voucher->isEvent() && $voucher->event_batch) {
            $vouchers = Voucher::where('event_batch', $voucher->event_batch)
                ->orderBy('code')
                ->get();
        } else {
            $vouchers = collect([$voucher]);
        }

        $frames = Frame::whereIn('slug', array_keys($voucher->items ?? []))->get()->keyBy('slug');
        $sizes = Size::whereIn('id', array_keys($voucher->sizeItems()))->get()->keyBy('id');

        return view('admin.vouchers.show', compact('voucher', 'vouchers', 'frames', 'sizes'));
    }

    public function destroy(Voucher $voucher)
    {
        if ($voucher->isEvent() && $voucher->event_batch) {
            $count = Voucher::where('event_batch', $voucher->event_batch)->count();
            Voucher::where('event_batch', $voucher->event_batch)->delete();

            return redirect()
                ->route('admin.vouchers.index')
                ->with('success', 'Event '.$voucher->event_name.' ('.$count.' voucher) berhasil dihapus.');
        }

        $code = $voucher->code;
        $voucher->delete();

        return redirect()
            ->route('admin.vouchers.index')
            ->with('success', 'Voucher '.$code.' berhasil dihapus.');
    }

    /**
     * Cetak invoice untuk event atau cash.
     */
    public function printInvoice(Request $request, Voucher $voucher)
    {
        $voucher->loadMissing([]);

        $items = $voucher->itemsDetailed();
        $costPerPerson = (int) array_sum(array_column($items, 'subtotal'));
        $totalCost = $voucher->isEvent()
            ? $costPerPerson * (int) $voucher->number_of_people + (int) $voucher->duration_hours * (int) $voucher->cost_per_hour
            : $costPerPerson;

        return view('admin.vouchers.print-invoice', compact('voucher', 'items', 'costPerPerson', 'totalCost'));
    }

    /**
     * Cetak satu voucher (untuk diberikan kepada pelanggan).
     */
    public function printVoucher(Request $request, Voucher $voucher)
    {
        $items = $voucher->itemsDetailed();

        return view('admin.vouchers.print-voucher', compact('voucher', 'items'));
    }

    /**
     * Normalisasi input admin menjadi satu baris voucher:
     * sizes[size_id] = qty  ->  items { size_id: qty }.
     */
    private function validated(Request $request, ?Voucher $voucher = null): array
    {
        $rules = [
            'type' => ['nullable', 'string', 'in:cash,event'],
            'code' => ['nullable', 'string', 'max:30', 'regex:/^[A-Za-z0-9-]+$/'],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'frames' => ['nullable', 'array'],
            'frames.*' => ['nullable', 'integer', 'min:0', 'max:50'],
            'sizes' => ['nullable', 'array'],
            'sizes.*' => ['nullable', 'integer', 'min:0', 'max:100'],
            'max_uses' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'expires_at' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
            // Event fields
            'event_code' => ['nullable', 'string', 'max:30', 'regex:/^[A-Za-z0-9-]+$/'],
            'pricing_mode' => ['nullable', 'in:people,duration'],
            'event_name' => ['nullable', 'string', 'max:100'],
            'event_date' => ['nullable', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'number_of_people' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'duration_hours' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'cost_per_hour' => ['nullable', 'integer', 'min:0'],
            'total_cost' => ['nullable', 'integer', 'min:0'],
            'discount' => ['nullable', 'integer', 'min:0', 'max:100'],
        ];

        if ($request->input('type') === Voucher::TYPE_EVENT) {
            $rules['event_name'] = ['required', 'string', 'max:100'];
            $rules['event_date'] = ['required', 'date'];
            if ($request->input('pricing_mode') === 'duration') {
                $rules['number_of_people'] = ['nullable', 'integer', 'min:1', 'max:10000'];
                $rules['start_time'] = ['required', 'date_format:H:i'];
                $rules['end_time'] = ['required', 'date_format:H:i'];
                $rules['duration_hours'] = ['required', 'integer', 'min:1', 'max:1000'];
                $rules['cost_per_hour'] = ['required', 'integer', 'min:0'];
            } else {
                $rules['number_of_people'] = ['required', 'integer', 'min:1', 'max:10000'];
            }
        }

        $request->merge([
            'cost_per_hour' => str_replace('.', '', (string) $request->input('cost_per_hour')),
            'total_cost' => str_replace('.', '', (string) $request->input('total_cost')),
        ]);

        $data = $request->validate($rules);

        $type = $data['type'] ?? Voucher::TYPE_CASH;

        $code = Str::upper(trim((string) ($type === Voucher::TYPE_EVENT ? ($data['event_code'] ?? '') : ($data['code'] ?? ''))));
        if ($code === '') {
            $code = $this->generateCode();
        }

        $duplicate = Voucher::where('code', $code)
            ->when($voucher, fn ($query) => $query->where('id', '!=', $voucher->id))
            ->exists();

        // Saat membuat event, kode dasar boleh sama karena sistem menambahkan
        // nomor urut di belakangnya. Saat edit, kode final tetap harus unik.
        if ($duplicate && ($type !== Voucher::TYPE_EVENT || $voucher !== null)) {
            $codeField = $type === Voucher::TYPE_EVENT ? 'event_code' : 'code';

            throw ValidationException::withMessages([
                $codeField => $type === Voucher::TYPE_EVENT
                    ? 'Kode event sudah dipakai. Gunakan kode dasar lain.'
                    : 'Kode voucher sudah dipakai. Gunakan kode lain atau kosongkan untuk dibuat otomatis.',
            ]);
        }

        $validSizeIds = Size::pluck('id')->map(fn ($id) => (int) $id)->all();
        $sizeIdsWithFrames = Size::whereHas('frames', fn ($query) => $query->where('is_active', true))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
        $validFrameSlugs = Frame::where('is_active', true)->pluck('slug')->all();

        $frameItems = [];
        foreach ((array) ($data['frames'] ?? []) as $slug => $qty) {
            $qty = (int) $qty;
            if ($qty > 0 && in_array((string) $slug, $validFrameSlugs, true)) {
                $frameItems[(string) $slug] = $qty;
            }
        }

        $sizeItems = [];
        foreach ((array) ($data['sizes'] ?? []) as $sizeId => $qty) {
            $qty = (int) $qty;
            if ($qty > 0 && ctype_digit((string) $sizeId) && in_array((int) $sizeId, $validSizeIds, true)) {
                $sizeItems[(int) $sizeId] = $qty;
            }
        }

        if (! $frameItems) {
            $requestedSizeIds = [];
            foreach ((array) ($data['sizes'] ?? []) as $sizeId => $qty) {
                if ((int) $qty > 0 && ctype_digit((string) $sizeId)) {
                    $requestedSizeIds[] = (int) $sizeId;
                }
            }

            $unavailable = array_values(array_diff($requestedSizeIds, $sizeIdsWithFrames));
            if ($unavailable) {
                $names = Size::whereIn('id', $unavailable)->orderBy('sort_order')->pluck('name')->implode(', ');

                throw ValidationException::withMessages([
                    'sizes' => 'Ukuran '.$names.' belum memiliki frame aktif. Tambahkan frame untuk ukuran tersebut di menu Frame, atau pilih ukuran lain.',
                ]);
            }
        }

        if (! $frameItems && ! $sizeItems) {
            throw ValidationException::withMessages([
                'frames' => 'Pilih minimal satu frame atau satu ukuran dan tentukan jumlah cetaknya.',
                'sizes' => 'Pilih minimal satu frame atau satu ukuran dan tentukan jumlah cetaknya.',
            ]);
        }

        // Frame diprioritaskan bila keduanya terisi.
        $items = $frameItems ?: $sizeItems;

        $sizes = Size::whereIn('id', array_keys(array_filter($items, fn ($k) => ctype_digit((string) $k), ARRAY_FILTER_USE_KEY)))->get()->keyBy('id');
        // hitung harga
        $costPerPerson = 0;
        $printCount = (int) array_sum($items);

        if ($frameItems) {
            $frames = Frame::whereIn('slug', array_keys($frameItems))->get()->keyBy('slug');
            foreach ($frameItems as $slug => $qty) {
                $frame = $frames->get($slug);
                if ($frame) {
                    $costPerPerson += (int) $frame->effectivePrice() * (int) $qty;
                }
            }
            $photoCount = 0;
            foreach ($frameItems as $slug => $qty) {
                $frame = $frames->get($slug);
                if ($frame) {
                    $photoCount += max(1, (int) $frame->photo_count) * (int) $qty;
                }
            }
        } else {
            $sizes = Size::whereIn('id', array_keys($sizeItems))->get()->keyBy('id');
            foreach ($sizeItems as $sizeId => $qty) {
                $size = $sizes->get($sizeId);
                if ($size) {
                    $costPerPerson += (int) $size->price * (int) $qty;
                }
            }
            $photoCount = max(1, $printCount);
        }

        $result = [
            'code' => $code,
            'type' => $type,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'items' => $items,
            'photos_per_voucher' => $photoCount > 0 ? $photoCount : 1,
            'max_uses' => (int) ($data['max_uses'] ?? 1),
            'expires_at' => ! empty($data['expires_at'])
                ? Carbon::parse($data['expires_at'])->endOfDay()
                : null,
            'is_active' => $request->boolean('is_active'),
            'discount' => (int) ($data['discount'] ?? 0),
        ];

        if ($type === Voucher::TYPE_EVENT) {
            $result['event_name'] = $data['event_name'];
            $result['event_code'] = $data['event_code'] ?? null;
            $result['pricing_mode'] = $data['pricing_mode'] ?? (($data['duration_hours'] ?? 0) > 0 || ! empty($data['start_time']) ? 'duration' : 'people');
            $result['event_date'] = ! empty($data['event_date'])
                ? Carbon::parse($data['event_date'])->startOfDay()
                : null;
            $result['start_time'] = $data['start_time'] ?? null;
            $result['end_time'] = $data['end_time'] ?? null;
            $result['number_of_people'] = (int) ($data['number_of_people'] ?? 1);
            $result['duration_hours'] = (int) ($data['duration_hours'] ?? 0);
            $result['cost_per_hour'] = (int) ($data['cost_per_hour'] ?? 0);

            // Hitung total biaya event: ukuran x jumlah cetak x jumlah orang
            // ditambah biaya durasi (bila ada).
            $result['total_cost'] = $costPerPerson * (int) $data['number_of_people']
                + ((int) ($data['duration_hours'] ?? 0) * (int) ($data['cost_per_hour'] ?? 0));
        }

        return $result;
    }

    private function generateEventCode(string $baseCode, int $index): string
    {
        do {
            $code = $baseCode.'-'.str_pad((string) $index, 3, '0', STR_PAD_LEFT);
            $index++;
        } while (Voucher::where('code', $code)->exists());

        return $code;
    }

    private function generateCode(): string
    {
        do {
            $code = 'PB-'.Str::upper(Str::random(6));
        } while (Voucher::where('code', $code)->exists());

        return $code;
    }
}
