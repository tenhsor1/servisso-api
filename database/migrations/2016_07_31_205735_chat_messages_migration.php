<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChatMessagesMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('chat_room_id')->unsigned();
            $table->integer('chat_participant_id')->unsigned();
            $table->text('message');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('chat_room_id')->references('id')->on('chat_rooms');
            $table->foreign('chat_participant_id')->references('id')->on('chat_participants');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('chat_messages');
    }
}
