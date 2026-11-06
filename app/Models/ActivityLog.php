<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Log aplikasi ini HANYA mencatat pembayaran yang dikonfirmasi.
 * Detail transaksi disimpan di kolom metadata (JSON):
 * metode, total, photo_count, items[{name, qty, price}], ip.
 */
class ActivityLog extends Model
{
    public const PAYMENT = 'pembayaran.confirmed';

    protected $fillable = [
        'session_id',
        'queue_number',
        'action',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function scopePayments($query)
    {
        return $query->where('action', self::PAYMENT);
    }

    public function scopeForSession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    public function getPaymentTotalAttribute(): int
    {
        return (int) ($this->metadata['total'] ?? 0);
    }

    public function getPaymentMethodAttribute(): string
    {
        return (string) ($this->metadata['metode'] ?? '-');
    }

    public function getPaymentPhotoCountAttribute(): int
    {
        return (int) ($this->metadata['photo_count'] ?? 0);
    }

    /** Contoh: "K-pop Frame x2, Neon Frame x1" */
    public function getPaymentItemsLabelAttribute(): string
    {
        $items = $this->metadata['items'] ?? [];

        if (! is_array($items) || ! $items) {
            return '-';
        }

        return collect($items)
            ->map(fn ($i) => ($i['name'] ?? '?').' x'.($i['qty'] ?? 1))
            ->implode(', ');
    }
}
