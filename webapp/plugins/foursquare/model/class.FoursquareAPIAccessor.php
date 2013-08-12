<?php
/**
 *
 * ThinkUp/webapp/plugins/foursquare/model/class.FoursquareAPIAccessor.php
 *
 * Copyright (c) 2012-2013 Aaron Kalair
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
 * Foursquare API Accessor
 *
 * Makes calls to the foursquare API
 *
 * @author Aaron Kalair <aaronkalair[at]gmail[dot][com]>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2013 Aaron Kalair
 */

class FoursquareAPIAccessor{
    /**
     * The base URL for making foursquare API requests.
     * @var str
     */
    var $api_domain = 'https://api.foursquare.com/v2/';
    /** Make a foursquare API request.
     * @param str $endpoint API endpoint to access
     * @param str $access_token OAuth token for the user
     * @param arr $fields Array of URL parameters
     * @return array Decoded JSON response
     */
    public function apiRequest($endpoint, $access_token, $fields=null) {
        // Add endpoint and OAuth token to the URL
        $url = $this->api_domain.$endpoint.'?oauth_token='.$access_token;

        // If there are additional parameters passed in add them to the URL also
        if ($fields != null){
            foreach( $fields as $key=>$value) {
                $url = $url.'&'.$key.'='.$value;
            }
        }

        // Foursquare requires us to add the date at the end of the request so get a date array
        $date = getdate();

        // Add the year month and day at the end of the URL
        $url = $url."&v=".$date['year'].$date['mon'].$date['mday'];

        // Get any results returned from this request
        $result = Utils::getURLContents($url);

        // Return the results
        $parsed_result = json_decode($result);
        if (isset($parsed_result->meta->errorType)) {
            $exception_description = $parsed_result->meta->errorType;
            if (isset($parsed_result->meta->errorDetail)) {
                $exception_description .= ": ".$parsed_result->meta->errorDetail;
            }
            throw new Exception($exception_description);
        } else {
            return $parsed_result;
        }
    }
    /**
     * Make an API request with an absolute URL, the URL you pass in is litterally the request you want to make
     * with OAuth tokens etc
     *
     * @param str $path - The URL of the API request you want to make
     * @param bool $decode_json Defaults to true, if true returns decoded JSON
     * @return array Decoded JSON response
     */
    public function rawPostApiRequest($path, $fields, $decode_json=true) {
        // Get the result of the API query
        $result = Utils::getURLContentsViaPost($path, $fields);
        // Decode the results
        if ($decode_json) {
            $result  = json_decode($result);
        }
        return $result;
    }
}
