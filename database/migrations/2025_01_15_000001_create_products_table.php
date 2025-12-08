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
        Schema::create('product', function (Blueprint $table) {
            $table->integer('product_id')->primary();
            $table->string('code', 50)->unique();
            $table->string('name', 200);
            $table->enum('type', ['organico', 'marca_univalle', 'comestibles'])->default('comestibles');
            $table->decimal('weight', 10, 2)->nullable()->comment('Peso en kg');
            $table->integer('unit_id')->nullable();
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            
            $table->foreign('unit_id')->references('unit_id')->on('unit_of_measure');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product');
    }
};










