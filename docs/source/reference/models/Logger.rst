Logger
======

ThinkUp/webapp/_lib/model/class.Logger.php

Copyright (c) 2009-2011 Gina Trapani

Logger singleton

Crawler logger outputs information about crawler to terminal or to file, depending on configuration.


Properties
----------

instance
~~~~~~~~



log
~~~



network_username
~~~~~~~~~~~~~~~~



verbosity
~~~~~~~~~



debug
~~~~~



html_output
~~~~~~~~~~~





Methods
-------

__construct
~~~~~~~~~~~
* **@param** str $location
* **@param** boolean $debug default false
* **@param** int $verbosity default 0; should be value of Logger::ALL_MSGS, Logger::USER_MSGS or Logger::ERROR_MSGS


Open the log file; Append to any prior file

.. code-block:: php5

    <?php
        private function __construct($location, $debug = false, $verbosity = 0) {
            if ( $location != false ) {
                $this->log = $this->openFile($location, 'a');
            }
            $this->debug = $debug;
            $this->verbosity = (int)$verbosity;
        }


getInstance
~~~~~~~~~~~

The singleton constructor

.. code-block:: php5

    <?php
        public static function getInstance() {
            if (!isset(self::$instance)) {
                $config = Config::getInstance();
                $debug = $config->getValue('debug') ? true : false;
    
                // check config for log_level
                $verbosity = $config->getValue('log_verbosity');
                if (!$verbosity && $verbosity !== 0) {
                    $verbosity = Logger::ALL_MSGS; // default to everything if config was not set
                }
    
                self::$instance = new Logger($config->getValue('log_location'), $debug, $verbosity);
    
            }
            return self::$instance;
        }


setUsername
~~~~~~~~~~~
* **@param** str $username


Set username

.. code-block:: php5

    <?php
        public function setUsername($username) {
            $this->network_username = $username;
        }


setVerbosity
~~~~~~~~~~~~
* **@param** int $level Either self::ALL_MSGS or self::USER_MSGS


Set the verbosity level of the log.

.. code-block:: php5

    <?php
        public function setVerbosity($level) {
            $this->verbosity = $level;
        }


enableHTMLOutput
~~~~~~~~~~~~~~~~

Turn on HTML output.

.. code-block:: php5

    <?php
        public function enableHTMLOutput() {
            $this->html_output = true;
        }


logStatus
~~~~~~~~~
* **@param** str $status_message
* **@param** str $classname The name of the class logging the info


Write to log

.. code-block:: php5

    <?php
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
                    if (isset($this->network_username)) {
                        $status_signature .= $this->network_username .' | ';
                    }
                    $status_signature .= $classname." | ";
                    if (strlen($status_message) > 0) {
                        $this->output($status_signature.$status_message); # Write status to log
                    }
                } else {
                    $message_wrapper = '<span style="color:#ccc">'.date("H:i", time()).'</span> ';
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
                    $message_wrapper .= '<span style="color:';
                    switch ($type) {
                        case self::ERROR:
                            $message_wrapper .= 'red">';
                            break;
                        case self::SUCCESS:
                            $message_wrapper .= 'green">';
                            break;
                        default:
                            $message_wrapper .= 'black">';
                    }
                    if (strlen($status_message) > 0) {
                        $this->output($message_wrapper.$status_message."</span><br >"); // Write status to log
                    }
                }
            }
        }


logInfo
~~~~~~~
* **@param** str $status_message
* **@param** str $classname


Write info message to log.

.. code-block:: php5

    <?php
        public function logInfo($status_message, $classname) {
            $this->logStatus($status_message, $classname, self::ALL_MSGS, self::INFO);
        }


logDebug
~~~~~~~~
* **@param** str $status_message
* **@param** str $classname


Write debug message to log if 'debug' config var is set to 'true'.

.. code-block:: php5

    <?php
        public function logDebug($status_message, $classname) {
            if ($this->debug) {
                $this->logStatus($status_message, $classname, self::ALL_MSGS, self::DEBUG);
            }
        }


logError
~~~~~~~~
* **@param** str $status_message
* **@param** str $classname


Write error message to log.

.. code-block:: php5

    <?php
        public function logError($status_message, $classname) {
            $this->logStatus($status_message, $classname, self::ERROR_MSGS, self::ERROR);
        }


logSuccess
~~~~~~~~~~
* **@param** str $status_message
* **@param** str $classname


Write success message to log.

.. code-block:: php5

    <?php
        public function logSuccess($status_message, $classname) {
            $this->logStatus($status_message, $classname, self::ALL_MSGS, self::SUCCESS);
        }


logUserInfo
~~~~~~~~~~~
* **@param** str $status_message
* **@param** str $classname


Write user-level info message to log.

.. code-block:: php5

    <?php
        public function logUserInfo($status_message, $classname) {
            $this->logStatus($status_message, $classname, self::USER_MSGS, self::INFO);
        }


logUserError
~~~~~~~~~~~~
* **@param** str $status_message
* **@param** str $classname


Write user-level error message to log.

.. code-block:: php5

    <?php
        public function logUserError($status_message, $classname) {
            $this->logStatus($status_message, $classname, self::ERROR_MSGS, self::ERROR);
        }


logUserSuccess
~~~~~~~~~~~~~~
* **@param** str $status_message
* **@param** str $classname


Write user-level success message to log.

.. code-block:: php5

    <?php
        public function logUserSuccess($status_message, $classname) {
            $this->logStatus($status_message, $classname, self::USER_MSGS, self::SUCCESS);
        }


addBreaks
~~~~~~~~~

Add a little whitespace to log file

.. code-block:: php5

    <?php
        private function addBreaks() {
            $this->output("");
        }


close
~~~~~

Close the log file

.. code-block:: php5

    <?php
        public function close() {
            $this->addBreaks();
            $this->closeFile($this->log);
            self::$instance = null;
        }


openFile
~~~~~~~~
* **@param** str $filename
* **@param** unknown_type $type


Open log file

.. code-block:: php5

    <?php
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


output
~~~~~~
* **@param** str $message


Output log message to file or terminal

.. code-block:: php5

    <?php
        protected function output($message) {
            if (isset($this->log)) {
                return fwrite($this->log, $message."\n");
            } else {
                echo $message.'
    ';
                @flush();
            }
        }


closeFile
~~~~~~~~~
* **@param** resource $filehandle


Close file

.. code-block:: php5

    <?php
        protected function closeFile($filehandle) {
            if (isset($filehandle)) {
                return fclose($filehandle);
            }
        }


deleteFile
~~~~~~~~~~
* **@param** str $filename


Delete log file

.. code-block:: php5

    <?php
        protected function deleteFile($filename) {
            return unlink($filename);
        }




