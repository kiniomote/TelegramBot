<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWarGamePurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('war_game_purchases', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('war_game_id')->unsigned();

            $table->foreign('war_game_id')
                ->references('id')->on('war_games')
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
        Schema::dropIfExists('war_game_purchases');
    }
}
