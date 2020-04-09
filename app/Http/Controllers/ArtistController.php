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
        $artists = \MySounds\Artist::paginate(10);
        return view('artists', ['artists' => $artists]);
    }

    /**
     * Show the form for creating a new artist
     *
     * @return Response
     */
    public function create()
    {
        // TODO add to cache
        $countries = json_decode( file_get_contents( "https://restcountries.eu/rest/v2/all") );
        $country_names = [ 'Please Select' ];
        foreach( $countries as $country ) {
            $country_names[] = $country->name;
        }
        return view('artist', ['title' => 'Add Artist', 'countries' => $country_names]);
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
        $artist->save();

        $url = $request->only('redirects_to');
        return redirect()->to($url['redirects_to']);
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
        // TODO add to cache
        $countries = json_decode( file_get_contents( "https://restcountries.eu/rest/v2/all") );
        $country_names = [ 'Please Select' ];
        foreach( $countries as $country ) {
            $country_names[] = $country->name;
        }

        $artist = \MySounds\Artist::find($id);
        return view('artist', ['title' => 'Edit Artist', 'artist' => $artist, 'countries' => $country_names]);
    }

    /**
     * Search for artist.
     *
     * @param  string  $q
     * @return Response
     */
    public function search(Request $request)
    {
        $q = $request->q;
        $artists = [];
        if ($q != "") {
            if ( stripos( $q, 'SELECT') === 0 ) {
                return $this->admin_search($q);
            } else {
                $artists = \DB::table('artists')
                    ->where('artist', 'LIKE', '%' . $q . '%')
                    ->orWhere('country', 'LIKE', '%' . $q . '%')
                    ->paginate(10)
                    ->appends(['q' => $q])
                    ->setPath('');
            }
        }
        if (count ( $artists ) > 0) {
            return view('artists', ['artists' => $artists]);
        } else {
            return view('artists', ['artists' => $artists])->withMessage('No Details found. Try to search again !');
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

        $paginate = new LengthAwarePaginator($artists, 10, 1, 1, [
            'path' =>  request()->url(),
            'query' => request()->query()
        ]);

        if (count ( $artists ) > 0) {
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
    public function destroy($id)
    {
        \MySounds\Artist::findOrFail($id)->delete();
        return redirect('/artists');
    }

}
