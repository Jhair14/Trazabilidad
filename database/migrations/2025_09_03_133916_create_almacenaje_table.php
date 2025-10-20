<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('Almacenaje', function (Blueprint $table) {
            $table->id('IdAlmacenaje');
            $table->integer('IdLote');
            $table->string('Ubicacion', 100);
            $table->string('Condicion', 100);
            $table->timestamp('FechaAlmacenaje')->nullable();
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
