<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/tests/classes/mock.TwitterOAuth.php
 *
 * Copyright (c) 2009-2013 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkup.com).
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
 *
 * Mock Twitter OAuth class for tests
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class TwitterOAuth {

    var $data_path = 'webapp/plugins/twitter/tests/data/';

    private $last_status_code = 200;

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
        $url = str_replace('https://api.twitter.com/1.1/', '', $url);
        $url = str_replace('http://search.twitter.com/', '', $url);
        $url = str_replace('/', '_', $url);
        $url = str_replace('&', '-', $url);
        $url = str_replace('?', '-', $url);
        $debug = (getenv('TEST_DEBUG')!==false) ? true : false;
        if ($debug) {
            echo "READING LOCAL DATA FILE: ".$FAUX_DATA_PATH.$url. "\n";
        }
        if (!file_exists($FAUX_DATA_PATH.$url)) {
            if (is_numeric($url) && strlen($url) == 3) {
                // if the URL is a 3 character, numeric string, set the last error code to it. For testing errors.
                $this->last_status_code = (int)$url;
            } else {
                $this->last_status_code = 404;
            }
            if ($debug) {
                echo "FILE NOT FOUND\n";
            }
            return '{"errors":[{"message":"Sorry, that page does not exist","code":34}]}';
        } else {
            $data = file_get_contents($FAUX_DATA_PATH.$url);
            $this->last_status_code = 200;
            return $data;
        }
    }

    /**
     * Set custom location of test data files.
     * @param str $data_path
     * @return void
     */
    public function setDataPath($data_path) {
        $this->data_path = $data_path;
        // print "data path is: " . $this->data_path . "\n";
    }

    /**
     * Set subfolder location of test data files
     * @param str $data_path_folder
     * @return void
     */
    public function setDataPathFolder($data_path_folder) {
        $this->data_path = $this->data_path.$data_path_folder;
        // print "data path is: " . $this->data_path . "\n";
    }

    public function http($url) {
        $FAUX_DATA_PATH = THINKUP_WEBAPP_PATH.'plugins/twitter/tests/data/';
        $url = str_replace('https://twitter.com/', '', $url);
        $url = str_replace('https://api.twitter.com/1.1/', '', $url);
        $url = str_replace('http://search.twitter.com/', '', $url);
        $url = str_replace('/', '_', $url);
        $url = str_replace('&', '-', $url);
        $url = str_replace('?', '-', $url);
        $debug = (getenv('TEST_DEBUG')!==false) ? true : false;
        if ($debug) {
            echo "READING LOCAL DATA FILE: ".$FAUX_DATA_PATH.$url . "\n";
        }
        return file_get_contents($FAUX_DATA_PATH.$url);
    }

    public function lastStatusCode() {
        return $this->last_status_code;
    }

    public function getRequestToken($oauth_callback = NULL) {
        if (!empty($oauth_callback)) {
            return array('oauth_token'=>urlencode($oauth_callback), 'oauth_token_secret'=>'dummytoken');
        } else {
            return array('oauth_token'=>'dummytoken', 'oauth_token_secret'=>'dummytoken');
        }
    }

    public function getAuthorizeURL($token) {
        return "test_auth_URL_".$token;
    }

    public function getAccessToken(){
        return array('oauth_token'=>'fake oauth token', 'oauth_token_secret'=>'fake oauth token secret');
    }
}
