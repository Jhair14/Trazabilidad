<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // =============================================
        // CORE PARAMETRIC TABLES
        // =============================================

        if (!Schema::hasTable('unit_of_measure')) {
            Schema::create('unit_of_measure', function (Blueprint $table) {
            $table->integer('unit_id')->primary();
            $table->string('code', 10)->unique();
            $table->string('name', 50);
            $table->string('description', 255)->nullable();
            $table->boolean('active')->default(true);
            });
        }

        if (!Schema::hasTable('status')) {
            Schema::create('status', function (Blueprint $table) {
            $table->integer('status_id')->primary();
            $table->string('entity_type', 50);
            $table->string('code', 50);
            $table->string('name', 100);
            $table->string('description', 255)->nullable();
            $table->integer('sort_order');
            $table->boolean('active')->default(true);
            });
        }

        if (!Schema::hasTable('movement_type')) {
            Schema::create('movement_type', function (Blueprint $table) {
            $table->integer('movement_type_id')->primary();
            $table->string('code', 20)->unique();
            $table->string('name', 100);
            $table->boolean('affects_stock')->default(true);
            $table->boolean('is_entry')->default(false);
            $table->boolean('active')->default(true);
            });
        }

        if (!Schema::hasTable('operator_role')) {
            Schema::create('operator_role', function (Blueprint $table) {
            $table->integer('role_id')->primary();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->string('description', 255)->nullable();
            $table->integer('access_level')->default(1);
            $table->boolean('active')->default(true);
            });
        }

        if (!Schema::hasTable('customer')) {
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
        }

        if (!Schema::hasTable('raw_material_category')) {
            Schema::create('raw_material_category', function (Blueprint $table) {
            $table->integer('category_id')->primary();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->string('description', 255)->nullable();
            $table->boolean('active')->default(true);
            });
        }

        if (!Schema::hasTable('supplier')) {
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
        }

        // =============================================
        // CONFIGURATION TABLES
        // =============================================

        if (!Schema::hasTable('standard_variable')) {
            Schema::create('standard_variable', function (Blueprint $table) {
            $table->integer('variable_id')->primary();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->string('unit', 50)->nullable();
            $table->string('description', 255)->nullable();
            $table->boolean('active')->default(true);
            });
        }

        if (!Schema::hasTable('machine')) {
            Schema::create('machine', function (Blueprint $table) {
            $table->integer('machine_id')->primary();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->string('description', 255)->nullable();
            $table->string('image_url', 500)->nullable();
            $table->boolean('active')->default(true);
            });
        }

        if (!Schema::hasTable('process')) {
            Schema::create('process', function (Blueprint $table) {
            $table->integer('process_id')->primary();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->string('description', 255)->nullable();
            $table->boolean('active')->default(true);
            });
        }

        if (!Schema::hasTable('operator')) {
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
        }

        // =============================================
        // INVENTORY TABLES
        // =============================================

        if (!Schema::hasTable('raw_material_base')) {
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
        }

        if (!Schema::hasTable('raw_material')) {
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
        }

        // =============================================
        // PRODUCTION TABLES (MAIN HIERARCHY)
        // =============================================

        if (!Schema::hasTable('customer_order')) {
            Schema::create('customer_order', function (Blueprint $table) {
            $table->integer('order_id')->primary();
            $table->integer('customer_id');
            $table->string('order_number', 50)->unique();
            $table->date('creation_date')->default(DB::raw('CURRENT_DATE'));
            $table->date('delivery_date')->nullable();
            $table->integer('priority')->default(1);
            $table->text('description')->nullable();
            $table->text('observations')->nullable();
            $table->foreign('customer_id')->references('customer_id')->on('customer');
            });
        }

        if (!Schema::hasTable('production_batch')) {
            Schema::create('production_batch', function (Blueprint $table) {
            $table->integer('batch_id')->primary();
            $table->integer('order_id');
            $table->string('batch_code', 50)->unique();
            $table->string('name', 100)->default('Unnamed Batch');
            $table->date('creation_date')->default(DB::raw('CURRENT_DATE'));
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->decimal('target_quantity', 15, 4)->nullable();
            $table->decimal('produced_quantity', 15, 4)->nullable();
            $table->string('observations', 500)->nullable();
            $table->foreign('order_id')->references('order_id')->on('customer_order');
            });
        }

        // =============================================
        // CONSUMPTION AND MOVEMENT TABLES
        // =============================================

        if (!Schema::hasTable('batch_raw_material')) {
            Schema::create('batch_raw_material', function (Blueprint $table) {
            $table->integer('batch_material_id')->primary();
            $table->integer('batch_id');
            $table->integer('raw_material_id');
            $table->decimal('planned_quantity', 15, 4);
            $table->decimal('used_quantity', 15, 4)->nullable();
            $table->foreign('batch_id')->references('batch_id')->on('production_batch');
            $table->foreign('raw_material_id')->references('raw_material_id')->on('raw_material');
            });
        }

        if (!Schema::hasTable('material_movement_log')) {
            Schema::create('material_movement_log', function (Blueprint $table) {
            $table->integer('log_id')->primary();
            $table->integer('material_id');
            $table->integer('movement_type_id');
            $table->integer('user_id')->nullable();
            $table->decimal('quantity', 15, 4);
            $table->decimal('previous_balance', 15, 4)->nullable();
            $table->decimal('new_balance', 15, 4)->nullable();
            $table->string('description', 500)->nullable();
            $table->timestamp('movement_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('material_id')->references('material_id')->on('raw_material_base');
            $table->foreign('movement_type_id')->references('movement_type_id')->on('movement_type');
            $table->foreign('user_id')->references('operator_id')->on('operator');
            });
        }

        // =============================================
        // PRODUCTION PROCESS TABLES
        // =============================================

        if (!Schema::hasTable('process_machine')) {
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
        }

        if (!Schema::hasTable('process_machine_variable')) {
            Schema::create('process_machine_variable', function (Blueprint $table) {
            $table->integer('variable_id')->primary();
            $table->integer('process_machine_id');
            $table->integer('standard_variable_id');
            $table->decimal('min_value', 10, 2);
            $table->decimal('max_value', 10, 2);
            $table->decimal('target_value', 10, 2)->nullable();
            $table->boolean('mandatory')->default(true);
            $table->foreign('process_machine_id')->references('process_machine_id')->on('process_machine');
            $table->foreign('standard_variable_id')->references('variable_id')->on('standard_variable');
            });
        }

        if (!Schema::hasTable('process_machine_record')) {
            Schema::create('process_machine_record', function (Blueprint $table) {
            $table->integer('record_id')->primary();
            $table->integer('batch_id');
            $table->integer('process_machine_id');
            $table->integer('operator_id');
            $table->text('entered_variables');
            $table->boolean('meets_standard');
            $table->string('observations', 500)->nullable();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->timestamp('record_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('batch_id')->references('batch_id')->on('production_batch');
            $table->foreign('process_machine_id')->references('process_machine_id')->on('process_machine');
            $table->foreign('operator_id')->references('operator_id')->on('operator');
            });
        }

        // =============================================
        // QUALITY CONTROL AND STORAGE TABLES
        // =============================================

        if (!Schema::hasTable('process_final_evaluation')) {
            Schema::create('process_final_evaluation', function (Blueprint $table) {
            $table->integer('evaluation_id')->primary();
            $table->integer('batch_id');
            $table->integer('inspector_id')->nullable();
            $table->string('reason', 500)->nullable();
            $table->string('observations', 500)->nullable();
            $table->timestamp('evaluation_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('batch_id')->references('batch_id')->on('production_batch');
            $table->foreign('inspector_id')->references('operator_id')->on('operator');
            });
        }

        if (!Schema::hasTable('storage')) {
            Schema::create('storage', function (Blueprint $table) {
            $table->integer('storage_id')->primary();
            $table->integer('batch_id');
            $table->string('location', 100);
            $table->string('condition', 100);
            $table->decimal('quantity', 15, 4);
            $table->string('observations', 500)->nullable();
            $table->timestamp('storage_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('retrieval_date')->nullable();
            $table->foreign('batch_id')->references('batch_id')->on('production_batch');
            });
        }

        // =============================================
        // MATERIAL MANAGEMENT TABLES
        // =============================================

        if (!Schema::hasTable('material_request')) {
            Schema::create('material_request', function (Blueprint $table) {
            $table->integer('request_id')->primary();
            $table->integer('order_id');
            $table->string('request_number', 50)->unique();
            $table->date('request_date')->default(DB::raw('CURRENT_DATE'));
            $table->date('required_date');
            $table->integer('priority')->default(1);
            $table->text('observations')->nullable();
            $table->foreign('order_id')->references('order_id')->on('customer_order');
            });
        }

        if (!Schema::hasTable('material_request_detail')) {
            Schema::create('material_request_detail', function (Blueprint $table) {
            $table->integer('detail_id')->primary();
            $table->integer('request_id');
            $table->integer('material_id');
            $table->decimal('requested_quantity', 15, 4);
            $table->decimal('approved_quantity', 15, 4)->nullable();
            $table->foreign('request_id')->references('request_id')->on('material_request');
            $table->foreign('material_id')->references('material_id')->on('raw_material_base');
            });
        }

        if (!Schema::hasTable('supplier_response')) {
            Schema::create('supplier_response', function (Blueprint $table) {
            $table->integer('response_id')->primary();
            $table->integer('request_id');
            $table->integer('supplier_id');
            $table->timestamp('response_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->decimal('confirmed_quantity', 15, 4)->nullable();
            $table->date('delivery_date')->nullable();
            $table->text('observations')->nullable();
            $table->decimal('price', 15, 2)->nullable();
            $table->foreign('request_id')->references('request_id')->on('material_request');
            $table->foreign('supplier_id')->references('supplier_id')->on('supplier');
            });
        }

        // =============================================
        // SEQUENCES FOR MANUAL ID MANAGEMENT
        // =============================================
        // Note: Sequences are PostgreSQL specific. For other databases, 
        // auto-incrementing IDs are handled automatically.
        // If using PostgreSQL, uncomment the following lines:

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE SEQUENCE unit_of_measure_seq START WITH 1');
            DB::statement('CREATE SEQUENCE status_seq START WITH 1');
            DB::statement('CREATE SEQUENCE movement_type_seq START WITH 1');
            DB::statement('CREATE SEQUENCE operator_role_seq START WITH 1');
            DB::statement('CREATE SEQUENCE customer_seq START WITH 1');
            DB::statement('CREATE SEQUENCE raw_material_category_seq START WITH 1');
            DB::statement('CREATE SEQUENCE supplier_seq START WITH 1');
            DB::statement('CREATE SEQUENCE standard_variable_seq START WITH 1');
            DB::statement('CREATE SEQUENCE machine_seq START WITH 1');
            DB::statement('CREATE SEQUENCE process_seq START WITH 1');
            DB::statement('CREATE SEQUENCE operator_seq START WITH 1');
            DB::statement('CREATE SEQUENCE raw_material_base_seq START WITH 1');
            DB::statement('CREATE SEQUENCE raw_material_seq START WITH 1');
            DB::statement('CREATE SEQUENCE customer_order_seq START WITH 1');
            DB::statement('CREATE SEQUENCE production_batch_seq START WITH 1');
            DB::statement('CREATE SEQUENCE batch_raw_material_seq START WITH 1');
            DB::statement('CREATE SEQUENCE material_movement_log_seq START WITH 1');
            DB::statement('CREATE SEQUENCE process_machine_seq START WITH 1');
            DB::statement('CREATE SEQUENCE process_machine_variable_seq START WITH 1');
            DB::statement('CREATE SEQUENCE process_machine_record_seq START WITH 1');
            DB::statement('CREATE SEQUENCE process_final_evaluation_seq START WITH 1');
            DB::statement('CREATE SEQUENCE storage_seq START WITH 1');
            DB::statement('CREATE SEQUENCE material_request_seq START WITH 1');
            DB::statement('CREATE SEQUENCE material_request_detail_seq START WITH 1');
            DB::statement('CREATE SEQUENCE supplier_response_seq START WITH 1');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop sequences first (PostgreSQL only)
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP SEQUENCE IF EXISTS supplier_response_seq');
            DB::statement('DROP SEQUENCE IF EXISTS material_request_detail_seq');
            DB::statement('DROP SEQUENCE IF EXISTS material_request_seq');
            DB::statement('DROP SEQUENCE IF EXISTS storage_seq');
            DB::statement('DROP SEQUENCE IF EXISTS process_final_evaluation_seq');
            DB::statement('DROP SEQUENCE IF EXISTS process_machine_record_seq');
            DB::statement('DROP SEQUENCE IF EXISTS process_machine_variable_seq');
            DB::statement('DROP SEQUENCE IF EXISTS process_machine_seq');
            DB::statement('DROP SEQUENCE IF EXISTS material_movement_log_seq');
            DB::statement('DROP SEQUENCE IF EXISTS batch_raw_material_seq');
            DB::statement('DROP SEQUENCE IF EXISTS production_batch_seq');
            DB::statement('DROP SEQUENCE IF EXISTS customer_order_seq');
            DB::statement('DROP SEQUENCE IF EXISTS raw_material_seq');
            DB::statement('DROP SEQUENCE IF EXISTS raw_material_base_seq');
            DB::statement('DROP SEQUENCE IF EXISTS operator_seq');
            DB::statement('DROP SEQUENCE IF EXISTS process_seq');
            DB::statement('DROP SEQUENCE IF EXISTS machine_seq');
            DB::statement('DROP SEQUENCE IF EXISTS standard_variable_seq');
            DB::statement('DROP SEQUENCE IF EXISTS supplier_seq');
            DB::statement('DROP SEQUENCE IF EXISTS raw_material_category_seq');
            DB::statement('DROP SEQUENCE IF EXISTS customer_seq');
            DB::statement('DROP SEQUENCE IF EXISTS operator_role_seq');
            DB::statement('DROP SEQUENCE IF EXISTS movement_type_seq');
            DB::statement('DROP SEQUENCE IF EXISTS status_seq');
            DB::statement('DROP SEQUENCE IF EXISTS unit_of_measure_seq');
        }

        // Drop tables in reverse order
        Schema::dropIfExists('supplier_response');
        Schema::dropIfExists('material_request_detail');
        Schema::dropIfExists('material_request');
        Schema::dropIfExists('storage');
        Schema::dropIfExists('process_final_evaluation');
        Schema::dropIfExists('process_machine_record');
        Schema::dropIfExists('process_machine_variable');
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
