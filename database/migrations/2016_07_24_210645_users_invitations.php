<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UsersInvitations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_invitations', function (Blueprint $table) {
            $table->increments('id');
			$table->integer('user_id')->unsigned();
			$table->text('code')->nullable();
			$table->string('to_user_email', 45);
			$table->integer('to_user_id')->unsigned()->default(0);
			$table->string('comment')->nullable();
			$table->string('invitation_type', 45);
			$table->tinyInteger('sent')->default(0);
            $table->timestamps();
			$table->softDeletes();
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
        Schema::drop('users_invitations');
    }
}
