<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Teams', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id_team');
            $table->string('name', 255);
            $table->string('abbreviation', 255);
            $table->string('logo')->default('team_icon_1.svg');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Teams');
    }
}