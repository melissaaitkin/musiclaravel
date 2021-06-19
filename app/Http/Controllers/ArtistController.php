<?php

namespace App\Http\Controllers;

use App\Music\Artist\Artist;
use App\Music\Song\Song;
use App\Traits\StoreImageTrait;
use DB;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use URL;

class ArtistController extends Controller
{

    use StoreImageTrait;

    /**
     * Display artists
     *
     * @return Response
     */
    public function index()
    {
        return view('artists', ['artists' => Artist::getArtists()]);
    }

    /**
     * Show the form for creating a new artist
     *
     * @return Response
     */
    public function create()
    {
        return view('artist', ['title' => 'Add Artist', 'countries' => getCountryNames()]);
    }

    /**
     * Store a newly created artist in the database
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $validator = $request->validate([
            'artist'    => 'required',
            'founded'   => 'nullable|integer',
            'disbanded' => 'nullable|integer',
        ]);

        $record = [];
        $record['artist']           = $request->artist;
        $record['country']          = $request->country;
        $record['group_members']    = $request->group_members;
        $record['location']         = $request->location;
        $record['notes']            = $request->notes;
        $record['founded']          = $request->founded;
        $record['disbanded']        = $request->disbanded;
        $record['is_group']         = isset($request->is_group);

        $artist = Artist::updateOrCreate(['id' => $request->id ?? null], $record);

        // Only save photo if save if successful
        if ($request->hasFile('photo')):
            $image      = $request->file('photo');
            $file_name  = strtolower(preg_replace("/[^a-zA-Z0-9]+/", "", $request->artist));
            $file_name  .= '.' . $image->extension();
            $this->storeImage($image, $file_name, 'artists/', [400, 400]);
            $artist->photo = $file_name;
            $artist->save();
        endif;

        $url = $request->only('redirects_to');
        $path = $url['redirects_to'] ?? '/artists';
        return redirect()->to($path);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */ 
    public function edit($id)
    {
        $artist = Artist::find($id);
        $albums = null;
        if ($artist->artist === 'Compilations'):
            $albums = Song::getArtistAlbums($id);
            array_unshift($albums, array('album' => 'Please Select'));
        endif;
        if (!empty($artist->photo)):
            if (strpos($artist->photo, 'cdn') === false):
                $artist->photo = URL::to('/') . '/storage/artists/' . $artist->photo;
            endif;
        endif;
        return view('artist', [
            'title'     => $artist->artist,
            'artist'    => $artist,
            'albums'    => $albums,
            'songs'     => Song::getArtistSongs($id, $artist->artist),
            'countries' => getCountryNames(),
        ]);
    }

    /**
     * Search for artist.
     *
     * @param  Request request
     * @return Response
     */
    public function search(Request $request)
    {
        $q = $request->q;
        if ($q != "") {
            $data = $this->retrieveArtists($q);
        } else {
            $data = $this->retrieveArtists(session()->get('artists_query'));
        }

        // Data object can be a view or a paginator
        if (get_class($data) === 'Illuminate\View\View') {
            return $data;
        } else {
            if ($data->total() > 0) {
                return view('artists', ['q' => $q, 'artists' => $data]);
            } else {
                return view('artists', ['q' => $q, 'artists' => $data])->withMessage('No artists found. Try to search again!');
            }
        }
    }

    /**
     * Retrieve artists
     *
     * @param  string $query
     * @return array
     */
    protected function retrieveArtists($query) {
        if ($query != "") {
            session()->put('artists_query', $query);
            if ( stripos( $query, 'SELECT') === 0 ) {
                return $this->adminSearch($query);
            } else {
                return Artist::search($query);
            }
        } else {
            return Artist::orderBy('artist')->paginate();
        }
    }

     /**
     * Perform admin search on artists
     *
     * @param  string $query
     * @return Response
     */
    public function adminSearch(string $query)
    {
        if (! isValidReadQuery($query)) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $artists = DB::select($query);

        } catch (\Illuminate\Database\QueryException $ex) {
            $artists = [];
        }
        $paginate = new LengthAwarePaginator($artists, count($artists), 10, 1, [
            'path' =>  request()->url(),
            'query' => request()->query(),
        ]);

        if (count($artists) > 0) {
            return view('artists', ['q' => $query, 'artists' => $paginate]);
        } else {
            return view('artists', ['q'  => $query, 'artists' => $paginate])->withMessage('No artists found. Try to search again!');
        }
    }

    /**
     * Remove the artist and all their songs from the database
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy(Request $request, $id)
    {
        Artist::destroy($id);
        return back();
    }

    /**
     * Return artists ajax
     *
     * @return Response
     */
    public function artist_ajax(Request $request)
    {
        $data = [];

        if ($request->has('q')) {
            $data = Artist::searchByName($request->q);
        }

        return response()->json($data);
    }

    public function songs($id) {
        $artist = Artist::where('id', $id)
            ->with(['songs' => function($q) use ($id) {
                $q->select('id', 'title')->where('artist_id', '=', $id);
            }])->get();
        return $artist[0]->songs;
    }

}
