<?php 

namespace MySounds\Music\AudioFile;

interface AudioFileInterface {

    /**
     * Require the title method is implemented.
     *
     * @return string
     */
    public function title();

    /**
     * Require the year method is implemented.
     *
     * @return integer
     */
    public function year();

    /**
     * Require the file_type method is implemented.
     *
     * @return string
     */
    public function file_type();

    /**
     * Require the track_no method is implemented.
     *
     * @return string
     */
    public function track_no();

    /**
     * Require the genre method is implemented.
     *
     * @return integer
     */
    public function genre();

    /**
     * Require the file_size method is implemented.
     *
     * @return integer
     */
    public function file_size();

    /**
     * Require the composer method is implemented.
     *
     * @return string
     */
    public function composer();
        
    /**
     * Require the playtime method is implemented.
     *
     * @return string
     */
    public function playtime();

    /**
     * Require the location method is implemented.
     *
     * @return string
     */
    public function location();

     /**
     * Require the notes method is implemented.
     *
     * @return string
     */
    public function notes();

}