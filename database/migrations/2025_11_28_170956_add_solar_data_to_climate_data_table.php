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
        Schema::table('climate_data', function (Blueprint $table) {
            $table->integer('cloudcover_mean')->nullable()->after('humidity_percent');
            $table->float('sunshine_duration')->nullable()->after('cloudcover_mean'); // seconds
            $table->float('shortwave_radiation_sum')->nullable()->after('sunshine_duration'); // MJ/m²
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('climate_data', function (Blueprint $table) {
            $table->dropColumn(['cloudcover_mean', 'sunshine_duration', 'shortwave_radiation_sum']);
        });
    }
};
