<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConstraintToSongsTable extends Migration
{

    public function up()
    {
        Schema::table('songs', function (Blueprint $table) {
            $table->foreign('artist_id')->references('id')->on('artists')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('songs', function (Blueprint $table) {
            //
        });
    }
}
