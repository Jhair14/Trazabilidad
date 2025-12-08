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
        Schema::create('order_destination_product', function (Blueprint $table) {
            $table->integer('destination_product_id')->primary();
            $table->integer('destination_id');
            $table->integer('order_product_id');
            $table->decimal('quantity', 15, 4);
            $table->text('observations')->nullable();
            $table->timestamps();
            
            $table->foreign('destination_id')->references('destination_id')->on('order_destination')->onDelete('cascade');
            $table->foreign('order_product_id')->references('order_product_id')->on('order_product')->onDelete('cascade');
            
            $table->index(['destination_id', 'order_product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_destination_product');
    }
};










