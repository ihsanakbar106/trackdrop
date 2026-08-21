<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSeederLookupTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('name')->nullable();
            $table->integer('phonecode')->nullable();
        });

        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
        });

        Schema::create('delivery_options', function (Blueprint $table) {
            $table->id();
            $table->string('delivery_option')->nullable();
        });

        Schema::create('payment_options', function (Blueprint $table) {
            $table->id();
            $table->string('payment_option')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_options');
        Schema::dropIfExists('delivery_options');
        Schema::dropIfExists('provinces');
        Schema::dropIfExists('countries');
    }
}
