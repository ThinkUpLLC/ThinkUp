<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.Logger.php
 *
 * Copyright (c) 2009-2010 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
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
 *
 * Logger singleton
 *
 * Crawler logger outputs information about crawler to terminal or to file, depending on configuration.
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class Logger {
    /**
     *
     * @var Logger singleton instance
     */
    private static $instance;
    /**
     *
     * @var resource Open file pointer
     */
    var $log = null;
    /**
     *
     * @var str $network_username The user we're logging about
     */
    var $network_username = null;

    /**
     * Open the log file; Append to any prior file
     * @param str $location
     */
    public function __construct($location) {
        if ( $location != false ) {
            $this->log = $this->openFile($location, 'a');
        }
    }

    /**
     * The singleton constructor
     */
    public static function getInstance() {
        $config = Config::getInstance();
        if (!isset(self::$instance)) {
            self::$instance = new Logger($config->getValue('log_location'));
        }
        return self::$instance;
    }

    /**
     * Set username
     * @param str $username
     */
    public function setUsername($username) {
        $this->network_username = $username;
    }

    /**
     * Write to log
     * @param str $status_message
     * @param str $classname The name of the class logging the info
     */
    public function logStatus($status_message, $classname) {
        $status_signature = date("Y-m-d H:i:s", time())." | ".
        (string) number_format(round(memory_get_usage() / 1024000, 2), 2)." MB | ";
        if (isset($this->network_username)) {
            $status_signature .= $this->network_username .' | ';
        }
        $status_signature .= $classname.":";
        if (strlen($status_message) > 0) {
            $this->output($status_signature.$status_message); # Write status to log
        }
    }

    /**
     * Add a little whitespace to log file
     */
    private function addBreaks() {
        $this->output("");
    }

    /**
     * Close the log file
     */
    public function close() {
        $this->addBreaks();
        $this->closeFile($this->log);
        self::$instance = null;
    }

    /**
     * Open log file
     * @param str $filename
     * @param unknown_type $type
     */
    protected function openFile($filename, $type) {
        if (array_search($type, array('w', 'a')) < 0) {
            $type = 'w';
        }
        $filehandle = null;
        if (is_writable($filename)) {
            $filehandle = fopen($filename, $type);// or die("can't open file $filename");
        }
        return $filehandle;
    }

    /**
     * Output log message to file or terminal
     * @param str $message
     */
    protected function output($message) {
        if (isset($this->log)) {
            return fwrite($this->log, $message."\n");
        } else {
            echo $message.'
';
        }
    }

    /**
     * Close file
     * @param resource $filehandle
     */
    protected function closeFile($filehandle) {
        if (isset($filehandle)) {
            return fclose($filehandle);
        }
    }

    /**
     * Delete log file
     * @param str $filename
     */
    protected function deleteFile($filename) {
        return unlink($filename);
    }
}