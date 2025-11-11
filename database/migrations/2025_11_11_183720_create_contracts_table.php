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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained('entities')->onDelete('cascade');
            $table->foreignId('supply_id')->constrained('supplies')->onDelete('cascade');
            $table->foreignId('utility_company_id')->constrained('utility_companies')->onDelete('cascade');
            $table->string('contract_identifier')->nullable();
            $table->string('rate_name');
            $table->decimal('contracted_power_kw_p1', 8, 3)->nullable();
            $table->decimal('contracted_power_kw_p2', 8, 3)->nullable();
            $table->decimal('contracted_power_kw_p3', 8, 3)->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
