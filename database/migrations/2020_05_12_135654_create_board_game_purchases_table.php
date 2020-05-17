<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBoardGamePurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('board_game_purchases', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('board_game_id')->unsigned();

            $table->foreign('board_game_id')
                ->references('id')->on('board_games')
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
        Schema::dropIfExists('board_game_purchases');
    }
}
