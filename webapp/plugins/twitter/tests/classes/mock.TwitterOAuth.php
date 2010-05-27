<?php
/**
 * Twitter OAuth class mock
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class TwitterOAuth {
    function __construct($consumer_key, $consumer_secret, $oauth_token = NULL, $oauth_token_secret = NULL) {
    }

    function oAuthRequest($url, $method = NULL, $args = array()) {
        global $SOURCE_ROOT_PATH;
        $url = Utils::getURLWithParams($url, $args);
        $FAUX_DATA_PATH = $SOURCE_ROOT_PATH . 'webapp/plugins/twitter/tests/testdata/';
        $url = str_replace('https://twitter.com/', '', $url);
        $url = str_replace('https://api.twitter.com/1/', '', $url);
        $url = str_replace('http://search.twitter.com/', '', $url);
        $url = str_replace('/', '_', $url);
        $url = str_replace('&', '-', $url);
        $url = str_replace('?', '-', $url);
        //        echo "READING LOCAL DATA FILE: ".$FAUX_DATA_PATH.$url. '
        //        ';
        return file_get_contents($FAUX_DATA_PATH.$url);
    }

    function http($url) {
        global $SOURCE_ROOT_PATH;
        $FAUX_DATA_PATH = $SOURCE_ROOT_PATH . 'webapp/plugins/twitter/tests/testdata/';
        $url = str_replace('https://twitter.com/', '', $url);
        $url = str_replace('https://api.twitter.com/1/', '', $url);
        $url = str_replace('http://search.twitter.com/', '', $url);
        $url = str_replace('/', '_', $url);
        $url = str_replace('&', '-', $url);
        $url = str_replace('?', '-', $url);
        //echo "READING LOCAL DATA FILE: ".$FAUX_DATA_PATH.$url;
        return file_get_contents($FAUX_DATA_PATH.$url);
    }

    function lastStatusCode() {
        return 200;
    }
}
?>
