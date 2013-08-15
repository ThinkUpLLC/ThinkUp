<?php
/**
 *
 * webapp/plugins/youtube/model/class.YouTubeAnalyticsAPIAccessor.php
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
 * YouTube Analytics API Accessor
 *
 * Makes calls to the YouTube Analytics API
 *
 * Copyright (c) 2013 Aaron Kalair
 *
 * @author Aaron Kalair <aaronkalair[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Aaron Kalair
 */

class YouTubeAnalyticsAPIAccessor{

    /**
     * @var str
     */
    var $api_domain = 'https://www.googleapis.com/youtube/analytics/v1/';
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

}
