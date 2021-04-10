<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlaylistsTable extends Migration
{

    public function up()
    {
        Schema::create('playlists', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->json('playlist');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('playlists');
    }
}
