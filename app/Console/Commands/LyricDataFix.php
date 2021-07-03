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
                            {--r= : String to replace}
                            {--id= : Song to find}
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
            if(! empty($options['r'])):
                $this->replaceLyric($options['s'], $options['r'], $options['c'], $options['id']);
            else:
                $this->getLyric($options['s'], $options['c'], $options['id']);
            endif;
        endif;
    }

    /**
     * Find and clean lyrics
     *
     */
    protected function getLyric($str, $clean, $id = NULL)
    {
        if ($id):
            $query = Song::select('id', 'title', 'lyrics')->where('id', $id);
        else:
            $query = Song::select('id', 'title', 'lyrics')->where('lyrics', 'LIKE', "%$str%");
            // $query = Song::select('id', 'title', 'lyrics')->where('lyrics', 'LIKE', "%$str%")->where('id', '>', 8769);
        endif;
        $songs = $query->get()->toArray();

        foreach ($songs as $song):
            try {
                $offset = 0;
                $lyric = $song['lyrics'];
                while ($pos = strpos($lyric, $str, $offset)):
                    $start = strrpos(substr($lyric, 0, $pos), PHP_EOL);
                    $end = stripos(substr($lyric, $pos), PHP_EOL);
                    if (empty($end)):
                        $end = strlen($lyric);
                    endif;
                    $target = substr($lyric, $start, $end + ($pos - $start));
                    $lyric = str_replace($target, '', $lyric);
                    $offset = $pos + 1;
                endwhile;
            } catch (Exception $e) {
                Log::info($e->getMessage());
            }

            Log::info($song);
            Log::info($lyric);

            if ($clean):
                Song::where('id', $song['id'])->update(['lyrics' => $lyric]);
            endif;
            exit;
        endforeach;
    }

    /**
     * Replace words in lyrics
     *
     */
    protected function replaceLyric($str, $repl, $clean, $id)
    {
        if ($id):
            $query = Song::select('id', 'title', 'lyrics')->where('id', $id);
            $song = $query->first()->toArray();

            $lyric = $song['lyrics'];
            $lyric = str_replace($str, $repl, $lyric);

            Log::info($song);
            Log::info($lyric);

            if ($clean):
                Song::where('id', $song['id'])->update(['lyrics' => $lyric]);
            endif;
        endif;
    }

}
