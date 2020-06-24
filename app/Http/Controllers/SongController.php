<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

use App\Music\Song\Song;
use App\Music\Artist\Artist;

use Storage;
use File;
use Exception;
use Config;

class SongController extends Controller
{

    /**
     * The media directory
     *
     * @var string
     */
    private $media_directory;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->media_directory = Redis::get('media_directory');
    }

    /**
     * Display songs
     *
     * @return Response
     */
    public function index()
    {
        $songs = Song::leftJoin('artists', 'songs.artist_id', '=', 'artists.id')
            ->select('songs.*','artist')
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
        return view('song', [
            'title'         => 'Add New Song',
            'file_types'    => Song::FILE_TYPES,
            'song_exists'   => false,
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
        Song::store($request);
        return redirect('/songs');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $song = Song::find($id);
        return view('song', [
            'song'          => $song,
            'title'         => $song->title,
            'artist_name'   => $song->artist->artist,
            'file_types'    => Song::FILE_TYPES,
            'song_exists'   => Storage::disk(config('filesystems.partition'))->has($this->media_directory . $song->location),
        ]);
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
        if ($q != "") {
            $data = $this->retrieve_songs($q);
        } else {
            $data = $this->retrieve_songs(session()->get('songs_query'));
        }
        // Data object can be a view or a paginator
        if (get_class($data) === 'Illuminate\View\View') {
            return $data;
        } else {
            if ($data->total() > 0) {
                return view('songs', ['q' => $q, 'songs' => $data]);
            } else {
                return view('songs', ['q' => $q, 'songs' => $data])->withMessage('No songs found. Try to search again !');
            }
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
            if (stripos( $query, 'SELECT') === 0) {
                return $this->admin_search($query);
            } else {
                return Song::search($query);
            }
        } else {
            return Song::subset();
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
        if (!is_valid_read_query($query)) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $songs = DB::select($query);

        } catch (\Illuminate\Database\QueryException $ex) {
            $songs = [];
        }
        $paginate = new LengthAwarePaginator($songs, count($songs), 10, 1, [
            'path' =>  request()->url(),
            'query' => request()->query()
        ]);

        if (count($songs) > 0) {
            return view('songs', ['q' => $query, 'songs' => $paginate]);
        } else {
            return view('songs', ['q'  => $query, 'songs' => $paginate])->withMessage('No songs found. Try to search again !');
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
        Song::destroy($id);
        return redirect('/songs');
    }

    public function play($id)
    {
        $song = Song::find($id);
        $location = $this->media_directory . $song->location;
        // TODO what to do with wma files
        if (Storage::disk(config('filesystems.partition'))->has($location)) {
            $contents = Storage::disk(config('filesystems.partition'))->get($location);
            return response($contents, 200)->header("Content-Type", 'audio/mpeg');
        }
    }

    /**
     * Retrieve all songs by various criteria
     *
     * @param Request
     * @return Response
     */
    public function all(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'        => 'numeric',
            'artist_id' => 'numeric',
            'offset'    => 'numeric',
            'limit'     => 'numeric',
        ]);

        // Validate parameters
        if ($validator->fails()):
            return ['errors' => $validator->errors(), 'status_code' => 422];
        endif;

        if (isset($request->id)) {
            if (isset($request->album)) {
                // Get songs in an album by song id
                $songs = Song::get_album_songs_by_song_id($request->id);
            } else {
                // Get song
                $songs = Song::find($request->id);
            }
        } else {
            // Get all songs
            $songs = Song::songs($request);
        }

        return ['songs' => $songs, 'status_code' => 200];
    }

    public function song($id)
    {
        return ['song' => Song::find($id), 'status_code' => 200];
    }
}