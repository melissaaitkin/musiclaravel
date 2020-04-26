<?php

namespace MySounds;

use Illuminate\Database\Eloquent\Model;

class Artist extends Model
{

    protected $table = 'artists';

    /**
     * Is the "artist" a Compilation?
     *
     * @param integer $id Artist id
     * @return boolean
     */
    public static function is_compilation($id)
    {
        $artist = Artist::where(["id" => $id])->get(['artist'])->first()->toArray();
        return $artist['artist'] === 'Compilations';
    }

}
