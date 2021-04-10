<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToArtists extends Migration
{

    public function up()
    {
        Schema::table('artists', function (Blueprint $table) {
            $table->text('location')->nullable();
            $table->text('photo')->nullable();
        });
    }

    public function down()
    {
        Schema::table('artists', function (Blueprint $table) {
            $table->dropColumn('location');
            $table->dropColumn('photo');
        });
    }
}
