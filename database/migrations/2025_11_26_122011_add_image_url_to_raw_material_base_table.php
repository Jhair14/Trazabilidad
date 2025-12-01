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
        // Verificar que la tabla existe antes de modificarla
        if (Schema::hasTable('raw_material_base') && !Schema::hasColumn('raw_material_base', 'image_url')) {
            Schema::table('raw_material_base', function (Blueprint $table) {
                $table->string('image_url', 500)->nullable()->after('description');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('raw_material_base', function (Blueprint $table) {
            $table->dropColumn('image_url');
        });
    }
};
