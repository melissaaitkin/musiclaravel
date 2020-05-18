<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([
    'middleware' => [
        'auth.mymusic.api',
    ],
], function () {

	Route::get('/songs', 'SongController@all')->name('songs');

	Route::get('/songs/{id}', 'SongController@song')->name('songs.song');

	Route::get('/playlists', 'PlaylistController@playlists')->name('playlists');

	Route::post('/playlists', 'PlaylistController@save')->name('playlists.save');

	Route::get('/playlists/songs', 'PlaylistController@songs')->name('playlists.songs');

});

