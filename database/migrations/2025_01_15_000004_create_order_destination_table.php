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
        Schema::create('order_destination', function (Blueprint $table) {
            $table->integer('destination_id')->primary();
            $table->integer('order_id');
            $table->string('address', 500);
            $table->string('reference', 200)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('contact_name', 200)->nullable();
            $table->string('contact_phone', 20)->nullable();
            $table->text('delivery_instructions')->nullable();
            $table->timestamps();
            
            $table->foreign('order_id')->references('order_id')->on('customer_order')->onDelete('cascade');
            
            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_destination');
    }
};










