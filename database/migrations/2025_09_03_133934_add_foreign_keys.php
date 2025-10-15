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
        // Almacenaje → Lote
        Schema::table('Almacenaje', function (Blueprint $table) {
            $table->foreign('IdLote')
                ->references('IdLote')->on('Lote')
                ->onDelete('cascade');
        });

        // LogMateriaPrima → MateriaPrimaBase
        Schema::table('LogMateriaPrima', function (Blueprint $table) {
            $table->foreign('IdMateriaPrimaBase')
                ->references('IdMateriaPrimaBase')->on('MateriaPrimaBase')
                ->onDelete('cascade');
        });

        // Lote → Proceso, Pedido
        Schema::table('Lote', function (Blueprint $table) {
            $table->foreign('IdProceso')
                ->references('IdProceso')->on('Proceso');
            $table->foreign('IdPedido')
                ->references('IdPedido')->on('Pedido');
        });

        // LoteMateriaPrima → Lote, MateriaPrima
        Schema::table('LoteMateriaPrima', function (Blueprint $table) {
            $table->foreign('IdLote')
                ->references('IdLote')->on('Lote')
                ->onDelete('cascade');
            $table->foreign('IdMateriaPrima')
                ->references('IdMateriaPrima')->on('MateriaPrima')
                ->onDelete('cascade');
        });

        // LoteMateriaPrimaBase → Lote, MateriaPrimaBase
        Schema::table('LoteMateriaPrimaBase', function (Blueprint $table) {
            $table->foreign('IdLote')
                ->references('IdLote')->on('Lote')
                ->onDelete('cascade');
            $table->foreign('IdMateriaPrimaBase')
                ->references('IdMateriaPrimaBase')->on('MateriaPrimaBase')
                ->onDelete('cascade');
        });

        // MateriaPrima → Proveedor, Pedido, MateriaPrimaBase
        Schema::table('MateriaPrima', function (Blueprint $table) {
            $table->foreign('IdProveedor')
                ->references('IdProveedor')->on('Proveedor');
            $table->foreign('IdPedido')
                ->references('IdPedido')->on('Pedido');
            $table->foreign('IdMateriaPrimaBase')
                ->references('IdMateriaPrimaBase')->on('MateriaPrimaBase');
        });

        // OperadorMaquina → Operador, Maquina
        Schema::table('OperadorMaquina', function (Blueprint $table) {
            $table->foreign('IdOperador')
                ->references('IdOperador')->on('Operador')
                ->onDelete('cascade');
            $table->foreign('IdMaquina')
                ->references('IdMaquina')->on('Maquina')
                ->onDelete('cascade');
        });

        // Pedido → Operador (IdCliente)
        Schema::table('Pedido', function (Blueprint $table) {
            $table->foreign('IdCliente')
                ->references('IdOperador')->on('Operador');
        });

        // ProcesoEvaluacionFinal → Lote
        Schema::table('ProcesoEvaluacionFinal', function (Blueprint $table) {
            $table->foreign('IdLote')
                ->references('IdLote')->on('Lote');
        });

        // ProcesoMaquina → Proceso, Maquina
        Schema::table('ProcesoMaquina', function (Blueprint $table) {
            $table->foreign('IdProceso')
                ->references('IdProceso')->on('Proceso');
            $table->foreign('IdMaquina')
                ->references('IdMaquina')->on('Maquina');
        });

        // ProcesoMaquinaRegistro → Lote, ProcesoMaquina
        Schema::table('ProcesoMaquinaRegistro', function (Blueprint $table) {
            $table->foreign('IdLote')
                ->references('IdLote')->on('Lote');
            $table->foreign('IdProcesoMaquina')
                ->references('IdProcesoMaquina')->on('ProcesoMaquina');
        });

        // ProcesoMaquinaVariable → ProcesoMaquina
        Schema::table('ProcesoMaquinaVariable', function (Blueprint $table) {
            $table->foreign('IdProcesoMaquina')
                ->references('IdProcesoMaquina')->on('ProcesoMaquina');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ProcesoMaquinaVariable', function (Blueprint $table) {
            $table->dropForeign(['IdProcesoMaquina']);
        });
        Schema::table('ProcesoMaquinaRegistro', function (Blueprint $table) {
            $table->dropForeign(['IdLote']);
            $table->dropForeign(['IdProcesoMaquina']);
        });
        Schema::table('ProcesoMaquina', function (Blueprint $table) {
            $table->dropForeign(['IdProceso']);
            $table->dropForeign(['IdMaquina']);
        });
        Schema::table('ProcesoEvaluacionFinal', function (Blueprint $table) {
            $table->dropForeign(['IdLote']);
        });
        Schema::table('Pedido', function (Blueprint $table) {
            $table->dropForeign(['IdCliente']);
        });
        Schema::table('OperadorMaquina', function (Blueprint $table) {
            $table->dropForeign(['IdOperador']);
            $table->dropForeign(['IdMaquina']);
        });
        Schema::table('MateriaPrima', function (Blueprint $table) {
            $table->dropForeign(['IdProveedor']);
            $table->dropForeign(['IdPedido']);
            $table->dropForeign(['IdMateriaPrimaBase']);
        });
        Schema::table('LoteMateriaPrimaBase', function (Blueprint $table) {
            $table->dropForeign(['IdLote']);
            $table->dropForeign(['IdMateriaPrimaBase']);
        });
        Schema::table('LoteMateriaPrima', function (Blueprint $table) {
            $table->dropForeign(['IdLote']);
            $table->dropForeign(['IdMateriaPrima']);
        });
        Schema::table('Lote', function (Blueprint $table) {
            $table->dropForeign(['IdProceso']);
            $table->dropForeign(['IdPedido']);
        });
        Schema::table('LogMateriaPrima', function (Blueprint $table) {
            $table->dropForeign(['IdMateriaPrimaBase']);
        });
        Schema::table('Almacenaje', function (Blueprint $table) {
            $table->dropForeign(['IdLote']);
        });
    }
};


