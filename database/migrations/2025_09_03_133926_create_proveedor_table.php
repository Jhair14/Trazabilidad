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
        Schema::create('Proveedor', function (Blueprint $table) {
            $table->integer('IdProveedor')->primary()->generatedAs()->always();
            $table->string('Nombre', 100);
            $table->string('Contacto', 100)->nullable();
            $table->string('Telefono', 20)->nullable();
            $table->string('Email', 100)->nullable();
            $table->string('Direccion', 255)->nullable();
            $table->timestamp('FechaCreacion')->nullable()->default(DB::raw('now()'));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Proveedor');
    }
};
