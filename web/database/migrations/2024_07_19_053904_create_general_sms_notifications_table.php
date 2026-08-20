<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeneralSmsNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('general_sms_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('sms_type')->nullable();
            $table->text('title')->nullable();
            $table->text('description')->nullable();
            $table->longText('data')->nullable();
            $table->boolean('active_status')->default(1)->nullable();
            $table->integer('priority')->nullable();
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
        Schema::dropIfExists('general_sms_notifications');
    }
}
