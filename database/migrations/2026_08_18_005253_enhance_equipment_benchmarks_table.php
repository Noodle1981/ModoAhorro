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
        Schema::table('equipment_benchmarks', function (Blueprint $table) {
            $table->foreignId('equipment_type_id')->nullable()->after('category_id')->constrained('equipment_types')->nullOnDelete();
            $table->decimal('efficiency_gain_factor', 5, 2)->default(0.30)->after('watts');
            $table->decimal('average_market_price', 12, 2)->default(0)->after('efficiency_gain_factor');
            $table->string('meli_search_term')->nullable()->after('average_market_price');
            $table->string('affiliate_link')->nullable()->after('meli_search_term');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_benchmarks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('equipment_type_id');
            $table->dropColumn([
                'efficiency_gain_factor',
                'average_market_price',
                'meli_search_term',
                'affiliate_link',
            ]);
        });
    }
};
