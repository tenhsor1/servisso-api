<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TokenPrediction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::create('token_prediction', function (Blueprint $table) {
            $table->increments('id');
			$table->string('token',100);
			$table->integer('expired')->unsigned();
			$table->integer('issued')->unsigned();
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
        Schema::drop('token_prediction');
    }
}
