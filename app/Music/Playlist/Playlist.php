<?php

namespace App\Music\Playlist;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Playlist extends Model
{

    protected $table = 'playlists';

    /**
     * The playlist name.
     *
     * @var string
     */
    protected $name;

    /**
     * The playlist.
     * A string of json.
     *
     * @var string
     */
    protected $playlist;

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
	 * The attributes that aren't mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [];

}