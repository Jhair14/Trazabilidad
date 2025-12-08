<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Añade campos para almacén destino de plantaCruds en order_destination
     */
    public function up(): void
    {
        Schema::table('order_destination', function (Blueprint $table) {
            if (!Schema::hasColumn('order_destination', 'almacen_destino_id')) {
                $table->integer('almacen_destino_id')->nullable()->after('almacen_origen_nombre');
            }
            if (!Schema::hasColumn('order_destination', 'almacen_destino_nombre')) {
                $table->string('almacen_destino_nombre')->nullable()->after('almacen_destino_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_destination', function (Blueprint $table) {
            if (Schema::hasColumn('order_destination', 'almacen_destino_id')) {
                $table->dropColumn('almacen_destino_id');
            }
            if (Schema::hasColumn('order_destination', 'almacen_destino_nombre')) {
                $table->dropColumn('almacen_destino_nombre');
            }
        });
    }
};
