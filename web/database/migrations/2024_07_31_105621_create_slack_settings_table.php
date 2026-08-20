<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSlackSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('slack_settings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('session_id')->nullable();
            $table->text('code')->nullable();
            $table->text('slack_access_token')->nullable();
            $table->text('slack_refresh_token')->nullable();
            $table->text('slack_webhook_url')->nullable();
            $table->string('channel_id')->nullable();
            $table->string('channel_name')->nullable();
            $table->text('klaviyo_api_keys')->nullable();
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
        Schema::dropIfExists('slack_settings');
    }
}
