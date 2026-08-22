<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTokenInfrastructureToSessions extends Migration
{
    public function up()
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->text('access_token')->nullable()->change();
            $table->text('refresh_token')->nullable();
            $table->timestamp('access_token_expires_at')->nullable();
            $table->timestamp('refresh_token_expires_at')->nullable();
            $table->string('token_kind', 32)->default('legacy');
        });
    }

    public function down()
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropColumn(['refresh_token', 'access_token_expires_at', 'refresh_token_expires_at', 'token_kind']);
            $table->string('access_token')->nullable()->change();
        });
    }
}
