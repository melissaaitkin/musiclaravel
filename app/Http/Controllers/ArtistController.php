<?php

namespace MySounds\Http\Controllers;

use Illuminate\Http\Request;

class ArtistController extends Controller
{

  /**
     * Display artists
     *
     * @return Response
     */
    public function index()
    {
        $artists = \MySounds\Artist::all();
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
        return view('artist', [ 'countries' => $country_names ]);
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
        $artist = new \MySounds\Artist;
        $artist->artist = $request->artist;
        $artist->is_group = isset( $request->is_group );
        $artist->country = $request->country;
        $artist->save();

        return redirect('/artists');
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
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        //
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
