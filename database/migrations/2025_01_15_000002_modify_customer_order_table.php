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
        Schema::table('customer_order', function (Blueprint $table) {
            $table->string('name', 200)->nullable()->after('order_number')->comment('Nombre del pedido');
            $table->string('status', 50)->default('pendiente')->after('name')->comment('Estado: pendiente, aprobado, rechazado, en_produccion, completado, cancelado');
            $table->timestamp('editable_until')->nullable()->after('status')->comment('Fecha límite para editar/cancelar');
            $table->timestamp('approved_at')->nullable()->after('editable_until');
            $table->integer('approved_by')->nullable()->after('approved_at')->comment('ID del operador que aprobó');
            $table->text('rejection_reason')->nullable()->after('approved_by')->comment('Razón de rechazo si aplica');
            
            $table->foreign('approved_by')->references('operator_id')->on('operator');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_order', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'name',
                'status',
                'editable_until',
                'approved_at',
                'approved_by',
                'rejection_reason'
            ]);
        });
    }
};









