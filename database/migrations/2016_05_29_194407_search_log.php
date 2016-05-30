<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SearchLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('search_log', function (Blueprint $table) {
            $table->increments('id');
			$table->string('ip',20)->nullable();
			$table->text('search_term')->nullable();
			$table->string('detected_category',50)->nullable();
			$table->string('correct_category',50)->nullable();
			$table->timestamp('correct_date')->nullable();
			$table->integer('id_admin')->unsigned()->nullable();
			$table->tinyInteger('verified')->nullable();			
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
        Schema::drop('search_log');
    }
}
