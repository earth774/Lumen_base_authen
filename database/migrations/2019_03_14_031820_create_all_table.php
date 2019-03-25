<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAllTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        // status table
        Schema::create('status', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 45);
            $table->timestamps();
        });

        // user_type table
        Schema::create('user_type', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255);
            $table->unsignedInteger('status_id')->default(1);
            $table->timestamps();
        });

        // user_user_type table
        Schema::create('user_user_type', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('user_type_id');
            $table->unsignedInteger('status_id')->default(1);
            $table->timestamps();
        });

        // gender table
        Schema::create('gender', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255);
            $table->unsignedInteger('status_id')->default(1);
            $table->timestamps();
        });

        // User table
        Schema::create('user', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('gender_id')->default(1);
            $table->string('username', 255);
            $table->string('fullname', 255);
            $table->string('email', 255);
            $table->string('facebook_id', 512)->nullable();
            $table->string('google_id', 512)->nullable();
            $table->string('password', 255);
            $table->string('profile_image', 255)->nullable();
            $table->dateTime('birthdate')->nullable();
            $table->string('phone_number', 15)->nullable();
            $table->unsignedInteger('status_id')->default(1);
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
        //
    }
}
