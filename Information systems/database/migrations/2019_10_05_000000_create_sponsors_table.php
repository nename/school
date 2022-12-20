<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSponsorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Sponsors', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id_sponsor');
            $table->string('name', 255);
            $table->integer('id_tournament')->unsigned();
            $table->foreign('id_tournament')->references('id_tournament')->on('Tournaments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Sponsors');
    }
}