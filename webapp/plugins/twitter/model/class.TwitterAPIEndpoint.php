<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/model/class.TwitterAPIEndpoint.php
 *
 * Copyright (c) 2013 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkup.com).
 *
 * ThinkUp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any
 * later version.
 *
 * ThinkUp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with ThinkUp.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * Twitter API Endpoint
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class TwitterAPIEndpoint {
    /**
     * Domain of Twitter's API calls
     * @var str
     */
    const API_DOMAIN = 'https://api.twitter.com/1.1';
    /**
     * API Payload file format
     * @var str
     */
    const API_FORMAT = 'json';
    /**
     * Path of endpoint
     * @var str
     */
    private $path = null;
    /**
     * Endpoint call allocation remaining
     * @var int Null if not set
     */
    private $remaining = null;
    /**
     * Endpoint call allocation limit
     * @var int Null if not set
     */
    private $limit = null;
    /**
     * Time remaining will reset
     * @var int
     */
    private $reset = null;
    /**
     * Constructor
     * @param str $path Path to endpoint
     * @return TwitterAPIEndpoint
     */
    public function __construct($path) {
        $this->path = $path;
    }
    /**
     * Get full path with domain and file extension
     * @return str path
     */
    public function getPath() {
        if (isset($this->path)) {
            return self::API_DOMAIN. $this->path. "." .self::API_FORMAT;
        } else {
            return null;
        }
    }
    /**
     * Get short path (without domain and file extension)
     * @return str path
     */
    public function getShortPath() {
        return $this->path;
    }
    /**
     * Get path with replaced ID.
     * @param str ID that will replace [id] in API endpoint path
     * @return str path
     */
    public function getPathWithID($id) {
        return str_replace(":id", $id, self::getPath());
    }
    /**
     * Get remaining call balance
     * @return int remaining call balance
     */
    public function getRemaining() {
        return $this->remaining;
    }
    /**
     * Set remaining call balance
     * @param int $remaining
     * @return void
     */
    public function setRemaining($remaining) {
        $this->remaining = $remaining;
    }
    /**
     * Get call limit
     * @return int limit
     */
    public function getLimit() {
        return $this->limit;
    }
    /**
     * Set call limit
     * @param int $limit
     * @return void
     */
    public function setLimit($limit) {
        $this->limit = $limit;
    }
    /**
     * Get reset time
     * @return int Current reset time
     */
    public function getReset() {
        return $this->reset;
    }
    /**
     * Set reset time
     * @param int $reset
     * @return void
     */
    public function setReset($reset) {
        $this->reset = $reset;
    }
    /**
     * Decrement the current remaining
     * @return void
     */
    public function decrementRemaining() {
        if (isset($this->remaining)) {
            $this->remaining--;
        } else {
            throw new Exception("Twitter API endpoint $path remaining is not set; cannot decrement");
        }
    }
    /**
     * Get API call remaining information formatted for logging.
     * @return str
     */
    public function getStatus() {
        if (isset($this->remaining) && isset($this->reset) && isset($this->limit)) {
            return $this->remaining." calls out of ".$this->limit." to ".$this->path." available until ".
            date('H:i', (int) $this->reset).".";
        } else {
            return "API rate limit balance unknown for ".(isset($this->path)?$this->path:"uninitialized endpoint").".";
        }
    }
    /**
     * Whether or not there are available API calls for this endpoint.
     * @param int $percent_use_ceiling Maximum percentage of API calls to use
     * @return bool
     */
    public function isAvailable($percent_use_ceiling) {
        if (!isset($this->remaining) || !isset($this->limit)) {
            throw new Exception("Endpoint not initialized");
        }
        if ((($this->remaining * 100 )/$this->limit) < $percent_use_ceiling) {
            return false;
        } else {
            return true;
        }
    }
}