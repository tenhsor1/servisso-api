<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TasksMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('category_id')->unsigned();
            $table->text('description');
            $table->boolean('delivery_service');
            $table->dateTime('date');
            $table->tinyInteger('status')->default(0)->comment('0 = open, 1 = closed, 2 = finished');
            $table->float('latitude')->nullable()->default(null);
            $table->float('longitude')->nullable()->default(null);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('category_id')->references('id')->on('categories');
        });
        DB::statement('ALTER TABLE tasks ADD COLUMN geom geometry(Point,4326);');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tasks');
    }
}
