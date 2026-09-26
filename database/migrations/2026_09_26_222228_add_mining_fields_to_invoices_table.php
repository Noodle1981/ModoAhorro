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
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('source_type')->nullable()->after('tariff');
            $table->string('shift_code')->nullable()->after('source_type');
            $table->decimal('demand_kw_peak', 10, 2)->nullable()->after('shift_code');
            $table->decimal('generator_hours', 8, 2)->nullable()->after('demand_kw_peak');
            $table->unsignedInteger('occupancy_count')->nullable()->after('generator_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'source_type',
                'shift_code',
                'demand_kw_peak',
                'generator_hours',
                'occupancy_count',
            ]);
        });
    }
};
