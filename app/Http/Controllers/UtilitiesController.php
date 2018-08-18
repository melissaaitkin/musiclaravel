<?php

namespace MySounds\Http\Controllers;

use MySounds\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UtilitiesController extends Controller
{

  private $others = [];
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
		- Get file info - year, track no,
		- Strip track no
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
								if ( $this->is_song( $song ) ) {
									$idx = strrpos( basename($song), '.' );
									if ( $idx !== false ) {
										$title = substr(basename($song), 0, $idx );
										$file_type = substr(basename($song), $idx + 1 );
										$song_arr = [ $title, $album_name, 9999, $file_type, $artist_id ];
										app('MySounds\Http\Controllers\SongController')->dynamic_store($song_arr);
									}
							  	}
						  	}
						}
				  	} else {
						if ( $this->is_song( $album ) ) {
							$song_parts = explode( '.', basename($album) );
							$song_arr = [ $song_parts[0], 'To Set', 9999, $song_parts[1], $artist_id ];
							app('MySounds\Http\Controllers\SongController')->dynamic_store($song_arr);							
					  	}
				  	}
				}
			} else {
				if ( $this->is_song( $artist ) ) {
					$song_parts = explode( '.', basename($artist) );
					$song_arr = [ $song_parts[0], 'To Set', 9999, $song_parts[1], 1 ];
					app('MySounds\Http\Controllers\SongController')->dynamic_store($song_arr);
				}
			}
		}
		return $result;
	}

	private function is_song( $file ) {
		$result = false;
		$types = [ 'wav', 'm4a', 'mp3', 'wma' ];
		$extension = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
		if ( in_array( $extension, $types ) ) {
		  $result = true;
		} else {
			if ( !in_array( $extension, $this->others)) {
			  $this->others[] = $extension;
			}
		}
		return $result;
	}

}