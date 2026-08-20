<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrackingPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tracking_pages', function (Blueprint $table) {
            $table->id();
            $table->integer('session_id')->nullable();
            $table->text('page_name')->nullable();
            $table->text('page_handle')->nullable();
            $table->text('feature_image')->nullable();
            $table->string('theme_type')->nullable(); // Shopify Open Store, Modern, Custom, Source code
            $table->bigInteger('shopify_page_id')->nullable();
            $table->text('shopify_page_template')->nullable();
            $table->text('edit_page_url')->nullable();
            $table->text('editor_url')->nullable();

            $table->text('permalink')->nullable();

            $table->text('uuid')->nullable();
            $table->text('store_uuid')->nullable();

            $table->longText('page_code')->nullable(); // for source code

            $table->text('page_url')->nullable();


            $table->boolean('automatic_transfer_on_new_theme_publish')->default(0)->nullable();
            $table->boolean('active_status')->default(0)->nullable();

            $table->longText('data')->nullable();
            $table->longText('logo')->nullable();
            $table->longText('icon')->nullable();
            $table->timestamp('tracking_page_published_at')->nullable();

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
        Schema::dropIfExists('tracking_pages');
    }
}
