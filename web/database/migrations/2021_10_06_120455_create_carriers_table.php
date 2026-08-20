<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarriersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carriers', function (Blueprint $table) {
            $table->id();
            $table->integer('carrier_service_id')->nullable();
            $table->string('name')->nullable();
            $table->string('code')->nullable();
            $table->string('picture')->nullable();
            $table->string('homepage')->nullable();
            $table->string('type')->nullable();
            $table->timestamps();

            $table->index('carrier_service_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('carriers');
    }
}
