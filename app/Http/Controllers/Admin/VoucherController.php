<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Frame;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VoucherController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->input('type', '');

        $vouchers = Voucher::query()
            ->when($type === 'cash', fn ($query) => $query->cash())
            ->when($type === 'event', fn ($query) => $query->event())
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.vouchers.index', compact('vouchers', 'type'));
    }

    public function create(Request $request)
    {
        $frames = Frame::orderBy('sort_order')->orderBy('name')->get();
        $type = $request->input('type') === Voucher::TYPE_EVENT ? Voucher::TYPE_EVENT : Voucher::TYPE_CASH;

        return view('admin.vouchers.create', compact('frames', 'type'));
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

        $created = [];
        for ($i = 1; $i <= $numberOfPeople; $i++) {
            $code = $this->generateEventCode($baseCode, $i);

            Voucher::create(array_merge($data, [
                'code' => $code,
                'type' => Voucher::TYPE_EVENT,
                'number_of_people' => $numberOfPeople,
                'max_uses' => 1,
                'used_count' => 0,
            ]));

            $created[] = $code;
        }

        $totalCost = (int) ($data['total_cost'] ?? 0);

        return redirect()
            ->route('admin.vouchers.index')
            ->with('success', 'Berhasil membuat '.$numberOfPeople.' voucher event ('.$created[0].' s.d. '.end($created).'). Total biaya: Rp. '.number_format($totalCost, 0, ',', '.'));
    }

    public function edit(Voucher $voucher)
    {
        $frames = Frame::orderBy('sort_order')->orderBy('name')->get();

        return view('admin.vouchers.edit', compact('voucher', 'frames'));
    }

    public function update(Request $request, Voucher $voucher)
    {
        $voucher->update($this->validated($request, $voucher));

        return redirect()
            ->route('admin.vouchers.index')
            ->with('success', 'Voucher '.$voucher->code.' berhasil diperbarui.');
    }

    public function destroy(Voucher $voucher)
    {
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

        $frames = Frame::whereIn('slug', array_keys($voucher->items ?? []))->get()->keyBy('slug');

        $items = [];
        foreach ((array) ($voucher->items ?? []) as $slug => $qty) {
            $frame = $frames->get($slug);
            if (! $frame) {
                continue;
            }
            $items[] = [
                'name' => $frame->name,
                'slug' => $slug,
                'qty' => (int) $qty,
                'price' => (int) $frame->price,
                'subtotal' => (int) $frame->price * (int) $qty,
            ];
        }

        $costPerPerson = array_sum(array_column($items, 'subtotal'));
        $totalCost = $voucher->isEvent()
            ? $costPerPerson * (int) $voucher->number_of_people
            : $costPerPerson;

        return view('admin.vouchers.print-invoice', compact('voucher', 'items', 'costPerPerson', 'totalCost'));
    }

    /**
     * Cetak satu voucher (untuk diberikan kepada pelanggan).
     */
    public function printVoucher(Request $request, Voucher $voucher)
    {
        $frames = Frame::whereIn('slug', array_keys($voucher->items ?? []))->get()->keyBy('slug');

        $items = [];
        foreach ((array) ($voucher->items ?? []) as $slug => $qty) {
            $frame = $frames->get($slug);
            if (! $frame) {
                continue;
            }
            $items[] = [
                'name' => $frame->name,
                'slug' => $slug,
                'qty' => (int) $qty,
                'photos_per_voucher' => (int) ($voucher->photos_per_voucher ?? 0),
            ];
        }

        return view('admin.vouchers.print-voucher', compact('voucher', 'items'));
    }

    /**
     * Normalisasi input admin menjadi satu baris voucher:
     * frames[slug] = qty  ->  items { slug: qty }.
     */
    private function validated(Request $request, ?Voucher $voucher = null): array
    {
        $rules = [
            'type' => ['nullable', 'string', 'in:cash,event'],
            'code' => ['nullable', 'string', 'max:30', 'regex:/^[A-Za-z0-9-]+$/'],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'frames' => ['required', 'array'],
            'frames.*' => ['nullable', 'integer', 'min:0', 'max:50'],
            'max_uses' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'expires_at' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
            // Event fields
            'event_code' => ['nullable', 'string', 'max:30', 'regex:/^[A-Za-z0-9-]+$/'],
            'event_name' => ['nullable', 'string', 'max:100'],
            'event_date' => ['nullable', 'date'],
            'number_of_people' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'total_cost' => ['nullable', 'integer', 'min:0'],
            'discount' => ['nullable', 'integer', 'min:0', 'max:100'],
        ];

        if ($request->input('type') === Voucher::TYPE_EVENT) {
            $rules['event_name'] = ['required', 'string', 'max:100'];
            $rules['event_date'] = ['required', 'date'];
            $rules['number_of_people'] = ['required', 'integer', 'min:1', 'max:10000'];
        }

        $data = $request->validate($rules);

        $type = $data['type'] ?? Voucher::TYPE_CASH;

        $code = Str::upper(trim((string) ($type === Voucher::TYPE_EVENT ? ($data['event_code'] ?? '') : ($data['code'] ?? ''))));
        if ($code === '') {
            $code = $this->generateCode();
        }

        $duplicate = Voucher::where('code', $code)
            ->when($voucher, fn ($query) => $query->where('id', '!=', $voucher->id))
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'code' => 'Kode voucher sudah dipakai. Gunakan kode lain atau kosongkan untuk dibuat otomatis.',
            ]);
        }

        $validSlugs = Frame::where('is_active', true)->pluck('slug')->all();
        $items = [];
        foreach ((array) ($data['frames'] ?? []) as $slug => $qty) {
            $qty = (int) $qty;
            if ($qty > 0 && in_array((string) $slug, $validSlugs, true)) {
                $items[(string) $slug] = $qty;
            }
        }

        if (! $items) {
            throw ValidationException::withMessages([
                'frames' => 'Pilih minimal satu frame dan tentukan jumlah cetaknya.',
            ]);
        }

        $result = [
            'code' => $code,
            'type' => $type,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'items' => $items,
            'max_uses' => (int) ($data['max_uses'] ?? 1),
            'expires_at' => ! empty($data['expires_at'])
                ? Carbon::parse($data['expires_at'])->endOfDay()
                : null,
            'is_active' => $request->boolean('is_active'),
            'discount' => (int) ($data['discount'] ?? 0),
        ];

        if ($type === Voucher::TYPE_EVENT) {
            $result['event_name'] = $data['event_name'];
            $result['event_date'] = ! empty($data['event_date'])
                ? Carbon::parse($data['event_date'])->endOfDay()
                : null;
            $result['number_of_people'] = (int) $data['number_of_people'];

            // Jumlah foto per voucher dihitung dari frame yang dipilih
            $photoCount = 0;
            foreach ($items as $slug => $qty) {
                $frame = Frame::where('slug', $slug)->where('is_active', true)->first();
                if ($frame) {
                    $photoCount += max(1, (int) $frame->photo_count) * (int) $qty;
                }
            }
            $result['photos_per_voucher'] = $photoCount > 0 ? $photoCount : 1;

            // Hitung total biaya event
            $costPerPerson = 0;
            foreach ($items as $slug => $qty) {
                $frame = Frame::where('slug', $slug)->where('is_active', true)->first();
                if ($frame) {
                    $costPerPerson += (int) $frame->price * (int) $qty;
                }
            }
            $result['total_cost'] = $costPerPerson * (int) $data['number_of_people'];
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
