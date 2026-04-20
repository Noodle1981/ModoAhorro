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
            $table->timestamp('calibrated_at')->nullable()->after('total_amount');
            // Aseguramos que recommended_kwh existe (podría estar en otra migración, pero por seguridad)
            if (!Schema::hasColumn('invoices', 'recommended_kwh')) {
                $table->decimal('recommended_kwh', 10, 3)->nullable()->after('calibrated_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['calibrated_at']);
        });
    }
};
