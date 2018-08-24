<?php

namespace MySounds\Http\Controllers;

use MySounds\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use LaravelMP3;

class UtilitiesController extends Controller
{

  /**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		if ( Auth::user()->id != 1 ) {
			abort(404);
		}
		return view('utilities');
	}

	/**
	 * Load songs
	 *
	 * @param  Request $request
	 * @return Response
	 */
	public function load_songs(Request $request)
	{
		if ( Auth::user()->id != 1 ) {
			abort(404);
		}
		if ( is_dir( $request->directory ) ) {
			$this->process_media_directory($request->directory);
		} else {
			$error = \Illuminate\Validation\ValidationException::withMessages([
				'directory' => ['This is not a valid directory'],
			]);
			throw $error;
		}
		return view('utilities', ['msg' => 'Songs have been loaded']);
	}

	/**
	 * Loop over sub directories and insert artists and songs
	 *
	 * @param  string $path
	 * @return Result
	 */
	private function process_media_directory($path) {
		/* TODO
		- Check artists and songs exist
		- Add file type array
		*/
		$result = [];
		$scan_artitsts = glob($path . '/*');
		foreach($scan_artitsts as $artist){
			if (is_dir($artist)) {
				$artist_arr = [ basename($artist), 1, 'To Set' ];
				$artist_id = app('MySounds\Http\Controllers\ArtistController')->dynamic_store($artist_arr);
				$scan_albums = glob($artist . '/*');
				foreach($scan_albums as $album){
					if (is_dir($album)) {
						$album_name = basename($album);
						$scan_songs = glob($album . '/*');
						foreach($scan_songs as $song){
							if (is_dir($song)) {
								error_log( "Something Weird Is Happening - " . basename($song) );
						  	} else {
								if ( app('MySounds\Http\Controllers\SongController')->is_song( $song ) ) {
									app('MySounds\Http\Controllers\SongController')->dynamic_store($song, basename($song), $album_name, $artist_id);
							  	}
						  	}
						}
				  	} else {
						if ( app('MySounds\Http\Controllers\SongController')->is_song( $album ) ) {
							app('MySounds\Http\Controllers\SongController')->dynamic_store($album, basename($album), 'To Set', $artist_id);					
					  	}
				  	}
				}
			} else {
				if ( app('MySounds\Http\Controllers\SongController')->is_song( $artist ) ) {
					app('MySounds\Http\Controllers\SongController')->dynamic_store($artist, basename($artist), 'To Set', 1);
				}
			}
		}
		return $result;
	}

}