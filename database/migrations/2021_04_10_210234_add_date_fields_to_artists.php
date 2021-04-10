<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDateFieldsToArtists extends Migration
{

    public function up()
    {
        Schema::table('artists', function (Blueprint $table) {
            $table->integer('founded')->nullable();
            $table->integer('disbanded')->nullable();
        });
    }

    public function down()
    {
        Schema::table('artists', function (Blueprint $table) {
            $table->dropColumn('founded');
            $table->dropColumn('disbanded');
        });
    }
}
