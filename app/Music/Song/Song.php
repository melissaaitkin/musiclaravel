<?php

namespace App\Music\Song;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Song extends Model
{

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
     * The artist id.
     *
     * @var integer
     */
    protected $artist_id;

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

    /**
     * Supported file types
     *
     * @var array
     */
    const FILE_TYPES = ['mp3', 'mp4', 'm4a', 'wav', 'wma'];

    /**
     * Encode the song's title.
     *
     * @param string $value
     * @return string
     */
    public function getTitleAttribute($value)
    {
        return utf8_encode($value);
    }

    /**
     * Encode the song's album.
     *
     * @param string $value
     * @return string
     */
    public function getAlbumAttribute($value)
    {
        return utf8_encode($value);
    }
    /**
     * Encode the song's location.
     *
     * @param string $value
     * @return string
     */
    public function getLocationAttribute($value)
    {
        return utf8_encode($value);
    }

    /**
     * Encode the song's artist.
     *
     * @param string $value
     * @return string
     */
    public function getArtistAttribute($value)
    {
        return utf8_encode($value);
    }

	/**
     * Create or update a song.
     *
	 * @param Request $request
     */
    public static function store($request)
    {
        $validator = $request->validate([
			'title'	=> 'required|max:255',
			'album'	=> 'required|max:255',
			'year'	=> 'required|integer'
        ]);

        $song = [];
        $song['title'] = $request->title;
        $song['album'] = utf8_decode($request->album);
        $song['year'] = $request->year;
        $song['file_type'] = $request->file_type;
        $song['track_no'] = $request->track_no;
        $song['genre'] = $request->genre;
        $song['location'] = utf8_decode($request->location);
        $song['artist_id'] = $request->artist_id;
        $song['filesize'] = $request->filesize ?? 0;
        $song['composer'] = $request->composer;
        $song['playtime'] = $request->playtime;
        $song['notes'] = $request->notes;

		if (isset($request->id)) {
			// updateOrCreate throwing duplicate error
			Song::where('id', $request->id)->update($song);
		} else {
			Song::create($song);
		}
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
    public static function dynamic_store($path, $album_name, $artist_id, $song)
    {
        $_song = [];
        $_song['title'] = $song->title();
        $_song['album'] = utf8_decode($album_name);
        $_song['year'] = $song->year();
        $_song['file_type'] = $song->file_type();
        $_song['track_no'] = $song->track_no();
        $_song['genre'] = $song->genre();
        $_song['location'] = utf8_decode($path);
        $_song['artist_id'] = $artist_id;
        $_song['filesize'] = $song->file_size();
        $_song['composer'] = $song->composer();
        $_song['playtime'] = $song->playtime();
        $_song['notes'] = $song->notes();
		Song::create($_song);
    }

    /**
     * Does the album exist
     *
     * @param integer $id Artist id
     * @param string $album_name Album name
     * @return boolean
     */
    public static function does_album_exist($id, $album_name)
    {
        $song = Song::where(["artist_id" => $id, "album" => $album_name])->first();
        return isset($song);
    }

    /**
     * Does the song exist
     *
     * @param integer $id Artist id
     * @param string $title Song title
     * @return boolean
     */
    public static function does_song_exist($id, $title)
    {
        // FIXME investigate processing of songs with no artists and remove duplication
        $song = Song::where(["artist_id" => $id, "title" => $title])->first();
        return isset($song);
    }

	/**
	* Returns all song titles
	*
	* @param string $album Restrict via album.
	* @return Collection Eloquent collection of song titles.
	*/
	public static function get_song_titles(string $album = null)
	{
		if ($album) {
			return Song::where('album', '=', $album)->get(['title']);
		} else {
			return Song::all(['title']);
		}
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

	public static function is_song($file) {
		$extension = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
		return in_array($extension, self::FILE_TYPES);
	}

	public static function get_artist_albums($id) {
		return Song::distinct('album')->where(["artist_id" => $id])->get(['album'])->toArray();
	}

	/**
	* Retrieve artist's songs.
	*
	* Retrieves the songs from the artist's albums and compilation albums.
	*
	* @param int  $id
	* @param string $artist
	*/
	public static function get_artist_songs($id, $artist) {
		return Song::select('id', 'title')
			->where(["artist_id" => $id])
			->orWhere(["notes" => $artist])
			->orderBy('title')
			->get();
	}

    /**
    * Retrieve album songs by song id.
    *
    * @param int $id
    */
    public static function get_album_songs_by_song_id($id) {
        return Song::select('id', 'title', 'album')
            ->where('artist_id', function($q1) use ($id)
                {
                    $q1->from('songs')
                      ->select('artist_id')
                      ->where('id', '=', $id);

                })
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
        return Song::select('songs.*', 'artist')
            ->leftJoin('artists', 'artists.id', '=', 'songs.artist_id')
            ->where('title', 'LIKE', '%' . $query . '%')
            ->orWhere('artist', 'LIKE', '%' . $query . '%')
            ->orWhere('album', 'LIKE', '%' . $query . '%')
            ->orWhere('songs.notes', 'LIKE', '%' . $query . '%')
            ->paginate()
            ->appends(['q' => $query])
            ->setPath('');
    }

    /**
    * Retrieve subset of songs
    */
    public static function subset() {
        return Song::select('songs.*', 'artist')
            ->leftJoin('artists', 'artists.id', '=', 'songs.artist_id')
            ->paginate();
    }

    public static function songs(Request $request)
    {
        $model = new Song();

        $query = $model->leftJoin('artists', 'artists.id', '=', 'songs.artist_id');
        if (isset($request->album)) {
            $query->where('album', '=', $request->album);
        }

        if (isset($request->artist_id)) {
            $query->where('artist_id', '=', $request->artist_id);
        }

        if (isset($request->artist)) {
            $query->orWhere("songs.notes", '=', $request->artist);
        }

        if (isset($request->offset) && isset($request->limit)) {
            $query->skip($request->offset)->take($request->limit);
        }

        if (isset($request->all)) {
            $songs = $query->get(['songs.*', 'artist']);
        } else {
           $songs = $query->get(['songs.id', 'songs.title', 'artists.artist']);
        }

        return $songs;
    }
}