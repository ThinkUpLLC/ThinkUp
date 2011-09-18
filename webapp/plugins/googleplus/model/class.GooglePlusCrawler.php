<?php
/**
 *
 * ThinkUp/webapp/plugins/googleplus/model/class.GooglePlusCrawler.php
 *
 * Copyright (c) 2011 Gina Trapani
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
 * Google+ Crawler
 *
 * Retrieves user data from Google+, converts it to ThinkUp objects, and stores them in the ThinkUp database.
 * All Google+ users are inserted with the network set to 'google+'
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011 Gina Trapani
 */
class GooglePlusCrawler {
    /**
     *
     * @var Instance
     */
    var $instance;
    /**
     *
     * @var Logger
     */
    var $logger;
    /**
     * @var str
     */
    var $access_token;
    /**
     *
     * @param Instance $instance
     * @return GooglePlusCrawler
     */
    public function __construct($instance, $access_token) {
        $this->instance = $instance;
        $this->logger = Logger::getInstance();
        $this->access_token = $access_token;
    }

    /**
     * If user doesn't exist in the datastore, fetch details from Google+ API and insert into the datastore.
     * If $reload_from_googleplus is true, update existing user details in store with data from Google+ API.
     * @param int $user_id Google+ user ID
     * @param str $found_in Where the user was found
     * @param bool $reload_from_googleplus Defaults to false; if true will query Google+ API and update existing user
     * @return User
     */
    public function fetchUser($user_id, $found_in, $force_reload_from_googleplus=false) {
        $network = 'google+';
        $user_dao = DAOFactory::getDAO('UserDAO');
        $user_object = null;
        if ($force_reload_from_googleplus || !$user_dao->isUserInDB($user_id, $network)) {
            // Get owner user details and save them to DB
            $fields = 'displayName,id,image,tagline';
            //@TODO: Actually fetch user data from Google+ API
            $user_details = GooglePlusAPIAccessor::apiRequest('people/'.$user_id, $this->access_token, $fields);
            $user_details->network = $network;

            $user = $this->parseUserDetails($user_details);

            if (isset($user)) {
                $user_object = new User($user, $found_in);
                $user_dao->updateUser($user_object);
            }
            if (isset($user_object)) {
                $this->logger->logSuccess("Successfully fetched ".$user_id. " ".$network."'s details from Google+",
                __METHOD__.','.__LINE__);
            } else {
                $this->logger->logInfo("Error fetching ".$user_id." ". $network."'s details from the Google+ API, ".
                "response was ".Utils::varDumpToString($user_details), __METHOD__.','.__LINE__);
            }
        }
        return $user_object;
    }

    /**
     * Check the validity of G+'s OAuth token by requestig the instance user's details.
     * Fetch details from Google+ API for the current instance user and insert into the datastore.
     * @return User
     */
    public function initializeInstanceUser($client_id, $client_secret, $access_token, $refresh_token, $owner_id) {
        $network = 'google+';
        $user_dao = DAOFactory::getDAO('UserDAO');
        $user_object = null;
        // Get owner user details and save them to DB
        $fields = 'displayName,id,image,tagline';
        $user_details = GooglePlusAPIAccessor::apiRequest('people/me', $this->access_token, $fields);

        if (isset($user_details->error->code) && $user_details->error->code == '401') {
            //Token has expired, fetch and save a new one
            $tokens = self::getOAuthTokens($client_id, $client_secret, $refresh_token, 'refresh_token');
            $owner_instance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
            $owner_instance_dao->updateTokens($owner_id, $this->instance->id, $access_token, $refresh_token);
            $this->access_token  = $tokens->access_token;
            //try again
            $user_details = GooglePlusAPIAccessor::apiRequest('people/me', $this->access_token, $fields);
        }

        $user_details->network = $network;
        $user = $this->parseUserDetails($user_details);
        if (isset($user)) {
            $user_object = new User($user, 'Owner initialization');
            $user_dao->updateUser($user_object);
        }
        if (isset($user_object)) {
            $this->logger->logSuccess("Successfully fetched ".$user_object->username. " ".$user_object->network.
            "'s details from Google+", __METHOD__.','.__LINE__);
        } else {
            $this->logger->logInfo("Error fetching ".$user_id." ". $network."'s details from the Google+ API, ".
                "response was ".Utils::varDumpToString($user_details), __METHOD__.','.__LINE__);
        }
        return $user_object;
    }

    public static function getOAuthTokens($client_id, $client_secret, $code_refresh_token, $grant_type,
    $redirect_uri=null) {
        //prep access token request URL as per http://code.google.com/apis/accounts/docs/OAuth2.html#SS
        $access_token_request_url = "https://accounts.google.com/o/oauth2/token";
        $fields = array(
            'client_id'=>urlencode($client_id),
            'client_secret'=>urlencode($client_secret),
            'grant_type'=>urlencode($grant_type)
        );
        if ($grant_type=='refresh_token') {
            $fields['refresh_token'] = $code_refresh_token;
        } elseif ($grant_type=='authorization_code') {
            $fields['code'] = $code_refresh_token;
        }
        if (isset($redirect_uri)) {
            $fields['redirect_uri'] = $redirect_uri;
        }
        //get tokens
        $tokens = GooglePlusAPIAccessor::rawPostApiRequest($access_token_request_url, $fields, true);
        return $tokens;
    }

    /**
     * Convert decoded JSON data from Google+ into a ThinkUp user object.
     * @param array $details
     * @retun array $user_vals
     */
    private function parseUserDetails($details) {
        if (isset($details->displayName) && isset($details->id)) {
            $user_vals = array();

            $user_vals["user_name"] = $details->displayName;
            $user_vals["full_name"] = $details->displayName;
            $user_vals["user_id"] = $details->id;
            $user_vals["avatar"] = $details->image->url;
            //@TODO: Fix getting user's primary URL
            $user_vals['url'] = '';
            $user_vals["follower_count"] = 0;
            $user_vals["location"] = '';
            if (isset($details->placesLived) && count($details->placesLived) > 0) {
                foreach ($details->placesLived as $placeLived){
                    if (isset($placeLived->primary))
                    $user_vals["location"] = $placeLived->value;
                }
            }
            $user_vals["description"] = isset($details->tagline)?$details->tagline:'';
            $user_vals["is_protected"] = 0; //All Google+ users are public
            $user_vals["post_count"] = 0;
            $user_vals["joined"] = null;
            $user_vals["network"] = $details->network;
            //this will help us in getting correct range of posts
            $user_vals["updated_time"] = isset($details->updated_time)?$details->updated_time:0;
            return $user_vals;
        }
    }
}
