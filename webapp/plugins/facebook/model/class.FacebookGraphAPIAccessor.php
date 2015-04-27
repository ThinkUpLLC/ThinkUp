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
 * Copyright (c) 2009-2015 Gina Trapani
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2015 Gina Trapani
 */
class FacebookGraphAPIAccessor {

    const API_DOMAIN = 'https://graph.facebook.com/v2.3/';

    /**
     * Make a Graph API request.
     * @param str $path
     * @param str $access_token
     * @param array $params HTTP parameters to include on URL
     * @param str $fields Comma-delimited list of fields to return from FB API
     * @return array Decoded JSON response
     */
    public static function apiRequest($path, $access_token=null, $params=null, $fields=null) {
        //Set up URL parameters
        $api_call_params = $params;
        if (isset($fields)) {
            //Add fields
            $params['fields'] = $fields;
        }
        $api_call_params_str = http_build_query($params);

        $url = self::API_DOMAIN.$path.'?'.$api_call_params_str;
        return self::apiRequestFullURL($url, $access_token);
    }
    /**
     * Make a Graph API request with the absolute URL. This URL needs to include the https://graph.facebook.com/ at
     * the start and all the query string parameters EXCEPT the acces token.
     *
     * This is for use in paging, when the API payload specifies the full URL for the next page.
     *
     * @param str $url
     * @param str $access_token
     * @return array Decoded JSON response
     */
    public static function apiRequestFullURL($url, $access_token=null) {
        $params = array();
        if (isset($access_token)) {
            //Add access_token
            $params['access_token'] = $access_token;
            $access_token_str = http_build_query($params);
            if (strpos($url, '?')===false) {
                $url = $url.'?'.$access_token_str;
            } else {
                $url = $url.'&'.$access_token_str;
            }
        }
        //DEBUG
        // if (php_sapi_name() == "cli") {//Crawler being run at the command line
        //     $logger = Logger::getInstance();
        //     $logger->logInfo("Graph API call: ".$url, __METHOD__.','.__LINE__);
        // }

        $result = Utils::getURLContents($url);
        try {
            return JSONDecoder::decode($result);
        } catch (JSONDecoderException $e) {
            return $result;
        }
    }
}
