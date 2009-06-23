<?php

class Config {
	var $debug;
	var $owner_username;
	var $owner_password;
	var $owner_user_id;
	var $site_root_path;
	var $bitly_api_key;
	var $bitly_login;
		
	function Config($twitter_username, $twitter_user_id) {
		global $TWITALYTIC_CFG;
		$this->site_root_path=$TWITALYTIC_CFG['site_root_path'];
		$this->debug=$TWITALYTIC_CFG['debug'];
		
		//$this->owner_username=$TWITALYTIC_CFG['owner_username'];
		$this->owner_username=$twitter_username;
		//$this->owner_user_id=$TWITALYTIC_CFG['owner_user_id'];
		$this->owner_user_id=$twitter_user_id;
		$this->site_root_path=$TWITALYTIC_CFG['site_root_path'];
		$this->bitly_api_key=$TWITALYTIC_CFG['bitly_api_key'];
		$this->bitly_login=$TWITALYTIC_CFG['bitly_login'];
		

		if (isset($_SERVER["SERVER_NAME"])) {
			$this->webapp_home = "http://".$_SERVER["SERVER_NAME"].$this->site_root_path;
		}

		//putenv($TWITALYTIC_CFG['time_zone']);

		if ($this->debug) {
			ini_set("display_errors", 1);
			ini_set("error_reporting", E_ALL);
		}

	}

}

?>