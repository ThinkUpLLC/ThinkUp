<?php
/**
 *
 * ThinkUp/webapp/plugins/googleplus/model/class.GooglePlusAPIAccessor.php
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
 * Google+ API Accessor
 *
 * Makes HTTP requests to the Google+ API given a user access token.
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
     * Make an API request.
     * @param str $path
     * @param str $access_token
     * @param arr $fields Array of URL parameters
     * @return array Decoded JSON response
     */
    public function apiRequest($path, $access_token, $fields=null) {
        $url = $this->api_domain.$path.'?access_token='.$access_token;
        if ($fields != null ) {
            foreach ($fields as $key=>$value) {
                $url = $url.'&'.$key.'='.$value;
            }
        }
        $result = Utils::getURLContents($url);
        return json_decode($result);
    }

    /**
     * Make a Graph API request with the absolute URL. This URL needs to include https://www.googleapis.com/plus/v1/ at
     * the start and the access token at the end as well as everything in between. It is literally the raw URL that
     * needs to be passed in.
     *
     * @param str $path
     * @param bool $decode_json Defaults to true, if true returns decoded JSON
     * @return array Decoded JSON response
     */
    public function rawPostApiRequest($path, $fields, $decode_json=true) {
        $result = Utils::getURLContentsViaPost($path, $fields);
        if ($decode_json) {
            $result = json_decode($result);
        }
        return $result;
    }
}
