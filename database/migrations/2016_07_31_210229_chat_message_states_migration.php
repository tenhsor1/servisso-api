<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChatMessageStatesMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_message_states', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('chat_message_id')->unsigned();
            $table->integer('chat_participant_id')->unsigned();
            $table->string('state', 20);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('chat_message_id')->references('id')->on('chat_messages');
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
        Schema::drop('chat_message_states');
    }
}
