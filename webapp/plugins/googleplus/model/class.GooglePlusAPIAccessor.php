<?php
/**
 *
 * ThinkUp/webapp/plugins/googleplus/model/class.GooglePlusAPIAccessor.php
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
 *
 *
 * Google+ API Accessor
 *
 * Makes HTTP requests to the Google+ API given a user access token.
 *
 * Copyright (c) 2011 Gina Trapani
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011 Gina Trapani
 */
class GooglePlusAPIAccessor {
    /**
     * Make an API request.
     * @param str $path
     * @param str $access_token
     * @param str $fields Comma-delimited list of fields to return from FB API
     * @return array Decoded JSON response
     */
    public static function apiRequest($path, $access_token, $fields=null) {
        $api_domain = 'https://www.googleapis.com/plus/v1/';
        $url = $api_domain.$path.'?access_token='.$access_token;
        if ($fields != null ) {
            $url = $url.'&fields='.$fields;
        }
        $result = Utils::getURLContents($url);
        return json_decode($result);
    }
    /**
     * Make a Graph API request with the absolute URL. This URL needs to include the https://graph.googleplus.com/ at
     * the start and the access token at the end as well as everything in between. It is literally the raw URL that
     * needs to be passed in.
     *
     * @param str $path
     * @param bool $decode_json Defaults to true, if true returns decoded JSON
     * @return array Decoded JSON response
     */
    public static function rawGetApiRequest($path, $decode_json=true) {
        $result = Utils::getURLContents($path);
        if ($decode_json) {
            $result = json_decode($result);
        }
        return $result;
    }

    /**
     * Make a Graph API request with the absolute URL. This URL needs to include the https://graph.googleplus.com/ at
     * the start and the access token at the end as well as everything in between. It is literally the raw URL that
     * needs to be passed in.
     *
     * @param str $path
     * @param bool $decode_json Defaults to true, if true returns decoded JSON
     * @return array Decoded JSON response
     */
    public static function rawPostApiRequest($path, $fields, $decode_json=true) {
        $result = Utils::getURLContentsViaPost($path, $fields);
        if ($decode_json) {
            $result = json_decode($result);
        }
        return $result;
    }
}
