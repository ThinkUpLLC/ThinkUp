<?php
/**
 *
 * ThinkUp/webapp/plugins/foursquare/model/mock.FoursquareAPIAccessor.php
 *
 * Copyright (c) 2012-2013 Aaron Kalair
 *
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
 *
 * Mock Foursquare API Accessor
 *
 * Reads test data files instead of the actual foursquare servers for the purposes of running tests.
 *
 * @author Aaron Kalair <aaronkalair[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011 Aaron Kalair
 */
class FoursquareAPIAccessor {
    /**
     * @var str
     */
    var $api_domain = 'https://api.foursquare.com/v2/';
    /**
     * @var str
     */
    var $access_token_request_url = "https://foursquare.com/oauth2/access_token";
    /**
     * @var str - Where the data foursquare would return lives
     */
    var $data_location = 'webapp/plugins/foursquare/tests/testdata/';

    /**
     * Make a foursquare API request.
     * @param str $path
     * @param str $access_token
     * @return array Decoded JSON response
     */
    public function apiRequest($path, $access_token, $fields=null) {
        // Add the path to the part of the API they want to access and OAuth token to the URL
        $url = $this->api_domain.$path.'?oauth_token='.$access_token;

        // If there are additional parameters passed in add them to the URL also
        if ($fields != null){
            foreach ( $fields as $key=>$value) {
                $url = $url.'&'.$key.'='.$value;
            }
        }

        // Get the path to where our test data lives
        $FAUX_DATA_PATH = THINKUP_ROOT_PATH . $this->data_location;
        // Strip the API domain from the request so we don't have long test data file names
        $url = str_replace($this->api_domain, '', $url);
        // Replace all special characters in the URL with a -
        $url = str_replace('/', '_', $url);
        $url = str_replace('&', '-', $url);
        $url = str_replace('?', '-', $url);
        $url = str_replace(':', '-', $url);
        // Decode the JSON in the reply
        return self::decodeFileContents($FAUX_DATA_PATH.$url);
    }

    /**
     * Decode JSON in our test files
     *
     * @param str $file_path
     * @param bool $decode_json
     * @return mixed Decoded JSON or text file contents
     */
    private static function decodeFileContents($file_path, $decode_json=true) {
        // If we have set the debug flag in the terminal set the debug variable to true
        $debug = (getenv('TEST_DEBUG')!==false) ? true : false;
        // If we are in debug mode report that were reading a local test file
        if ($debug) {
            echo "READING LOCAL TEST DATA FILE: ".$file_path. '
';
        }
        // Get the contents of the files
        $contents=  file_get_contents($file_path);
        // If we want to decode the JSON
        if ($decode_json) {
            // Decode the JSON
            $decoded = json_decode($contents);
            // If the decode failed and were in debug mode tell the user
            if ($decoded == null && $debug) {
                echo "JSON was not decoded! Check if it is valid JSON at http://jsonlint.com/
";
            }
            // Return the decoded JSON
            return $decoded;
        } else {
            // Return the text file contents
            return $contents;
        }
    }

    /**
     * Make an API request with an absolute URL, the URL you pass in is litterally the request you want to make
     * with OAuth tokens etc
     *
     * Note: In this mock API accessor this method actually just converts the URL into the file string and then passes
     * it to rawAPIRequest to get the decoded JSON
     *
     * @param str $path - The URL of the API request you want to make
     * @param bool $decode_json Defaults to true, if true returns decoded JSON
     * @return array Decoded JSON response
     */
    public function rawPostApiRequest($path, $fields, $decode_json=true) {
        // Variable to add all the fields to
        $fields_string = '';
        // Add each field and its value to the fields string
        foreach ($fields as $key=>$value) {
            $fields_string .= $key.'='.$value.'&';
        }
        // Strip the API domain from the request so we don't have long test data file names
        $path = str_replace($this->access_token_request_url, '', $path);
        // Replace the &'s in the string with a -
        rtrim($fields_string,'&');
        $file_path = $path . $fields_string;
        $debug = (getenv('TEST_DEBUG')!==false) ? true : false;
        // If we are in debug mode report that were reading a local test file
        if ($debug) {
            echo "READING LOCAL TEST DATA FILE: ".$file_path. '
';
        }

        // Make the request
        return self::rawApiRequest($file_path, $decode_json);
    }

    /**
     * Internal use only - Decode the JSON the request would return
     *
     * @param str $path
     * @param book $decode_json If true, return decoded JSON
     * @return array Decoded JSON response
     */
    private function rawApiRequest($path, $decode_json=true) {
        // Set the URL to the path passed in
        $url = $path;
        // Set the datapath to the root + where our test data lives
        $FAUX_DATA_PATH = THINKUP_ROOT_PATH . $this->data_location;
        // Strip special characters and replace with a -
        $url = preg_replace('/([\?\&])access_token\=[^\?\&]+([\?\&])*/', "$1", $url);
        $url = preg_replace('/[\?\&]$/', '', $url);
        $url = str_replace($this->api_domain, '', $url);
        $url = str_replace('/', '_', $url);
        $url = str_replace('&', '-', $url);
        $url = str_replace('?', '-', $url);
        $url = str_replace(':', '-', $url);
        return self::decodeFileContents($FAUX_DATA_PATH.$url, $decode_json);
    }
    /**
     * For testing purposes only
     * @param str $folder
     */
    public function setDataLocation($folder) {
        $this->data_location = $this->data_location.$folder;
    }
}