<?php

namespace MySounds\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Storage;
use File;

//use \DateTime;
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
        $songs = \DB::table('songs')
            ->leftJoin('artists', 'songs.artist_id', '=', 'artists.id')
            ->select('songs.*', 'artist')
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
        $song->filesize    = $request->filesize ?? 0;
        $song->composer    = $request->composer;
        $song->playtime    = $request->playtime;        
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
        $_song->year = $song_info['year'] ?? 9999;
        $_song->file_type = $song_info['file_type'] ?? '';
        $_song->track_no = $song_info['track_no'] ?? '';
        $_song->genre = $song_info['genre'] ?? '';
        $_song->filesize = $song_info['filesize'] ?? 0;
        $_song->composer = $song_info['composer'] ?? '';
        $_song->playtime = $song_info['playtime'] ?? 0;
        $_song->location = $path;
        $_song->artist_id = $artist_id;
        try {
            $_song->save();
        } catch (\Illuminate\Database\QueryException $ex) {
            Log::error( 'An exception occured adding song: ' . $path . ' : ' . $ex->getMessage() );
        } catch (\Exception $e ) {
            Log::error( 'An exception occured adding song: ' . $path . ' : ' . $e->getMessage() );
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
        $location = str_replace(array('C:\\', '\\'), array('', '/'), $song->location);
        return view('song', [
            'song' => $song,
            'artists' => $artists,
            'file_types' => $this->file_types,
            'song_exists' => Storage::disk('partitionC')->has($location),
        ]);
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
        // FIXME investigate processing of songs with no artists and remove duplication
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
            return \DB::table('songs')
                ->leftJoin('artists', 'songs.artist_id', '=', 'artists.id')
                ->select('songs.*', 'artist')
                ->where ( 'title', 'LIKE', '%' . $query . '%' )
                ->orWhere ( 'artist', 'LIKE', '%' . $query . '%' )
                ->orWhere ( 'album', 'LIKE', '%' . $query . '%' )
                ->paginate(10)
                ->appends(['q' => $query])
                ->setPath('');
        } else {
            return \DB::table('songs')
                ->leftJoin('artists', 'songs.artist_id', '=', 'artists.id')
                ->select('songs.*', 'artist')
                ->paginate(10);
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
     * @return array
     */
    private function retrieve_song_info($path, $filename) {
        $this->file_info = $this->ID3_extractor->analyze($path);

         try {
            $song_info = $this->extract_tag_info();
            if ( empty( $song_info['title'] ) ) {
                $idx = strrpos($filename, '.');
                if ( $idx !== false ) {
                    $song_info['title'] = substr($filename, 0, $idx );
                }
            }
        } catch ( Exception $e ) {
            error_log( "Exception occurred processing - " .  $path . " : " . $e->getMessage() );
        }

        return $song_info;
    }

    public function is_song($file) {
        $result = false;
        $extension = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
        if (in_array( $extension, $this->file_types)) {
          $result = true;
        }
        return $result;
    }

    // TODO CREATE mp3/mp4 songs objects
    private function extract_tag_info() {
        $song_info = [];
        $song_info['file_type'] = $this->file_info['fileformat'];
        switch ( $this->file_info['fileformat'] ) {
            case "mp3":
                $song_info['title'] = $this->file_info["tags"]["id3v2"]["title"][0] ?? '';
                $song_info['genre'] = $this->file_info["tags"]["id3v2"]["genre"][0] ?? '';
                $song_info['track_no'] = $this->file_info["tags"]["id3v2"]["track_number"][0] ?? '';
                $song_info['year'] = $this->file_info["tags"]["id3v2"]["year"][0] ?? 9999;
                $song_info['filesize'] = $this->file_info["filesize"] ?? 0;
                $song_info['composer'] = '';
                $song_info['playtime'] = $this->file_info["playtime_string"] ?? '';     
                break;
            case "mp4":
                $song_info['title'] = $this->file_info["quicktime"]["comments"]["title"][0] ?? '';
                $song_info['genre'] = $this->file_info["quicktime"]["comments"]["genre"][0] ?? '';
                $song_info['track_no'] = $this->file_info["quicktime"]["comments"]["track_number"][0] ?? '';
                //1984-01-23T08:00:00Z
                $date_str = $this->file_info["quicktime"]["comments"]["creation_date"][0] ?? '';
                if (empty($date_str)) {
                    $year = 9999;
                } else {
                    $date_time = new \DateTime($date_str);
                    $year = $date_time->format('Y');
                }
                $song_info['year'] = $year;
                $song_info['filesize'] = $this->file_info["filesize"] ?? 0;
                $song_info['composer'] = $this->file_info["quicktime"]["comments"]["composer"][0] ?? '';  
                $song_info['playtime'] = $this->file_info["playtime_string"] ?? '';                
                break;   
            default:
                //no luck    
        }
        return $song_info;
    }

    public function play($id)
    {
        $song = \MySounds\Song::find($id);
        $location = str_replace(array('C:\\', '\\'), array('', '/'), $song->location);
        // TODO what to do with wma files
        if (Storage::disk('partitionC')->has($location)) {
            $contents = Storage::disk('partitionC')->get($location);
            return response($contents, 200)->header("Content-Type", 'audio/mpeg');
        }
    }

}