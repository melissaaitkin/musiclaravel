<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

use getID3;
use Exception;
use Storage;
use Log;

use App\Music\Artist\Artist;
use App\Music\Song\Song;
use App\Music\AudioFile\AudioFile;
use App\Music\AudioFile\MP3;
use App\Music\AudioFile\MP4;

class UtilitiesController extends Controller
{

    private $ID3_extractor;

    /**
     * The media directory
     *
     * @var string
     */
    private $media_directory;

    /**
     * The file system root
     *
     * @var string
     */
    private $partition_root;

     /**
     * Constructor
     */
    public function __construct()
    {
        $this->ID3_extractor = new \getID3;
        $this->media_directory = Redis::get('media_directory');
        $this->partition_root = config('filesystems.disks')[config('filesystems.partition')]['root'];
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return view('utilities', ['media_directory' => $this->media_directory]);
    }

    /**
     * Load songs
     *
     * @param  Request $request
     * @return Response
     */
    public function load_songs(Request $request)
    {
        try {
            // Check media library has been set
            if (empty($this->media_directory)) {
                return view('utilities')->withErrors(["The media library needs to be set at <a href='/settings'>Settings</a>"]);
            }
            if (!empty($request->artist_directory)) {
                if (Storage::disk(config('filesystems.partition'))->has($this->media_directory . $request->artist_directory)) {
                    if (isset($request->entire_library)) {
                        $this->process_media_directory();
                    } else {
                        $dirs = explode('\\', $request->artist_directory);
                        $artist_id = $this->process_artist($dirs[count($dirs)-1]);
                        $this->process_artist_directory($request->artist_directory, $artist_id);

                    }
                } else {
                    return view('utilities')->withErrors(['The artist directory is not a valid directory']);
                }
            }
            // Processing songs inside a random directory
            if (!empty($request->random_directory)) {
                if (is_dir($request->random_directory)) {
                    $scan_songs = glob($request->random_directory . '/*');
                    foreach($scan_songs as $song) {
                        $this->process_song_and_artist($song);
                    }
                } else {
                    return view('utilities')->withErrors(['The random directory not a valid directory']);
                }
            }
        } catch (Exception $e) {
            return view('utilities')->withErrors([$e->getMessage()]);
        }
        return view('utilities', ['msg' => 'Songs have been loaded']);
    }

    /**
     * Loop over sub directories and insert artists and songs
     *
     * @return Result
     */
    private function process_media_directory() {
        $result = [];
        $scan_items = glob(Redis::get('media_directory') . '/*');
        foreach($scan_items as $item){
            if (is_dir($item)) {
                $artist_id = $this->process_artist($item);
                $this->process_artist_directory($item, $artist_id);
            } else {
                if (Song::is_song($item)) {
                    $this->process_song($artist_id, $item);
                }
            }
        }
        return $result;
    }

    /**
     * Loop over sub directories and insert artists and songs
     *
     * @param  string $path
     * @return Result
     */
    private function process_artist_directory(string $artist_dir, int $artist_id) {
        $scan_albums = glob($this->partition_root . $this->media_directory . $artist_dir . '/*');
        foreach($scan_albums as $album) {
            if (is_dir($album)) {
                $this->process_album($album, $artist_id);
            } else {
                if (Song::is_song($album)) {
                    $this->process_song($artist_id, $album);
                }
            }
        }
    }

    private function process_artist(string $item) {
        $artist_arr = [basename($item), 1, 'To Set'];
        $artist_id = Artist::get_id($artist_arr[0]);
        if (!$artist_id) {
            $artist_id = Artist::dynamic_store($artist_arr);
        }
        return $artist_id;
    }

    private function process_album(string $album, int $artist_id) {
        $album_name = basename($album);
        if (preg_match('/[\[\]]/', $album_name)) {
            throw new Exception("Album directory contains square brackets");
        }
        $album_exists = Song::does_album_exist($artist_id, $album_name);
        if (!$album_exists) {
            $is_compilation = Artist::is_compilation($artist_id);
            $scan_songs = glob($album . '/*');
            foreach($scan_songs as $song){
                if (Song::is_song($song)) {
                    $song_info = $this->retrieve_song_info($song, basename($song), $is_compilation);
                    Song::dynamic_store($song, $album_name, $artist_id, $song_info);
                    // If the song is in a compilation but the artist does not exist, add the artist.
                    if ($song_info->is_compilation()) {
                        if (!empty($song_info->notes())) {
                            if (!Artist::get_id($song_info->notes())) {
                                Artist::dynamic_store([$song_info->notes(), 1, 'To Set']);
                            }
                        }
                    }
                }
            }
        }
    }

