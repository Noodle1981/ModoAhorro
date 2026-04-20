<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('usage_adjustments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->boolean('adjusted')->default(false);
            $table->timestamp('adjusted_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('usage_adjustments');
    }
};
