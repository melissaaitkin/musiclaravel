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

Route::get('/songs', function () {
	$songs = \MySounds\Song::all();
	return view('songs', ['songs' => $songs]);
});

Route::get('/song', function () {
	$songs = \MySounds\Song::all();
	return view('song', ['songs' => $songs]);
});

/**
 * Add A New Song
 */
Route::post('/song', function (Request $request) {
//    $data = $request->validate([
  //      'title' => 'required|max:255',
    //	]);

  //  if ($validator->fails()) {
    //    return redirect('/')
      //      ->withInput()
        //    ->withErrors($validator);
    //}

    $song = new \MySounds\Song;
    $song->title = $request->title;
    $song->album = $request->album;
    $song->year = $request->year;
    $song->artist_id = 1;
    $song->save();

    return redirect('/');
});

/**
 * Delete An Existing Song
 */
Route::delete('/song/{id}', function ($id) {
    //
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
