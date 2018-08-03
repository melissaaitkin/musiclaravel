<?php

namespace MySounds\Http\Controllers;

use MySounds\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UtilitiesController extends Controller
{
    /**
     * Load songs
     *
     * @param  int  $id
     * @return Response
     */
    public function load_songs(Request $request)
    {
        if ( Auth::user()->id != 1 ) {
            abort(404);
        }
        if ( is_dir( $request->directory ) ) {
            var_dump($this->scanner($request->directory));
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
            if(is_dir($item))
                $result[basename($item)] = $this->scanner($item);
            else
                $result[] = basename($item);
        }
        return $result;
    }

}