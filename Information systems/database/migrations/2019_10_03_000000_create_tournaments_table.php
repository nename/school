<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTournamentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Tournaments', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id_tournament');
            $table->string('name', 255);
            $table->datetime('start_date');
            $table->datetime('end_date');
            $table->text('registration_ended')->nullable();
            $table->text('registration_fee');
            $table->text('description');
            $table->integer('number_of_players')->unsigned();
            $table->text('reward');
            $table->integer('max_number_of_teams')->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Tournaments');
    }
}