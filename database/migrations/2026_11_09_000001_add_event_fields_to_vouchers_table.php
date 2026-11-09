<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->string('type')->default('cash')->after('is_active')->comment('cash atau event');
            $table->string('event_name')->nullable()->after('type');
            $table->timestamp('event_date')->nullable()->after('event_name');
            $table->unsignedInteger('photos_per_voucher')->nullable()->after('event_date');
            $table->unsignedInteger('number_of_people')->nullable()->after('photos_per_voucher');
            $table->unsignedInteger('total_cost')->nullable()->after('number_of_people');
        });
    }

    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'event_name',
                'event_date',
                'photos_per_voucher',
                'number_of_people',
                'total_cost',
            ]);
        });
    }
};
