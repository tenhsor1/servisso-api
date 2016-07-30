<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MessagesMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sender_id')->unsigned();
            $table->string('sender_type', 45);
            $table->integer('receiver_id')->unsigned();
            $table->string('receiver_type', 45);
            $table->integer('object_id')->nullable()->unsigned();
            $table->string('object_type', 45)->nullable();
            $table->text('message');
            $table->tinyInteger('status')->default(0)->comment('0 = unseen, 1 = seen');
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
        Schema::drop('messages');
    }
}