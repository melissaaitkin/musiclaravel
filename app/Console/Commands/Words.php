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

    protected $brands = [];

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
        $this->setBrands();
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
        $this->countries[] = 'American';
        $this->countries[] = 'Americana';
        $this->countries[] = 'Africa';
        $this->countries[] = 'Afrika';
        $this->countries[] = 'Arab';
        $this->countries[] = 'Arabs';
        $this->countries[] = 'Argentines';
        $this->countries[] = 'Asia';
        $this->countries[] = 'Asian';
        $this->countries[] = 'Asiatic';
        $this->countries[] = 'Europe';
        $this->countries[] = 'Russian';
        unset($this->countries['Multipe']);
        unset($this->countries['Unknown']);
    }

    private function isCountry($word) {
        return in_array($word, $this->countries);
    }

    private function setPlaces() {
        // Cannot handle West Memphis or New York City Great Britain, Lake Charles, Los Angeles, Buenos Aires
        $this->places = [
            'Aberdeen',
            'Aberdine',
            'Acapulco',
            'Accrington',
            'Aires',
            'Alabama',
            'Alaska',
            'Alberta',
            'Albuquerque',
            'Algiers',
            'Allentown',
            'Amazon',
            'Amazonians',
            'Amsterdam',
            'Anaheim',
            'Andalusia',
            'Angeles',
            'Annan',
            'Antarctic',
            'Appalachia',
            'Appel',
            'Argonauts',
            'Arizona',
            'Arkansas',
            'Athens',
            'Atlanta',
            'Atlantic',
            'Atlantis',
            'Avalon',
            'Bablyon',
            'Bali',
            'Bangkok',
            'Bissau',
            'Boston',
            'Brasilia',
            'Brooklyn',
            'Bronx',
            'Calgary',
            'Cebu',
            'Chicago',
            'Detroit',
            'Ebudae',
            'Fitzroy',
            'Galveston',
            'Galilee',
            'Greenville',
            'Guantanamo',
            'Hobart',
            'Khartoum',
            'L.A',
            'Macquarie',
            'Melbourne',
            'Memphis',
            'Mississippi',
            'Montague',
            'Moscow',
            'Napoli',
            'Orleans',
            'PA',
            'Palau',
            'Perth',
            'Peru',
            'Reno',
            'Rockville',
            'Rosedale',
            'Siberia',
            'Slidell',
            'Sydney',
            'Tiree',
            'Trenton',
            'Winnemucca',
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
        // May is complex, will often be a word
        return in_array($word, $this->months);
    }

    private function setNames() {
        $this->names = [
            'Aaliyah',
            'Abdul',
            'Abe',
            'Abel',
            'Abigail',
            'Abraham',
            'Adam',
            'Agamemnon',
            'Ajax',
            'Al',
            'Alan',
            'Albert',
            'Aldous',
            'Alec',
            'Alejandro',
            'Alex',
            'Alexander',
            'Alexandra',
            'Alfie',
            'Ali',
            'Alice',
            'Alison',
            'Allan',
            'Alvin',
            'Allison',
            'Alma',
            'Amadeus',
            'Amanda',
            'Amy',
            'Andre',
            'Andy',
            'Ana',
            'Angelica',
            'Ann',
            'Anna',
            'Annabelle',
            'Anne',
            'Annie',
            'Anthony',
            'Anton',
            'Antone',
            'Antonio',
            'Antony',
            'Apepig',
            'Apollo', // also a place
            'Aristophanes',
            'Arthur',
            'Astaire',
            'Astrid',
            'Athena',
            'Blassie',
            'Bobby',
            'Capone',
            'Carla',
            'Charles',
            'Chino',
            'Confusious',
            'Darwin',
            'Dave',
            'Dickins',
            'Dooler',
            'Edison',
            'Elvis',
            'Erin',
            'Fred',
            'Galileo',
            'Gavrilo',
            'God',
            'Giuseppe',
            'Hoople',
            'Horner',
            'Hugh',
            'Jack',
            'Joe',
            'John',
            'Johnson',
            'Kaufman',
            'Kevin',
            'Laver',
            'Matt',
            'Maria',
            'Mohamed',
            'Moses',
            'Mott',
            'Muhammad',
            'Peter',
            'Popeye',
            'Princip',
            'Rayvon',
            'Rod',
            'Ross',
            'Roxie',
            'Sadie',
            'Sally',
            'Samantha',
            'Samson',
            'Seavers',
            'Shaggy',
            'Sophia',
            'Tartanella',
            'Thatcher',
            'Thomas',
            'William',
        ];
    }

    private function isName($word) {
        return in_array($word, $this->names);
    }

    private function setBrands() {
        $this->names = [
            'ABC',
            'Adidas',
            'Amtracks',
            'Armalite',
            'Armani',
            'Bacardi',
            'CBS',
            'Coca',
            'Coke',
            'MTV',
            'NBC',
            'NRA',
        ];
    }

    private function isBrand($word) {
        return in_array($word, $this->brands);
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
                404, 712, 819, 908, 911, 1273, 1314, 1425, 1477, 2133, 2206, 2225, 2344, 2601, 3156, 3165, 3198, 3427, 3965, 3966, 3968, 4145, 4261, 4892, 5621, 5727, 5728, 5737, 6218, 6502, 8036, 8587, 9143, 9183, 9473, 9550, 9762,
            ])
            // this shouldn't be returning anyway
            // ->whereNotIn('songs.id', [3053])
            ->whereNotIn('artist_song.artist_id', [
                23, 84, 107, 197, 209, 211, 248, 280, 469, 607, 611, 763, 802, 821, 838, 841, 846, 1317, 1453,
            ])
            ->whereNotIn('album', [
                'Turkish Groove', 'African Women', 'Bocelli Greatest Hits', 'Buena Vista Social Club', 'Everything Is Possible!',
                "Edith Piaf - 20 'French' Hit Singles",
            ])
            ->whereNotIn('lyrics', ['unavailable', 'Instrumental', 'inapplicable']);

        if ($song_ids):
            $query->whereIn('songs.id', $song_ids);
        endif;
        $lyrics = $query->get()->toArray();

        foreach ($lyrics as $song):
            try {
                $lyric = str_replace([PHP_EOL], [' '], $song['lyrics']);
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
        if ($this->isCountry($tmp_word) || $this->isPlace($tmp_word) || $this->isMonth($tmp_word) || $this->isName($tmp_word) || $this->isBrand($tmp_word)):
            return $tmp_word;
        else:
            return strtolower($word);
        endif;
    }
}
