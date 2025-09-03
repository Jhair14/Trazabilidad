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
        Schema::create('MateriaPrima', function (Blueprint $table) {
            $table->integer('IdMateriaPrima')->primary()->generatedAs();
            $table->string('Nombre', 100);
            $table->date('FechaRecepcion');
            $table->string('Proveedor', 100)->nullable();
            $table->decimal('Cantidad', 10, 2)->nullable();
            $table->string('Estado', 50)->nullable()->default('solicitado');
            $table->string('Unidad', 10)->nullable();
            $table->boolean('RecepcionConforme')->nullable();
            $table->text('FirmaRecepcion')->nullable();
            $table->integer('IdProveedor')->nullable();
            $table->integer('IdPedido')->nullable();
            $table->integer('IdMateriaPrimaBase')->nullable();

            // Relaciones
            $table->foreign('IdProveedor')
                ->references('IdProveedor')->on('Proveedor')
                ->onDelete('set null');
            $table->foreign('IdPedido')
                ->references('IdPedido')->on('Pedido')
                ->onDelete('set null');
            $table->foreign('IdMateriaPrimaBase')
                ->references('IdMateriaPrimaBase')->on('MateriaPrimaBase')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('MateriaPrima');
    }
};
