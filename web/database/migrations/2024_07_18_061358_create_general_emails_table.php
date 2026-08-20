<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeneralEmailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('general_emails', function (Blueprint $table) {
            $table->id();
            $table->string('email_type')->nullable();
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
        Schema::dropIfExists('general_emails');
    }
}
