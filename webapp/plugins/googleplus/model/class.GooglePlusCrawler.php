<?php
/**
 *
 * ThinkUp/webapp/plugins/googleplus/model/class.GooglePlusCrawler.php
 *
 * Copyright (c) 2009-2011 Gina Trapani
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
 * All Google+ users are inserted with the network set to 'googleplus'
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2011 Gina Trapani
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
     * @var int Maximum amount of time the crawler should spend fetching a profile or page in seconds
     */
    var $max_crawl_time;
    /**
     *
     * @param Instance $instance
     * @return GooglePlusCrawler
     */
    public function __construct($instance, $access_token, $max_crawl_time) {
        $this->instance = $instance;
        $this->logger = Logger::getInstance();
        $this->access_token = $access_token;
        $this->max_crawl_time = $max_crawl_time;
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
        $network = 'googleplus';
        $user_dao = DAOFactory::getDAO('UserDAO');
        $user_object = null;
        if ($force_reload_from_googleplus || !$user_dao->isUserInDB($user_id, $network)) {
            // Get owner user details and save them to DB
            $fields = 'displayName,id,image,tagline';
            //@TODO: Actually fetch user data from Google+ API
            $user_details = GooglePlusAPIAccessor::apiRequest('/people/'.$user_id, $this->access_token, $fields);
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
            if (count($details->placesLived) > 0) {
                foreach ($details->placesLived as $placeLived){
                    if (isset($placeLived->primary))
                        $user_vals["location"] = $placeLived->value;
                }
            }
            $user_vals["description"] = isset($details->tagline)?$details->tagline:'';
            $user_vals["is_protected"] = 1; //for now, assume a Google+ user is private
            $user_vals["post_count"] = 0;
            $user_vals["joined"] = null;
            $user_vals["network"] = $details->network;
            //this will help us in getting correct range of posts
            $user_vals["updated_time"] = isset($details->updated_time)?$details->updated_time:0;
            return $user_vals;
        }
    }
}
