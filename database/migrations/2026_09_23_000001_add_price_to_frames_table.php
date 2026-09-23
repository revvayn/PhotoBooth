<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('frames', function (Blueprint $table) {
            $table->unsignedInteger('price')->default(30000)->after('photo_count');
        });
    }

    public function down(): void
    {
        Schema::table('frames', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};