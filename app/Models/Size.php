<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Size extends Model
{
    protected $fillable = [
        'name',
        'width_cm',
        'height_cm',
        'price',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'width_cm' => 'float',
        'height_cm' => 'float',
        'price' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function frames(): HasMany
    {
        return $this->hasMany(Frame::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getAspectRatioAttribute(): float
    {
        return $this->height_cm > 0 ? $this->width_cm / $this->height_cm : 1.0;
    }

    public function getDimensionLabelAttribute(): string
    {
        return $this->formatCm($this->width_cm).' × '.$this->formatCm($this->height_cm).' cm';
    }

    /**
     * Rasio gambar harus sama dengan rasio ukuran kertas (toleransi 2,5%).
     */
    public function matchesDimensions(int $widthPx, int $heightPx, float $tolerance = 0.025): bool
    {
        if ($widthPx <= 0 || $heightPx <= 0 || $this->height_cm <= 0 || $this->width_cm <= 0) {
            return false;
        }

        $expected = $this->width_cm / $this->height_cm;
        $actual = $widthPx / $heightPx;

        return abs($actual - $expected) / $expected <= $tolerance;
    }

    public function templateFileName(): string
    {
        $name = preg_replace('/[^a-z0-9]+/i', '-', $this->name) ?: 'ukuran';

        return 'template-'.strtolower(trim($name, '-')).'-'
            .$this->formatCm($this->width_cm).'x'.$this->formatCm($this->height_cm).'cm.png';
    }

    private function formatCm(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.') ?: '0';
    }
}
