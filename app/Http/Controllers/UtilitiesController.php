<?php

namespace MySounds\Http\Controllers;

use MySounds\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use LaravelMP3;
use Exception;
use Mysounds\Artist;

class UtilitiesController extends Controller
{

  /**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
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
		// FIXME duplicates are still being created such as Clarence Carter - Patches (original name versus name retrieved from ID3 extractor)
		try {
			if (is_dir( $request->directory)) {
				if (isset($request->entire_library)) {
					$this->process_media_directory($request->directory);
				} else {
					$dirs = explode('\\', $request->directory);
					$artist_id = $this->process_artist($dirs[count($dirs)-1]);
					$this->process_artist_directory($request->directory, $artist_id);

				}
			} else {
				return view('utilities')->withErrors(['This is not a valid directory']);
			}
		} catch (Exception $e) {
			return view('utilities')->withErrors([$e->getMessage()]);
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
		$result = [];
		$scan_items = glob($path . '/*');
		foreach($scan_items as $item){
			if (is_dir($item)) {
				$artist_id = $this->process_artist($item);
				$this->process_artist_directory($item, $artist_id);
			} else {
				if (app('MySounds\Http\Controllers\SongController')->is_song($item)) {
					$this->process_song($artist_id, $item);
				}
			}
		}
		return $result;
	}

	/**
	 * Loop over sub directories and insert artists and songs
	 *
	 * @param  string $path
	 * @return Result
	 */
	private function process_artist_directory(string $artist_dir, int $artist_id) {
		$scan_albums = glob($artist_dir . '/*');
		foreach($scan_albums as $album) {
			if (is_dir($album)) {
				$this->process_album($album, $artist_id);
			} else {
				if (app('MySounds\Http\Controllers\SongController')->is_song($album)) {
					$this->process_song($artist_id, $album);
				}
			}
		}
	}

	private function process_artist(string $item) {
		$artist_arr = [basename($item), 1, 'To Set'];
		$artist_id = app('MySounds\Http\Controllers\ArtistController')->get_id($artist_arr[0]);
		if (!$artist_id) {
			$artist_id = app('MySounds\Http\Controllers\ArtistController')->dynamic_store($artist_arr);
		}
		return $artist_id;
	}

	private function process_album(string $album, int $artist_id) {
		$album_name = basename($album);
		if (preg_match('/[\[\]]/', $album_name)) {
			throw new Exception("Album directory contains square brackets");
		}
		$album_exists = app('MySounds\Http\Controllers\SongController')->does_album_exist($artist_id, $album_name);
		if (!$album_exists) {
			$is_compilation = Artist::is_compilation($artist_id);
			$scan_songs = glob($album . '/*');
			foreach($scan_songs as $song){
				if (app('MySounds\Http\Controllers\SongController')->is_song($song)) {
					app('MySounds\Http\Controllers\SongController')->dynamic_store($song, basename($song), $album_name, $artist_id, $is_compilation);
				}
			}
		}
	}

	private function process_song(int $artist_id, string $song) {
		$song_exists = app('MySounds\Http\Controllers\SongController')->does_song_exist($artist_id, $song);
		if ( !$song_exists ) {
			$is_compilation = Artist::is_compilation($artist_id);
			app('MySounds\Http\Controllers\SongController')->dynamic_store($song, basename($song), 'To Set', $artist_id, $is_compilation);
		}
	}

}