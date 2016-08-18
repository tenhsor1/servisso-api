<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BranchVerificationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('branch_verification', function (Blueprint $table) {
            $table->increments('id');
			$table->integer('branch_id')->unsigned();
			
			// posibles valores: (phone,address,identity)
			$table->string('verification_type',50); 
			
			// posibles valores: (INE, pasaporte,licencia de conducir,cédula profesional)
			$table->string('description',150)->nullable(); 
			
			$table->string('url_verification_img')->nullable();
			$table->softDeletes();
            $table->timestamps();
			$table->foreign('branch_id')->references('id')->on('branches');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('branch_verification');
    }
}
