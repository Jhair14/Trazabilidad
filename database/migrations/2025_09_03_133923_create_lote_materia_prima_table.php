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
        Schema::create('LoteMateriaPrima', function (Blueprint $table) {
            $table->integer('IdLote');
            $table->integer('IdMateriaPrima');
            $table->decimal('Cantidad', 10, 2)->default(0);
            $table->primary(['IdLote', 'IdMateriaPrima']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('LoteMateriaPrima');
    }
};
