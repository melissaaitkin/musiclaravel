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

Route::get('/', function () {
    return view('welcome');
});

Route::get('404', function () {
    return abort(404);
});

Route::middleware(['auth'])->group(function () {

    // Home route

    Route::get('home', 'HomeController@index');


    // Song Routes

    Route::get('songs', 'SongController@index');

    Route::any('songs/search', 'SongController@search');

    Route::get('song', 'SongController@create');

    Route::post('song', 'SongController@store');

    Route::get('song/{id}', 'SongController@edit');

    Route::delete('song/{id}', 'SongController@destroy');

    Route::get('song/play/{id}', 'SongController@play')->name('song.play');

    // Artist Routes

    Route::get('artists', 'ArtistController@index');

    Route::any('artists/search', 'ArtistController@search');

    Route::get('artist', 'ArtistController@create');

    Route::post('artist', 'ArtistController@store');

    Route::get('artist/{id}', 'ArtistController@edit');

    Route::delete('artist/{id}', 'ArtistController@destroy')->name('artist.destroy');

    Route::get('artist-select-ajax', 'ArtistController@artist_ajax');

    // Playlist routes

    Route::get('playlists', 'PlaylistController@index');

    Route::delete('playlists/{playlist}', 'PlaylistController@destroy')->name('playlists.destroy');

    // Genres routes
    Route::get('genres', 'GenreController@index');

    // Lyric routes
    Route::get('lyrics/{id}', 'LyricController@show');

    Route::post('lyrics', 'LyricController@store');

    // Image routes
    Route::get('cover/{id}', 'ImageAPIController@coverArt');

});

Route::middleware(['auth'])->prefix('internalapi')->group(function () {

    // Routes that can be called both internally and externally
    Route::get('songs', 'SongController@all');

    Route::get('songs/{id}', 'SongController@song');

    Route::get('playlists', 'PlaylistController@playlists');

    Route::get('playlists/songs', 'PlaylistController@songs');

    Route::post('playlists', 'PlaylistController@save');

    Route::get('genres/songs', 'GenreController@songs');

 });


Route::group(['middleware' => 'role:super-user'], function() {

    // Utilties/Configuration Routes

    Route::get("utilities", ["uses" => "UtilitiesController@index"])->name('utilities.utilities');

    Route::post("load", ["uses" => "UtilitiesController@loadSongs"])->name('utilities.load');

    Route::get("settings", "SettingsController@index");

    Route::post('settings', 'SettingsController@settings');

    Route::get('query', 'QueryController@index');

    Route::post('query', 'QueryAPIController@query');
});

Auth::routes();
