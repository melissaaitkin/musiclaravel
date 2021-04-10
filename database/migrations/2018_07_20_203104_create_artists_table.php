<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArtistsTable extends Migration
{
    public function up()
    {
        Schema::create('artists', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('artist');
            $table->boolean('is_group');
            $table->string('country')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('artists');
    }
}
