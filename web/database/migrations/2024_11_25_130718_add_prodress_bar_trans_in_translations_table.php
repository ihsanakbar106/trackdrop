<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProdressBarTransInTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('translations', function (Blueprint $table) {
            $table->string('pb_delivered')->after('package_content')->default('Delivered')->nullable();
            $table->string('pb_out_for_delivery')->after('package_content')->default('Out for Delivery')->nullable();
            $table->string('pb_in_transit')->after('package_content')->default('In Transit')->nullable();
            $table->string('pb_ordered')->after('package_content')->default('Ordered')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('translations', function (Blueprint $table) {
            //
        });
    }
}
