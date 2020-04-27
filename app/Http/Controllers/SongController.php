<?php

namespace MySounds\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MySounds\Song as Song;
use MySounds\Music\AudioFile\AudioFile as AudioFile;
use MySounds\Music\AudioFile\MP3 as MP3;
use MySounds\Music\AudioFile\MP4 as MP4;
use Storage;
use File;
use Exception;

use getID3;

class SongController extends Controller
{
    private $file_types = [ 'mp3', 'mp4', 'm4a', 'wav', 'wma' ];

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
        // TODO add notes field to songs to describe conductor/orchestra of classical pieces.
        // TODO move calls to Songs to Song object and clean up Song references.
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
		return view('song', [
            'title' => 'Add New Song',
            'artists' => $artists,
            'file_types' => $this->file_types,
            'song_exists' => false,
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

		$song->title 		= $request->title;
		$song->album 		= $request->album;
		$song->year 		= $request->year;
		$song->file_type   	= $request->file_type;
		$song->track_no    	= $request->track_no;
		$song->genre 		= $request->genre;
		$song->location    	= $request->location;
		$song->artist_id   	= $request->artist_id;
		$song->filesize    	= $request->filesize ?? 0;
		$song->composer    	= $request->composer;
		$song->playtime 	= $request->playtime;
		$song->notes 		= $request->notes;
		$song->save();

	    return redirect('/songs');
    }

    /**
     * Store an dynamically created song in the database
     *
     * @param array song
     * @param string filename
     * @param string album_name
     * @param integer artist_it
     * @param boolean is_compilation
     */
    public function dynamic_store($path, $filename, $album_name, $artist_id, $is_compilation)
    {
        try {
            $song = $this->retrieve_song_info($path, $filename, $is_compilation);
            $_song = new Song;
            $_song->title = $song->title();
            $_song->album = $album_name;
            $_song->year = $song->year();
            $_song->file_type = $song->file_type();
            $_song->track_no = $song->track_no();
            $_song->genre = $song->genre();
            $_song->filesize = $song->file_size();
            $_song->composer = $song->composer();
            $_song->playtime = $song->playtime();
            $_song->location = $path;
            $_song->artist_id = $artist_id;
            $_song->notes = $song->notes();

            $_song->save();
        } catch (Exception $e) {
            Log::error('An exception occured adding song: ' . $path . ' : ' . $e->getMessage());
            abort(404, "An error occurred processing the song");
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
        $artists = \MySounds\Artist::orderBy('artist')->get(['id', 'artist']);
        $song = \MySounds\Song::find($id);
        $location = str_replace(array('C:\\', '\\'), array('', '/'), $song->location);
        return view('song', [
            'song' => $song,
            'title' => $song->title,
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
                ->orWhere ( 'songs.notes', 'LIKE', '%' . $query . '%' )
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
     * @param boolean $is_compilation Is song part of a compilation ablum
     * @return array
     */
    private function retrieve_song_info($path, $filename, $is_compilation) {
        $file_info = $this->ID3_extractor->analyze($path);

        if (isset($file_info['error'])) {
            throw new Exception($this->file_info['error'][0]);
        }
        switch ($file_info['fileformat']) {
            case "mp3":
                $song = new MP3($path, $filename, $is_compilation, $file_info);
                break;
            case "mp4":
                $song = new MP4($path, $filename, $is_compilation, $file_info);
                break;
            default:
                $song = new AudioFile($file_info['fileformat'], $path, $filename, $is_compilation, $file_info);
                break;
        }
        return $song;
    }

    public function is_song($file) {
        $result = false;
        $extension = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
        if (in_array( $extension, $this->file_types)) {
          $result = true;
        }
        return $result;
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

    public function all(Request $request)
    {
        if (isset($request->album)){
            return Song::where('album', '=', $request->album)->get(['title']);
        } else {
            return Song::all(['title']);
        }
    }

}