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
        if (!Schema::hasTable('order_envio_tracking')) {
            Schema::create('order_envio_tracking', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('order_id')->nullable()->index();
                $table->integer('destination_id')->nullable()->index();
                $table->integer('envio_id')->nullable()->index();
                $table->string('envio_codigo')->nullable();
                $table->string('status')->default('pending');
                $table->text('error_message')->nullable();
                $table->json('request_data')->nullable();
                $table->json('response_data')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('order_envio_tracking')) {
            Schema::dropIfExists('order_envio_tracking');
        }
    }
};
