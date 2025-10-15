<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ProcesoMaquina', function (Blueprint $table) {
            $table->integer('IdProcesoMaquina')->primary()->generatedAs()->always();
            $table->integer('IdProceso');
            $table->integer('IdMaquina');
            $table->integer('Numero');
            $table->string('Nombre', 100);
            $table->string('Imagen', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ProcesoMaquina');
    }
};
