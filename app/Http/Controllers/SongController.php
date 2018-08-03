<?php

namespace MySounds\Http\Controllers;

use Illuminate\Http\Request;

class SongController extends Controller
{

  /**
     * Display songs
     *
     * @return Response
     */
    public function index()
    {
		$songs = \MySounds\Song::all();
		return view('songs', ['songs' => $songs]);
    }

    /**
     * Show the form for creating a new song
     *
     * @return Response
     */
    public function create()
    {
		$artists = \MySounds\Artist::all( [ 'id', 'artist']);
		return view('song', ['artists' => $artists]);
    }

    /**
     * Store a newly created song in the database
     *
     * @return Response
     */
    public function store(Request $request)
    {
	    $validator = $request->validate([
	        'title' => 'required|max:255',
	        'album' => 'required|max:255',
	        'year' => 'required|integer|between:1700,2100'
	    ]);

	    $song = new \MySounds\Song;
	    $song->title = $request->title;
	    $song->album = $request->album;
	    $song->year = $request->year;
	    $song->artist_id = $request->artist_id;
	    $song->save();

	    return redirect('/songs');
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
     * Remove the song from the database
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
	    \MySounds\Song::findOrFail($id)->delete();
	    return redirect('/songs');
    }

}
