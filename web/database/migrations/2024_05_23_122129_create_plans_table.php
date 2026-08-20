<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->integer('type')->nullable();
            $table->boolean('unlimited')->default(false)->nullable();
            $table->string('category', 191)->nullable();
            $table->string('name', 191)->nullable();
            $table->decimal('price', 8, 2)->nullable();
            $table->decimal('usage_charges', 8, 2)->default(0)->nullable();
            $table->string('response_limit')->nullable();
            $table->decimal('capped_amount', 8, 2)->nullable();
            $table->string('terms', 191)->nullable();
            $table->integer('trial_days')->nullable();
            $table->boolean('test')->default(false);
            $table->boolean('on_install')->default(false);
            $table->integer('sort')->nullable();
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
        Schema::dropIfExists('plans');
    }
}
