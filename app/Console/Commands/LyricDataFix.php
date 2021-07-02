<?php

namespace App\Console\Commands;

use App\Music\Song\Song;
use Exception;
use Illuminate\Console\Command;
use Log;

class LyricDataFix extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:lyric
                            {--df : Data fix}
                            {--s= : String to find}
                            {--c : Clean lyric}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs lyric analysis and cleanup';

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

        if(! empty($options['df'])):
            $this->getLyric($options['s'], isset($options['c']));
        endif;
    }

    /**
     * Find and clean lyrics
     *
     */
    protected function getLyric($str, $clean)
    {
        // $query = Song::select('id', 'title', 'lyrics')->where('id', 143);
        $query = Song::select('id', 'title', 'lyrics')->where('lyrics', 'LIKE', "%$str%");
        $songs = $query->get()->toArray();
        
        foreach ($songs as $song):
            try {
                Log::info($song['lyrics']);
                $offset = 0;
                $lyric = $song['lyrics'];
                while ($pos = strpos($lyric, $str, $offset)):
                    // Log::info($pos);
                    $start = strrpos(substr($lyric, 0, $pos), PHP_EOL);
                    // Log::info($start);
                    $end = stripos(substr($lyric, $pos), PHP_EOL);
                    // Log::info($end);
                    $target = substr($lyric, $start, $end + ($pos - $start));
                    // Log::info($target);
                    $lyric = str_replace($target, '', $lyric);
                    $offset = $pos + 1;
                endwhile;
                Log::info($lyric);
            } catch (Exception $e) {
                Log::info($e->getMessage());
            }
            if ($clean):
                Song::where('id', $song['id'])->update(['lyrics' => $lyric]);
            endif;
            exit;
        endforeach;
    }

}
