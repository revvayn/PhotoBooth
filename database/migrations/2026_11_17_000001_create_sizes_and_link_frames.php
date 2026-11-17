<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sizes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->decimal('width_cm', 6, 2);
            $table->decimal('height_cm', 6, 2);
            $table->unsignedInteger('price')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();
        $defaults = [
            ['name' => '2R', 'width_cm' => 6.00, 'height_cm' => 9.00, 'price' => 25000, 'sort_order' => 1],
            ['name' => '4R', 'width_cm' => 10.20, 'height_cm' => 15.20, 'price' => 30000, 'sort_order' => 2],
            ['name' => 'A4', 'width_cm' => 21.00, 'height_cm' => 29.70, 'price' => 35000, 'sort_order' => 3],
        ];

        foreach ($defaults as $size) {
            DB::table('sizes')->insert($size + [
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        Schema::table('frames', function (Blueprint $table) {
            $table->unsignedBigInteger('size_id')->nullable()->after('category')->index();
            // Harga frame kini opsional: kosong berarti mengikuti harga ukuran.
            $table->unsignedInteger('price')->nullable()->default(null)->change();
        });

        // Frame lama yang kategorinya cocok langsung memakai harga ukuran.
        // Harga frame dibiarkan bisa diisi lagi oleh admin sebagai override.
        foreach ($defaults as $size) {
            $sizeId = DB::table('sizes')->where('name', $size['name'])->value('id');
            if (! $sizeId) {
                continue;
            }

            DB::table('frames')
                ->where('category', $size['name'])
                ->update(['size_id' => $sizeId, 'price' => null]);
        }
    }

    public function down(): void
    {
        // Harga frame harus kembali wajib sebelum kolom diubah.
        if (Schema::hasTable('sizes') && Schema::hasColumn('frames', 'size_id')) {
            $inherited = DB::table('frames')
                ->join('sizes', 'sizes.id', '=', 'frames.size_id')
                ->whereNull('frames.price')
                ->select('frames.id', 'sizes.price as size_price')
                ->get();

            foreach ($inherited as $row) {
                DB::table('frames')->where('id', $row->id)->update(['price' => $row->size_price]);
            }
        }

        DB::table('frames')->whereNull('price')->update(['price' => 30000]);

        Schema::table('frames', function (Blueprint $table) {
            $table->unsignedInteger('price')->nullable(false)->default(30000)->change();
            $table->dropColumn('size_id');
        });

        Schema::dropIfExists('sizes');
    }
};
