<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable(false)->unique();
            $table->integer('plan_id')->nullable();
            $table->bigInteger('meta_field_id')->nullable();
            $table->string('survey_limit')->nullable();
            $table->bigInteger('credits')->default(0)->nullable();
            $table->bigInteger('used_credits')->default(0)->nullable();
            $table->string('shopifyPlus')->nullable();
            $table->string('partnerDevelopment')->nullable();
            $table->bigInteger('shop_widgets_metafield_id')->nullable();
            $table->bigInteger('warrenty_insurance_metafield_id')->nullable();
            $table->bigInteger('custom_checkout_metafield_id')->nullable();
            $table->bigInteger('gift_wrap_metafield_id')->nullable();
            $table->bigInteger('delivery_date_metafield_id')->nullable();
            $table->bigInteger('address_validation_metafield_id')->nullable();
            $table->bigInteger('age_verif_metafield_id')->nullable();
            $table->string('shop')->nullable(false);
            $table->boolean('is_online')->nullable(false);
            $table->string('state')->nullable(false);
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
        Schema::dropIfExists('sessions');
    }
}
