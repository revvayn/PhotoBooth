<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('frames', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('slug');
            $table->unsignedTinyInteger('photo_count')->nullable()->after('image_path');
            $table->json('slots')->nullable()->after('photo_count');
        });
    }

    public function down(): void
    {
        Schema::table('frames', function (Blueprint $table) {
            $table->dropColumn(['image_path', 'photo_count', 'slots']);
        });
    }
};
