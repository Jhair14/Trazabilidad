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
        // Core parametric tables
        Schema::create('unit_of_measure', function (Blueprint $table) {
            $table->integer('unit_id')->primary();
            $table->string('code', 10)->unique();
            $table->string('name', 50);
            $table->string('description', 255)->nullable();
            $table->boolean('active')->default(true);
        });

        Schema::create('status', function (Blueprint $table) {
            $table->integer('status_id')->primary();
            $table->string('entity_type', 50);
            $table->string('code', 50);
            $table->string('name', 100);
            $table->string('description', 255)->nullable();
            $table->integer('sort_order');
            $table->boolean('active')->default(true);
        });

        Schema::create('movement_type', function (Blueprint $table) {
            $table->integer('movement_type_id')->primary();
            $table->string('code', 20)->unique();
            $table->string('name', 100);
            $table->boolean('affects_stock')->default(true);
            $table->boolean('is_entry')->default(false);
            $table->boolean('active')->default(true);
        });

        Schema::create('operator_role', function (Blueprint $table) {
            $table->integer('role_id')->primary();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->string('description', 255)->nullable();
            $table->integer('access_level')->default(1);
            $table->boolean('active')->default(true);
        });

        Schema::create('customer', function (Blueprint $table) {
            $table->integer('customer_id')->primary();
            $table->string('business_name', 200);
            $table->string('trading_name', 200)->nullable();
            $table->string('tax_id', 20)->nullable()->unique();
            $table->string('address', 255)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('contact_person', 100)->nullable();
            $table->boolean('active')->default(true);
        });

        Schema::create('raw_material_category', function (Blueprint $table) {
            $table->integer('category_id')->primary();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->string('description', 255)->nullable();
            $table->boolean('active')->default(true);
        });

        Schema::create('supplier', function (Blueprint $table) {
            $table->integer('supplier_id')->primary();
            $table->string('business_name', 200);
            $table->string('trading_name', 200)->nullable();
            $table->string('tax_id', 20)->nullable()->unique();
            $table->string('contact_person', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('address', 255)->nullable();
            $table->boolean('active')->default(true);
        });

        // Configuration tables
        Schema::create('standard_variable', function (Blueprint $table) {
            $table->integer('variable_id')->primary();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->string('unit', 50)->nullable();
            $table->string('description', 255)->nullable();
            $table->boolean('active')->default(true);
        });

        Schema::create('machine', function (Blueprint $table) {
            $table->integer('machine_id')->primary();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->string('description', 255)->nullable();
            $table->string('image_url', 500)->nullable();
            $table->boolean('active')->default(true);
        });

        Schema::create('process', function (Blueprint $table) {
            $table->integer('process_id')->primary();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->string('description', 255)->nullable();
            $table->boolean('active')->default(true);
        });

        Schema::create('operator', function (Blueprint $table) {
            $table->integer('operator_id')->primary();
            $table->integer('role_id');
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('username', 60)->unique();
            $table->string('password_hash', 255);
            $table->string('email', 100)->nullable();
            $table->boolean('active')->default(true);
            $table->foreign('role_id')->references('role_id')->on('operator_role');
        });

        // Inventory tables
        Schema::create('raw_material_base', function (Blueprint $table) {
            $table->integer('material_id')->primary();
            $table->integer('category_id');
            $table->integer('unit_id');
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->string('description', 255)->nullable();
            $table->decimal('available_quantity', 15, 4)->default(0);
            $table->decimal('minimum_stock', 15, 4)->default(0);
            $table->decimal('maximum_stock', 15, 4)->nullable();
            $table->boolean('active')->default(true);
            $table->foreign('category_id')->references('category_id')->on('raw_material_category');
            $table->foreign('unit_id')->references('unit_id')->on('unit_of_measure');
        });

        Schema::create('raw_material', function (Blueprint $table) {
            $table->integer('raw_material_id')->primary();
            $table->integer('material_id');
            $table->integer('supplier_id');
            $table->string('supplier_batch', 100)->nullable();
            $table->string('invoice_number', 100)->nullable();
            $table->date('receipt_date');
            $table->date('expiration_date')->nullable();
            $table->decimal('quantity', 15, 4);
            $table->decimal('available_quantity', 15, 4);
            $table->boolean('receipt_conformity')->nullable();
            $table->string('observations', 500)->nullable();
            $table->foreign('material_id')->references('material_id')->on('raw_material_base');
            $table->foreign('supplier_id')->references('supplier_id')->on('supplier');
        });

        // Production tables
        Schema::create('customer_order', function (Blueprint $table) {
            $table->integer('order_id')->primary();
            $table->integer('customer_id');
            $table->string('order_number', 50)->unique();
            $table->date('creation_date')->default(now());
            $table->date('delivery_date')->nullable();
            $table->integer('priority')->default(1);
            $table->text('description')->nullable();
            $table->text('observations')->nullable();
            $table->foreign('customer_id')->references('customer_id')->on('customer');
        });

        Schema::create('production_batch', function (Blueprint $table) {
            $table->integer('batch_id')->primary();
            $table->integer('order_id');
            $table->string('batch_code', 50)->unique();
            $table->string('name', 100)->default('Unnamed Batch');
            $table->date('creation_date')->default(now());
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->decimal('target_quantity', 15, 4)->nullable();
            $table->decimal('produced_quantity', 15, 4)->nullable();
            $table->string('observations', 500)->nullable();
            $table->foreign('order_id')->references('order_id')->on('customer_order');
        });

        // Continue with remaining tables...
        Schema::create('batch_raw_material', function (Blueprint $table) {
            $table->integer('batch_material_id')->primary();
            $table->integer('batch_id');
            $table->integer('raw_material_id');
            $table->decimal('planned_quantity', 15, 4);
            $table->decimal('used_quantity', 15, 4)->nullable();
            $table->foreign('batch_id')->references('batch_id')->on('production_batch');
            $table->foreign('raw_material_id')->references('raw_material_id')->on('raw_material');
        });

        Schema::create('material_movement_log', function (Blueprint $table) {
            $table->integer('log_id')->primary();
            $table->integer('material_id');
            $table->integer('movement_type_id');
            $table->integer('user_id')->nullable();
            $table->decimal('quantity', 15, 4);
            $table->decimal('previous_balance', 15, 4)->nullable();
            $table->decimal('new_balance', 15, 4)->nullable();
            $table->string('description', 500)->nullable();
            $table->timestamp('movement_date')->default(now());
            $table->foreign('material_id')->references('material_id')->on('raw_material_base');
            $table->foreign('movement_type_id')->references('movement_type_id')->on('movement_type');
            $table->foreign('user_id')->references('operator_id')->on('operator');
        });

        // Additional tables for process management
        Schema::create('process_machine', function (Blueprint $table) {
            $table->integer('process_machine_id')->primary();
            $table->integer('process_id');
            $table->integer('machine_id');
            $table->integer('step_order');
            $table->string('name', 100);
            $table->string('description', 255)->nullable();
            $table->integer('estimated_time')->nullable();
            $table->foreign('process_id')->references('process_id')->on('process');
            $table->foreign('machine_id')->references('machine_id')->on('machine');
        });

        // Add remaining tables as needed...
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('process_machine');
        Schema::dropIfExists('material_movement_log');
        Schema::dropIfExists('batch_raw_material');
        Schema::dropIfExists('production_batch');
        Schema::dropIfExists('customer_order');
        Schema::dropIfExists('raw_material');
        Schema::dropIfExists('raw_material_base');
        Schema::dropIfExists('operator');
        Schema::dropIfExists('process');
        Schema::dropIfExists('machine');
        Schema::dropIfExists('standard_variable');
        Schema::dropIfExists('supplier');
        Schema::dropIfExists('raw_material_category');
        Schema::dropIfExists('customer');
        Schema::dropIfExists('operator_role');
        Schema::dropIfExists('movement_type');
        Schema::dropIfExists('status');
        Schema::dropIfExists('unit_of_measure');
    }
};