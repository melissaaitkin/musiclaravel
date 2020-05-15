<?php

namespace MySounds\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use MySounds\Music\Artist\Artist as Artist;
use MySounds\Music\Song\Song as Song;

class ArtistController extends Controller
{

    /**
     * Display artists
     *
     * @return Response
     */
    public function index()
    {
        return view('artists', ['artists' => Artist::get_artists()]);
    }

    /**
     * Show the form for creating a new artist
     *
     * @return Response
     */
    public function create()
    {
        return view('artist', ['title' => 'Add Artist', 'countries' => get_country_names()]);
    }

    /**
     * Store a newly created artist in the database
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        Artist::store($request);

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
        if ($artist->artist === 'Compilations') {
            $albums = Song::get_artist_albums($id);
            array_unshift($albums, array('album' => 'Please Select'));
        }
        return view('artist', [
            'title'     => $artist->artist,
            'artist'    => $artist,
            'albums'    => $albums,
            'songs'     => Song::get_artist_songs($id, $artist->artist),
            'countries' => get_country_names(),
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
        $artists = [];
        if ($q != "") {
            $artists = $this->retrieve_artists($q);
        } else {
            $artists = $this->retrieve_artists(session()->get('artists_query'));
        }
        // Artists can be returned as an array or a paginated object.
        if (is_array($artists) && count($artists) == 0) {
            return view('artists', ['artists' => $artists])->withMessage('No Details found. Try to search again !');
        } else {
            return view('artists', ['artists' => $artists]);
        }
    }

    /**
     * Retrieve artists
     *
     * @param  string $query
     * @return array
     */
    protected function retrieve_artists($query) {
        if ($query != "") {
            session()->put('artists_query', $query);
            if ( stripos( $query, 'SELECT') === 0 ) {
                return $this->admin_search($query);
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
    public function admin_search(string $query)
    {
        if ( stripos( $query, 'DELETE') !== false || stripos( $query, 'UPDATE') ) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $artists = \DB::select($query);
        } catch (\Illuminate\Database\QueryException $ex) {
            $artists = [];
        }
        $paginate = new LengthAwarePaginator($artists, count($artists), 10, 1, [
            'path' =>  request()->url(),
            'query' => request()->query()
        ]);

        if (count($artists) > 0) {
            return view('artists', ['artists' => $paginate]);
        } else {
            return view('artists', ['artists' => $paginate])->withMessage('No Details found. Try to search again !');
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

}
