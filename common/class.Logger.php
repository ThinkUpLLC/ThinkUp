<?php

class Logger {
	var $log;
	var $twitter_username;
	
	function Logger($twitter_username) {
		//global $TWITALYTIC_CFG;
		//$this -> twitter_username = $TWITALYTIC_CFG['owner_username'];
		$this -> twitter_username=$twitter_username;
		/*
		 *   Initialize log
		 */
		$path_to_logs		= realpath('logs/')."/";		# Where will you keep the log
		$log_file			= 'crawler.log'; 				# Name the log file
		$this -> log = $this -> openFile($path_to_logs.$log_file,'a');		# Append to any prior file

	}
	
	
	function logStatus($status_message, $classname)  {
		$status_signature = date("Y-m-d H:i:s",time())." | ". (string) round(memory_get_usage()/1024000, 2) ." MB | $this->twitter_username | $classname:";
		if ( strlen($status_message) > 0)  {
			$this->writeFile($this -> log, $status_signature.$status_message);  # Write status to log
		}
	}
	
	private function addBreaks() {
		$this->writeFile($this -> log, "");  # Add a little whitespace
	}
	
	
	function close() {
		$this->addBreaks();
		$this->closeFile($this -> log); 
	}
	
	
	function openFile ($filename,$type) {
		if (array_search($type,array('w','a')) < 0) { $type = 'w'; }
		$filehandle = fopen($filename,$type) or die("can't open file $filename");
	    return $filehandle;
	}

	function writeFile ($filehandle,$message) {
		return fwrite($filehandle, $message . "\n");
	}

	function closeFile ($filehandle) {
		return fclose($filehandle);
	}

	function deleteFile ($filename) {
		return unlink($filename);
	}
	
	
}
	




?>