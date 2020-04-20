<?php

namespace MySounds\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ArtistController extends Controller
{

  /**
     * Display artists
     *
     * @return Response
     */
    public function index()
    {
        // TODO add notes field to artist to describe mixed nationalities
        // Add group member field
        // Work with Compilation/Compilations
        $artists = \MySounds\Artist::orderBy('artist')->paginate(10);
        return view('artists', ['artists' => $artists]);
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
        $validator = $request->validate([
            'artist' => 'required|max:255',
        ]);

        if (isset($request->id)){
            $artist = \MySounds\Artist::findOrFail($request->id);
        } else {
            $artist = new \MySounds\Artist;
        }

        $artist->artist = $request->artist;
        $artist->is_group = isset($request->is_group);
        $artist->country = $request->country;
        $artist->group_members = $request->group_members;
        $artist->notes = $request->notes;
        $artist->save();

        $url = $request->only('redirects_to');
        $path = $url['redirects_to'] ?? '/artists';
        return redirect()->to($path);
    }

    /**
     * Store an dynamically created artist in the database
     *
     * @param array $artist
     * @return integer Artist id
     */
    public function dynamic_store(array $artist)
    {
        $_artist = new \MySounds\Artist;
        $_artist->artist = $artist[0];
        $_artist->is_group = $artist[1];
        $_artist->country = $artist[2];
        $_artist->save();
        return $_artist->id;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Does the artist exist
     *
     * @param  string $artist_name Artist name
     * @return boolean
     */
    public function get_id($artist_name)
    {
        $artist = \MySounds\Artist::where("artist", $artist_name)->first();
        return $artist->id ?? false;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */ 
    public function edit($id)
    {
        // TODO add return to list button
        $artist = \MySounds\Artist::find($id);
        $songs = \MySounds\Song::select('title')->where(["artist_id" => $id])->orderBy('title')->get();
        return view('artist', [
            'title' => $artist->artist,
            'artist' => $artist,
            'songs' => $songs,
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
                return \DB::table('artists')
                    ->where('artist', 'LIKE', '%' . $query . '%')
                    ->orWhere('country', 'LIKE', '%' . $query . '%')
                    ->paginate(10)
                    ->appends(['q' => $query])
                    ->setPath('');
            }
        } else {
            return \MySounds\Artist::orderBy('artist')->paginate(10);
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
        \MySounds\Artist::findOrFail($id)->delete();
        return back();
    }

}
