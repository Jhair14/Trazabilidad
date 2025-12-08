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
        Schema::create('order_envio_tracking', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('destination_id');
            $table->unsignedBigInteger('envio_id')->nullable();
            $table->string('envio_codigo', 50)->nullable();
            $table->string('status', 50)->default('pending'); // pending, success, failed
            $table->text('error_message')->nullable();
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('order_id')->references('order_id')->on('customer_order')->onDelete('cascade');
            $table->foreign('destination_id')->references('destination_id')->on('order_destination')->onDelete('cascade');
            
            // Indexes
            $table->index(['order_id', 'destination_id']);
            $table->index('status');
            $table->index('envio_codigo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_envio_tracking');
    }
};
