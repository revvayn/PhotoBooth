<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sticker extends Model
{
    protected $fillable = [
        'name',
        'type',
        'emoji',
        'image_path',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return '/storage/'.$this->image_path;
    }

    /**
     * Nilai yang digambar di kanvas: teks emoji atau URL gambar.
     */
    public function getDisplayAttribute(): ?string
    {
        return $this->type === 'image' ? $this->image_url : $this->emoji;
    }
}
