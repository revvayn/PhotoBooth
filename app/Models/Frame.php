<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Frame extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'image_path',
        'photo_count',
        'price',
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

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return '/storage/'.$this->image_path;
    }
}
