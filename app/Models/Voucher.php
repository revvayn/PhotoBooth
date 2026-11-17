<?php

namespace App\Models;

use App\Models\ActivityLog;
use App\Models\Frame;
use App\Models\Size;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'items',
        'type',
        'event_name',
        'event_batch',
        'event_code',
        'pricing_mode',
        'event_date',
        'start_time',
        'end_time',
        'duration_hours',
        'cost_per_hour',
        'photos_per_voucher',
        'number_of_people',
        'total_cost',
        'discount',
        'max_uses',
        'used_count',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'items' => 'array',
        'max_uses' => 'integer',
        'used_count' => 'integer',
        'event_date' => 'date',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'photos_per_voucher' => 'integer',
        'number_of_people' => 'integer',
        'duration_hours' => 'integer',
        'cost_per_hour' => 'integer',
        'total_cost' => 'integer',
        'discount' => 'integer',
    ];

    protected static function booted(): void
    {
        static::created(function (Voucher $voucher): void {
            $items = collect($voucher->itemsDetailed())
                ->map(fn (array $item) => [
                    'name' => ! empty($item['dimension_label'])
                        ? $item['name'].' ('.$item['dimension_label'].')'
                        : $item['name'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                ])
                ->values()
                ->all();

            $total = $voucher->isEvent()
                ? (int) $voucher->total_cost
                : (int) $voucher->cost_per_person;

            ActivityLog::create([
                'session_id' => 'voucher:'.$voucher->id,
                'queue_number' => $voucher->code,
                'action' => ActivityLog::PAYMENT,
                'metadata' => [
                    'metode' => 'voucher',
                    'total' => $total,
                    'photo_count' => (int) $voucher->print_count,
                    'items' => $items,
                    'ip' => request()->ip(),
                    'voucher_code' => $voucher->code,
                ],
            ]);
        });
    }

    public const TYPE_CASH = 'cash';
    public const TYPE_EVENT = 'event';

    public function scopeCash($query)
    {
        return $query->where('type', self::TYPE_CASH);
    }

    public function scopeEvent($query)
    {
        return $query->where('type', self::TYPE_EVENT);
    }

    public function isEvent(): bool
    {
        return $this->type === self::TYPE_EVENT;
    }

    public function isCash(): bool
    {
        return $this->type === self::TYPE_CASH;
    }

    public function isSizeMode(): bool
    {
        $items = is_array($this->items) ? $this->items : [];
        if (! $items) {
            return false;
        }

        foreach (array_keys($items) as $key) {
            if (! ctype_digit((string) $key) || ! Size::where('id', (int) $key)->exists()) {
                return false;
            }
        }

        return true;
    }

    public function isFrameMode(): bool
    {
        return ! $this->isSizeMode() && ! empty($this->items);
    }

    /**
     * Normalisasi isi voucher menjadi [size_id => jumlah cetak].
     * Format lama {frame_slug: qty} tetap dibaca agar data lama tidak hilang.
     */
    public function sizeItems(): array
    {
        $raw = is_array($this->items) ? $this->items : [];
        if (! $raw) {
            return [];
        }

        // Mode ukuran: items sudah berisi size_id.
        if ($this->isSizeMode()) {
            $items = [];
            foreach ($raw as $key => $qty) {
                $qty = (int) $qty;
                if ($qty > 0 && ctype_digit((string) $key)) {
                    $items[(int) $key] = ($items[(int) $key] ?? 0) + $qty;
                }
            }

            return $items;
        }

        // Mode frame: konversi frame ke ukuran (untuk keperluan kuartal/label).
        $sizes = Size::query()->get(['id', 'name'])->keyBy('id');
        $items = [];

        foreach ($raw as $key => $qty) {
            $qty = (int) $qty;
            if ($qty <= 0) {
                continue;
            }

            $frame = Frame::where('slug', (string) $key)->first(['size_id', 'category']);
            $sizeId = $frame?->size_id
                ?? optional($sizes->firstWhere('name', $frame?->category))->id;

            if ($sizeId) {
                $items[(int) $sizeId] = ($items[(int) $sizeId] ?? 0) + $qty;
            }
        }

        return $items;
    }

    /**
     * Rincian ukuran voucher: nama, dimensi, harga, jumlah, dan subtotal.
     */
    public function sizeItemsDetailed(): array
    {
        $items = $this->sizeItems();
        if (! $items) {
            return [];
        }

        $sizes = Size::whereIn('id', array_keys($items))->get()->keyBy('id');

        $rows = [];
        foreach ($items as $sizeId => $qty) {
            $size = $sizes->get($sizeId);
            if (! $size) {
                continue;
            }

            $rows[] = [
                'size_id' => $size->id,
                'name' => $size->name,
                'dimension_label' => $size->dimension_label,
                'price' => (int) $size->price,
                'qty' => (int) $qty,
                'subtotal' => (int) $size->price * (int) $qty,
            ];
        }

        return $rows;
    }

    /**
     * Total jumlah cetak seluruh ukuran.
     */
    public function getPrintCountAttribute(): int
    {
        return (int) array_sum($this->sizeItems());
    }

    /**
     * Hitung total biaya per orang berdasarkan ukuran x jumlah cetak.
     */
    public function getCostPerPersonAttribute(): int
    {
        if ($this->isFrameMode()) {
            $items = is_array($this->items) ? $this->items : [];
            $frames = Frame::whereIn('slug', array_keys($items))->get()->keyBy('slug');

            $total = 0;
            foreach ($items as $slug => $qty) {
                $frame = $frames->get($slug);
                if ($frame) {
                    $total += (int) $frame->effectivePrice() * (int) $qty;
                }
            }

            return $total;
        }

        return (int) array_sum(array_column($this->sizeItemsDetailed(), 'subtotal'));
    }

    /**
     * Hitung total biaya seluruh event (per orang x jumlah orang).
     */
    public function getTotalEventCostAttribute(): int
    {
        return $this->cost_per_person * (int) $this->number_of_people;
    }

    /**
     * Biaya per orang setelah diskon, dibulatkan ke bawah ke kelipatan 1000.
     */
    public function getFinalCostPerPersonAttribute(): int
    {
        $original = $this->cost_per_person;
        $discount = (int) $this->discount;

        if ($discount <= 0) {
            return $original;
        }

        $discounted = (int) floor($original * (1 - $discount / 100));

        if ($discounted >= 1000) {
            return (int) (floor($discounted / 1000) * 1000);
        }

        return $discounted;
    }

    /**
     * Total biaya (semua orang) setelah diskon, dibulatkan ke bawah ke kelipatan 1000.
     */
    public function getFinalCostAttribute(): int
    {
        if ($this->isEvent()) {
            $original = (int) $this->total_cost;
        } else {
            $original = $this->cost_per_person;
        }

        $discount = (int) $this->discount;

        if ($discount <= 0) {
            return $original;
        }

        $discounted = (int) floor($original * (1 - $discount / 100));

        if ($discounted >= 1000) {
            return (int) (floor($discounted / 1000) * 1000);
        }

        return $discounted;
    }

    /**
     * Label tipe voucher untuk ditampilkan di admin.
     */
    public function getTypeLabelAttribute(): string
    {
        return $this->isEvent() ? 'Event' : 'Cash';
    }

    /**
     * Label badge warna untuk tipe voucher.
     */
    public function getTypeBadgeClassAttribute(): string
    {
        return $this->isEvent() ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function eventWindow(): array
    {
        if (! $this->isEvent() || ! $this->event_date) {
            return [null, null];
        }

        $start = $this->event_date->copy()->startOfDay();
        $end = null;

        if ($this->start_time) {
            [$hour, $minute] = array_pad(explode(':', $this->start_time), 2, 0);
            $start = $this->event_date->copy()->setTime((int) $hour, (int) $minute);
        }

        if ($this->end_time) {
            [$hour, $minute] = array_pad(explode(':', $this->end_time), 2, 0);
            $end = $this->event_date->copy()->setTime((int) $hour, (int) $minute);
            if ($end->lessThanOrEqualTo($start)) {
                $end = $end->addDay();
            }
        } elseif ((int) $this->duration_hours > 0) {
            $end = $start->copy()->addHours((int) $this->duration_hours);
        }

        return [$start, $end];
    }

    public function isExpired(): bool
    {
        if ($this->isEvent() && $this->event_date && ($this->start_time || $this->end_time || (int) $this->duration_hours > 0)) {
            [, $end] = $this->eventWindow();
            return $end !== null && now()->gt($end);
        }

        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isExhausted(): bool
    {
        return $this->max_uses > 0 && $this->used_count >= $this->max_uses;
    }

    public function isUsable(): bool
    {
        if (! $this->is_active || $this->isExpired() || $this->isExhausted()) {
            return false;
        }

        if ($this->isEvent() && $this->event_date && ($this->start_time || $this->end_time || (int) $this->duration_hours > 0)) {
            [$start] = $this->eventWindow();
            if ($start !== null && now()->lt($start)) {
                return false;
            }
        }

        return true;
    }

    public function getRemainingUsesAttribute(): ?int
    {
        if ($this->max_uses < 1) {
            return null;
        }

        return max(0, $this->max_uses - $this->used_count);
    }

    /**
     * Label ringkas untuk tabel admin: "4R x2, A4 x1".
     */
    public function getItemsLabelAttribute(): string
    {
        if ($this->isFrameMode()) {
            $items = is_array($this->items) ? $this->items : [];
            $frames = Frame::whereIn('slug', array_keys($items))->pluck('name', 'slug');

            return collect($items)
                ->map(fn ($qty, $slug) => ($frames[$slug] ?? $slug).' x'.(int) $qty)
                ->implode(', ');
        }

        $items = $this->sizeItemsDetailed();

        if (! $items) {
            return '-';
        }

        return collect($items)
            ->map(fn (array $item) => $item['name'].' x'.$item['qty'])
            ->implode(', ');
    }

    /**
     * Perkiraan jumlah foto: total lembar cetak yang dipesan.
     * Frame baru dipilih pelanggan di kiosk, jadi angka ini bukan hitungan
     * slot foto per frame.
     */
    public function itemsDetailed(): array
    {
        $raw = is_array($this->items) ? $this->items : [];
        if (! $raw) {
            return [];
        }

        if ($this->isFrameMode()) {
            $frames = Frame::whereIn('slug', array_keys($raw))->with('size')->get()->keyBy('slug');

            $rows = [];
            foreach ($raw as $slug => $qty) {
                $frame = $frames->get($slug);
                $rows[] = [
                    'type' => 'frame',
                    'slug' => $slug,
                    'size_id' => $frame?->size?->id ?? $frame?->size_id,
                    'name' => $frame?->name ?? $slug,
                    'dimension_label' => $frame?->size?->dimension_label,
                    'price' => (int) ($frame?->effectivePrice() ?? 0),
                    'qty' => (int) $qty,
                    'photo_count' => max(1, (int) ($frame?->photo_count ?? 0)),
                    'subtotal' => (int) ($frame?->effectivePrice() ?? 0) * (int) $qty,
                ];
            }

            return $rows;
        }

        return array_map(function (array $row) {
            return array_merge($row, [
                'type' => 'size',
                'slug' => null,
                'photo_count' => 1,
                'subtotal' => $row['subtotal'] ?? ((int) $row['price'] * (int) $row['qty']),
            ]);
        }, $this->sizeItemsDetailed());
    }
}
