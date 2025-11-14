
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('equipment_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('equipment_id')->nullable();
            $table->unsignedBigInteger('old_equipment_id')->nullable();
            $table->unsignedBigInteger('new_equipment_id')->nullable();
            $table->string('action'); // alta, baja, reemplazo, backup, etc.
            $table->text('reason')->nullable();
            $table->timestamp('action_date')->nullable();
            $table->timestamp('replacement_date')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->timestamps();

            $table->foreign('equipment_id')->references('id')->on('equipment')->onDelete('cascade');
            $table->foreign('old_equipment_id')->references('id')->on('equipment')->onDelete('set null');
            $table->foreign('new_equipment_id')->references('id')->on('equipment')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('set null');
        });
    }
    public function down() {
        Schema::dropIfExists('equipment_history');
    }
};
