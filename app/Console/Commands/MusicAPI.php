<?php

namespace App\Console\Commands;

use App\Music\Artist\Artist;
use App\Music\Song\Song;
use DB;
use Exception;
use Illuminate\Console\Command;
use Log;

class MusicAPI extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:music
                            {--years : Get song years}
                            {--photos : Get song photos}
                            {--ids= : Comma separated list of song ids}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs music api jobs';

    /**
     * X RAPID API KEY
     *
     * @var string
     */
    protected $x_rapid_api_key;

    /**
     * Deezer API HOST
     *
     * @var string
     */
    protected $deezer_host;

    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        $this->x_rapid_api_key = config('app.x_rapid_api_key');
        $this->deezer_host = config('app.deezer_host');
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

        $ids = null;
        if(! empty($options['ids'])):
            $ids = explode(',', $options['ids']);
        endif;

        if(! empty($options['years'])):
            $this->updateSongYear($ids);
        endif;

        if(! empty($options['photos'])):
            $this->updatePhotos($ids);
        endif;
    }

    /**
     * Update song year.
     *
     */
    protected function updateSongYear($ids)
    {
        $query = Song::leftJoin('artists', 'songs.artist_id', '=', 'artists.id')
            ->select('songs.id', 'songs.title', 'artist')
            ->where('songs.year', 9999);
        if ($ids):
            $query->whereIn('songs.id', $ids);
        endif;
        $songs = $query->get();

        foreach ($songs as $song):
            Log::info($song->id . ':' . $song->title);
            try {
                $track = $this->search($song->title, $song->artist);
                if ($track):
                    if ($track_info):
                        if (isset($track_info->release_date) && isset($track_info->album->release_date)):
                            if (strlen($track_info->release_date) == 10 && strlen($track_info->album->release_date) == 10):
                                $year = substr($track_info->release_date, 0, 4);
                                $album_year = substr($track_info->album->release_date, 0, 4);
                                $diff = abs($year - $album_year);
                                if ($diff > 2):
                                    Log::info('Disparity in release dates: ' . $year . ' ' . $album_year);
                                else:
                                    Log::info('Updating year to : ' . $year);
                                    $song->year = $year;
                                    $song->save();
                                endif;
                            endif;
                        else:
                            Log::info('Issues with dates');
                            Log::info($track_info);
                        endif;
                    else:
                        Log::info('Not found');
                    endif;
                endif;
            } catch (Exception $e) {
                Log::info($e->getMessage());
            }

        endforeach;

    }

    /**
     * Update artist photos and genres.
     *
     */
    protected function updatePhotos($ids)
    {
        $query = "SELECT a.id, artist, (SELECT s.title FROM songs as s WHERE s.artist_id = a.id or s.notes = a.artist LIMIT 1) as title FROM artists a where genres is null";
        $artists = DB::select(DB::raw($query));

        foreach ($artists as $artist):
            Log::info($artist->artist . ':' . $artist->title);
            try {
                $track = $this->search($artist->title, $artist->artist);
                if ($track):
                    Log::info("Track");
                    Log::info(print_r($track,true));
                    $photo = $track->artist->picture_big ?? '';
                    $album_info = $this->album($track->album->id);
                    Log::info("Album");
                    Log::info(print_r($album_info,true));
                    $genres = [];
                    if (isset($album_info->genres->data)):
                        foreach($album_info->genres->data as $genre):
                            $genres[] = $genre->name;
                        endforeach;
                    endif;
                    if (!empty($photo) || !empty($genres)):
                        $a = Artist::find($artist->id);
                        $a->photo = $photo;
                        $a->genres = implode(',', $genres);
                        $a->save();
                    endif;
                endif;
            } catch (Exception $e) {
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
            CURLOPT_HTTPHEADER => array(
                "X-RapidAPI-Key: " . $this->x_rapid_api_key,
                "X-RapidAPI-Host: " . $this->deezer_host,
            ),
        ]);

        $response = curl_exec($curl);
        $response = json_decode($response);

        if (isset($response->error)):
            throw new Exception($response->error->message);
        endif;

        return $response;
    }

    /**
     * Perform broad search via API.
     *
     * @param string $song Song title
     * @param string $artist Song artist
     */
    private function search($title, $artist) {
        $response = $this->executeCurlRequest('https://' . $this->deezer_host . '/search' . "?q=" . urlencode($title));

        if (isset($response->data)) {
            foreach($response->data as $track) {
                if ($artist == $track->artist->name) {
                    return $track;
                }
            }
        }

        return false;
    }

    /**
     * Get track info via API.
     *
     * @param int $id Track id
     */
    private function track($id) {
        return $this->executeCurlRequest('https://' . $this->deezer_host . '/track/' . $id);
    }

    /**
     * Get album info via API.
     *
     * @param int $id Track id
     */
    private function album($id) {
        return $this->executeCurlRequest('https://' . $this->deezer_host . '/album/' . $id);
    }

    /**
     * Get artist info via API.
     *
     * @param int $id Track id
     */
    private function artist($id) {
        return $this->executeCurlRequest('https://' . $this->deezer_host . '/artist/' . $id);
    }

}
