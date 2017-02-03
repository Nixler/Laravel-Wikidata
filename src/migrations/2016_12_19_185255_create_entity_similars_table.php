<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEntitySimilarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entity_similars', function (Blueprint $table) {

            $table->bigIncrements('id');
            $table->bigInteger('entity_id')->unsigned()->unique();
            $table->integer('similar_type')->unsigned();
            $table->mediumText('similar_id');
            $table->integer('rank');
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
        Schema::dropIfExists('entity_similars');
    }
}
