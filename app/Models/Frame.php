<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Frame extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'image_path',
        'photo_count',
        'price',
        'size_id',
        'slots',
        'category',
        'color_class',
        'border_class',
        'accent_class',
        'caption',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'photo_count' => 'integer',
        'price' => 'integer',
        'slots' => 'array',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class);
    }

    /**
     * Harga efektif: override frame bila diisi, selain itu harga ukuran.
     */
    public function effectivePrice(): int
    {
        if ($this->price !== null) {
            return (int) $this->price;
        }

        return (int) ($this->size?->price ?? 0);
    }

    public function getSizeLabelAttribute(): string
    {
        return $this->size?->name ?? (string) $this->category;
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return '/storage/'.$this->image_path;
    }

    /**
     * Apakah gambar frame saat ini masih sesuai rasio ukurannya?
     * Null bila belum ada gambar/ukuran atau file tidak terbaca.
     */
    public function imageMatchesSize(): ?bool
    {
        if (! $this->size || ! $this->image_path) {
            return null;
        }

        $path = Storage::disk('public')->path($this->image_path);
        if (! is_file($path)) {
            return null;
        }

        $info = @getimagesize($path);
        if (! $info) {
            return null;
        }

        return $this->size->matchesDimensions((int) $info[0], (int) $info[1]);
    }
}
