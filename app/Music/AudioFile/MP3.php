<?php 

namespace App\Music\AudioFile;

class MP3 implements AudioFileInterface {

    const FILE_TYPE = 'mp3';

    /**
     * @var string
     */
    private $location;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var bool
     */
    private $is_compilation;

    /**
     * @var array
     */
    private $file_info;

	/**
     * @param string $location
     * @param string $filename
     * @param bool $is_compilation
     * @param array $file_info
     */
    function __construct(String $location, String $filename, bool $is_compilation, array $file_info)
    {
        $this->location = $location;
        $this->filename = $filename;
        $this->is_compilation = $is_compilation;
        $this->file_info = $file_info;
    }

    /**
     * Return song title.
     *
     * @return string
     */
    public function title() {
        return $this->file_info["tags"]["id3v2"]["title"][0] ?? '';
    }

    /**
     * Return year.
     *
     * @return integer
     */
    public function year() {
        return $this->file_info["tags"]["id3v2"]["year"][0] ?? 9999;
    }

    /**
     * Return file_type.
     *
     * @return string
     */
    public function file_type() {
        return self::FILE_TYPE;
    }

    /**
     * Require the track_no method is implemented.
     *
     * @return string
     */
    public function track_no() {
        return $this->file_info["tags"]["id3v2"]["track_number"][0] ?? '';
    }

    /**
     * Return genre.
     *
     * @return string
     */
    public function genre() {
        return $this->file_info["tags"]["id3v2"]["genre"][0] ?? '';
    }

    /**
     * Return file_size.
     *
     * @return integer
     */
    public function file_size() {
        return $this->file_info["filesize"] ?? 0;
    }

    /**
     * Return composer.
     *
     * @return string
     */
    public function composer() {
        return '';
    }
        
    /**
     * Return the playtime.
     *
     * @return string
     */
    public function playtime() {
        return $this->file_info["playtime_string"] ?? '';  
    }

    /**
     * Return the file location.
     *
     * @return string
     */
    public function location() {
        return $this->location;
    }

    /**
     * Return notes.
     *
     * @return string
     */
    public function notes() {
        $notes = '';
        if ($this->is_compilation) {
           $notes = $this->file_info["tags"]["id3v2"]["artist"][0];
        }
        return $notes;
    }

}