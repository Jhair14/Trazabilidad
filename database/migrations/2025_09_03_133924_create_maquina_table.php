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
        Schema::create('Maquina', function (Blueprint $table) {
            $table->integer('IdMaquina')->primary()->generatedAs()->always();
            $table->string('Nombre', 100);
            $table->string('ImagenUrl', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Maquina');
    }
};
