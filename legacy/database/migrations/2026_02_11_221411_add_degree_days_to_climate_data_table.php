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
        if (!Schema::hasColumn('climate_data', 'cooling_degree_days')) {
            Schema::table('climate_data', function (Blueprint $table) {
                $table->decimal('cooling_degree_days', 8, 2)->nullable();
            });
        }

        if (!Schema::hasColumn('climate_data', 'heating_degree_days')) {
            Schema::table('climate_data', function (Blueprint $table) {
                $table->decimal('heating_degree_days', 8, 2)->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('climate_data', function (Blueprint $table) {
            $table->dropColumn(['cooling_degree_days', 'heating_degree_days']);
        });
    }
};
