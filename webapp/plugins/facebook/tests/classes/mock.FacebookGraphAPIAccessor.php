<?php
/**
 *
 * ThinkUp/webapp/plugins/facebook/model/class.FacebookGraphAPIAccessor.php
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
 * Mock Facebook Graph API Accessor
 *
 * Reads test data files instead of the actual Facebook servers for the purposes of running tests.
 *
 * Copyright (c) 2009-2013 Gina Trapani
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 */
class FacebookGraphAPIAccessor {
    /**
     * Make a Graph API request.
     * @param str $path
     * @param str $access_token
     * @return array Decoded JSON response
     */
    public static function apiRequest($path, $access_token, $fields=null) {
        $api_domain = 'https://graph.facebook.com';
        $url = $api_domain.$path;//.'?access_token='.$access_token;

        $FAUX_DATA_PATH = THINKUP_WEBAPP_PATH.'plugins/facebook/tests/testdata/';
        $url = str_replace('https://graph.facebook.com/', '', $url);
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
     * Make a Graph API request with the absolute URL. This URL needs to
     * include the https://graph.facebook.com/ at the start and the
     * access token at the end as well as everything in between. It is
     * literally the raw URL that needs to be passed in.
     *
     * @param str $path
     * @param book $decode_json If true, return decoded JSON
     * @return array Decoded JSON response
     */
    public static function rawApiRequest($path, $decode_json=true) {
        $url = $path;

        $FAUX_DATA_PATH = THINKUP_WEBAPP_PATH.'plugins/facebook/tests/testdata/';

        $url = preg_replace('/([\?\&])access_token\=[^\?\&]+([\?\&])*/', "$1", $url);
        $url = preg_replace('/[\?\&]$/', '', $url);

        $url = str_replace('https://graph.facebook.com/', '', $url);
        //$url = str_replace('?access_token=fauxaccesstoken', '', $url);
        $url = str_replace('/', '_', $url);
        $url = str_replace('&', '-', $url);
        $url = str_replace('?', '-', $url);
        return self::decodeFileContents($FAUX_DATA_PATH.$url, $decode_json);
    }
}
