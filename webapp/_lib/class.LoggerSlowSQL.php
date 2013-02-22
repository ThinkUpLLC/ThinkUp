<?php
/**
 *
 * ThinkUp/webapp/_lib/class.LoggerSlowSQL.php
 *
 * Copyright (c) 2009-2013 Gina Trapani
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
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 */
class LoggerSlowSQL {
    var $log;

    public function __construct($location) {
        $this->log = $this->openFile($location, 'a'); # Append to any prior file
    }

    public function setUsername($uname) {
        $this->twitter_username = $uname;
    }

    public function logQuery($query, $time) {
        $log_signature = date("Y-m-d H:i:s", time())." | ".(string) number_format(round(memory_get_usage() / 1024000,
        2), 2)." MB | ";
        if (strlen($query) > 0) {
            $this->writeFile($this->log, $log_signature.$query." | ".$time." Seconds"); # Write status to log
        }
    }

    private function addBreaks() {
        $this->writeFile($this->log, ""); # Add a little whitespace
    }

    public function close() {
        $this->addBreaks();
        $this->closeFile($this->log);
    }

    public function openFile($filename, $type) {
        if (array_search($type, array('w', 'a')) < 0) {
            $type = 'w';
        }
        $filehandle = fopen($filename, $type);// or die("can't open file $filename");
        return $filehandle;
    }

    public function writeFile($filehandle, $message) {
        return fwrite($filehandle, $message."\n");
    }

    public function closeFile($filehandle) {
        return fclose($filehandle);
    }

    public function deleteFile($filename) {
        return unlink($filename);
    }
}