<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEntityTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entity_translations', function (Blueprint $table) {

            $table->bigIncrements('id');
            $table->bigInteger('entity_id')->unsigned();
            $table->string('locale')->index();
            $table->string('title')->nullable();
            $table->string('headline')->nullable();
            $table->text('wiki')->nullable();

            $table->unique(['entity_id','locale']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('entity_translations');
    }
}
