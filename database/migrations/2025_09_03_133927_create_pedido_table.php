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
        Schema::create('Pedido', function (Blueprint $table) {
            $table->integer('IdPedido')->primary()->generatedAs();
            $table->integer('IdCliente');
            $table->timestamp('FechaCreacion')->nullable()->default(DB::raw('now()'));
            $table->string('Estado', 50)->nullable()->default('pendiente');
            $table->text('Observaciones')->nullable();
            $table->text('Descripcion')->nullable();

            // Relaciones
            $table->foreign('IdCliente')
                ->references('IdOperador')->on('Operador')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Pedido');
    }
};
