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
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('contract_number')->nullable()->change();
            $table->foreignId('utility_company_id')->nullable()->change();
            $table->string('meter_number')->nullable()->change();
            $table->string('client_number')->nullable()->change();
            $table->string('tariff_type')->nullable()->change();
            $table->date('start_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('contract_number')->nullable(false)->change();
            $table->foreignId('utility_company_id')->nullable(false)->change();
            $table->string('meter_number')->nullable(false)->change();
            $table->string('client_number')->nullable(false)->change();
            $table->string('tariff_type')->nullable(false)->change();
            $table->date('start_date')->nullable(false)->change();
        });
    }
};
