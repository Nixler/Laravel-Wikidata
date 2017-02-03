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
        Schema::create('entity_claims', function (Blueprint $table) {

            $table->bigIncrements('id');
            $table->bigInteger('entity_id')->unsigned();
            $table->integer('prop_id')->unsigned();
            $table->string('type');
            $table->integer('rank')->unsigned();
            $table->string('value');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('entity_claims');
    }
}
