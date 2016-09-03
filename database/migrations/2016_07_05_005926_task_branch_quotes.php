<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TaskBranchQuotes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_branch_quotes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('task_branch_id')->unsigned();
            $table->decimal('price', 10, 2)->nullable();
            $table->text('description');
			$table->tinyInteger('status')->default(0)->comment('0 = open, 1 = accepted');
			$table->dateTime('date');
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('task_branch_id')->references('id')->on('task_branches');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('task_branch_quotes');
    }
}
