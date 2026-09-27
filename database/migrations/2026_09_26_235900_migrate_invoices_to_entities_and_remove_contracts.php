<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        // 1. Agregar entity_id a invoices si no existe
        if (! Schema::hasColumn('invoices', 'entity_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->foreignId('entity_id')->nullable()->after('id');
            });
        }

        // 2. Migrar entity_id desde contracts a invoices si contracts existe
        if (Schema::hasTable('contracts') && Schema::hasColumn('invoices', 'contract_id')) {
            if (DB::getDriverName() === 'sqlite') {
                DB::statement('UPDATE invoices SET entity_id = (SELECT entity_id FROM contracts WHERE contracts.id = invoices.contract_id) WHERE entity_id IS NULL');
            } else {
                DB::statement('UPDATE invoices INNER JOIN contracts ON invoices.contract_id = contracts.id SET invoices.entity_id = contracts.entity_id WHERE invoices.entity_id IS NULL');
            }
        }

        // 3. En MySQL podemos dropear foreign key y columna contract_id; en SQLite la dejamos nullable para no corromper la definicion de schema
        if (Schema::hasColumn('invoices', 'contract_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->unsignedBigInteger('contract_id')->nullable()->change();
            });
        }

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('invoices', 'entity_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn('entity_id');
            });
        }
    }
};
