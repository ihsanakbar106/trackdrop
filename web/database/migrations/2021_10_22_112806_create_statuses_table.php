<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('statuses', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('fulfillment_id')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('carrier_api_id')->nullable();
            $table->string('carrier_code')->nullable();
            $table->string('status')->nullable();
            $table->longText('track_info')->nullable();
            $table->timestamps();

            $table->index('fulfillment_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('statuses');
    }
}
