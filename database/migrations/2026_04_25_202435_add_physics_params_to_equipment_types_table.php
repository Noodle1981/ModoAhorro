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
        Schema::table('equipment_types', function (Blueprint $table) {
            $table->boolean('is_thermal_sensitive')->default(false)->after('is_climatization');
            $table->decimal('base_efficiency_ratio', 5, 2)->default(1.0)->after('efficiency');
            $table->decimal('thermal_efficiency_penalty', 5, 2)->default(0.0)->after('base_efficiency_ratio');
            $table->integer('default_tank')->default(3)->after('thermal_efficiency_penalty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_types', function (Blueprint $table) {
            //
        });
    }
};
