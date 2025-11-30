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
        if (Schema::hasTable('raw_material') && !Schema::hasColumn('raw_material', 'receipt_signature')) {
            Schema::table('raw_material', function (Blueprint $table) {
                $table->text('receipt_signature')->nullable()->after('receipt_conformity');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('raw_material', function (Blueprint $table) {
            $table->dropColumn('receipt_signature');
        });
    }
};
