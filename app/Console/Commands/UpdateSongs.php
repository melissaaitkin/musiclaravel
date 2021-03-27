<?php

namespace App\Console\Commands;

use App\Music\Song\Song;
use Exception;
use Illuminate\Console\Command;
use Log;

class UpdateSongs extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:songs
                            {--lyrics : Update lyrics}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs song utilities';

    /**
     * Chart Lyrics API URL
     *
     * @var string
     */
    protected $url = "http://api.chartlyrics.com/apiv1.asmx/";

    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $options = $this->options();

        if(isset($options['lyrics'])):
            $this->updateLyrics();
        endif;
    }

    /**
     * Update song lyrics.
     *
     */
    protected function updateLyrics()
    {

        $songs = Song::leftJoin('artists', 'songs.artist_id', '=', 'artists.id')
            ->select('songs.id', 'songs.title', 'artist')
            ->whereNull('songs.lyrics')
            ->get();

        foreach ($songs as $song):

            try {
                $curl = curl_init();

                curl_setopt_array($curl, [
                    CURLOPT_URL => $this->url . "SearchLyricDirect?artist=" . urlencode($song->artist) . "&song=" . urlencode($song->title),
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

                if ($err):
                    Log::info($err);
                else:
                    $xml = simplexml_load_string($response);
                    if (isset($xml) && !empty($xml->Lyric)):
                        $song->lyrics = $xml->Lyric;
                        $song->save();
                    else:
                        throw new Exception('Not found');
                    endif;
                endif;

            } catch (Exception $e) {
                $song->lyrics = 'unavailable';
                $song->save();
                Log::info($song->title . ' ' . $song->artist);
                Log::info($e->getMessage());
            }

        endforeach;

    }

}