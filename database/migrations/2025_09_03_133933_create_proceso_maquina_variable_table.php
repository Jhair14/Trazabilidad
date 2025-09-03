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
        Schema::create('ProcesoMaquinaVariable', function (Blueprint $table) {
            $table->integer('IdVariable')->primary()->generatedAs();
            $table->integer('IdProcesoMaquina');
            $table->string('Nombre', 100);
            $table->decimal('ValorMin', 10, 2);
            $table->decimal('ValorMax', 10, 2);

            // Relaciones
            $table->foreign('IdProcesoMaquina')
                ->references('IdProcesoMaquina')->on('ProcesoMaquina')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ProcesoMaquinaVariable');
    }
};
