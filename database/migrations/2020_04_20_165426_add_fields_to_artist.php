<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToArtist extends Migration
{

    public function up()
    {
        Schema::table('artists', function (Blueprint $table) {
            $table->text('group_members')->nullable();
            $table->text('notes')->nullable();
        });    
    }

    public function down()
    {
        Schema::table('artists', function (Blueprint $table) {
            $table->dropColumn(['group_members', 'notes']);
        });
    }
}
