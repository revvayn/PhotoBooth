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
    public function index()
    {
        $vouchers = Voucher::orderByDesc('created_at')->paginate(15);

        return view('admin.vouchers.index', compact('vouchers'));
    }

    public function create()
    {
        $frames = Frame::orderBy('sort_order')->orderBy('name')->get();

        return view('admin.vouchers.create', compact('frames'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $voucher = Voucher::create($data);

        return redirect()
            ->route('admin.vouchers.index')
            ->with('success', 'Voucher '.$voucher->code.' berhasil dibuat.');
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
     * Normalisasi input admin menjadi satu baris voucher:
     * frames[slug] = qty  ->  items { slug: qty }.
     */
    private function validated(Request $request, ?Voucher $voucher = null): array
    {
        $data = $request->validate([
            'code' => ['nullable', 'string', 'max:30', 'regex:/^[A-Za-z0-9-]+$/'],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'frames' => ['required', 'array'],
            'frames.*' => ['nullable', 'integer', 'min:0', 'max:50'],
            'max_uses' => ['required', 'integer', 'min:1', 'max:100000'],
            'expires_at' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $code = Str::upper(trim((string) ($data['code'] ?? '')));
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

        return [
            'code' => $code,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'items' => $items,
            'max_uses' => (int) $data['max_uses'],
            // Input tanggal berarti voucher masih berlaku sampai akhir hari itu.
            'expires_at' => ! empty($data['expires_at'])
                ? Carbon::parse($data['expires_at'])->endOfDay()
                : null,
            'is_active' => $request->boolean('is_active'),
        ];
    }

    private function generateCode(): string
    {
        do {
            $code = 'PB-'.Str::upper(Str::random(6));
        } while (Voucher::where('code', $code)->exists());

        return $code;
    }
}
