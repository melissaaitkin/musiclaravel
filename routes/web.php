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
	return view('welcome');
});

Route::get('/404', function () {
    return abort(404);
});

Route::middleware(['auth'])->group(function () {

	Route::get("/utilities", ["uses" => "UtilitiesController@index", "middleware" => "admin"]);

	Route::post('/load', 'UtilitiesController@load_songs');

	Route::get('/songs', 'SongController@index');

	Route::any('/songs/search', 'SongController@search');

	Route::get('/song', 'SongController@create');

	Route::post('/song', 'SongController@store');

	Route::get('/song/{id}', 'SongController@edit');

	Route::delete('/song/{id}', 'SongController@destroy');

	Route::get('/song/play/{id}', 'SongController@play')->name('song.play');

	Route::get('/artists', 'ArtistController@index');

	Route::any('/artists/search', 'ArtistController@search');

	Route::get('/artist', 'ArtistController@create');

	Route::post('/artist', 'ArtistController@store');

	Route::get('/artist/{id}', 'ArtistController@edit');

	Route::delete('/artist/{id}', 'ArtistController@destroy')->name('artist.destroy');

	Route::get('/home', 'SongController@index');

	Route::get('/playlists', 'PlaylistController@index');

	Route::delete('/playlists/{playlist}', 'PlaylistController@destroy')->name('playlists.destroy');



});

Route::middleware(['auth'])->prefix('internalapi')->group(function () {

	Route::get('/songs', 'SongController@all');

	Route::get('/songs/{id}', 'SongController@song');

	Route::get('/playlists', 'PlaylistController@playlists');

	Route::get('/playlists/songs', 'PlaylistController@songs');

	Route::post('/playlists', 'PlaylistController@save');

 });

Auth::routes();
