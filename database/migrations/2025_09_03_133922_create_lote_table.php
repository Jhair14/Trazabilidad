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
        Schema::create('Lote', function (Blueprint $table) {
            $table->integer('IdLote')->primary()->generatedAs();
            $table->date('FechaCreacion');
            $table->string('Estado', 50)->nullable();
            $table->string('Nombre', 100)->default('Lote sin nombre');
            $table->integer('IdProceso')->nullable();
            $table->integer('IdPedido')->nullable();

            // Relaciones
            $table->foreign('IdProceso')
                ->references('IdProceso')->on('Proceso')
                ->onDelete('set null');
            $table->foreign('IdPedido')
                ->references('IdPedido')->on('Pedido')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Lote');
    }
};
