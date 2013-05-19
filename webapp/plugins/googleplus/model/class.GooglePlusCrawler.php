<?php
/**
 *
 * ThinkUp/webapp/plugins/googleplus/model/class.GooglePlusCrawler.php
 *
 * Copyright (c) 2011-2013 Gina Trapani
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
 * Google+ Crawler
 *
 * Retrieves user data from Google+, converts it to ThinkUp objects, and stores them in the ThinkUp database.
 * All Google+ users are inserted with the network set to 'google+'
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 Gina Trapani
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
     * @var GooglePlusAPIAccessor
     */
    var $api_accessor;
    /**
     *
     * @param Instance $instance
     * @return GooglePlusCrawler
     */
    public function __construct($instance, $access_token) {
        $this->instance = $instance;
        $this->logger = Logger::getInstance();
        $this->access_token = $access_token;
        $this->api_accessor = new GooglePlusAPIAccessor();
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
            $fields = array('fields'=>'displayName,id,image,tagline,verified');
            $user_details =  $this->api_accessor->apiRequest('people/'.$user_id, $this->access_token, $fields);
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
     * @param str $client_id
     * @param str $client_secret
     * @param str $access_token
     * @param str $refresh_token
     * @param str $owner_id
     * @return User
     */
    public function initializeInstanceUser($client_id, $client_secret, $access_token, $refresh_token, $owner_id) {
        $network = 'google+';
        $user_dao = DAOFactory::getDAO('UserDAO');
        $user_object = null;
        // Get owner user details and save them to DB
        $fields = array('fields'=>'displayName,id,image,tagline,verified');
        $user_details =  $this->api_accessor->apiRequest('people/me', $this->access_token, $fields);

        if (isset($user_details->error->code) && $user_details->error->code == '401') {
            //Token has expired, fetch and save a new one
            $tokens = self::getOAuthTokens($client_id, $client_secret, $refresh_token, 'refresh_token');
            if (isset($tokens->error) || !isset($tokens->access_token)) {
                $error_msg = "Oops! Something went wrong while obtaining OAuth tokens.<br>Google says \"";
                if (isset($tokens->error)) {
                    $error_msg .= $tokens->error;
                } else {
                    $error_msg .= Utils::varDumpToString($tokens);
                }
                $error_msg .=".\" Please double-check your settings and try again.";
                $this->logger->logError($error_msg, __METHOD__.','.__LINE__);
            } else {
                $owner_instance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
                $owner_instance_dao->updateTokens($owner_id, $this->instance->id, $access_token, $refresh_token);
                $this->access_token  = $tokens->access_token;
                //try again
                $user_details =  $this->api_accessor->apiRequest('people/me', $this->access_token, $fields);
            }
        }

        if (isset($user_details)) {
            $user_details->network = $network;
            $user = $this->parseUserDetails($user_details);
        }
        if (isset($user)) {
            $user_object = new User($user, 'Owner initialization');
            $user_dao->updateUser($user_object);
        }
        if (isset($user_object)) {
            $this->logger->logSuccess("Successfully fetched ".$user_object->username. " ".$user_object->network.
            "'s details from Google+", __METHOD__.','.__LINE__);
        } else {
            $this->logger->logInfo("Error fetching user details from the Google+ API, response was ".
            Utils::varDumpToString($user_details), __METHOD__.','.__LINE__);
        }
        return $user_object;
    }

    /**
     * Retrieve OAuth and refresh tokens from Google API as per:
     * http://code.google.com/apis/accounts/docs/OAuth2.html#SS
     * @param str $client_id
     * @param str $client_secret
     * @param str $code_refresh_token Either the refresh token or Google-provided code
     * @param str $grant_type Either 'refresh_token' or 'authorization_code'
     * @param str $redirect_uri
     * @return Object with access_token and refresh_token member vars
     */
    public function getOAuthTokens($client_id, $client_secret, $code_refresh_token, $grant_type,
    $redirect_uri=null) {
        //prep access token request URL
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
        $tokens =  $this->api_accessor->rawPostApiRequest($access_token_request_url, $fields, true);
        return $tokens;
    }

    /**
     * Capture the current instance users's posts and store them in the database.
     * @return null
     */
    public function fetchInstanceUserPosts() {
        //For now only capture the most recent 20 posts
        //@TODO Page back through all the archives
        $fields = array('alt'=>'json', 'maxResults'=>20, 'pp'=>1);
        $user_posts =  $this->api_accessor->apiRequest('people/'.$this->instance->network_user_id.
        '/activities/public', $this->access_token, $fields);

        if (isset($user_posts->items)) {
            $post_dao = DAOFactory::getDAO('PostDAO');
            $link_dao = DAOFactory::getDAO('LinkDAO');
            foreach ($user_posts->items as $item) {
                $should_capture_post = false;
                //For now we're only capturing posts and shares
                //@TODO Capture all types of posts
                if ($item->verb == "post") {
                    $post['post_text'] = $item->object->content;
                    $should_capture_post = true;
                } elseif ($item->verb == "share") {
                    $post['post_text'] = (isset($item->annotation))?$item->annotation:'';
                    $should_capture_post = true;
                }
                if ($should_capture_post) {
                    $post['post_id'] = $item->id;
                    $post['author_username'] = $item->actor->displayName;
                    $post['author_fullname'] = $item->actor->displayName;
                    $post['author_avatar'] = $item->actor->image->url;
                    $post['author_user_id'] = $item->actor->id;
                    $post['pub_date'] = $item->published;
                    $post['source'] = '';
                    $post['is_protected'] = false;
                    $post['network'] = 'google+';
                    $post['reply_count_cache'] = $item->object->replies->totalItems;
                    $post['favlike_count_cache'] = $item->object->plusoners->totalItems;
                    $post['retweet_count_cache'] = $item->object->resharers->totalItems;
                    $inserted_post_key = $post_dao->addPost($post);

                    //If no post was added, at least update reply/fave/reshare counts and post text
                    if ($inserted_post_key === false) {
                        $post_dao->updateFavLikeCount($post['post_id'], 'google+', $post['favlike_count_cache']);
                        $post_dao->updateReplyCount($post['post_id'], 'google+', $post['reply_count_cache']);
                        $post_dao->updateRetweetCount($post['post_id'], 'google+', $post['retweet_count_cache']);
                        $post_dao->updatePostText($post['post_id'], 'google+', $post['post_text']);
                    }

                    if (isset($item->object->attachments) && isset($item->object->attachments[0]->url)) {
                        $link_url = $item->object->attachments[0]->url;
                        $link = new Link(array(
                    "url"=>$link_url,
                    "expanded_url"=>'',
                    "image_src"=>(isset($item->object->attachments[0]->image->url))
                        ?$item->object->attachments[0]->image->url:'',
                    "caption"=>'',
                    "description"=>(isset($item->object->attachments[0]->content))
                        ?$item->object->attachments[0]->content:'',
                    "title"=>(isset($item->object->attachments[0]->displayName))
                        ?$item->object->attachments[0]->displayName:'',
                    "post_key"=>$inserted_post_key
                        ));
                        $added_links = $link_dao->insert($link);
                    }
                }
                $post = null;
                $link = null;
            }
        }
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
            $user_vals["is_verifed"] = isset($details->verified)?$details->verified:'';
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
