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
        Schema::create('LoteMateriaPrimaBase', function (Blueprint $table) {
            $table->integer('IdLoteMateriaPrimaBase')->primary()->generatedAs()->always();
            $table->integer('IdLote');
            $table->integer('IdMateriaPrimaBase');
            $table->decimal('Cantidad', 10, 2);
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
