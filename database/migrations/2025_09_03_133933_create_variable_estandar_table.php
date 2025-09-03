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
        Schema::create('VariableEstandar', function (Blueprint $table) {
            $table->integer('IdVariableEstandar')->primary()->generatedAs();
            $table->string('Nombre', 100);
            $table->string('Unidad', 50)->nullable();
            $table->string('Descripcion', 255)->nullable();
            $table->boolean('Activo')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('VariableEstandar');
    }
};
