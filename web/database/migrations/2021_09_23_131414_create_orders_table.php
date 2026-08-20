<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('session_id')->nullable();
            $table->bigInteger('shopify_order_id')->nullable();
            $table->bigInteger('checkout_id')->nullable();
            $table->bigInteger('location_id')->nullable();
            $table->bigInteger('shopify_fulfillment_order_id')->nullable();
            $table->string('name')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('country')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('order_status_url')->nullable();
            $table->string('fulfillment_status')->nullable();
            $table->string('financial_status')->nullable();
            $table->text('customer')->nullable();
            $table->float('total_line_items_price')->nullable();
            $table->float('total_price')->nullable();
            $table->float('subtotal_price')->nullable();
            $table->string('currency')->nullable();
            $table->string('checkout_token')->nullable();
            $table->timestamp('last_tracking_status_check_time')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->text('shipping_address')->nullable();
            $table->text('billing_address')->nullable();
            $table->timestamps();

            $table->index('session_id');
            $table->index('shopify_order_id');
            $table->index('checkout_id');
            $table->index('location_id');
            $table->index('shopify_fulfillment_order_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
