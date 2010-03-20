<?php 
require_once ('OAuth.php');

/**
 * Twitter OAuth class mock
 */
class TwitterOAuth {/*{{{*/
    function __construct($consumer_key, $consumer_secret, $oauth_token = NULL, $oauth_token_secret = NULL) {/*{{{*/
    }

    
    function oAuthRequest($url, $args = array(), $method = NULL) {/*{{{*/
        global $FAUX_DATA_PATH;
        $url = str_replace('https://twitter.com/', '', $url);
        $url = str_replace('/', '_', $url);
        $url = str_replace('&', '-', $url);
        return file_get_contents($FAUX_DATA_PATH.$url);
    }
	
    function lastStatusCode() {
        return 200;
    }
}
?>
