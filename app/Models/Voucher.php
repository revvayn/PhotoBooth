<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'items',
        'max_uses',
        'used_count',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'items' => 'array',
        'max_uses' => 'integer',
        'used_count' => 'integer',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isExhausted(): bool
    {
        return $this->max_uses > 0 && $this->used_count >= $this->max_uses;
    }

    public function isUsable(): bool
    {
        return $this->is_active && ! $this->isExpired() && ! $this->isExhausted();
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
