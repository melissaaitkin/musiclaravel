<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSongsTable extends Migration
{

    public function up()
    {
        Schema::create('songs', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('title');
            $table->text('album')->nullable();
            $table->smallInteger('year')->nullable();
            $table->char('file_type', 4);
            $table->string('track_no')->nullable();
            $table->string('genre')->nullable();
            $table->string('location')->nullable();
            $table->string('composer')->nullable();
            $table->string('playtime')->nullable();
            $table->integer('filesize')->nullable();
            $table->integer('artist_id')->unsigned();
            $table->timestamps();       
        });
    }

    public function down()
    {
        Schema::dropIfExists('songs');
    }
}
