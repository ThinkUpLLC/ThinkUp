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
 * Facebook Graph API Accessor
 *
 * Makes HTTP requests to the Facebook Graph API given a user access token.
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
     * @param str $fields Comma-delimited list of fields to return from FB API
     * @return array Decoded JSON response
     */
    public static function apiRequest($path, $access_token, $fields=null) {
        $api_domain = 'https://graph.facebook.com';
        if (strpos($path, '?')===false) {
            $url = $api_domain.$path.'?access_token='.$access_token;
        } else {
            $url = $api_domain.$path.'&access_token='.$access_token;
        }
        if ($fields != null ) {
            $url = $url.'&fields='.$fields;
        }
        $result = Utils::getURLContents($url);
        return json_decode($result);
    }
    /**
     * Make a Graph API request with the absolute URL. This URL needs to include the https://graph.facebook.com/ at
     * the start and the access token at the end as well as everything in between. It is literally the raw URL that
     * needs to be passed in.
     *
     * @param str $path
     * @param book $decode_json Defaults to true, if true returns decoded JSON
     * @return array Decoded JSON response
     */
    public static function rawApiRequest($path, $decode_json=true) {
        if ($decode_json) {
            $result = Utils::getURLContents($path);
            return json_decode($result);
        } else {
            return Utils::getURLContents($path);
        }
    }
}
