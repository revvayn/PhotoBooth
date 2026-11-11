<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QueueCounter extends Model
{
    protected $fillable = [
        'date',
        'last_number',
    ];

    protected $casts = [
        'date' => 'date',
        'last_number' => 'integer',
    ];
}
