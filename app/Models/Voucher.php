<?php

namespace App\Models;

use App\Models\ActivityLog;
use App\Models\Frame;
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
            $frames = Frame::whereIn('slug', array_keys(is_array($voucher->items) ? $voucher->items : []))
                ->get()
                ->keyBy('slug');

            $items = collect(is_array($voucher->items) ? $voucher->items : [])
                ->map(fn ($qty, $slug) => [
                    'name' => $frames->get($slug)?->name ?? $slug,
                    'qty' => (int) $qty,
                    'price' => (int) ($frames->get($slug)?->price ?? 0),
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
                    'photo_count' => (int) $voucher->photo_count,
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

    /**
     * Hitung total biaya per orang berdasarkan items (frame slug => qty).
     */
    public function getCostPerPersonAttribute(): int
    {
        $items = is_array($this->items) ? $this->items : [];
        if (! $items) {
            return 0;
        }

        $frames = Frame::whereIn('slug', array_keys($items))->get()->keyBy('slug');

        $total = 0;
        foreach ($items as $slug => $qty) {
            $frame = $frames->get($slug);
            if ($frame) {
                $total += (int) $frame->price * (int) $qty;
            }
        }

        return $total;
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
     * Label ringkas untuk tabel admin: "K-pop Frame x2, Neon Frame x1".
     */
    public function getItemsLabelAttribute(): string
    {
        $items = is_array($this->items) ? $this->items : [];

        if (! $items) {
            return '-';
        }

        $frames = Frame::whereIn('slug', array_keys($items))->pluck('name', 'slug');

        return collect($items)
            ->map(fn ($qty, $slug) => ($frames[$slug] ?? $slug).' x'.(int) $qty)
            ->implode(', ');
    }

    public function getPhotoCountAttribute(): int
    {
        $items = is_array($this->items) ? $this->items : [];
        $frames = Frame::whereIn('slug', array_keys($items))->get()->keyBy('slug');

        return (int) collect($items)->map(function ($qty, $slug) use ($frames) {
            $frame = $frames->get($slug);

            return $frame ? max(1, (int) $frame->photo_count) : 0;
        })->sum();
    }
}
