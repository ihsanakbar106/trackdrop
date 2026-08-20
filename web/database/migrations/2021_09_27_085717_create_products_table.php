<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->integer('session_id')->nullable();
            $table->bigInteger('shopify_product_id')->nullable();
            $table->bigInteger('shopify_variant_id')->nullable();
            $table->longText('body_html')->nullable();
            $table->text('title')->nullable();
            $table->string('product_type')->nullable();
            $table->text('handle')->nullable()->nullable();
            $table->string('published_scope')->nullable();
            $table->text('tags')->nullable();
            $table->string('vendor')->nullable();
            $table->text('image')->nullable();
            $table->text('options')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->boolean('status')->default(false);
            $table->boolean('is_gifted')->default(false);
            $table->string('product_status')->nullable();
            $table->timestamps();

            $table->index('session_id');
            $table->index('shopify_product_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
