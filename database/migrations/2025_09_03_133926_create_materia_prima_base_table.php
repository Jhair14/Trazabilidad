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
        Schema::create('MateriaPrimaBase', function (Blueprint $table) {
            $table->integer('IdMateriaPrimaBase')->primary()->generatedAs();
            $table->string('Nombre', 100);
            $table->string('Unidad', 10);
            $table->decimal('Cantidad', 10, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('MateriaPrimaBase');
    }
};
