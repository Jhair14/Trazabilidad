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
        Schema::create('ProcesoMaquinaRegistro', function (Blueprint $table) {
            $table->integer('Id')->primary()->generatedAs();
            $table->integer('IdLote');
            $table->integer('NumeroMaquina');
            $table->string('NombreMaquina', 100);
            $table->text('VariablesIngresadas');
            $table->boolean('CumpleEstandar');
            $table->timestamp('FechaRegistro')->nullable()->default(DB::raw('now()'));
            $table->integer('IdProcesoMaquina')->nullable();

            // Relaciones
            $table->foreign('IdLote')
                ->references('IdLote')->on('Lote')
                ->onDelete('cascade');
            $table->foreign('IdProcesoMaquina')
                ->references('IdProcesoMaquina')->on('ProcesoMaquina')
                ->onDelete('set null');
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
