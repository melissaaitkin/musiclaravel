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

Route::middleware(['auth'])->group(function () {

	Route::get("/utilities", ["uses" => "UtilitiesController@index", "middleware" => "admin"]);

	Route::post('/load', 'UtilitiesController@load_songs');

	Route::get('/songs', 'SongController@index');

	Route::any('/songs/search', 'SongController@search');

	Route::get('/song', 'SongController@create');

	Route::post('/song', 'SongController@store');

	Route::get('/song/{id}', 'SongController@edit');

	Route::delete('/song/{id}', 'SongController@destroy');

	Route::get('/artists', 'ArtistController@index');

	Route::any('/artists/search', 'ArtistController@search');

	Route::get('/artist', 'ArtistController@create');

	Route::post('/artist', 'ArtistController@store');

	Route::delete('/artist/{id}', 'ArtistController@destroy');

});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
