<?php
/**
 * Mock Twitter OAuth class for tests
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class TwitterOAuth {
    /**
     * Constructor
     * @param str $consumer_key
     * @param str $consumer_secret
     * @param str $oauth_token
     * @param str $oauth_token_secret
     * @return TwitterOAuth
     */
    public function __construct($consumer_key, $consumer_secret, $oauth_token = NULL, $oauth_token_secret = NULL) {
    }

    public function oAuthRequest($url, $method = NULL, $args = array()) {
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

    public function http($url) {
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

    public function lastStatusCode() {
        return 200;
    }

    public function getRequestToken() {
        return array('oauth_token'=>'dummytoken', 'oauth_token_secret'=>'dummytoken');
    }

    public function getAuthorizeURL($token) {
        return "test_auth_URL_".$token;
    }
}
