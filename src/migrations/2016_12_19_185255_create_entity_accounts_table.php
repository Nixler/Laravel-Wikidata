<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEntityAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entity_accounts', function (Blueprint $table) {

            $table->bigIncrements('id');
            $table->bigInteger('entity_id')->unsigned()->unique();
            $table->string('provider')->unsigned();
            $table->string('account_id')->unsigned();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('entity_accounts');
    }
}
