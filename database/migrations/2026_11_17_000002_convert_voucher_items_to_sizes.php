<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Voucher kini menyimpan ukuran + jumlah cetak, bukan frame.
     * Format lama {frame_slug: qty} dikonversi ke {size_id: qty}.
     */
    public function up(): void
    {
        $sizes = DB::table('sizes')->pluck('name', 'id');
        if ($sizes->isEmpty()) {
            return;
        }

        $vouchers = DB::table('vouchers')->select('id', 'items')->orderBy('id')->get();

        foreach ($vouchers as $voucher) {
            $items = json_decode((string) $voucher->items, true);
            if (! is_array($items) || ! $items) {
                continue;
            }

            $converted = [];
            $changed = false;

            foreach ($items as $key => $qty) {
                $qty = (int) $qty;
                if ($qty <= 0) {
                    $changed = true;
                    continue;
                }

                $sizeId = null;

                if (ctype_digit((string) $key) && $sizes->has((int) $key)) {
                    $sizeId = (int) $key;
                } else {
                    $frame = DB::table('frames')->where('slug', (string) $key)->first();
                    if ($frame) {
                        if (! empty($frame->size_id) && $sizes->has((int) $frame->size_id)) {
                            $sizeId = (int) $frame->size_id;
                        } else {
                            $matching = $sizes->search($frame->category);
                            $sizeId = $matching !== false ? (int) $matching : null;
                        }
                    }
                }

                if (! $sizeId) {
                    $changed = true;
                    continue;
                }

                if ((string) $key !== (string) $sizeId) {
                    $changed = true;
                }

                $converted[$sizeId] = ($converted[$sizeId] ?? 0) + $qty;
            }

            if ($changed) {
                DB::table('vouchers')->where('id', $voucher->id)->update([
                    'items' => json_encode($converted),
                ]);
            }
        }
    }

    /**
     * Kembalikan ke format lama memakai frame pertama dari masing-masing ukuran.
     */
    public function down(): void
    {
        $vouchers = DB::table('vouchers')->select('id', 'items')->orderBy('id')->get();

        foreach ($vouchers as $voucher) {
            $items = json_decode((string) $voucher->items, true);
            if (! is_array($items) || ! $items) {
                continue;
            }

            $converted = [];
            $changed = false;

            foreach ($items as $key => $qty) {
                $qty = (int) $qty;
                if ($qty <= 0) {
                    continue;
                }

                $slug = (string) $key;

                if (ctype_digit($slug)) {
                    $frame = DB::table('frames')
                        ->where('size_id', (int) $key)
                        ->orderBy('id')
                        ->first();

                    if ($frame) {
                        $slug = $frame->slug;
                        $changed = true;
                    }
                }

                $converted[$slug] = ($converted[$slug] ?? 0) + $qty;
            }

            if ($changed) {
                DB::table('vouchers')->where('id', $voucher->id)->update([
                    'items' => json_encode($converted),
                ]);
            }
        }
    }
};
