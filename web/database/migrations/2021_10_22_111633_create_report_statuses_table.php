<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_statuses', function (Blueprint $table) {
            $table->id();
            $table->integer('carrier_service_id')->nullable(); // tractory or easypost carrier statuses
            $table->string('status')->nullable();
            $table->string('public_text')->nullable();
            $table->string('background')->nullable();
            $table->string('colors')->nullable();
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
        Schema::dropIfExists('report_statuses');
    }
}
