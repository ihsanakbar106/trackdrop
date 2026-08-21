<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('shop_id')->nullable();
            $table->string('language')->default('English')->nullable();
            $table->boolean('is_default')->default(0)->nullable();
            $table->string('track_your_order')->default('TRACK YOUR ORDER')->nullable();
            $table->string('order_number')->default('Order number')->nullable();
            $table->string('tracking_number')->default('Tracking number')->nullable();
            $table->string('email_phone_number')->default('Email or Phone number')->nullable();
            $table->string('track_btn')->default('Track')->nullable();
            $table->string('order_number_placeholder')->default('Enter your order number')->nullable();
            $table->string('order_number_error')->default('Please enter order number')->nullable();
            $table->string('tracking_number_placeholder')->default('Enter your tracking number')->nullable();
            $table->string('tracking_number_error')->default('Please enter tracking number')->nullable();
            $table->string('email_phone_number_placeholder')->default('Enter your email or phone number')->nullable();
            $table->string('email_phone_number_error')->default('Please enter your email or phone number')->nullable();
            $table->string('order_status_text')->default('Your order is')->nullable();
            $table->string('carrier_title')->default('Carrier')->nullable();
            $table->string('product_title')->default('Product (s)')->nullable();
            $table->string('page_not_publish')->default('Tracking page not published!')->nullable();
            $table->string('package_content')->default('Package Contents')->nullable();
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
        Schema::dropIfExists('translations');
    }
}
