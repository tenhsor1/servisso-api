<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FaceChatResponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('face_chat_responses', function (Blueprint $table) {
            $table->increments('id');
			$table->integer("id_chat")->unsigned();
			$table->string("step",50);
			$table->text("response");
			$table->string("response_type",20);
            $table->timestamps();
			$table->foreign('id_chat')->references('id')->on('face_chat');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('face_chat_responses');
    }
}
