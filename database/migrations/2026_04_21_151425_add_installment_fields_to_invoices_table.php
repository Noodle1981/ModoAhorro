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
            $table->integer('installment_number')->nullable()->after('invoice_number');
            $table->integer('total_installments')->nullable()->default(2)->after('installment_number');
            $table->decimal('bimonthly_consumption_kwh', 10, 3)->nullable()->after('total_energy_consumed_kwh');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['installment_number', 'total_installments', 'bimonthly_consumption_kwh']);
        });
    }
};
