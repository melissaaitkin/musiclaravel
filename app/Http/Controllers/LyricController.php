<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Music\Song\Song;
use Illuminate\Http\Request;

class LyricController extends Controller
{

    /**
     * Show the song lyrics
     *
     * @param int $id
     * @return string
     */
    public function show($id)
    {
        $song = Song::find($id);
        return view('lyrics', ['song' => $song]);
    }

}
