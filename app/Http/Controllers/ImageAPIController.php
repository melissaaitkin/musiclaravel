<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Music\Song\Song;
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
        $song = Song::find($id);
        $location = explode(DIRECTORY_SEPARATOR, $song->location);
        $location = $this->media_directory . $location[0] . DIRECTORY_SEPARATOR . $location[1];
        $files = Storage::disk(config('filesystems.partition'))->files($location);
        $path = null;
        if (count($files) > 0):
            foreach ($files as $file):
                if (strpos($file, 'Large.jpg')):
                    $path = config('filesystems.disks')[config('filesystems.partition')]['root'] . $file;
                    break;
                endif;
             endforeach;
         endif;

        if ($path):
            return Response::download($path);
        endif;
        return '';
    }
}
