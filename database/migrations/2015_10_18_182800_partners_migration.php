<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PartnersMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->increments('id');
			$table->string('email',70);
			$table->string('password',100);
			$table->string('name',45);
			$table->string('lastname',45);
			$table->string('birthdate',45);
			$table->string('phone',20);
			$table->string('address',45);
			$table->string('zipcode',45);
			$table->integer('state_id')->unsigned();
			$table->integer('country_id')->unsigned();
			$table->string('status',45);
			$table->integer('plan_id')->unsigned();
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
        Schema::drop('partners');
    }
}