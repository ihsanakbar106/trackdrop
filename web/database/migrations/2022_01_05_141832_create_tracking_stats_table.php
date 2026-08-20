<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrackingStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tracking_stats', function (Blueprint $table) {
            $table->id();
            $table->integer('session_id')->nullable();
            $table->bigInteger('order_id')->nullable();
            $table->bigInteger('shopify_order_id')->nullable();
            $table->bigInteger('fulfillment_id')->nullable();
            $table->bigInteger('shopify_fulfillment_id')->nullable();
            $table->bigInteger('count')->nullable();
            $table->string('status')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('browser')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('operating_system')->nullable();
            $table->string('device')->nullable();
            $table->timestamps();

            $table->index('session_id');
            $table->index('order_id');
            $table->index('shopify_order_id');
            $table->index('fulfillment_id');
            $table->index('shopify_fulfillment_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tracking_stats');
    }
}
