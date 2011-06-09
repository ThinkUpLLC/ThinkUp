LoggerSlowSQL
=============

ThinkUp/webapp/_lib/model/class.LoggerSlowSQL.php

Copyright (c) 2009-2011 Gina Trapani




Properties
----------

log
~~~





Methods
-------

__construct
~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function __construct($location) {
            $this->log = $this->openFile($location, 'a'); # Append to any prior file
        }


setUsername
~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function setUsername($uname) {
            $this->twitter_username = $uname;
        }


logQuery
~~~~~~~~



.. code-block:: php5

    <?php
        public function logQuery($query, $time) {
            $log_signature = date("Y-m-d H:i:s", time())." | ".(string) number_format(round(memory_get_usage() / 1024000,
            2), 2)." MB | ";
            if (strlen($query) > 0) {
                $this->writeFile($this->log, $log_signature.$query." | ".$time." Seconds"); # Write status to log
            }
        }


addBreaks
~~~~~~~~~



.. code-block:: php5

    <?php
        private function addBreaks() {
            $this->writeFile($this->log, ""); # Add a little whitespace
        }


close
~~~~~



.. code-block:: php5

    <?php
        public function close() {
            $this->addBreaks();
            $this->closeFile($this->log);
        }


openFile
~~~~~~~~



.. code-block:: php5

    <?php
        public function openFile($filename, $type) {
            if (array_search($type, array('w', 'a')) < 0) {
                $type = 'w';
            }
            $filehandle = fopen($filename, $type);// or die("can't open file $filename");
            return $filehandle;
        }


writeFile
~~~~~~~~~



.. code-block:: php5

    <?php
        public function writeFile($filehandle, $message) {
            return fwrite($filehandle, $message."\n");
        }


closeFile
~~~~~~~~~



.. code-block:: php5

    <?php
        public function closeFile($filehandle) {
            return fclose($filehandle);
        }


deleteFile
~~~~~~~~~~



.. code-block:: php5

    <?php
        public function deleteFile($filename) {
            return unlink($filename);
        }




