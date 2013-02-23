<?php
/**
 *
 * ThinkUp/webapp/plugins/googleplus/model/mock.GooglePlusAPIAccessor.php
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
 * Mock Google+ API Accessor
 *
 * Reads test data files instead of the actual Google servers for the purposes of running tests.
 *
 * Copyright (c) 2011-2013 Gina Trapani
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 Gina Trapani
 */
class GooglePlusAPIAccessor {
    /**
     * @var str
     */
    var $api_domain = 'https://www.googleapis.com/plus/v1/';
    /**
     * @var str
     */
    var $access_token_request_url = "https://accounts.google.com/o/oauth2/token";
    /**
     * @var str
     */
    var $data_location = 'webapp/plugins/googleplus/tests/apidata/';
    /**
     * Make a Google+ API request.
     * @param str $path
     * @param str $access_token
     * @return array Decoded JSON response
     */
    public function apiRequest($path, $access_token, $fields=null) {
        $url = $this->api_domain.$path.'?access_token='.$access_token;
        if ($fields != null ) {
            foreach ($fields as $key=>$value) {
                $url = $url.'&'.$key.'='.$value;
            }
        }
        $FAUX_DATA_PATH = THINKUP_ROOT_PATH . $this->data_location;
        $url = str_replace($this->api_domain, '', $url);
        $url = str_replace(':', '', $url);
        $url = str_replace('/', '_', $url);
        $url = str_replace('&', '-', $url);
        $url = str_replace('?', '-', $url);
        return self::decodeFileContents($FAUX_DATA_PATH.$url);
    }

    private static function decodeFileContents($file_path, $decode_json=true) {
        $debug = (getenv('TEST_DEBUG')!==false) ? true : false;
        if ($debug) {
            echo "READING LOCAL TEST DATA FILE: ".$file_path. '
';
        }
        $contents=  file_get_contents($file_path);
        if ($decode_json) {
            $decoded = json_decode($contents);
            if ($decoded == null && $debug) {
                echo "JSON was not decoded! Check if it is valid JSON at http://jsonlint.com/
";
            }
            return $decoded;
        } else {
            return $contents;
        }
    }

    /**
     * Make a Graph API request with the absolute URL. This URL needs to include the
     * https://www.googleapis.com/plus/v1/ at the start and the access token at the end as well as everything in
     * between. It is literally the raw URL that needs to be passed in.
     *
     * @param str $path
     * @param bool $decode_json Defaults to true, if true returns decoded JSON
     * @return array Decoded JSON response
     */
    public function rawPostApiRequest($path, $fields, $decode_json=true) {
        $fields_string = '';
        foreach ($fields as $key=>$value) {
            $fields_string .= $key.'='.$value.'&';
        }

        // Replace the &'s in the string with a -
        $fields_string = str_replace('&', '-', $fields_string);
        $fields_string = rtrim($fields_string,'-');

        $file_path = $path . $fields_string;

        $debug = (getenv('TEST_DEBUG')!==false) ? true : false;
        return self::rawApiRequest($file_path, $decode_json);
    }

    /**
     * Internal use only
     * @param str $path
     * @param book $decode_json If true, return decoded JSON
     * @return array Decoded JSON response
     */
    private function rawApiRequest($path, $decode_json=true) {
        $url = $path;

        $FAUX_DATA_PATH = THINKUP_ROOT_PATH . $this->data_location;

        $url = preg_replace('/([\?\&])access_token\=[^\?\&]+([\?\&])*/', "$1", $url);
        $url = preg_replace('/[\?\&]$/', '', $url);
        $url = str_replace($this->api_domain, '', $url);
        $url = str_replace($this->access_token_request_url, 'tok-', $url);
        $url = str_replace(':', '', $url);
        $url = str_replace('/', '_', $url);
        $url = str_replace('&', '-', $url);
        $url = str_replace('?', '-', $url);
        // If we are in debug mode report that were reading a local test file
        if ($debug) {
            echo "READING LOCAL TEST DATA FILE: ".$FAUX_DATA_PATH.$url. '
';
        }
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
