<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChatParticipantsMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_participants', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('chat_room_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->integer('object_id')->unsigned()->nullable();
            $table->string('object_type', 45)->nullable();
            $table->boolean('open')->default(false);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('chat_room_id')->references('id')->on('chat_rooms');
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
        Schema::drop('chat_participants');
    }
}
