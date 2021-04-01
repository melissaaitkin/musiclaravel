<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LyricAPIController extends Controller
{

    /**
     * Chart Lyrics API URL
     *
     * @var string
     */
    protected $url = "http://api.chartlyrics.com/apiv1.asmx/";

    /**
     * Retrieve song lyrics
     *
     * @param  Request $request
     * @return Response
     */
    public function getLyrics(Request $request)
    {
        if (! isset($request->artist) || ! isset($request->song)) {
            echo "Please supply artist and song parameters";
            exit;
        }

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $this->url . "SearchLyricDirect?artist=" . urlencode($request->artist) . "&song=" . urlencode($request->song),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array("Content-type: text/xml"),
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            // echo $response;
            $xml = simplexml_load_string($response);

            if (isset($xml) && ! empty($xml->Lyric)) {
                // echo "Track ID: " . $xml->TrackId . "</br>";
                // echo "Lyric ID: " . $xml->LyricId . "</br>";
                // echo "Cover Art: " . $xml->LyricCovertArtUrl . "</br></br>";
                echo $request->song . "</br></br>";
                echo $xml->Lyric;
            } else {
                echo "SONG cannot be found";
            }
        }
    }

}
