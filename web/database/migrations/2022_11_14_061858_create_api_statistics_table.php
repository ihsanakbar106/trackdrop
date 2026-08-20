<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApiStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_statistics', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('session_id')->nullable();
            $table->bigInteger('order_id')->nullable();
            $table->bigInteger('fulfillment_id')->nullable();
            $table->bigInteger('request_count')->nullable();
            $table->string('shipment_status')->nullable();
            $table->text('message')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('api_statistics');
    }
}
