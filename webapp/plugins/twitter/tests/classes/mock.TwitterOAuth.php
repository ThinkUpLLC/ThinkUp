<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/tests/classes/mock.TwitterOAuth.php
 *
 * Copyright (c) 2009-2010 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
 *
 * ThinkUp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any
 * later version.
 *
 * ThinkUp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with ThinkUp.  If not, see
 * <http://www.gnu.org/licenses/>.
 */
/**
 * Mock Twitter OAuth class for tests
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class TwitterOAuth {
  
    var $data_path = 'webapp/plugins/twitter/tests/testdata/';
    
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
        $url = Utils::getURLWithParams($url, $args);

        $FAUX_DATA_PATH = THINKUP_ROOT_PATH . $this->data_path;
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
    
    public function setDataPath($data_path) {
      $this->data_path = $data_path;
      // print "data path is: " . $this->data_path . "\n";
    }

    public function http($url) {
        $FAUX_DATA_PATH = THINKUP_ROOT_PATH . 'webapp/plugins/twitter/tests/testdata/';
        $url = str_replace('https://twitter.com/', '', $url);
        $url = str_replace('https://api.twitter.com/1/', '', $url);
        $url = str_replace('http://search.twitter.com/', '', $url);
        $url = str_replace('/', '_', $url);
        $url = str_replace('&', '-', $url);
        $url = str_replace('?', '-', $url);
        // echo "READING LOCAL DATA FILE: ".$FAUX_DATA_PATH.$url . "\n";
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

    public function getAccessToken(){
        return 'fake access token';
    }
}
