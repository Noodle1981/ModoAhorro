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
        if (!Schema::hasColumn('equipment_usages', 'kwh_reconciled')) {
            Schema::table('equipment_usages', function (Blueprint $table) {
                $table->decimal('kwh_reconciled', 10, 4)->nullable();
                $table->tinyInteger('tank_assignment')->nullable();
                $table->json('audit_logs')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_usages', function (Blueprint $table) {
            $table->dropColumn(['kwh_reconciled', 'tank_assignment', 'audit_logs']);
        });
    }
};
