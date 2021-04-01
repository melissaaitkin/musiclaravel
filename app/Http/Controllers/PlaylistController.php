<?php

namespace App\Http\Controllers;

use App\Music\Playlist\Playlist;
use App\Music\Song\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PlaylistController extends Controller
{

    /**
     * Display playlists
     *
     * @return Response
     */
    public function index()
    {
        $playlists = Playlist::get(['name']);
        return view('playlists', ['playlists' => $playlists ?? []]);
    }

    /**
     * Remove the playlist
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($playlist)
    {
        Playlist::where(['name' => $playlist])->delete();
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
        $playlists = Playlist::get(['name']);
        return ['playlists' => $playlists, 'status_code' => 200];
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

        $playlist = Playlist::where(['name' => $request->playlist])->get(['playlist'])->toArray();
        return ['songs' => json_decode($playlist[0]['playlist']), 'status_code' => 200];
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

        $playlist = Playlist::firstOrNew(array('name' => $request->playlist));
        $playlist->name = $request->playlist;

        $song = Song::find($request->id);
        if(isset($playlist->playlist)) {
            $existing_playlist = (array) json_decode($playlist->playlist);
        } else {
            $playlist->playlist = [];
        }
        $existing_playlist[] = ['id' => $request->id, 'title' => $song['title']];
        $playlist->playlist = json_encode($existing_playlist);
        $playlist->save();

        return ['status_code' => 200];
    }
}
