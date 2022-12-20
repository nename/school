<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTournamentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Users_Tournaments', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id_user')->unsigned();
            $table->foreign('id_user')->references('id_user')->on('Users');
            $table->integer('id_tournament')->unsigned();
            $table->foreign('id_tournament')->references('id_tournament')->on('Tournaments');
            $table->string('role_tournament', 255);
            $table->primary(['id_user', 'id_tournament']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Users_Tournaments');
    }
}