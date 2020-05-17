<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoleGamePurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('role_game_purchases', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('role_game_id')->unsigned();

            $table->foreign('role_game_id')
                ->references('id')->on('role_games')
                ->onDelete('cascade');
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
        Schema::dropIfExists('role_game_purchases');
    }
}
