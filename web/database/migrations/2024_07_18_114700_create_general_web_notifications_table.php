<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeneralWebNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('general_web_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('notification_type')->nullable();
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
        Schema::dropIfExists('general_web_notifications');
    }
}
