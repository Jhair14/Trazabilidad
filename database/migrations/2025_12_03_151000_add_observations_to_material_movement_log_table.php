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
        Schema::table('material_movement_log', function (Blueprint $table) {
            if (!Schema::hasColumn('material_movement_log', 'observations')) {
                $table->text('observations')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_movement_log', function (Blueprint $table) {
            if (Schema::hasColumn('material_movement_log', 'observations')) {
                $table->dropColumn('observations');
            }
        });
    }
};
