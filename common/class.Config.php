<?php 
class Config {
    var $debug;
    var $twitter_username;
    var $twitter_password;
    var $twitter_user_id;
    var $site_root_path;
    var $bitly_api_key;
    var $bitly_login;
    var $oauth_consumer_key;
    var $oauth_consumer_secret;
    var $archive_limit;
    var $log_location;
    var $app_title;
    var $flickr_api_key;
    
    function Config($twitter_username = null, $twitter_user_id = null) {
        global $THINKTANK_CFG;
        $this->site_root_path = $THINKTANK_CFG['site_root_path'];
        $this->debug = $THINKTANK_CFG['debug'];
        
        $this->twitter_username = $twitter_username;
        $this->twitter_user_id = $twitter_user_id;
        $this->site_root_path = $THINKTANK_CFG['site_root_path'];
        $this->bitly_api_key = $THINKTANK_CFG['bitly_api_key'];
        $this->bitly_login = $THINKTANK_CFG['bitly_login'];
        $this->oauth_consumer_key = $THINKTANK_CFG['oauth_consumer_key'];
        $this->oauth_consumer_secret = $THINKTANK_CFG['oauth_consumer_secret'];
        $this->archive_limit = $THINKTANK_CFG['archive_limit'];
        $this->log_location = $THINKTANK_CFG['log_location'];
        $this->app_title = $THINKTANK_CFG['app_title'];
        
        if (isset($THINKTANK_CFG['flickr_api_key']))
            $this->flickr_api_key = $THINKTANK_CFG['flickr_api_key'];
        else
            $this->flickr_api_key = '';
        if (isset($_SERVER["SERVER_NAME"]))
            $this->webapp_home = "http://".$_SERVER["SERVER_NAME"].$this->site_root_path;
            //putenv($THINKTANK_CFG['time_zone']);
        if ($this->debug) {
            ini_set("display_errors", 1);
            ini_set("error_reporting", E_ALL);
        }
    }
}

?>
