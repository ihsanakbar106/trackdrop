<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmsNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms_notifications', function (Blueprint $table) {
            $table->id();
            $table->integer('session_id')->nullable();
            $table->bigInteger('notification_id')->nullable();
            $table->string('sms_type')->nullable();
            $table->text('title')->nullable();
            $table->text('description')->nullable();
            $table->longText('data')->nullable();
            $table->boolean('active_status')->default(0)->nullable();
            $table->integer('priority')->nullable();
            $table->timestamps();
            $table->index('session_id');
            $table->index('notification_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sms_notifications');
    }
}
