<?php 
class Config {

    private static $instance;
    
    var $debug;
    var $site_root_path;
    var $log_location;
    var $app_title;
    
    //TODO: Put plugin specific settings into the database
    var $network_username;
    var $network_user_id;
    var $bitly_api_key;
    var $bitly_login;
    var $oauth_consumer_key;
    var $oauth_consumer_secret;
    var $archive_limit;
    var $flickr_api_key;

    
    // The singleton method
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new Config();
        }
        return self::$instance;
    }
    
    public function getValue($key) {
        $value = isset($this->config[$key]) ? $this->config[$key] : null;
        return $value;
    }

    public function setValue($key, $value) {
        $value = $this->config[$key] = $value;
        return $value;
    }

    function Config($network_username = null, $network_user_id = null) {
        global $THINKTANK_CFG;
        $this->config = $THINKTANK_CFG;
        $this->site_root_path = $THINKTANK_CFG['site_root_path'];
        $this->debug = $THINKTANK_CFG['debug'];
        $this->log_location = $THINKTANK_CFG['log_location'];
        $this->app_title = $THINKTANK_CFG['app_title'];
        $this->site_root_path = $THINKTANK_CFG['site_root_path'];
        
        //TODO: Stop storing network uname/id here
        $this->network_username = $network_username;
        $this->network_user_id = $network_user_id;
        
        $this->bitly_api_key = $THINKTANK_CFG['bitly_api_key'];
        $this->bitly_login = $THINKTANK_CFG['bitly_login'];
        $this->oauth_consumer_key = $THINKTANK_CFG['oauth_consumer_key'];
        $this->oauth_consumer_secret = $THINKTANK_CFG['oauth_consumer_secret'];
        $this->archive_limit = $THINKTANK_CFG['archive_limit'];
        
        if (isset($THINKTANK_CFG['flickr_api_key'])) {
            $this->flickr_api_key = $THINKTANK_CFG['flickr_api_key'];
        } else {
            $this->flickr_api_key = '';
        }
        if (isset($_SERVER["SERVER_NAME"])) {
            $this->webapp_home = "http://".$_SERVER["SERVER_NAME"].$this->site_root_path;
        }
        //putenv($THINKTANK_CFG['time_zone']);
        if ($this->debug) {
            ini_set("display_errors", 1);
            ini_set("error_reporting", E_ALL);
        }
    }
}
?>
