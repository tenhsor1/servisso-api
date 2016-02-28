<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BranchesMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('branches', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->unsigned();
			$table->string('address',150);
			$table->string('phone',20);
			$table->double('latitude',11,7);
			$table->double('longitude',11,7);
			$table->integer('state_id')->unsigned()->nullable();
            $table->string('schedule',100)->nullable();
			$table->integer('role_id')->unsigned()->nullable();
            $table->integer('role')->unsigned()->nullable();
			$table->softDeletes();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('branches');
    }
}
