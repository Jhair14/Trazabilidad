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
        Schema::create('Almacenaje', function (Blueprint $table) {
            $table->integer('IdAlmacenaje')->primary()->generatedAs();
            $table->integer('IdLote');
            $table->string('Ubicacion', 100);
            $table->string('Condicion', 100);
            $table->timestamp('FechaAlmacenaje')->nullable()->default(DB::raw('now()'));

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
        Schema::dropIfExists('Almacenaje');
    }
};
