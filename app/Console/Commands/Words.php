<?php

namespace App\Console\Commands;

use App\Music\Song\Song;
use Exception;
use Illuminate\Console\Command;
use Log;

class Words extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:words
                            {--cloud : Word cloud}
                            {--sids= : Comma separated list of song ids}
                            {--aids= : Comma separated list of artist ids}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs word utilities';

    /**
     * The word cloud
     *
     * @var array
     */
    protected $word_cloud = [];

    protected $countries;

    protected $places;

    protected $months = [];

    protected $names = [];

    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        $this->setCountries();
        $this->setPlaces();
        $this->setMonths();
        $this->setNames();
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

        $song_ids = null;
        if(! empty($options['sids'])):
            $song_ids = explode(',', $options['sids']);
        endif;

        $artist_ids = null;
        if(! empty($options['aids'])):
            $artist_ids = explode(',', $options['aids']);
        endif;

        if(! empty($options['cloud'])):
            $this->getWordCloud($song_ids, $artist_ids);
        endif;
    }

    public function setCountries() {
        $this->countries = getCountryNames();
        $this->countries[] = 'U.S';
        $this->countries[] = 'USA';
        $this->countries[] = 'America';
        $this->countries[] = 'Africa';
        $this->countries[] = 'Afrika';
        $this->countries[] = 'Asia';
        $this->countries[] = 'Europe';
    }

    private function isCountry($word) {
        foreach ($this->countries as $country):
            if (strpos($word, $country) === 0):
                return true;
            endif;
        endforeach;
        return false;
    }

    private function setPlaces() {
        // Cannot handle West Memphis or New York City Great Britain, Lake Charles
        $this->places = [
            'Rockville',
            'Slidell',
            'Memphis',
            'Boston',
            'PA',
            'Montague',
            'Melbourne',
            'Sydney',
            'Hobart',
            'Perth',
            'Greenville',
            'Brasilia',
            'Trenton',
            'Aberdeen',
            'Alaska',
            'Alberta',
            'Albuquerque',
            'Algiers',
            'Allentown',
            'Amsterdam',
            'Antarctic',
            'Arkansas',
            'Fitzroy',
            'Athens',
            'Avalon',
            'Galveston',
            'Galilee',
            'Acapulco',
            'Accrington',
            'Amazon',
            'Mississippi',
            'Rosedale',
            'Alabamba',
            'Bangkok',
            'Calgary',
            'Anaheim',
            'Reno',
            'Winnemucca',
            'L.A',
            'Brooklyn',
            'Bronx',
            'Chicago',
            'Orleans',
            'Napoli',
            'Siberia',
            'Ebudae',
            'Khartoum',
            'Bissau',
            'Palau',
            'Avalon',
            'Fiji',
            'Tiree',
            'Bablyon',
            'Peru',
            'Cebu',
            'Bali',
            'Macquarie',
            'Alabama',
            'Alaska',
            'Angeles',
        ];
    }

    private function isPlace($word) {
        return in_array($word, $this->places);
    }

    private function setMonths() {
        $this->months = [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December',
        ];
    }

    private function isMonth($word) {
        return in_array($word, $this->months);
    }

    private function setNames() {
        $this->names = [
            'Andy',
            'Peter',
            'Kaufman',
            'Fred',
            'Blassie',
            'Elvis',
            'Charles',
            'Darwin',
            'Matt',
            'Mott',
            'Hoople',
            'Moses',
            'John',
            'Tartanella',
            'Joe',
            'Erin',
            'Rod',
            'Laver',
            'God',
            'Thatcher',
            'Rayvon',
            'Shaggy',
            'Dooler',
            'Seavers',
            'Mohamed',
            'Aaliyah',
            'Abigail',
            'Abraham',
            'Confusious',
            'Adam',
            'Agamemnon',
            'Alexander',
            'Alexandra',
            'Alison',
            'Alma',
            'Amy',
            'Antonio',
            'Antony',
            'Aristophanes',
            'Arthur',
            'Astrid',
            'Athena',
            'Galileo',
            'Abdul',
            'Abe',
            'Thomas',
            'Edison',
            'Johnson',
            'Alan',
            'Albert',
            'Alec',
            'Eiffel',
            'Alejandro',
            'Alex',
            'Alfie',
            'Alice',
            'Allison',
            'Alma',
            'Roxie',
            'Chino',
            'Jack',
            'Horner',
            'Dickins',
            'Ross',
            'Bobby',
            'MTV',
            'NBC',
            'ABC',
            'CBS',
            'NRA',
            'Coca',
            'Giuseppe',
            'Maria',
            // 'Al',
            'Capone',
            'Kevin',
            'Hugh',
            'Carla',
            'William',
            'Dave',
            'Popeye',
            'Allan',
            'Alvin',
            'Amanda',
            'Ana',
            'Angelica',
            'Anna',
            'Annabelle',
            'Anne',
            'Annie',
            'Anton',
            'Antone',
            'Apepig',
            'Gavrilo',
            'Princip',
            'Sophia',
        ];
    }

    private function isName($word) {
        foreach ($this->names as $name):
            if (strpos($word, $name) === 0):
                return true;
            endif;
        endforeach;
        return false;
    }

    /**
     * Get word cloud.
     *
     */
    protected function getWordCloud($song_ids, $artist_ids)
    {
        $query = Song::select('songs.id', 'title', 'lyrics')
            ->join('artist_song', 'songs.id', '=', 'artist_song.song_id')
            ->whereNotIn('songs.id', [
                819, 908, 911, 1273, 1314, 1425, 2133, 2225, 2344, 2601, 3156, 3165, 3198, 3965, 3968, 4145, 4261, 4892, 5737, 6218, 6502, 8036, 8587, 9143, 9183, 9762,
            ])
            // this shouldn't be returning anyway
            ->whereNotIn('songs.id', [3053])
            ->whereNotIn('artist_song.artist_id', [
                23, 84, 107, 197, 209, 211, 248, 280, 469, 607, 611, 802, 821, 838, 841, 846, 1317, 1453,
            ])
            ->whereNotIn('album', [
                'Turkish Groove', 'African Women', 'Bocelli Greatest Hits', 'Buena Vista Social Club', 'Everything Is Possible!',
                "Edith Piaf - 20 'French' Hit Singles",
            ])
            ->whereNotIn('lyrics', ['unavailable', 'Instrumental']);

        if ($song_ids):
            $query->whereIn('songs.id', $song_ids);
        endif;
        $lyrics = $query->get()->toArray();

        foreach ($lyrics as $song):
            try {
                $lyric = str_replace([PHP_EOL], [' '], $song['lyrics']);
                // $lyric = str_replace([PHP_EOL, ',', '. ', '?', '(', ')'], [' ', '', ' ', '', '', ''], $lyric['lyrics']);
                $words = explode(' ', $lyric);

                foreach ($words as $word):
                    $this->processWord($word, $song['id']);
                  endforeach;
            } catch (Exception $e) {
                Log::info($e->getMessage());
            }

        endforeach;
        ksort($this->word_cloud);
        Log::info($this->word_cloud);
    }

    /**
     * Get word cloud.
     *
     */
    public function processWord($word, $id) {
        // Ignore non-Latin words.
        if (preg_match('/^\p{Latin}+$/', $word)):
            // strip 's off the end of a word
            // $word = str_replace(["'s", '!'], ['', ''], $word);
            // Remove trailing period
            // $word = rtrim($word, '.');
            // Remove trailing or prefixed single quotation marks
            // $word = trim($word, "'");
            // $word = ltrim($word, "~");
            // $word = ltrim($word, "{");
            // $word = rtrim($word, "}");
            // $word = ltrim($word, "[");
            // $word = rtrim($word, "]");
            // $word = trim($word, '"');
            // $word = trim($word, '-');
            // $word = trim($word);
            // Retain capitilisation for countries, months, names etc
            $word = $this->setCase($word);
            if (! empty($word)):
                if (!isset($this->word_cloud[$word])):
                    $this->word_cloud[$word] = [];
                endif;
                if (is_array($this->word_cloud[$word])):
                    if (count($this->word_cloud[$word]) == 20):
                        $this->word_cloud[$word] = 21;
                    else:
                        $this->word_cloud[$word][] = $id;
                    endif;
                else:
                    $this->word_cloud[$word] += 1;
                endif;
            endif;
        endif;
    }

    public function setCase($word) {
        $tmp_word = ucfirst(strtolower($word));
        if ($this->isCountry($tmp_word) || $this->isPlace($tmp_word)  || $this->isMonth($tmp_word)  || $this->isName($tmp_word)):
            return $tmp_word;
        else:
            return strtolower($word);
        endif;
    }
}
