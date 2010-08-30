<?php
/**
 * Location Object
 * @author Ekansh Preet Singh <ekanshpreet[at]gmail[dot]com>
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 */

class Location {
    var $id;
    /**
     * @var str
     */
    var $short_name;
    /**
     * @var str
     */
    var $full_name;
    /**
     * @var str
     */
    var $latlng;
    
    /**
     * Constructor
     * @param array $val Array of key/value pairs
     */
    public function __construct($val) {
        $this->id = $val["id"];
        $this->short_name = $val["short_name"];
        $this->full_name = $val["full_name"];
        $this->latlng = $val["latlng"];
    }
}