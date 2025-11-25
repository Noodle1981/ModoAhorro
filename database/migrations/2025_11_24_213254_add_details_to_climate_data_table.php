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
            $table->decimal('cooling_degree_days', 8, 2)->nullable()->after('temp_avg');
            $table->decimal('heating_degree_days', 8, 2)->nullable()->after('cooling_degree_days');
            $table->decimal('precipitation_mm', 8, 2)->nullable()->after('heating_degree_days');
            $table->decimal('wind_speed_kmh', 8, 2)->nullable()->after('precipitation_mm');
            $table->integer('humidity_percent')->nullable()->after('wind_speed_kmh');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('climate_data', function (Blueprint $table) {
            $table->dropColumn([
                'cooling_degree_days',
                'heating_degree_days',
                'precipitation_mm',
                'wind_speed_kmh',
                'humidity_percent'
            ]);
        });
    }
};
