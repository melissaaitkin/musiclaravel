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
        $playlists = unserialize(Redis::get('playlists'));
        if (!$playlists) {
            $playlists = [];
        }
        return view('playlists', ['playlists' => array_keys($playlists)]);
    }

    /**
     * Remove the playlist
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($playlist)
    {
        $records = unserialize(Redis::get('playlists'));
        unset($records[$playlist]);
        Redis::set('playlists', serialize($records));
        return redirect('/playlists');
    }

    /**
     * Retrieve playlists
     *
     * @param Request $request
     * @return Response
     */
    public function playlists(Request $request)
    {
        $playlists = unserialize(Redis::get('playlists'));
        return ['playlists' => array_keys($playlists), 'status_code' => 200];
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
        if ($validator->fails()):
            return ['errors' => $validator->errors(), 'status_code' => 422];
        endif;

        $records = unserialize(Redis::get('playlists'));
        foreach ($records as $playlist => $songs) {
           if ($playlist === $request->playlist) {
                $data = $songs;
           }
        }

        return ['songs' => $data ?? null, 'status_code' => 200];
    }


    /**
     * Add songs to a playlist
     *
     * @param Request $request
     * @return Response
     */
    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'        => 'required|numeric',
            'playlist'  => 'required|max:100',
        ]);

        // Validate parameters
        if ($validator->fails()):
            return ['errors' => $validator->errors(), 'status_code' => 422];
        endif;

        $records = unserialize(Redis::get('playlists'));
        if (!isset($records[$request->playlist])) {
            $records[$request->playlist] = [];
        }
        $song = Song::find($request->id);
        $records[$request->playlist][$request->id] = $song['title'];

        Redis::set('playlists', serialize($records));

        return ['status_code' => 200];
    }
}