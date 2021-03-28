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

                $lyric = $this->directSearch($song->artist, $song->title);
                if (empty($lyric)):
                    $lyric = $this->search($song->artist, $song->title);
                endif;

                if (! empty($lyric)):
                    $song->lyrics = $lyric['lyric'];
                    $song->cover_art = serialize(['api' => $lyric['cover_art']]);
                    $song->save();
                else:
                    throw new Exception('Not found');
                endif;

            } catch (Exception $e) {
                $song->lyrics = 'unavailable';
                $song->save();
                Log::info($song->title . ' ' . $song->artist);
                Log::info($e->getMessage());
            }

        endforeach;

    }

    private function executeCurlRequest($url) {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
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

        if ($err):
            throw new Exception($err);
        endif;

        return $response;
    }

    /**
     * Perform direct search via API.
     *
     * @param $string $artist Song artist
     * @param $string $song Song title
     */
    private function directSearch($artist, $song) {
        $lyric = [];

        $response = $this->executeCurlRequest($this->url . "SearchLyricDirect?artist=" . urlencode($artist) . "&song=" . urlencode($song));
        $xml = simplexml_load_string($response);
        if (isset($xml) && ! empty($xml->Lyric)):
            $lyric = ['lyric' => $xml->Lyric, 'cover_art' => $xml->LyricCovertArtUrl ?? ''];
        endif;

        return $lyric;
    }

    /**
     * Perform broader search via API.
     *
     * @param $string $artist Song artist
     * @param $string $song Song title
     */
    private function search($artist, $song) {
        $lyric = [];

        $response = $this->executeCurlRequest($this->url . "SearchLyric?artist=" . urlencode($artist) . "&song=" . urlencode($song));
        $xml = simplexml_load_string($response);
        if (isset($xml->SearchLyricResult) && ! empty($xml->SearchLyricResult)):

            foreach ($xml->SearchLyricResult as $result):
                if (strcasecmp($artist, $result->Artist) === 0):
                    if (strcasecmp($song, $result->Song) === 0):
                        $lyric = $this->getLyric($result->LyricId, $result->LyricChecksum);
                    endif;
                endif;
            endforeach;
        endif;

        return $lyric;
    }

    /**
     * Retrieve lyric by id via API.
     *
     * @param $string $id Chart Lyric Id
     * @param $string $checksum Checksum
     */
    private function getLyric($id, $checksum) {
        $lyric = [];

        $response = $this->executeCurlRequest($this->url . "GetLyric?lyricId=" . $id . "&lyricCheckSum=" . $checksum);
        $xml = simplexml_load_string($response);
        if (isset($xml) && ! empty($xml->Lyric)):
            $lyric = ['lyric' => (string) $xml->Lyric, 'cover_art' => (string) $xml->LyricCovertArtUrl ?? ''];
        endif;

        return $lyric;
    }

}

