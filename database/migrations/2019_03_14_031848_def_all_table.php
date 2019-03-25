<?php

use Illuminate\Database\Migrations\Migration;

class DefAllTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('status')->insert(
            [[
                'name' => 'ACTIVE',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ], [
                'name' => 'INACTIVE',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ], [
                'name' => 'DELETED',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ], [
                'name' => 'WAITING',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ], [
                'name' => 'QUEUED',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ], [
                'name' => 'PENDING',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ], [
                'name' => 'PROCESSING',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ], [
                'name' => 'DRAFT',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]]
        );

        // user_type table
        DB::table('user_type')->insert(
            [[
                'name' => 'customer',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]]
        );
        // user_type table
        DB::table('user_user_type')->insert(
            [[
                'user_id' => 1,
                'user_type_id' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]]
        );

        // gender table
        DB::table('gender')->insert(
            [[
                'name' => 'ไม่ระบุ',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ], [
                'name' => 'ชาย',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ], [
                'name' => 'หญิง',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]]
        );

        // User table
        DB::table('user')->insert(
            [[
                'gender_id' => 1,
                'username' => 'customer',
                'fullname' => 'customer',
                'email' => 'customer@gmail.com',
                'facebook_id' => null,
                'google_id' => null,
                'password' => '$2y$10$g6K.K8Noy5W1W9f6sbj5g./rvxwNRiwmB024FWQlWRUoao8YoI7KC',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]]
        );
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
