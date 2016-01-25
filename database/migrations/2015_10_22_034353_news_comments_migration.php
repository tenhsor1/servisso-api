<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NewsCommentsMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('news_comments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('news_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->text('comment');
            $table->tinyInteger('user_type');
			$table->integer('role_id')->unsigned();
			$table->integer('role')->unsigned(); 
            $table->timestamps();  
            $table->softDeletes();

            $table->foreign('news_id')->references('id')->on('news')->onDelete('cascade');    
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('news_comments');
    }
}
