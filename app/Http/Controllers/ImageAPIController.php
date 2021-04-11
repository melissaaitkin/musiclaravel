<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Music\Song\Song;
use Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class ImageAPIController extends Controller
{
    /**
     * The media directory
     *
     * @var string
     */
    private $media_directory;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->media_directory = Redis::get('media_directory');
    }

    public function coverArt($id) {
        if (! Cache::store('redis')->has('song_photo_' . $id)):
            $song = Song::find($id);
            $location = explode('/', $song->location);
            // Handle MAC and Win directory structures.
            if (count($location) < 2):
                $location = explode('\\', $song->location);
            endif;
            $location = $this->media_directory . $location[0] . DIRECTORY_SEPARATOR . $location[1];
            $files = Storage::disk(config('filesystems.partition'))->files($location);
            $path = null;
            if (count($files) > 0):
                foreach ($files as $file):
                    if (strpos($file, 'Large.jpg')):
                        $path = config('filesystems.disks')[config('filesystems.partition')]['root'] . $file;
                        Cache::store('redis')->put('song_photo_' . $id, $path, 86400);
                        break;
                    endif;
                 endforeach;
             endif;
        else:
            $path = Cache::store('redis')->get('song_photo_' . $id);
        endif;

        if (!$path):
            $path = Storage::disk('public')->path('black.jpeg');
        endif;
        return Response::download($path);
    }
}