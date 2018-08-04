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
			$this->scanner($request->directory);
		} else {
			$error = \Illuminate\Validation\ValidationException::withMessages([
				'directory' => ['This is not a valid directory'],
			]);
			throw $error;
		}
		return view('utilities', ['msg' => 'Songs have been loaded']);
	}

	private function scanner($path) {
		$result = [];
		$scan_artitsts = glob($path . '/*');
		foreach($scan_artitsts as $artist){
			if (is_dir($artist)) {
				echo "ARTIST - " . basename($artist) . "</br>";
				$scan_albums = glob($artist . '/*');
				foreach($scan_albums as $album){
					if (is_dir($album)) {
						echo "ALBUM - " . basename($album) . "</br>";
						$scan_songs = glob($album . '/*');
						foreach($scan_songs as $song){
							if (is_dir($song)) {
								echo "SOMETHING WEIRD IS HAPPENINGALBUM - " . basename($song) . "</br>";
						  	} else {
								if ( $this->is_song( $song ) ) {
									echo "ALBUM SONG - " . basename($song) . "</br>";
							  	}
						  	}
						}
				  	} else {
						if ( $this->is_song( $album ) ) {
							echo "ARTIST SONG - " . basename($album) . "</br>";
					  	}
				  	}
				}
			} else {
				if ( $this->is_song( $artist ) ) {
					echo "STRAY SONG - " . basename($artist) . "</br>";
				}
				//echo " is a " . pathinfo( $item, PATHINFO_EXTENSION ). "</br>";
				//echo "filesize " . filesize($item). "</br>";
			}
		}
		var_dump($this->others);
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