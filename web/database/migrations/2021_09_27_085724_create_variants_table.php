<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVariantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('variants', function (Blueprint $table) {
            $table->id();
            $table->integer('session_id');
            $table->bigInteger('shopify_variant_id');
            $table->bigInteger('shopify_product_id');
            $table->bigInteger('product_id');
            $table->string('title');
            $table->text('image')->nullable();
            $table->integer('position')->nullable();
            $table->float('price');
            $table->string('sku')->nullable();
            $table->string('inventory_policy')->nullable();
            $table->string('compare_at_price')->nullable();
            $table->string('fulfillment_service')->nullable();
            $table->string('inventory_management')->nullable();
            $table->binary('taxable')->nullable();
            $table->string('barcode')->nullable();
            $table->float('weight')->nullable();
            $table->string('weight_unit')->nullable();
            $table->text('admin_graphql_api_id')->nullable();
            $table->bigInteger('inventory_item_id')->nullable();
            $table->integer('inventory_quantity')->nullable();
            $table->integer('old_inventory_quantity')->nullable();
            $table->binary('requires_shipping')->nullable();
            $table->float('grams')->nullable();
            $table->string('option1')->nullable();
            $table->string('option2')->nullable();
            $table->string('option3')->nullable();
            $table->timestamps();

            $table->index('session_id');
            $table->index('shopify_product_id');
            $table->index('shopify_variant_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('variants');
    }
}
