<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Music\Song\Song as Song;

class GenreController extends Controller
{

    /**
     * Display genres
     *
     * @return Response
     */
    public function index()
    {
        // Retrieve genres, weeding out empty or null values.
        $genres = Song::select('genre')->where('genre', '>', '')->groupBy('genre')->get();
        return view('genres', ['genres' => $genres]);
    }

    /**
     * Retrieve songs in a genre
     *
     * @param Request $request
     * @return Response
     */
    public function songs(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'genre' => 'required|max:100',
        ]);

        // Validate parameters
        if ($validator->fails()):
            return ['errors' => $validator->errors(), 'status_code' => 422];
        endif;

        $songs = Song::where(['genre' => $request->genre])->get(['id', 'title'])->toArray();
        return ['songs' => $songs, 'status_code' => 200];
    }

}