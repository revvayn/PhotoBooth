<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->string('event_batch')->nullable()->after('event_name');
            $table->string('event_code')->nullable()->after('event_batch');
            $table->unsignedInteger('duration_hours')->nullable()->after('number_of_people');
            $table->unsignedInteger('cost_per_hour')->nullable()->after('duration_hours');
        });
    }

    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn(['event_batch', 'event_code', 'duration_hours', 'cost_per_hour']);
        });
    }
};