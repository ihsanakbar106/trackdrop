<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->integer('session_id')->nullable();
            $table->text('sender_name')->nullable();
            $table->text('sender_email')->nullable();
            $table->text('merchant_email')->nullable();
            $table->text('from_sms_number')->nullable();
            $table->text('slack_client_id')->nullable();
            $table->text('slack_secret_id')->nullable();
            $table->timestamps();
            $table->index('session_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settings');
    }
}
