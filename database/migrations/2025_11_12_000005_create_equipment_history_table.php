<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('equipment_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('old_equipment_id')->constrained('equipment')->onDelete('cascade');
            $table->foreignId('new_equipment_id')->nullable()->constrained('equipment')->onDelete('cascade');
            $table->date('replacement_date')->nullable();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onDelete('set null');
            $table->string('action')->default('reemplazo'); // reemplazo, baja, backup, etc.
            $table->timestamps();
        });
    }
    public function down() {
        Schema::dropIfExists('equipment_history');
    }
};
