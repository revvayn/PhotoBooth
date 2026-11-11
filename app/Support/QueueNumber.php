<?php

namespace App\Support;

use App\Models\QueueCounter;
use Illuminate\Support\Facades\DB;

/**
 * Nomor antrian harian dengan format DDMMYY-XXX, misalnya #101126-001.
 * Angka terakhir berurutan per hari supaya mudah dibaca dan dicari.
 */
class QueueNumber
{
    public static function next(): string
    {
        $now = now();
        $date = $now->toDateString();

        $sequence = DB::transaction(function () use ($date) {
            DB::table('queue_counters')->insertOrIgnore([
                'date' => $date,
                'last_number' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $counter = QueueCounter::where('date', $date)->lockForUpdate()->firstOrFail();
            $counter->increment('last_number');

            return (int) QueueCounter::whereKey($counter->getKey())->value('last_number');
        });

        return '#'.$now->format('dmy').'-'.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
    }
}
