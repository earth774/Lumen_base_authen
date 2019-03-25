<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RefAllTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // user table
        Schema::table('user', function (Blueprint $table) {
            $table->foreign('gender_id')->references('id')->on('gender');
            $table->foreign('status_id')->references('id')->on('status');
        });

        // user_type table
        Schema::table('user_type', function (Blueprint $table) {
            $table->foreign('status_id')->references('id')->on('status');
        });

        // user_user_type table
        Schema::table('user_user_type', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('user');
            $table->foreign('user_type_id')->references('id')->on('user_type');
            $table->foreign('status_id')->references('id')->on('status');
        });

        // user_user_type table
        Schema::table('gender', function (Blueprint $table) {
            $table->foreign('status_id')->references('id')->on('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
