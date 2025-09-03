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
        Schema::create('ProcesoEvaluacionFinal', function (Blueprint $table) {
            $table->integer('Id')->primary()->generatedAs();
            $table->integer('IdLote');
            $table->string('EstadoFinal', 50);
            $table->string('Motivo', 255)->nullable();
            $table->timestamp('FechaEvaluacion')->nullable()->default(DB::raw('now()'));

            // Relaciones
            $table->foreign('IdLote')
                ->references('IdLote')->on('Lote')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ProcesoEvaluacionFinal');
    }
};
