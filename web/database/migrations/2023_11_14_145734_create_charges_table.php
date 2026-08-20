<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('charges', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('charge_id')->nullable();
            $table->bigInteger('session_id')->nullable();
            $table->bigInteger('plan_id')->nullable();
            $table->bigInteger('reference_charge')->nullable();
            $table->string('status')->nullable();
            $table->string('name')->nullable();
            $table->string('terms')->nullable();
            $table->string('type')->nullable();
            $table->float('price')->nullable();
            $table->string('interval')->nullable();
            $table->string('capped_amount')->nullable();
            $table->text('description')->nullable();
            $table->boolean('trial_days')->nullable();
            $table->boolean('test')->default(0);
            $table->timestamp('billing_on')->nullable();
            $table->timestamp('activated_on')->nullable();
            $table->timestamp('trial_ends_on')->nullable();
            $table->timestamp('cancelled_on')->nullable();
            $table->timestamp('expires_on')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->index('charge_id');
            $table->index('session_id');
            $table->index('plan_id');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('charges');
    }
};
