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
        Schema::table('storage', function (Blueprint $table) {
            $table->decimal('pickup_latitude', 10, 8)->nullable()->after('observations');
            $table->decimal('pickup_longitude', 11, 8)->nullable()->after('pickup_latitude');
            $table->string('pickup_address', 500)->nullable()->after('pickup_longitude');
            $table->string('pickup_reference', 255)->nullable()->after('pickup_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('storage', function (Blueprint $table) {
            $table->dropColumn(['pickup_latitude', 'pickup_longitude', 'pickup_address', 'pickup_reference']);
        });
    }
};

