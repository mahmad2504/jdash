<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
			$table->string('name');
			$table->string('description')->nullable();
			$table->string('jiraquery');
			$table->integer('user_id');
			$table->string('last_synced')->default("Never");
			$table->string('estimation')->default(0);
			$table->string('jirauri');
			$table->date('sdate');
			$table->date('edate');
			$table->boolean('jira_dependencies')->default(0);
			$table->boolean('dirty')->default(1);
			$table->float('progress')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('projects');
    }
}
