<?php

namespace MySounds\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis as Redis;

use MySounds\Music\Song\Song as Song;

class PlaylistController extends Controller
{

    /**
     * Display playlists
     *
     * @return Response
     */
    public function index()
    {

        $playlists = [];
        $records = json_decode(Redis::get('playlists'));
        foreach ($records as $k => $v) {
           $playlists[$k] = [];
           $songs = [];
           foreach ($v as $id => $title) {
            $songs[$id] = $title;
           }
           $playlists[$k] = json_encode($songs);
        }

        return view('playlists', ['playlists' => $playlists]);
    }

    /**
     * Show the form for creating a playlist
     *
     * @return Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created playlist
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
    }

}
