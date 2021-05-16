<?php

namespace App\Music\Song;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Log;

class Song extends Model
{

    /**
     * Supported file types
     *
     * @var array
     */
    const FILE_TYPES = ['mp3', 'mp4', 'm4a', 'wav', 'wma'];

    protected $table = 'songs';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $id;

    /**
     * The title.
     *
     * @var string
     */
    protected $title;

    /**
     * The album.
     *
     * @var string
     */
    protected $album;

    /**
     * The year.
     *
     * @var integer
     */
    protected $year;

    /**
     * The file type.
     *
     * @var string
     */
    protected $file_type;

    /**
     * The track number.
     *
     * @var string
     */
    protected $track_no;

    /**
     * The genre.
     *
     * @var string
     */
    protected $genre;

    /**
     * The song file location.
     *
     * @var string
     */
    protected $location;

    /**
     * The composer.
     *
     * @var string
     */
    protected $composer;

    /**
     * The song length.
     *
     * @var string
     */
    protected $playtime;

    /**
     * The song file size.
     *
     * @var integer
     */
    protected $filesize;

    /**
     * Created date.
     *
     * @var datetime
     */
    protected $created_at;

    /**
     * Updated date.
     *
     * @var datetime
     */
    protected $updated_at;

    /**
     * Notes about the song.
     *
     * @var string
     */
    protected $notes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The number of records to return for pagination.
     *
     * @var int
     */
    protected $perPage = 10;

    public function artists()
    {
        return $this->belongsToMany('App\Music\Artist\Artist');
    }

    /**
     * Create or update a song.
     *
     * @param Request $request
     */
    public static function store($request)
    {
        $validator = $request->validate([
            'title' => 'required|max:255',
            'album' => 'required|max:255',
            'year'  => 'required|integer',
        ]);

        $song = [];
        $song['title'] = $request->title;
        $song['album'] = $request->album;
        $song['year'] = $request->year;
        $song['file_type'] = $request->file_type;
        $song['track_no'] = $request->track_no;
        $song['genre'] = $request->genre;
        $song['location'] = $request->location;
        $song['filesize'] = $request->filesize ?? 0;
        $song['composer'] = $request->composer;
        $song['playtime'] = $request->playtime;
        $song['notes'] = $request->notes;

        if (isset($request->id)):
            $song['id'] = $request->id;
        endif;

        $updated_song = Song::updateOrCreate($song);

        // Make any updates to artist/s
        $existing_artists = [];
        foreach($updated_song->artists as $artist) {
            $existing_artists[] = $artist->id;
        }
        if (empty($request->artists)):
            $request->artists = [];
        endif;
        $inserts = array_diff($request->artists, $existing_artists);
        foreach($inserts as $artist):
            $updated_song->artists()->attach(['artist' => $artist]);
        endforeach;
        $deletes = array_diff($existing_artists, $request->artists);
        foreach($deletes as $artist):
            $updated_song->artists()->detach(['artist' => $artist]);
        endforeach;

    }

    /**
     * Update song lyrics.
     *
     * @param Request $request
     */
    public static function updateLyrics($request)
    {
        $validator = $request->validate([
            'id' => 'required|max:255',
        ]);

        $song = Song::find($request->id);
        $song->lyrics = $request->lyrics;
        $song->save();
    }

    /**
     * Create a song via the music loading process.
     *
     * @param string path
     * @param string album_name
     * @param integer artist_it
     * @param array ID3 song array
     *
     * @param Request $request
     */
    public static function dynamicStore($path, $album_name, $artist_id, $song)
    {
        $_song = [];
        $_song['title'] = $song->title();
        $_song['album'] = $album_name;
        $_song['year'] = $song->year();
        $_song['file_type'] = $song->fileType();
        $_song['track_no'] = $song->trackNo();
        $_song['genre'] = $song->genre();
        $_song['location'] = $path;
        $_song['filesize'] = $song->fileSize();
        $_song['composer'] = $song->composer();
        $_song['playtime'] = $song->playtime();
        $_song['notes'] = $song->notes();

        $new_song = Song::create($_song);

        $new_song->artists()->attach(['artist' => $artist_id]);
    }

