<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeamsMatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Teams_Matches', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id_team')->unsigned();
            $table->foreign('id_team')->references('id_team')->on('Teams');
            $table->integer('id_match')->unsigned();
            $table->foreign('id_match')->references('id_match')->on('Matches');
            $table->integer('score')->nullable();
            $table->primary(['id_team', 'id_match']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Teams_Matches');
    }
}