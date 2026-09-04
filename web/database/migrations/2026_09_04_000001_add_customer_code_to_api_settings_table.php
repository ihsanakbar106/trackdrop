<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomerCodeToApiSettingsTable extends Migration
{
    public function up()
    {
        Schema::table('api_settings', function (Blueprint $table) {
            $table->string('customer_code')->nullable()->after('api_key');
        });
    }

    public function down()
    {
        Schema::table('api_settings', function (Blueprint $table) {
            $table->dropColumn('customer_code');
        });
    }
}
