<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TaskBranchLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_branch_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('task_branch_id')->unsigned();
            $table->string('type', 50);
            $table->string('value', 50)->nullable();
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
        Schema::drop('task_branch_logs');
    }
}
