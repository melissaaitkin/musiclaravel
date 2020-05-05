<?php

namespace MySounds\Music\Song;

use Illuminate\Database\Eloquent\Model;

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
        $song['album'] = $request->album;
        $song['year'] = $request->year;
        $song['file_type'] = $request->file_type;
        $song['track_no'] = $request->track_no;
        $song['genre'] = $request->genre;
        $song['location'] = $request->location;
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
        $_song['album'] = $album_name;
        $_song['year'] = $song->year();
        $_song['file_type'] = $song->file_type();
        $_song['track_no'] = $song->track_no();
        $_song['genre'] = $song->genre();
        $_song['location'] = $path;
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

}