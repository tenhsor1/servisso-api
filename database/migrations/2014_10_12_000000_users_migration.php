<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UsersMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email',70)->unique();
            $table->string('password', 100);
            $table->string('name', 45);
            $table->string('lastname', 45)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('address', 90)->nullable();
            $table->string('zipcode', 10)->nullable();
            $table->integer('state_id')->unsigned()->nullable();
			$table->integer('role_id')->unsigned()->nullable();
            $table->integer('role')->unsigned()->nullable();
            $table->string('token', 600)->nullable();
            $table->boolean('confirmed')->default(false);
			$table->tinyInteger('enabled_companies')->default(0);
			$table->integer('invitations')->unsigned()->default(0);		
			$table->bigInteger('id_origin')->unsigned()->default(0);
			$table->tinyInteger('type_origin')->default(0)->comment('0 = servisso, 1 = servisso-face-bot');
			//$table->tinyInteger('user_as')->default(0);
            $table->softDeletes();
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
        Schema::drop('users');
    }
}
