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
        Schema::create('ProcesoMaquinaRegistro', function (Blueprint $table) {
            $table->integer('Id')->primary()->generatedAs()->always();
            $table->integer('IdLote');
            $table->integer('NumeroMaquina');
            $table->string('NombreMaquina', 100);
            $table->text('VariablesIngresadas');
            $table->boolean('CumpleEstandar');
            $table->timestamp('FechaRegistro')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->integer('IdProcesoMaquina')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ProcesoMaquinaRegistro');
    }
};
