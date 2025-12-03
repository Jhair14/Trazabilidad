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
        Schema::create('order_product', function (Blueprint $table) {
            $table->integer('order_product_id')->primary();
            $table->integer('order_id');
            $table->integer('product_id');
            $table->decimal('quantity', 15, 4);
            $table->string('status', 50)->default('pendiente')->comment('pendiente, aprobado, rechazado');
            $table->text('rejection_reason')->nullable();
            $table->integer('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('observations')->nullable();
            $table->timestamps();
            
            $table->foreign('order_id')->references('order_id')->on('customer_order')->onDelete('cascade');
            $table->foreign('product_id')->references('product_id')->on('product');
            $table->foreign('approved_by')->references('operator_id')->on('operator');
            
            $table->index(['order_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_product');
    }
};




