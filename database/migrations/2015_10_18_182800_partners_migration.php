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
			$table->string('email',70)->unique();
			$table->string('password',100);
			$table->string('name',45);
			$table->string('lastname',45);
			$table->string('birthdate',45)->nullable();
			$table->string('phone',20);
			$table->string('address',150);
			$table->string('zipcode',45)->nullable();
			$table->integer('state_id')->unsigned();
			$table->integer('country_id')->unsigned();
			$table->string('status',45)->nullable();
			$table->integer('plan_id')->unsigned()->default(0);
			$table->integer('role_id')->unsigned()->default(0);
			$table->integer('role')->unsigned()->default(0);
            $table->string('token', 500)->nullable();
            $table->boolean('confirmed')->default(false);
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
