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
        Schema::create('LogMateriaPrima', function (Blueprint $table) {
            $table->integer('IdLog')->primary()->generatedAs();
            $table->integer('IdMateriaPrimaBase');
            $table->timestamp('Fecha')->default(DB::raw('now()'));
            $table->string('TipoMovimiento', 20);
            $table->decimal('Cantidad', 10, 2);
            $table->string('Descripcion', 255)->nullable();

            // Relaciones
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
        Schema::dropIfExists('LogMateriaPrima');
    }
};
