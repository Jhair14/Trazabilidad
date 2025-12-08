<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_destination', function (Blueprint $table) {
            if (!Schema::hasColumn('order_destination', 'almacen_origen_id')) {
                $table->integer('almacen_origen_id')->nullable()->after('delivery_instructions');
            }
            if (!Schema::hasColumn('order_destination', 'almacen_origen_nombre')) {
                $table->string('almacen_origen_nombre')->nullable()->after('almacen_origen_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_destination', function (Blueprint $table) {
            if (Schema::hasColumn('order_destination', 'almacen_origen_nombre')) {
                $table->dropColumn('almacen_origen_nombre');
            }
            if (Schema::hasColumn('order_destination', 'almacen_origen_id')) {
                $table->dropColumn('almacen_origen_id');
            }
        });
    }
};
