<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NotificationsMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('receiver_id')->nullable();
            $table->integer('object_id')->unsigned();
            $table->string('object_type', 128);
            $table->integer('sender_id');
            $table->string('sender_type');
            $table->string('verb', 90);
            $table->string('extra', 1000)->nullable();
            $table->boolean('is_open')->default(false);
            $table->boolean('is_read')->default(false);
            $table->tinyInteger('type')->default(0)->comment('0 = GENERAL, 1 = MESSAGE');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('receiver_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('notifications');
    }
}
