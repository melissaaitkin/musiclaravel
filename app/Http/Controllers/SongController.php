<?php

namespace MySounds\Http\Controllers;

use Illuminate\Http\Request;
use LaravelMP3;

class SongController extends Controller
{

    private $file_types = [ 'wav', 'm4a', 'mp3', 'wma' ];

  /**
     * Display songs
     *
     * @return Response
     */
    public function index()
    {
		$songs = \MySounds\Song::paginate(10);
		return view('songs', ['songs' => $songs]);
    }

    /**
     * Show the form for creating a new song
     *
     * @return Response
     */
    public function create()
    {
		$artists = \MySounds\Artist::all( [ 'id', 'artist']);
		return view('song', ['artists' => $artists]);
    }

    /**
     * Store a newly created song in the database
     *
     * @param Request request
     * @return Response
     */
    public function store(Request $request)
    {
	    $validator = $request->validate([
	        'title' => 'required|max:255',
	        'album' => 'required|max:255',
	        'year' => 'required|integer'
	    ]);

	    $song = new \MySounds\Song;
	    $song->title       = $request->title;
	    $song->album       = $request->album;
	    $song->year        = $request->year;
        $song->file_type   = $request->file_type;
        $song->track_no    = $request->track_no;
        $song->genre       = $request->genre;
	    $song->artist_id   = $request->artist_id;
	    $song->save();

	    return redirect('/songs');
    }

    /**
     * Store an dynamically created song in the database
     *
     * @param array song
     * @return integer Song id
     */
    public function dynamic_store($path, $filename, $album_name, $artist_id)
    {
        $song_info = $this->retrieve_song_info($path, $filename);
        $_song = new \MySounds\Song;
        $_song->title = $song_info['title'] ?? '';
        $_song->album = $album_name;
        $_song->year = $song_info['year'] ?? '9999';
        $_song->file_type = $song_info['file_type'] ?? '';
        $_song->track_no = $song_info['track_no'] ?? '';
        $_song->genre = $song_info['genre'] ?? '';
        $_song->location = $path;
        $_song->artist_id = $artist_id;
        try {
            $_song->save();
        } catch (\Illuminate\Database\QueryException $ex){
             error_log( 'An exception occured adding song: ' . $path . ' : ' . $ex->getMessage() );
        } catch ( Exception $e ) {
            error_log( 'An exception occured adding song: ' . $path . ' : ' . $e->getMessage() );
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $artists = \MySounds\Artist::all( [ 'id', 'artist']);
        $song = \MySounds\Song::find($id);
        return view('song', [ 'song' => $song, 'artists' => $artists]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        //
    }

    /**
     * Remove the song from the database
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
	    \MySounds\Song::findOrFail($id)->delete();
	    return redirect('/songs');
    }

    /**
     * Retrieve song info via ID3
     *
     * @param string $path Full file path
     * @param string $filename Filename
     * @return array;
     */
    private function retrieve_song_info($path, $filename) {
        LaravelMP3::reset();
        $song_info = [];

        $idx = strrpos($filename, '.');
        if ( $idx !== false ) {
            $song_info['title'] = substr($filename, 0, $idx );
            $song_info['file_type'] = substr($filename, $idx + 1 );
        }

        try {
            $file_info = LaravelMP3::load($path);
            if (!isset($file_info['error'])) {
                $title = LaravelMP3::getTitle($path)[0] ?? '';
                if ( !empty( $title ) ) {
                    $song_info['title'] = $title;
                }
                $song_info['genre'] = LaravelMP3::getGenre($path)[0] ?? '';
                $song_info['track_no'] = LaravelMP3::getTrackNo($path)[0] ?? '';
                $song_info['year'] = LaravelMP3::getYear($path)[0] ?? '9999';
            } else {
                error_log( "Error occurred processing - " . $path );
                foreach ( $file_info['error'] as $error) {
                    error_log( $error );
                }
            }
        } catch ( Exception $e ) {
            error_log( "Exception occurred processing - " .  $path . " : " . $e->getMessage() );
        }

        return $song_info;
    }

    public function is_song( $file ) {
        $result = false;
        $extension = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
        if ( in_array( $extension, $this->file_types ) ) {
          $result = true;
        }
        return $result;
    }

}