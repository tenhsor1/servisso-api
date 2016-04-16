<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UserSocialsMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_socials', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('email',70);
            $table->string('name', 45);
            $table->string('platform', 45);
            $table->string('platform_id', 45);
            $table->string('avatar', 255)->nullable();
            $table->string('token', 255);
            $table->softDeletes();
            $table->timestamps();
            $table->unique(array('user_id', 'platform'));
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('user_socials');
    }
}
