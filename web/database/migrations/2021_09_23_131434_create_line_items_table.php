<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLineItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('line_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('session_id')->nullable();
            $table->bigInteger('order_id')->nullable();
            $table->bigInteger('shopify_order_id')->nullable();
            $table->bigInteger('shopify_lineitem_id')->nullable();
            $table->bigInteger('shopify_fulfillment_order_id')->nullable();
            $table->bigInteger('variant_id')->nullable();
            $table->bigInteger('product_id')->nullable();
            $table->string('title')->nullable();
            $table->integer('quantity')->nullable();
            $table->integer('grams')->nullable();
            $table->string('sku')->nullable();
            $table->float('price')->nullable();
            $table->integer('fulfillable_quantity')->nullable();
            $table->string('fulfillment_status')->nullable();
            $table->string('fulfillment_service')->nullable();
            $table->string('fulfillment_response')->nullable();
            $table->string('variant_title')->nullable();
            $table->text('properties')->nullable();
            $table->text('image')->nullable();
            $table->timestamps();

            $table->index('session_id');
            $table->index('order_id');
            $table->index('shopify_order_id');
            $table->index('shopify_lineitem_id');
            $table->index('variant_id');
            $table->index('product_id');
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
        Schema::dropIfExists('line_items');
    }
}
