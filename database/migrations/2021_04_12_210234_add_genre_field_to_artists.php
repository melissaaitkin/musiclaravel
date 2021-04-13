<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGenreFieldToArtists extends Migration
{

    public function up()
    {
        Schema::table('artists', function (Blueprint $table) {
            $table->text('genres')->nullable();
        });
    }

    public function down()
    {
        Schema::table('artists', function (Blueprint $table) {
            $table->dropColumn('genres');
        });
    }
}
