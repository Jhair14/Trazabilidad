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
        Schema::create('Operador', function (Blueprint $table) {
            $table->integer('IdOperador')->primary()->generatedAs();
            $table->string('Nombre', 100);
            $table->string('Cargo', 50)->nullable();
            $table->string('Usuario', 60)->unique();
            $table->string('PasswordHash', 255);
            $table->string('Email', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Operador');
    }
};