    private function process_song(int $artist_id, string $song) {
        $song_exists = Song::does_song_exist($artist_id, $song);
        if ( !$song_exists ) {
            $is_compilation = Artist::is_compilation($artist_id);
            $song_info = $this->retrieve_song_info($song, basename($song), $is_compilation);
            Song::dynamic_store($song, 'To Set', $artist_id, $song_info);
        }
    }

    /**
     * Add artists and songs to the database from a temporary directory and
     * move the songs into the media library
     *
     * @param String $song Song name including path
     */
    private function process_song_and_artist(string $song) {
        try {
            Log::info("Processing " . $song);
            $song_info = $this->retrieve_song_info($song, basename($song), false);

            Log::info("Artist " . $song_info->artist());

            if (empty($song_info->artist())) {
                throw new Exception("Error processing " . $song . ": unknown artist");
            }

            $artist_id = Artist::get_id($song_info->artist());

            // Process artist
            if (!$artist_id) {
                // Create artist in database.
                $artist_id = Artist::dynamic_store([$song_info->artist(), 1, 'To Set']);
            }

            // Make artist folder
            // Artist might exist in a compilation, so also check for a physical folder.
            if (!Storage::disk(config('filesystems.partition'))->exists($this->media_directory . $song_info->artist())) {
                Log::info("Making artist directory");
                // Create artist folder in media library.
                Storage::disk(config('filesystems.partition'))->makeDirectory($this->media_directory . $song_info->artist());
            }

            // Make album folder
            if (!Storage::disk(config('filesystems.partition'))->exists($this->media_directory. $song_info->artist() . DIRECTORY_SEPARATOR . $song_info->album())) {
                Log::info("Making album directory");
                // Create album folder under artist in media library.
                Storage::disk(config('filesystems.partition'))->makeDirectory($this->media_directory . $song_info->artist() . DIRECTORY_SEPARATOR . $song_info->album());
            }

            // Process song
            if (!Song::does_song_exist($artist_id, $song_info->title())) {
                Log::info("Adding and moving song " . $song_info->title());
                $new_song_location = $song_info->artist() . "\\" . $song_info->album() . DIRECTORY_SEPARATOR . $song_info->title() . "." . $song_info->file_type(); 
                // Create song in datathis->media_directory.
                Song::dynamic_store($new_song_location, $song_info->album(), $artist_id, $song_info);
                Storage::disk(config('filesystems.partition'))->move(str_replace($this->partition_root, '', $song), $this->media_directory . $new_song_location);
            } else {
                // Song already exists, delete this one
                Log::info("Deleting existing song");
                Storage::disk(config('filesystems.partition'))->delete(str_replace($this->partition_root, '', $song));
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    /**
     * Retrieve song info via ID3
     *
     * @param string $path Full file path
     * @param string $filename Filename
     * @param boolean $is_compilation Is song part of a compilation ablum
     * @return array
     */
    private function retrieve_song_info($path, $filename, $is_compilation) {
        $file_info = $this->ID3_extractor->analyze($path);

        if (isset($file_info['error'])) {
            throw new Exception("Error processing " . $path . ": " . $file_info['error'][0]);
        }

        if ($file_info['fileformat'] === 'quicktime') {
            throw new Exception("Error processing " . $path . ": incompatible file type");
        }

        switch ($file_info['fileformat']) {
            case "mp3":
                $song = new MP3($path, $filename, $is_compilation, $file_info);
                break;
            case "mp4":
                $song = new MP4($path, $filename, $is_compilation, $file_info);
                break;
            default:
                $song = new AudioFile($file_info['fileformat'], $path, $filename, $is_compilation, $file_info);
                break;
        }
        return $song;
    }

}