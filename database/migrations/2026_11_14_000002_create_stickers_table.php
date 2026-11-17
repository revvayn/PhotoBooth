<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stickers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('emoji')->comment('emoji atau image');
            $table->string('emoji', 32)->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Stiker bawaan supaya palet kiosk tidak kosong pada instalasi baru.
        // Admin tetap bisa menambah, menonaktifkan, atau menghapusnya.
        $now = now();
        $defaults = [
            ['Ceria', '😀'],
            ['Ngakak', '😂'],
            ['Cinta', '😍'],
            ['Sayang', '🥰'],
            ['Keren', '😎'],
            ['Bintang', '🤩'],
            ['Party', '🎉'],
            ['Hati', '💖'],
            ['Kilau', '✨'],
            ['Bunga', '🌸'],
            ['Unicorn', '🦄'],
            ['Semangat', '🔥'],
        ];

        DB::table('stickers')->insert(array_map(
            fn (array $item, int $index) => [
                'name' => $item[0],
                'type' => 'emoji',
                'emoji' => $item[1],
                'image_path' => null,
                'is_active' => true,
                'sort_order' => $index,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            $defaults,
            array_keys($defaults)
        ));
    }

    public function down(): void
    {
        Schema::dropIfExists('stickers');
    }
};
