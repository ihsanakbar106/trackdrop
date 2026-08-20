<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFulfillmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fulfillments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('session_id')->nullable();
            $table->bigInteger('order_id')->nullable();
            $table->bigInteger('shopify_order_id')->nullable();
            $table->bigInteger('fulfillment_id')->nullable();
            $table->bigInteger('location_id')->nullable();
            $table->string('shipment_id')->nullable();
            $table->string('name')->nullable();
            $table->string('tracking_company')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('fulfillment_status')->nullable();
            $table->string('status')->nullable();
            $table->string('service')->nullable();
            $table->string('shipment_status')->default('Pending')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('tracking_url')->nullable();

            $table->string('country')->nullable();
            $table->string('first_date')->nullable();
            $table->string('last_date')->nullable();
            $table->longText('tracking_complete_info')->nullable();
            $table->longText('track_info')->nullable();
            $table->longText('line_items')->nullable();
            $table->timestamp('shipment_register_time')->nullable();
            $table->text('shipment_last_event')->nullable();
            $table->string('original_country')->nullable();
            $table->timestamp('shipment_last_update_time')->nullable();
            $table->timestamps();

            $table->index('session_id');
            $table->index('fulfillment_id');
            $table->index('order_id');
            $table->index('shopify_order_id');
            $table->index('location_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fulfillments');
    }
}
