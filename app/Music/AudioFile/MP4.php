<?php 

namespace App\Music\AudioFile;

class MP4 implements AudioFileInterface {

    const FILE_TYPE = 'mp4';

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
        $title = $this->file_info["quicktime"]["comments"]["title"][0] ?? '';
        return replaceSpecialFileSystemChars($title);
    }

    /**
     * Return song artist.
     *
     * @return string
     */
    public function artist() {
        $artist = $this->file_info["quicktime"]["comments"]["artist"][0] ?? '';
        return replaceSpecialFileSystemChars($artist);
    }

    /**
     * Return year.
     *
     * @return integer
     */
    public function year() {
        //1984-01-23T08:00:00Z
        $date_str = $this->file_info["quicktime"]["comments"]["creation_date"][0] ?? '';
        if(empty($date_str)):
            $year = 9999;
        else:
            $date_time = new DateTime($date_str);
            $year = $date_time->format('Y');
        endif;
        return $year;
    }

    /**
     * Return file_type.
     *
     * @return string
     */
    public function fileType() {
        return self::FILE_TYPE;
    }

    /**
     * Return song album.
     *
     * @return string
     */
    public function album() {
        $album = $this->file_info["quicktime"]["comments"]["album"][0] ?? 'Unknown Album';
        if (! empty($album)) {
            $album = replaceSpecialFileSystemChars($album);
        } else {
            $album = 'Unknown Album';
        }
        return $album;
    }

    /**
     * Return track_no.
     *
     * @return string
     */
    public function trackNo() {
        return $this->file_info["quicktime"]["comments"]["track_number"][0] ?? '';
    }

    /**
     * Return genre.
     *
     * @return string
     */
    public function genre() {
        return $this->file_info["quicktime"]["comments"]["genre"][0] ?? '';
    }

    /**
     * Return file_size.
     *
     * @return integer
     */
    public function fileSize() {
        return $this->file_info["filesize"] ?? 0;
    }

    /**
     * Return composer.
     *
     * @return string
     */
    public function composer() {
        return $this->file_info["quicktime"]["comments"]["composer"][0] ?? '';  
    }
        
    /**
     * Require the playtime.
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
        return '';
    }

    /**
     * Return whether song is part of a compilation.
     *
     * @return bool
     */
    public function isCompilation() {
        return $this->is_compilation;
    }

}
