<?php
/**
 *
 * ThinkUp/webapp/_lib/class.Logger.php
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
 * Logger singleton
 *
 * Crawler logger outputs information about crawler to terminal or to file, depending on configuration.
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class Logger {
    /**
     *
     * @var array of Logger singleton instances
     */
    private static $loggers = array();
    /**
     *
     * @var resource Open file pointer
     */
    var $log = null;
    /**
     *
     * @var str $service_user The user we're logging about
     */
    var $service_user = null;
    /**
     *
     * @var int All (user and developer) messages
     */
    const ALL_MSGS = 0;
    /**
     *
     * @var int User-level messages
     */
    const USER_MSGS = 1;
    /**
     *
     * @var int Just error messages
     */
    const ERROR_MSGS = 2;
    /**
     *
     * @var int Information-type messages
     */
    const INFO = 0;
    /**
     *
     * @var int Error-type messages
     */
    const ERROR = 1;
    /**
     *
     * @var int Success-type messages
     */
    const SUCCESS = 2;
    /**
     * @var int debugging messages -- log only
     */
    const DEBUG = 3;
    /**
     *
     * @var int Log verbosity level (self::ALL_MSGS, self::USER_MSGS or self::ERROR_MSGS)
     */
    var $verbosity = 0;
    /**
     * @var bool whether to output debug-level log stmts
     */
    var $debug = false;
    /**
     * @var string the name of the location, e.g. as in the config file.
     */
    var $loc_name = null;
    /**
     * @var bool Whether or not output should be HTML
     */
    var $html_output = false;
    /**
     * Open the log file; Append to any prior file
     * @param str $location
     * @param boolean $debug default false
     * @param int $verbosity default 0; should be value of Logger::ALL_MSGS, Logger::USER_MSGS or Logger::ERROR_MSGS
     */
    private function __construct($location, $loc_name, $debug = false, $verbosity = 0) {
        if ( $location != false ) {
            $this->log = $this->openFile($location, 'a');
        }
        $this->debug = $debug;
        $this->loc_name = $loc_name;
        $this->verbosity = (int)$verbosity;
    }

    /**
     * The singleton constructor
     */
    public static function getInstance($log_location = null) {
        if (!$log_location) {
            $log_location = 'log_location'; // the default log location
        }

        if (!isset(self::$loggers[$log_location])) {

            $config = Config::getInstance();
            $debug = $config->getValue('debug') ? true : false;

            // check config for log_level
            $verbosity = $config->getValue('log_verbosity');
            if (!$verbosity && $verbosity !== 0) {
                $verbosity = Logger::ALL_MSGS; // default to everything if config was not set
            }
            $logfile = $config->getValue($log_location);
            $log = new Logger($logfile, $log_location, $debug, $verbosity);
            self::$loggers[$log_location] = $log;
        }
        return self::$loggers[$log_location];
    }

    /**
     * Set username
     * @param str $username
     */
    public function setUsername($username) {
        $this->service_user = $username;
    }

    /**
     * Set the verbosity level of the log.
     * @param int $level Either self::ALL_MSGS or self::USER_MSGS
     */
    public function setVerbosity($level) {
        $this->verbosity = $level;
    }

    /**
     * Turn on HTML output.
     */
    public function enableHTMLOutput() {
        $this->html_output = true;
    }
    /**
     * Write to log
     * @param str $status_message
     * @param str $classname The name of the class logging the info
     */
    private function logStatus($status_message, $classname, $verbosity = self::ALL_MSGS, $type = self::INFO) {
        if ($this->verbosity <= $verbosity) {
            if (!$this->html_output) {
                $status_signature = date("Y-m-d H:i:s", time())." | ".
                (string) number_format(round(memory_get_usage() / 1024000, 2), 1)."MB | ";
                switch ($type) {
                    case self::ERROR:
                        $status_signature .= 'ERROR  | ';
                        break;
                    case self::SUCCESS:
                        $status_signature .= 'SUCCESS| ';
                        break;
                    case self::DEBUG:
                        $status_signature .= 'DEBUG  | ';
                        break;
                    default:
                        $status_signature .= 'INFO   | ';
                }
                if (isset($this->service_user)) {
                    $status_signature .= $this->service_user .' | ';
                }
                $status_signature .= $classname." | ";
                if (strlen($status_message) > 0) {
                    $this->output($status_signature.$status_message); # Write status to log
                }
            } else {
                $message_wrapper = '<tr><td><small>'.date("H:i", time()).'</small></td> <td class="crawl-log-component">';
                $just_classname = explode('::', $classname);
                if (isset($just_classname[0])) {
                    if ( $just_classname[0] == 'CrawlerTwitterAPIAccessorOAuth') {
                        $just_classname[0] = 'TwitterCrawler';
                    }
                    if ( strtoupper(substr ( $just_classname[0] , strlen($just_classname[0])-3, 3  ))  == 'DAO') {
                        $just_classname[0] = 'Database';
                    }
                    $message_wrapper .= $just_classname[0].": ";
                }
                $message_wrapper .= '</td> <td class="';
                switch ($type) {
                    case self::ERROR:
                        $message_wrapper .= 'form-group error">';
                        break;
                    case self::SUCCESS:
                        $message_wrapper .= 'form-group success">';
                        break;
                    default:
                        $message_wrapper .= 'form-group warning">';
                }
                if (strlen($status_message) > 0) {
                    $this->output($message_wrapper.$status_message.'</td></tr>'); // Write status to log
                }
            }
        }
    }

    /**
     * Write info message to log.
     * @param str $status_message
     * @param str $classname
     */
    public function logInfo($status_message, $classname) {
        $this->logStatus($status_message, $classname, self::ALL_MSGS, self::INFO);
    }

    /**
     * Write debug message to log if 'debug' config var is set to 'true'.
     * @param str $status_message
     * @param str $classname
     */
    public function logDebug($status_message, $classname) {
        if ($this->debug) {
            $this->logStatus($status_message, $classname, self::ALL_MSGS, self::DEBUG);
        }
    }

    /**
     * Write error message to log.
     * @param str $status_message
     * @param str $classname
     */
    public function logError($status_message, $classname) {
        $this->logStatus($status_message, $classname, self::ERROR_MSGS, self::ERROR);
    }

    /**
     * Write success message to log.
     * @param str $status_message
     * @param str $classname
     */
    public function logSuccess($status_message, $classname) {
        $this->logStatus($status_message, $classname, self::ALL_MSGS, self::SUCCESS);
    }


    /**
     * Write user-level info message to log.
     * @param str $status_message
     * @param str $classname
     */
    public function logUserInfo($status_message, $classname) {
        $this->logStatus($status_message, $classname, self::USER_MSGS, self::INFO);
    }

    /**
     * Write user-level error message to log.
     * @param str $status_message
     * @param str $classname
     */
    public function logUserError($status_message, $classname) {
        $this->logStatus($status_message, $classname, self::ERROR_MSGS, self::ERROR);
    }

    /**
     * Write user-level success message to log.
     * @param str $status_message
     * @param str $classname
     */
    public function logUserSuccess($status_message, $classname) {
        $this->logStatus($status_message, $classname, self::USER_MSGS, self::SUCCESS);
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
        unset(self::$loggers[$this->loc_name]);
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
        if (is_writable($filename) || is_writable(dirname($filename))) {
            $filehandle = fopen($filename, $type);// or die("can't open file $filename");
        } else {
            error_log("Unable to write log file: " . $filename);
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
            @flush();
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
