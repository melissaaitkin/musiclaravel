<?php

namespace MySounds\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

use getID3;

class SongController extends Controller
{
    private $file_types = [ 'mp3', 'm4a', 'wav', 'wma' ];

    private $ID3_extractor;

    private $file_info;

     /**
     * Constructor
     */
    public function __construct()
    {
        $this->ID3_extractor = new \getID3;
    }

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
		return view('song', ['title' => 'Add New Song', 'artists' => $artists, 'file_types' => $this->file_types]);
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

        if ( isset($request->id)){
            $song = \MySounds\Song::findOrFail($request->id);
        } else {
            $song = new \MySounds\Song;
        }

	    $song->title       = $request->title;
	    $song->album       = $request->album;
	    $song->year        = $request->year;
        $song->file_type   = $request->file_type;
        $song->track_no    = $request->track_no;
        $song->genre       = $request->genre;
        $song->location    = $request->location;        
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
        return view('song', [ 'title' => 'Edit Song', 'song' => $song, 'artists' => $artists, 'file_types' => $this->file_types ]);
    }

    /**
     * Does the album exist
     *
     * @param integer $id Artist id
     * @param  string $artist_name Album name
     * @return boolean
     */
    public function does_album_exist($id, $album_name)
    {
        $song = \MySounds\Song::where(["artist_id" => $id, "album" => $album_name])->first();
        $exists = false;
        if (isset($song->id)) {
            $exists = true;
        }
        return $exists;
    }

    /**
     * Does the song exist
     *
     * @param integer $id Artist id
     * @param  string $title Song title
     * @return boolean
     */
    public function does_song_exist($id, $title)
    {
        $song = \MySounds\Song::where(["artist_id" => $id, "title" => $title])->first();
        $exists = false;
        if (isset($song->id)) {
            $exists = true;
        }
        return $exists;
    }

    /**
     * Search for song.
     *
     * @param  string  $q
     * @return Response
     */
    public function search(Request $request)
    {
        $q = $request->q;
        $songs = [];
        if ($q != "") {
            if ( stripos( $q, 'SELECT') === 0 ) {
                return $this->admin_search($q);
            } else {
                $songs = \MySounds\Song::where ( 'title', 'LIKE', '%' . $q . '%' )->orWhere ( 'album', 'LIKE', '%' . $q . '%' )->paginate (10)->setPath ( '' );
            }
        }
        if (count ( $songs ) > 0) {
            return view('songs', ['songs' => $songs]);
        } else {
            return view('songs', ['songs' => $songs])->withMessage('No Details found. Try to search again !');
        }
    }

     /**
     * Perform admin search on songs
     *
     * @param  string $query
     * @return Response
     */
    public function admin_search(string $query)
    {
        if ( stripos( $query, 'DELETE') !== false || stripos( $query, 'UPDATE') ) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $songs = \DB::select($query);
        } catch (\Illuminate\Database\QueryException $ex) {
            $songs = [];
        }

        $paginate = new LengthAwarePaginator($songs, 10, 1, 1, [
            'path' =>  request()->url(),
            'query' => request()->query()
        ]);

        if (count ( $songs ) > 0) {
            return view('songs', ['songs' => $paginate]);
        } else {
            return view('songs', ['songs' => $paginate])->withMessage('No Details found. Try to search again !');
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
        // Analyze file and store returned data in $file_info
        //TODO also retrieve composer, file size and $file_info["playtime_string"]
        // TEST edge cases
        $this->file_info = $this->ID3_extractor->analyze($path);

         try {
            $song_info = $this->extract_tag_info();
            if ( empty( $song_info['title'] ) ) {
                $idx = strrpos($filename, '.');
                if ( $idx !== false ) {
                    $song_info['title'] = substr($filename, 0, $idx );
                    $song_info['file_type'] = $this->file_info['fileformat'];
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

    private function extract_tag_info() {
        $song_info = [];
        switch ( $this->file_info['fileformat'] ) {
            case "mp3":
                $song_info['title'] = $this->file_info["tags"]["id3v2"]["title"][0];
                $song_info['genre'] = $this->file_info["tags"]["id3v2"]["genre"][0];
                $song_info['track_no'] = $this->file_info["tags"]["id3v2"]["track_number"][0];
                $song_info['year'] = $this->file_info["tags"]["id3v2"]["year"][0];
                break;
            case "mp4":
                $song_info['title'] = $this->file_info["quicktime"]["comments"]["title"][0];
                $song_info['genre'] = $this->file_info["quicktime"]["comments"]["genre"][0];
                $song_info['track_no'] = $this->file_info["quicktime"]["comments"]["track_number"][0];
                $song_info['year'] = $this->file_info["quicktime"]["comments"]["creation_date"][0];
                break;   
            default:
                //no luck    
        }
        return $song_info;
    }

}