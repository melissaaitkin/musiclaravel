<?php

namespace MySounds\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redis as Redis;

use Log;

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
        foreach ($records as $playlist => $songs) {
           $playlists[] = $playlist;
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

    /**
     * Retrieve songs in a playlist
     *
     * @param Request $request
     * @return Response
     */
    public function songs(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'playlist' => 'required|max:100',
        ]);

        // Validate parameters
        if($validator->fails()):
            return ['errors' => $validator->errors(), 'status_code' => 422];
        endif;

        $records = json_decode(Redis::get('playlists'));
        foreach ($records as $k => $v) {
           if ($k === $request->playlist) {
                $data = $v;
           }
        }

        return ['data' => $data ?? null, 'status_code' => 200];
    }

}