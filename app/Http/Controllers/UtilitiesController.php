<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Music\Artist\Artist;
use App\Music\AudioFile\AudioFile;
use App\Music\AudioFile\MP3;
use App\Music\AudioFile\MP4;
use App\Music\Song\Song;
use Exception;
use getID3;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Log;
use Redirect;
use Storage;

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
        $this->ID3_extractor = new getID3;
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
    public function loadSongs(Request $request)
    {
        try {
            // Check media library has been set
            if(empty($this->media_directory)):
                return view('utilities')->withErrors(["The media library needs to be set at <a href='/settings'>Settings</a>"]);
            endif;
            if(! empty($request->artist_directory)):
                if(Storage::disk(config('filesystems.partition'))->has($this->media_directory . $request->artist_directory)):
                    if(isset($request->entire_library)):
                        $this->processMediaDirectory();
                    else:
                        $dirs = explode('\\', $request->artist_directory);
                        $artist_id = $this->processArtist($dirs[count($dirs)-1]);
                        $this->processArtistDirectory($request->artist_directory, $artist_id);

                    endif;
                else:
                    return Redirect::route('utilities.utilities')
                        ->with(['artist_directory' => $request->artist_directory])
                        ->withErrors(['The artist directory not a valid directory']);
                endif;
            endif;
            // Processing songs inside a random directory
            if(! empty($request->random_directory)):
                if(is_dir($request->random_directory)):
                    $scan_songs = glob($request->random_directory . '/*');
                    foreach($scan_songs as $song):
                        $this->processSongAndArtist($song);
                    endforeach;
                else:
                    return Redirect::route('utilities.utilities')
                        ->with(['random_directory' => $request->random_directory])
                        ->withErrors(['The random directory not a valid directory']);
                endif;
            endif;

        } catch (Exception $e) {
            return view('utilities')->withErrors([$e->getMessage()]);
        }
        return Redirect::route('utilities.utilities')
            ->with([
                'msg' => 'Songs have been loaded',
                'random_directory' => $request->random_directory,
                'artist_directory' => $request->artist_directory,
            ]);
    }

    /**
     * Loop over sub directories and insert artists and songs
     *
     * @return Result
     */
    private function processMediaDirectory() {
        $result = [];
        $scan_items = glob(Redis::get('media_directory') . '/*');
        foreach($scan_items as $item):
            if(is_dir($item)):
                $artist_id = $this->processArtist($item);
                $this->processArtistDirectory($item, $artist_id);
            else:
                if (Song::isSong($item)):
                    $this->processSong($artist_id, $item);
                endif;
            endif;
        endforeach;
        return $result;
    }

    /**
     * Loop over sub directories and insert artists and songs
     *
     * @param  string $path
     * @return Result
     */
    private function processArtistDirectory(string $artist_dir, int $artist_id) {
        $scan_albums = glob($this->partition_root . $this->media_directory . $artist_dir . '/*');
        foreach($scan_albums as $album):
            if(is_dir($album)):
                $this->processAlbum($artist_dir, $album, $artist_id);
            else:
                if(Song::isSong($album)):
                    $this->processSong($artist_id, $album);
                endif;
            endif;
        endforeach;
    }

    private function processArtist(string $item) {
        $artist_arr = [basename($item), 1, 'To Set'];
        $artist_id = Artist::getID($artist_arr[0]);
        if(! $artist_id):
            $artist_id = Artist::dynamicStore($artist_arr);
        endif;
        return $artist_id;
    }

    private function processAlbum(string $artist, string $album, int $artist_id) {
        $album_name = basename($album);
        if(preg_match('/[\[\]]/', $album_name)):
            throw new Exception("Album directory contains square brackets");
        endif;
        $album_exists = Song::doesAlbumExist($artist_id, $album_name);
        if(! $album_exists):
            $is_compilation = Artist::isCompilation($artist_id);
            $scan_songs = glob($album . '/*');
            foreach($scan_songs as $song):
                if(Song::isSong($song)):
                    $song_info = $this->retrieveSongInfo($song, basename($song), $is_compilation);
                    $location = $artist . DIRECTORY_SEPARATOR . $album_name . DIRECTORY_SEPARATOR . basename($song);
                    Song::dynamicStore($location, $album_name, $artist_id, $song_info);
                    // If the song is in a compilation but the artist does not exist, add the artist.
                    if($song_info->isCompilation()):
                        if(! empty($song_info->notes())):
                            if(! Artist::getID($song_info->notes())):
                                Artist::dynamicStore([$song_info->notes(), 1, 'To Set']);
                            endif;
                        endif;
                    endif;
                endif;
            endforeach;
        endif;
    }

    private function processSong(int $artist_id, string $song) {
        $song_exists = Song::doesSongExist($artist_id, $song);
        if(! $song_exists):
            $is_compilation = Artist::isCompilation($artist_id);
            $song_info = $this->retrieveSongInfo($song, basename($song), $is_compilation);
            Song::dynamicStore($song, 'To Set', $artist_id, $song_info);
        endif;
    }

    /**
     * Add artists and songs to the database from a temporary directory and
     * move the songs into the media library
     *
     * @param String $song Song name including path
     */
    private function processSongAndArtist(string $song) {
        try {
            Log::info("Processing " . $song);
            $song_info = $this->retrieveSongInfo($song, basename($song), false);

            Log::info("Artist " . $song_info->artist());

            if(empty($song_info->artist())):
                throw new Exception("Error processing " . $song . ": unknown artist");
            endif;

            $artist_id = Artist::getID($song_info->artist());

            // Process artist
            if(! $artist_id):
                // Create artist in database.
                $artist_id = Artist::dynamicStore([$song_info->artist(), 1, 'To Set']);
            endif;

            // Make artist folder
            // Artist might exist in a compilation, so also check for a physical folder.
            if(! Storage::disk(config('filesystems.partition'))->exists($this->media_directory . $song_info->artist())):
                Log::info("Making artist directory");
                // Create artist folder in media library.
                Storage::disk(config('filesystems.partition'))->makeDirectory($this->media_directory . $song_info->artist());
            endif;

            // Make album folder
            if(! Storage::disk(config('filesystems.partition'))->exists($this->media_directory . $song_info->artist() . DIRECTORY_SEPARATOR . $song_info->album())):
                Log::info("Making album directory " . $song_info->album());
                // Create album folder under artist in media library.
                Storage::disk(config('filesystems.partition'))->makeDirectory($this->media_directory . $song_info->artist() . DIRECTORY_SEPARATOR . $song_info->album());
            endif;

            // Process song
            if(! Song::does_song_exist($artist_id, $song_info->title())):
                Log::info("Adding and moving song " . $song_info->title());
                $new_song_location = $song_info->artist() . "\\" . $song_info->album() . DIRECTORY_SEPARATOR . $song_info->title() . "." . $song_info->file_type(); 
                // Create song in datathis->media_directory.
                Song::dynamicStore($new_song_location, $song_info->album(), $artist_id, $song_info);
                Storage::disk(config('filesystems.partition'))->move(str_replace($this->partition_root, '', $song), $this->media_directory . $new_song_location);
            else:
                // Song already exists, delete this one
                Log::info("Deleting existing song");
                Storage::disk(config('filesystems.partition'))->delete(str_replace($this->partition_root, '', $song));
            endif;
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
    private function retrieveSongInfo($path, $filename, $is_compilation) {
        $file_info = $this->ID3_extractor->analyze($path);

        if(isset($file_info['error'])):
            throw new Exception("Error processing " . $path . ": " . $file_info['error'][0]);
        endif;

        if($file_info['fileformat'] === 'quicktime'):
            throw new Exception("Error processing " . $path . ": incompatible file type");
        endif;

        switch($file_info['fileformat']):
            case "mp3":
                $song = new MP3($path, $filename, $is_compilation, $file_info);
                break;
            case "mp4":
                $song = new MP4($path, $filename, $is_compilation, $file_info);
                break;
            default:
                $song = new AudioFile($file_info['fileformat'], $path, $filename, $is_compilation, $file_info);
                break;
        endswitch;
        return $song;
    }

}
