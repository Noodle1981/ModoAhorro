<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('entities', function (Blueprint $table) {
            // Business hours for office/trade entities
            $table->time('opens_at')->nullable()->after('people_count');
            $table->time('closes_at')->nullable()->after('opens_at');
            $table->json('operating_days')->nullable()->after('closes_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entities', function (Blueprint $table) {
            $table->dropColumn(['opens_at', 'closes_at', 'operating_days']);
        });
    }
};
