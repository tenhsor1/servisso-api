<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AdminMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email',70);
            $table->string('password',100);
            $table->string('name',45);
            $table->string('last_name',45);
            $table->string('address',90);
            $table->string('phone',20);
            $table->string('zipcode', 10);
            $table->integer('state_id')->unsigned();
            $table->integer('country_id')->unsigned();
            $table->integer('role_id')->unsigned();
            $table->integer('update_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('admins');
    }
}
