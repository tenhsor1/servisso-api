<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CompaniesMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('partner_id')->unsigned();
			$table->string('name',60);
			$table->text('description',500);
			$table->integer('category_id')->unsigned();
			$table->integer('role_id')->unsigned()->nullable();
            $table->integer('role')->unsigned()->nullable();
			$table->string('image',120)->nullable();
			$table->string('thumbnail',120)->nullable();
			$table->softDeletes();
            $table->timestamps();

			$table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('partner_id')->references('id')->on('partners');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('companies');
    }
}
