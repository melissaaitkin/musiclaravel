<?php

namespace MySounds\Http\Controllers;

use MySounds\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UtilitiesController extends Controller
{

  /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        if ( Auth::user()->id != 1 ) {
            abort(404);
        }
        return view('utilities');
    }

    /**
     * Load songs
     *
     * @param  Request $request
     * @return Response
     */
    public function load_songs(Request $request)
    {
        if ( Auth::user()->id != 1 ) {
            abort(404);
        }
        if ( is_dir( $request->directory ) ) {
            $this->scanner($request->directory);
        } else {
            $error = \Illuminate\Validation\ValidationException::withMessages([
                'directory' => ['This is not a valid directory'],
            ]);
            throw $error;
        }
        return view('utilities', ['msg' => 'Songs have been loaded']);
    }

    private function scanner($path) {
        $result = [];
        $scan = glob($path . '/*');
        foreach($scan as $item){
            echo basename($item);
            if(is_dir($item)) {
                echo " - directory</br>";
                $result[basename($item)] = $this->scanner($item);
            } else {
                $result[] = basename($item);
                echo " is a " . pathinfo( $item, PATHINFO_EXTENSION ). "</br>";
                echo "filesize " . filesize($item). "</br>";
            }
        }
        return $result;
    }

}