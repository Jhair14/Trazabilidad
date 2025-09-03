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
        Schema::create('OperadorMaquina', function (Blueprint $table) {
            $table->integer('IdOperador');
            $table->integer('IdMaquina');
            $table->primary(['IdOperador', 'IdMaquina']);

            // Relaciones
            $table->foreign('IdOperador')
                ->references('IdOperador')->on('Operador')
                ->onDelete('cascade');
            $table->foreign('IdMaquina')
                ->references('IdMaquina')->on('Maquina')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('OperadorMaquina');
    }
};
