<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeneralSlackNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('general_slack_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('slack_type')->nullable();
            $table->text('title')->nullable();
            $table->text('description')->nullable();
            $table->longText('data')->nullable();
            $table->boolean('active_status')->default(0)->nullable();
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
        Schema::dropIfExists('general_slack_notifications');
    }
}
