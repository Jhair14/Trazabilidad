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
        Schema::create('MaquinaVariable', function (Blueprint $table) {
            $table->integer('IdVariable')->primary()->generatedAs()->always();
            $table->integer('IdMaquina')->nullable();
            $table->string('Nombre', 100)->nullable();
            $table->decimal('ValorMin', 10, 2)->nullable();
            $table->decimal('ValorMax', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('MaquinaVariable');
    }
};