    /**
     * Does the album exist
     *
     * @param integer $id Artist id
     * @param string $album_name Album name
     * @return boolean
     */
    public static function doesAlbumExist($id, $album_name)
    {
        $song = Song::where('album', $album_name)
            ->with(['artists' => function($q) use ($id) {
                $q->where('artist_id', '=', $id);
            }])
            ->first();
        return isset($song);
    }

    /**
     * Does the song exist
     *
     * @param integer $id Artist id
     * @param string $title Song title
     * @return boolean
     */
    public static function doesSongExist($id, $title)
    {
        // FIXME investigate processing of songs with no artists and remove duplication
        $song = Song::where('title', $title)
            ->with(['artists' => function($q) use ($id) {
                $q->where('artist_id', '=', $id);
            }])
            ->first();
        return isset($song);
    }

    /**
    * Returns all song titles
    *
    * @param string $album Restrict via album.
    * @return Collection Eloquent collection of song titles.
    */
    public static function getSongTitles(string $album = null)
    {
        if($album):
            return Song::where('album', '=', $album)->get(['title']);
        else:
            return Song::all(['title']);
        endif;
    }

    /**
    * Remove the song
    *
    * @param  int  $id
    */
    public static function destroy($id)
    {
        Song::findOrFail($id)->delete();
    }

    public static function isSong($file) {
        $extension = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
        return in_array($extension, self::FILE_TYPES);
    }

    public static function getArtistAlbums($id) {
        return Song::distinct('album')->where(["artist_id" => $id])->get(['album'])->toArray();
    }

    /**
    * Retrieve artist's songs.
    *
    * Retrieves the songs from the artist's albums and compilation albums.
    *
    * @param int $id
    * @param string $artist
    */
    public static function getArtistSongs($id, $artist) {
        return Song::select('id', 'title')
            ->whereHas('artists', function($q) use ($id) {
                $q->where('artist_id', '=', $id);
            })
            ->orWhere(["notes" => $artist])
            ->orderBy('title')
            ->get();
    }

    /**
    * Retrieve album songs by song id.
    *
    * @param int $id
    */
    public static function getAlbumSongsBySongID($id) {
        // FIXME handle common album names like Greatest Hits
        return Song::select('id', 'title', 'album')
            ->where('album', function($q2)  use ($id)
                {
                    $q2->from('songs')
                      ->select('album')
                      ->where('id', '=', $id);
                })
            ->get();
    }

    /**
    * Search for songs
    *
    * @param string $query
    */
    public static function search($query) {
        return Song::select('songs.*')
            ->whereHas('artists', function($q) use($query) {
                $q->where('artist', 'LIKE', '%' . $query . '%');
            })
            ->orWhere('title', 'LIKE', '%' . $query . '%')
            ->orWhere('album', 'LIKE', '%' . $query . '%')
            ->orWhere('songs.notes', 'LIKE', '%' . $query . '%')
            ->paginate()
            ->appends(['q' => $query])
            ->setPath('');
    }

    public static function songs(Request $request)
    {
        if (isset($request->all)):
            $query = Song::select('songs.*')->with('artists:artist');
        else:
            $query = Song::select('id', 'title')->with('artists:artist');
        endif;

        if(isset($request->album)):
            $query->where('album', '=', $request->album);
        endif;

        if(isset($request->artist_id)):
            $artist_id = $request->artist_id;
            $query = Song::with(['artists' => function($q) use ($artist_id) {
                $q->where('artist_id', '=', $artist_id);
            }]);
        endif;

        if(isset($request->artist)):
            $query->orWhere("songs.notes", '=', $request->artist);
        endif;

        if(isset($request->offset) && isset($request->limit)):
            $query->skip($request->offset)->take($request->limit);
        endif;

        return $query->get();
    }

}
