<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Users', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id_user');
            $table->string('name', 255);
            $table->string('surname', 255);
            $table->string('nickname', 255)->unique();
            $table->string('email', 255)->unique();
            $table->string('password', 255);
            $table->date('birthdate')->nullable();
            $table->string('role_user', 255)->nullable();
            $table->integer('id_team')->unsigned()->nullable();
            $table->foreign('id_team')->references('id_team')->on('Teams');
            $table->string('role_team', 255)->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Users');
    }
}