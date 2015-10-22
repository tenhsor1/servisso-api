<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BranchesTable extends Migration
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
			$table->string('address',70);
			$table->string('phone',20);
			$table->double('latitude',11,7);
			$table->double('longitude',11,7);
			$table->integer('state_id')->unsigned();
			$table->string('schedule',100);
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
        Schema::drop('branches');
    }
}
