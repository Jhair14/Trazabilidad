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
        Schema::create('LoteMateriaPrimaBase', function (Blueprint $table) {
            $table->integer('IdLoteMateriaPrimaBase')->primary()->generatedAs();
            $table->integer('IdLote');
            $table->integer('IdMateriaPrimaBase');
            $table->decimal('Cantidad', 10, 2);

            // Relaciones
            $table->foreign('IdLote')
                ->references('IdLote')->on('Lote')
                ->onDelete('cascade');
            $table->foreign('IdMateriaPrimaBase')
                ->references('IdMateriaPrimaBase')->on('MateriaPrimaBase')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('LoteMateriaPrimaBase');
    }
};
