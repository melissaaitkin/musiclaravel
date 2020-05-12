<?php

namespace MySounds\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB as DB;

use MySounds\Music\Song\Song as Song;
use MySounds\Music\Artist\Artist as Artist;
use Storage;
use File;
use Exception;

class SongController extends Controller
{

	/**
	 * Display songs
	 *
	 * @return Response
	 */
	public function index()
	{
		$songs = Song::leftJoin('artists', 'songs.artist_id', '=', 'artists.id')
		    ->select('songs.*','artist')
		    ->paginate(10);

		return view('songs', ['songs' => $songs]);
	}

	/**
	 * Show the form for creating a new song
	 *
	 * @return Response
	 */
	public function create()
	{
		return view('song', [
			'title'         => 'Add New Song',
			'artists'       => Artist::get_all_artists(['id', 'artist']),
			'file_types'    => Song::FILE_TYPES,
			'song_exists'   => false,
		]);
	}

	/**
	 * Store a newly created song in the database
	 *
	 * @param Request request
	 * @return Response
	 */
	public function store(Request $request)
	{
		Song::store($request);
		return redirect('/songs');
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$song = Song::find($id);
		$location = str_replace(array('C:\\', '\\'), array('', '/'), $song->location);
		return view('song', [
			'song'          => $song,
			'title'         => $song->title,
			'artists'       => Artist::orderBy('artist')->get(['id', 'artist']),
			'file_types'    => Song::FILE_TYPES,
			'song_exists'   => Storage::disk('partitionC')->has($location),
		]);
	}



	/**
	 * Search for song.
	 *
	 * @param Request
	 * @return Response
	 */
	public function search(Request $request)
	{
		$q = $request->q;
		$artists = [];
		if ($q != "") {
			$songs = $this->retrieve_songs($q);
		} else {
			$songs = $this->retrieve_songs(session()->get('songs_query'));
		}
		if (count($songs) > 0) {
			return view('songs', ['songs' => $songs]);
		} else {
			return view('songs', ['songs' => $songs])->withMessage('No Details found. Try to search again !');
		}
	}

	/**
	 * Retrieve songs
	 *
	 * @param  string $query
	 * @return array
	 */
	protected function retrieve_songs($query) {
		if ($query != "") {
			session()->put('songs_query', $query);
			return Song::search($query);
		} else {
			return Song::subset();
		}
	}

	/**
	 * Remove the song from the database
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		Song::destroy($id);
		return redirect('/songs');
	}

	public function play($id)
	{
		$song = Song::find($id);
		$location = str_replace(array('C:\\', '\\'), array('', '/'), $song->location);
		// TODO what to do with wma files
		if (Storage::disk('partitionC')->has($location)) {
			$contents = Storage::disk('partitionC')->get($location);
			return response($contents, 200)->header("Content-Type", 'audio/mpeg');
		}
	}

	/**
	 * Retrieve all songs by various criteria
	 *
	 * @param Request
	 * @return Response
	 */
	public function all(Request $request)
	{
		$validator = Validator::make($request->all(), [
            'id'        => 'numeric',
            'artist_id'	=> 'numeric',
            'offset'   	=> 'numeric',
            'limit'		=> 'numeric',
        ]);

        // Validate parameters
        if ($validator->fails()):
            return ['errors' => $validator->errors(), 'status_code' => 422];
        endif;

		if (isset($request->album) && isset($request->id)) {
			// Get songs in an album by song id
			$songs = Song::get_album_songs_by_song_id($request->id);
		} else {
			$songs = Song::songs($request);
		}

		return ['songs' => $songs, 'status_code' => 200];
	}

	public function song($id)
	{
		return ['song' => Song::find($id), 'status_code' => 200];
	}
}