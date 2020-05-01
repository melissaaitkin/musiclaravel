<?php

namespace MySounds\Http\Controllers;

use Illuminate\Http\Request;
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
        // TODO retrieve from Redis when add playlist functionality is added
        $records = Song::get(['id','title'])->take(4)->toArray();
        $songs = [];
        foreach($records as $record) {
            $songs[$record['id']] = $record['title'];
        }
        $playlists = [
            'dance'  => json_encode($songs),
        ];
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
