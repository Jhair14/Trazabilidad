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
        // This table is now created in create_complete_database_schema migration
        // This migration is kept for backward compatibility but does nothing
        // to avoid conflicts with the complete schema migration
        if (!Schema::hasTable('unit_of_measure')) {
            Schema::create('unit_of_measure', function (Blueprint $table) {
                $table->integer('unit_id')->primary();
                $table->string('code', 10)->unique();
                $table->string('name', 50);
                $table->string('description', 255)->nullable();
                $table->boolean('active')->default(true);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_of_measure');
    }
};
