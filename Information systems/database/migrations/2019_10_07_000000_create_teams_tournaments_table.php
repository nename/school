<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeamsTournamentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Teams_Tournaments', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id_team')->unsigned();
            $table->foreign('id_team')->references('id_team')->on('Teams');
            $table->integer('id_tournament')->unsigned();
            $table->foreign('id_tournament')->references('id_tournament')->on('Tournaments');
            $table->primary(['id_team', 'id_tournament']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Teams_Tournaments');
    }
}