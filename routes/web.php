<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Http\Request;

Route::get('/', function () {
	$songs = \MySounds\Song::all();
	return view('welcome', ['songs' => $songs]);
});

Route::get('/utilities', function () {
    if ( Auth::user()->id != 1 ) {
        abort(404);
    }
    return view('utilities');
});

Route::post('/load', 'UtilitiesController@load_songs');

Route::get('/songs', function () {
	$songs = \MySounds\Song::all();
	return view('songs', ['songs' => $songs]);
});

Route::get('/song', function () {
	$artists = \MySounds\Artist::all( [ 'id', 'artist']);
	return view('song', ['artists' => $artists]);
});

/**
 * Add A New Song
 */
Route::post('/song', function (Request $request) {
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
});

/**
 * Delete An Existing Song
 */
Route::delete('/song/{id}', function ($id) {
    \MySounds\Song::findOrFail($id)->delete();
    return redirect('/songs');
});

Route::get('/artist', function () {
    // TODO add to cache
    $countries = json_decode( file_get_contents( "https://restcountries.eu/rest/v2/all") );
    $country_names = [ 'Please Select' ];
    foreach( $countries as $country ) {
        $country_names[] = $country->name;
    }
    return view('artist', [ 'countries' => $country_names ]);
});

/**
 * Add A New Artist
 */
Route::post('/artist', function (Request $request) {
    $validator = $request->validate([
        'artist' => 'required|max:255',
    ]);
    $artist = new \MySounds\Artist;
    $artist->artist = $request->artist;
    $artist->is_group = isset( $request->is_group );
    $artist->country = $request->country;
    $artist->save();

    return redirect('/artists');
});

Route::get('/artists', function () {
    $artists = \MySounds\Artist::all();
    return view('artists', ['artists' => $artists]);
});

/**
 * Delete An Existing Artist
 */
Route::delete('/artist/{id}', function ($id) {
    \MySounds\Artist::findOrFail($id)->delete();
    return redirect('/artists');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
