<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Matches', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id_match')->unsigned();
            $table->integer('round_number')->unsigned();
            $table->integer('id_tournament')->unsigned();
            $table->foreign('id_tournament')->references('id_tournament')->on('Tournaments')->onDelete('cascade');
            $table->integer('id_next_match')->unsigned()->nullable();
            $table->foreign('id_next_match')->references('id_match')->on('Matches')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Matches');
    }
}