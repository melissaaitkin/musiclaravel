<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArtistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('artists', function (Blueprint $table) {
            $table->increments('id');
            $table->string('artist');
            $table->boolean('is_group');
            $table->string('country');            
            $table->timestamps();
            $table->softDeletes();
            // TODO add foreign keys and casacding deletes
            //$table->foreign('user_id')
              //  ->references('id')->on('users')
                //->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('artists');
    }
}
