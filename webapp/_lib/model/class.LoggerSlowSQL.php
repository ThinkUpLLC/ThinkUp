<?php
class LoggerSlowSQL {
    var $log;

    function __construct($location) {
        $this->log = $this->openFile($location, 'a'); # Append to any prior file
    }

    function setUsername($uname) {
        $this->twitter_username = $uname;
    }

    function logQuery($query, $time) {
        $log_signature = date("Y-m-d H:i:s", time())." | ".(string) number_format(round(memory_get_usage() / 1024000, 2), 2)." MB | ";
        if (strlen($query) > 0) {
            $this->writeFile($this->log, $log_signature.$query." | ".$time." Seconds"); # Write status to log
        }
    }

    private function addBreaks() {
        $this->writeFile($this->log, ""); # Add a little whitespace
    }

    function close() {
        $this->addBreaks();
        $this->closeFile($this->log);
    }

    function openFile($filename, $type) {
        if (array_search($type, array('w', 'a')) < 0) {
            $type = 'w';
        }
        $filehandle = fopen($filename, $type);// or die("can't open file $filename");
        return $filehandle;
    }

    function writeFile($filehandle, $message) {
        return fwrite($filehandle, $message."\n");
    }

    function closeFile($filehandle) {
        return fclose($filehandle);
    }

    function deleteFile($filename) {
        return unlink($filename);
    }


}

?>
