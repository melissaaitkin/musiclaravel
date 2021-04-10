<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNotesFieldToSongs extends Migration
{

    public function up()
    {
        Schema::table('songs', function (Blueprint $table) {
            $table->text('notes')->nullable();
        });   
    }

    public function down()
    {
        Schema::table('songs', function (Blueprint $table) {
            $table->dropColumn(['notes']);
        });
    }
}
