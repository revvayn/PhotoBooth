<?php

use App\Services\FrameAnalyzer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Segarkan slot frame lama: versi sebelumnya menyimpan bounding box yang
     * di-inset sehingga foto tidak pernah mengisi lubang sampai tepi. Slot baru
     * memakai bounding box persis; bentuk melengkung dipotong di klien dari
     * alpha PNG frame.
     */
    public function up(): void
    {
        if (! Schema::hasTable('frames')) {
            return;
        }

        $analyzer = app(FrameAnalyzer::class);

        DB::table('frames')
            ->whereNotNull('image_path')
            ->orderBy('id')
            ->chunkById(20, function ($frames) use ($analyzer) {
                foreach ($frames as $frame) {
                    try {
                        $path = Storage::disk('public')->path($frame->image_path);
                        if (! is_file($path)) {
                            continue;
                        }
                        $slots = $analyzer->analyzePng($path);
                    } catch (Throwable) {
                        // Frame tanpa PNG valid dibiarkan memakai slot lamanya.
                        continue;
                    }

                    DB::table('frames')->where('id', $frame->id)->update([
                        'slots' => json_encode($slots),
                        'photo_count' => count($slots),
                        'updated_at' => now(),
                    ]);
                }
            });
    }

    public function down(): void
    {
        // Data slot tidak dikembalikan; versi lama hanya mengecilkan foto.
    }
};
