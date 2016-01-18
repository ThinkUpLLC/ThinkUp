<?php
/**
 *
 * ThinkUp/webapp/plugins/instagram/model/class.InstagramCrawler.php
 *
 * Copyright (c) 2013-2016 Dimosthenis Nikoudis, Gina Trapani
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
 * Instagram Crawler
 *
 * @author Dimosthenis Nikoudis <dnna[at]dnna[dot]gr>
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013-2016 Dimosthenis Nikoudis, Gina Trapani
 */
class InstagramCrawler {
    /**
     * @var InstagramInstance
     */
    var $instance;
    /**
     * @var Logger
     */
    var $logger;
    /**
     * @var InstagramAPIAccessor
     */
    var $api_accessor;
    /**
     * Current Instagram user.
     * @var User
     */
    var $user;
    /**
     * @param InstagramInstance $instance
     * @return InstagramCrawler
     */
    public function __construct($instance, $access_token, $max_api_calls) {
        $this->instance = $instance;
        $this->logger = Logger::getInstance();
        $this->api_accessor = new InstagramAPIAccessor($access_token, $max_api_calls);
    }
    /**
     * If user doesn't exist in the datastore, fetch details from instagram API and insert into the datastore.
     * If $reload_from_instagram is true, update existing user details in store with data from instagram API.
     * @param int $user_id instagram user ID
     * @param str $found_in Where the user was found
     * @param str $username
     * @param str $full_name
     * @param str $avatar
     * @param bool $reload_from_instagram Defaults to false; if true will query instagram API and update existing user
     * @return User
     */
    public function fetchUser($user_id, $found_in, $username, $full_name, $avatar, $force_reload_from_instagram=false) {
        $this->logger->logInfo("Start fetching user ".$username, __METHOD__.','.__LINE__);
        //assume all users except the instance user is a instagram profile, not a page
        $network = ($user_id == $this->instance->network_user_id)?$this->instance->network:'instagram';
        $user_dao = DAOFactory::getDAO('UserDAO');
        $user_object = null;
        if ($force_reload_from_instagram || !$user_dao->isUserInDB($user_id, $network)) {
            // Get user details and save them to DB
            $user_details = null;
            try {
                $user_details = $this->api_accessor->apiRequest('user', array('user_id'=> $user_id));
                $this->logger->logSuccess("Successfully fetched ".$user_id. " ".$network.
                    "'s details from Instagram", __METHOD__.','.__LINE__);
                $user_details->network = $network;
                $user = $this->parseUserDetails($user_details);
            } catch (APICallPermissionDeniedException $e) {
                $user = array();
                $user["user_name"] = $username;
                $user["full_name"] = $full_name;
                $user["user_id"] = $user_id;
                $user["avatar"] = $avatar;
                $user['url'] = '';
                $user["location"] = '';
                $user["description"] = '';
                $user["is_protected"] = 1;
                $user["joined"] = ''; //Column 'joined' cannot be null
                $user["network"] = 'instagram';
                $this->logger->logInfo("Private user ".$username. " with limited details", __METHOD__.','.__LINE__);
            } catch (APIErrorException $e) {
                $this->logger->logInfo(get_class($e)." fetching ".$user_id." ". $network.
                    "'s details from Instagram API, error was ".$e->getMessage(), __METHOD__.','.__LINE__);
            }

            if (isset($user)) {
                $user_object = new User($user, $found_in);
                $updated_user = $user_dao->updateUser($user_object);
                if ($updated_user > 0) {
                    $this->logger->logInfo("Updated user successfully", __METHOD__.','.__LINE__);
                } else {
                    $this->logger->logInfo("No changes to user ".$username, __METHOD__.','.__LINE__);
                }
            } else {
                $this->logger->logInfo("Error parsing user details ".Utils::varDumpToString($user_details),
                    __METHOD__.','.__LINE__);
            }
        } else {
            $this->logger->logInfo("No need to update user ", __METHOD__.','.__LINE__);
        }
        return $user_object;
    }

    /**
     * Get whether or not a user is private based on the relationship with current user. Only call this for users
     * who had a successful get user call.
     * @param  str $user_id
     * @return int 1 or 0
     */
    public function getIsUserPrivate($user_id) {
        $is_protected = 0;
        if ($user_id !== $this->instance->network_user_id) {
            $relationship = $this->api_accessor->apiRequest( 'relationship', array('user_id'=>$user_id) );
            if ($relationship->target_user_is_private) {
                $is_protected = 1;
            }
        }
        return $is_protected;
    }

    /**
     * Convert an Instagram user object into a ThinkUp user object.
     * @param array $details
     * @return array $user_vals
     */
    private function parseUserDetails(Instagram\User $details) {
        $user_vals = null;
        if ($details->getUserName() != null && $details->getId() != null) {
            $user_vals = array();

            $user_vals["post_count"] = $details->getMediaCount();
            $user_vals["follower_count"] = $details->getFollowersCount();
            $user_vals["friend_count"] = $details->getFollowsCount();
            $user_vals["user_name"] = $details->getUserName();
            $user_vals["full_name"] = $details->getFullName();
            $user_vals["user_id"] = $details->getId();
            $user_vals["avatar"] = $details->getProfilePicture();
            $user_vals['url'] = $details->getWebsite()!=null?$details->getWebsite():'';
            $user_vals["location"] = '';
            $user_vals["description"] = $details->getBio()!=null?$details->getBio():'';
            $user_vals["joined"] = ''; //Column 'joined' cannot be null
            $user_vals["network"] = $details->network;
            //this will help us in getting correct range of posts
            $user_vals["updated_time"] = isset($details->updated_time)?$details->updated_time:0;

            try {
                $user_vals['is_protected'] = $this->getIsUserPrivate($user_vals["user_id"]);
            } catch (APICallPermissionDeniedException $e) {
                $user_vals['is_protected'] = 1;
            } catch (APIErrorException $e) {
                $this->logger->logInfo("Error fetching ".$details->username. "'s details. Instagram says '"
                    .$e->getMessage()."'", __METHOD__.','.__LINE__);
            }
        }
        return $user_vals;
    }
    /**
     * Fetch and save the posts and replies for the crawler's instance. This function will loop back through the
     * user's or pages archive of posts.
     *
     * // If archive isn't loaded, attempt to load it
     * api_call = /users/{id}/media/recent
     *
     * If instances.is_archive_loaded == 0 and instances.last_post_id != ''
     * set api_call param max_id = last_post_id.
     *
     *     posts = Fetch page of media via api_call
     *
     *     insert posts
     * endif
     *
     * //Get recent
     * api_call = /users/{id}/media/recent
     * param min_timestamp = instance.last_crawled_time (-week if not set)
     * posts = Fetch page of media via api_call
     *
     * insert posts
     *
     */
    public function fetchPostsAndReplies() {
        $id = $this->instance->network_user_id;
        $network = $this->instance->network;

        //Force-refresh instance user in data store
        $this->user = self::fetchUser($this->instance->network_user_id, 'Owner info', $this->instance->network_username,
            null, null, true);

        //Update follower count in history store
        if (isset($this->user->follower_count) && $this->user->follower_count>0) {
            $count_dao = DAOFactory::getDAO('CountHistoryDAO');
            $count_dao->insert($this->user->user_id, 'instagram', $this->user->follower_count, null, 'followers');
        }

        // If archive isn't loaded, attempt to load it
        if (!$this->instance->is_archive_loaded_posts && $this->instance->last_post_id != '') {
            $this->logger->logUserInfo("Media archive is not loaded, fetching starting from max_id "
                .$this->instance->last_post_id, __METHOD__.','.__LINE__);
            $api_param = array('max_id' => $this->instance->last_post_id);

            try {
                $posts = $this->api_accessor->apiRequest('media', $api_param);
                $fetch_next_page = true;
            } catch (APICallLimitExceededException $e) {
                $fetch_next_page = false;
            } catch (APIErrorException $e) {
                $fetch_next_page = false;
            }

            while ($fetch_next_page) {
                if ($posts->count() > 0) {
                    $this->logger->logInfo($posts->count()." Instagram posts found", __METHOD__.','.__LINE__);

                    $this->processPosts($posts, $network);

                    if ($posts->getNext() != null) {
                        $api_param['max_id'] = $posts->getNext();
                        try {
                            $posts = $this->api_accessor->apiRequest('media', $api_param);
                        } catch (APICallLimitExceededException $e) {
                            $fetch_next_page = false;
                            $this->instance->last_post_id = $api_param['max_id'];
                        } catch (APIErrorException $e) {
                            $fetch_next_page = false;
                            $this->instance->last_post_id = $api_param['max_id'];
                        }
                    } else {
                        $fetch_next_page = false;
                        $this->instance->is_archive_loaded_posts = true;
                    }
                } else {
                    $this->logger->logInfo("No Instagram posts found before max id ".$this->instance->last_post_id,
                        __METHOD__.','.__LINE__);
                    $fetch_next_page = false;
                }
            }
        }

        if ($this->instance->is_archive_loaded_posts) {
            $this->logger->logUserInfo("Media archive is loaded",__METHOD__.','.__LINE__);
        }

        //Get recent media
        $api_param = array('count' => 20);

        $this->logger->logUserInfo("About to request recent media",__METHOD__.','.__LINE__);
        try {
            $posts = $this->api_accessor->apiRequest('media', $api_param);
            $fetch_next_page = true;
            $this->logger->logUserInfo("Recent media requested with params ".Utils::varDumpToString($api_param),
                __METHOD__.','.__LINE__);
        } catch (APICallLimitExceededException $e) {
            $fetch_next_page = false;
        } catch (APIErrorException $e) {
            $fetch_next_page = false;
        }

        while ($fetch_next_page) {
            if ($posts->count() > 0) {
                $this->logger->logInfo($posts->count()." Instagram posts found", __METHOD__.','.__LINE__);

                $did_capture_new_data = $this->processPosts($posts, $network);

                if ($did_capture_new_data && $posts->getNext() != null) {
                    $api_param['max_id'] = $posts->getNext();
                    try {
                        $posts = $this->api_accessor->apiRequest('media', $api_param);
                        $fetch_next_page = true;
                    } catch (APICallLimitExceededException $e) {
                        $fetch_next_page = false;
                        $this->instance->last_post_id = $api_param['max_id'];
                    } catch (APIErrorException $e) {
                        $fetch_next_page = false;
                        $this->instance->last_post_id = $api_param['max_id'];
                    }
                } else {
                    if (!$did_capture_new_data) {
                        $this->logger->logInfo("No new data captured in last set, stopping here",
                            __METHOD__.','.__LINE__);
                    } else {
                        $this->logger->logInfo("No posts in the next set", __METHOD__.','.__LINE__);
                    }
                    $fetch_next_page = false;
                }
            } else {
                $this->logger->logInfo("No Instagram posts found for ID $id", __METHOD__.','.__LINE__);
                $fetch_next_page = false;
            }
        }
    }
    /**
     * Fetch a user's likes and page back to capture archive.
     */
    public function fetchLikes() {
        if (!isset($this->user)) {
            //Force-refresh instance user in data store
            $this->user = self::fetchUser($this->instance->network_user_id, 'Owner info',
                $this->instance->network_username, null, null, true);
        }
        $network = 'instagram';

        $this->logger->logUserInfo("Fetching likes, next max like id is " .$this->instance->next_max_like_id,
            __METHOD__.','.__LINE__);
        $api_param = array('max_like_id' => $this->instance->next_max_like_id);

        try {
            $posts = $this->api_accessor->apiRequest('likes', $api_param);
            $fetch_next_page = true;
        } catch (APICallLimitExceededException $e) {
            $fetch_next_page = false;
        } catch (APIErrorException $e) {
            $fetch_next_page = false;
        }

        $favorite_dao = DAOFactory::getDAO('FavoritePostDAO');

        while ($fetch_next_page) {
            if ($posts->count() > 0) {
                $this->logger->logInfo($posts->count()." Instagram posts found", __METHOD__.','.__LINE__);

                //Insert posts
                $did_capture_new_data = $this->processPosts($posts, $network, false);

                //Insert faves
                foreach ($posts as $post) {
                    $vals = array('post_id'=>$post->getId(), 'network'=>'instagram',
                        'author_user_id'=> $post->getUser()->getId());
                    $favorite_dao->addFavorite($this->instance->network_user_id, $vals);
                }

                if ($did_capture_new_data && $posts->getNext() !== null) {
                    $api_param['max_like_id'] = $posts->getNext();
                    try {
                        $this->logger->logUserInfo("Fetching more likes, next max like id is "
                            .$api_param['max_like_id'], __METHOD__.','.__LINE__);
                        $posts = $this->api_accessor->apiRequest('likes', $api_param);
                    } catch (APICallLimitExceededException $e) {
                        $fetch_next_page = false;
                        $this->instance->next_max_like_id = $api_param['max_like_id'];
                    } catch (APIErrorException $e) {
                        $fetch_next_page = false;
                        $this->instance->next_max_like_id = $api_param['max_like_id'];
                    }
                } else {
                    if (!$did_capture_new_data) {
                        $this->logger->logInfo("No new data captured in this set, stopping here",
                            __METHOD__.','.__LINE__);
                    }
                    if ($posts->getNext() == null) {
                        $this->logger->logInfo("No next page", __METHOD__.','.__LINE__);
                    }
                    $fetch_next_page = false;
                    $this->instance->next_max_like_id = null;
                }
            } else {
                $this->logger->logInfo("No Instagram posts found before max id ".$this->instance->last_post_id,
                    __METHOD__.','.__LINE__);
                $fetch_next_page = false;
                $this->instance->next_max_like_id = null;
            }
        }
    }
    /**
     * Fetch and save the instance users's followers.
     *
     * if is_archive_loaded
     *     if ($this->instance->total_follows_in_system !== $this->user->follow_count) {
     *         is_archive_loaded = false;
     *
     * if !is_archive_loaded
     *     if followed_by_next_cursor is set
     *         pageThroughFollowers(followed_by_next_cursor)
     *     else
     *         pageThroughFollowers()
     *
     * if is_archive_loaded
     *     updateStaleFollows()
     *
     */
    public function fetchFollowers() {
        if ($this->instance->is_archive_loaded_follows) {
            $this->logger->logUserInfo("Starting with follow archive marked as loaded",__METHOD__.','.__LINE__);
            if ($this->instance->total_follows_in_system !== $this->user->follower_count) {
                $this->instance->is_archive_loaded_follows = false;
                $this->logger->logUserInfo("Marking follow archive as NOT loaded",__METHOD__.','.__LINE__);
            }
        }

        //If archive is not loaded, page through followers
        if (!$this->instance->is_archive_loaded_follows) {
            $this->pageThroughFollowers($this->instance->followed_by_next_cursor);
        }

        //If archive is loaded, updateStaleFollows
        if ($this->instance->is_archive_loaded_follows) {
            $this->updateStaleFollows();
        }
    }
    /**
     * Fetch and save the instance users's friends.
     *
     * if is_archive_loaded
     *     if ($this->instance->total_follows_in_system !== $this->user->friend_count) {
     *         is_archive_loaded = false;
     *
     * if !is_archive_loaded
     *     if followed_by_next_cursor is set
     *         pageThroughFriends(followed_by_next_cursor)
     *     else
     *         pageThroughFriends()
     *
     * if is_archive_loaded
     *     updateStaleFollows()
     *
     */
    public function fetchFriends() {
        if (!isset($this->user)) {
            //Force-refresh instance user in data store
            $this->user = self::fetchUser($this->instance->network_user_id, 'Owner info',
                $this->instance->network_username, null, null, true);
        }

        $follow_dao = DAOFactory::getDAO('FollowDAO');
        $this->instance->total_friends_in_system = $follow_dao->countTotalFriends($this->instance->network_user_id,
            'instagram');

        $this->logger->logUserInfo($this->instance->total_friends_in_system." friends in system, ".
            $this->user->friend_count." friends according to Instagram", __METHOD__.','.__LINE__);

        if ($this->instance->total_friends_in_system < $this->user->friend_count) {
            $this->instance->is_archive_loaded_friends = false;
        } elseif ($this->instance->total_friends_in_system > $this->user->friend_count) {
            $this->instance->is_archive_loaded_friends = true;
        } else {
            $this->instance->is_archive_loaded_friends = true;
        }

        //If archive is not loaded, page through friends
        if (!$this->instance->is_archive_loaded_friends) {
            $this->logger->logInfo("Friend archive  is not loaded, start paging", __METHOD__.','.__LINE__);
            $this->pageThroughFriends($this->instance->follows_next_cursor);
        }

        //If archive is loaded, updateStaleFollows
        if ($this->instance->is_archive_loaded_friends) {
            $this->logger->logInfo("Friend archive loaded, start updating stale friendships", __METHOD__.','.__LINE__);
            $this->updateStaleFollows(true);
        }
    }
    /**
     * Grab oldest follow relationship, check if it exists, and update table.
     * @param bool $friends_only If true, only check for stale friends, not just follows in general
     */
    public function updateStaleFollows($friends_only = false) {
        $follow_dao = DAOFactory::getDAO('FollowDAO');
        $continue_fetching = true;
        while ($continue_fetching) {
            $old_follow = null;
            if ($friends_only) {
                $old_follow = $follow_dao->getOldestFriend($this->instance->network_user_id, 'instagram');
                $this->logger->logInfo("Checking stale friends", __METHOD__.','.__LINE__);
            } else {
                $old_follow = $follow_dao->getOldestFollow('instagram');
                $this->logger->logInfo("Checking stale followers", __METHOD__.','.__LINE__);
            }
            if ($old_follow !== null) {
                // $old_follow["followee_id"];
                // $old_follow["follower_id"];
                if ($old_follow['followee_id'] == $this->instance->network_user_id) {
                    $user_to_check = $old_follow["follower_id"];
                } elseif  ($old_follow["follower_id"] == $this->instance->network_user_id) {
                    $user_to_check = $old_follow["followee_id"];
                } else {
                    // print_r($old_follow);
                    // print_r($this->instance);
                    // It should never come to this.
                    $continue_fetching = false;
                }

                if (isset($user_to_check)) {
                    try {
                        $this->logger->logInfo("Checking stale follow last seen ".$old_follow["last_seen"],
                            __METHOD__.','.__LINE__);
                        $relationship = $this->api_accessor->apiRequest( 'relationship',
                            array('user_id'=>$user_to_check) );

                        //Outgoing status
                        //outgoing_status values: follows, none, requested_by
                        if ($relationship->outgoing_status == 'follows') {
                            $this->logger->logInfo("Outgoing status is follows. ".
                                $this->instance->network_user_id." follows ". $user_to_check, __METHOD__.','.__LINE__);

                            //$follow_dao->update($user_to_check, $this->instance->network_user_id, 'instagram', true);

                            //store relationship in follows table
                            if ($follow_dao->followExists($user_to_check, $this->instance->network_user_id,
                                'instagram')) {
                                $this->logger->logInfo("Update", __METHOD__.','.__LINE___);
                                // follow relationship already exists
                                $follow_dao->update($user_to_check, $this->instance->network_user_id, 'instagram');
                            } else {
                                $this->logger->logInfo("Insert", __METHOD__.','.__LINE___);
                                // follow relationship does not exist yet
                                $follow_dao->insert($user_to_check, $this->instance->network_user_id, 'instagram');
                            }
                        } else {
                            $this->logger->logInfo("Outgoing status: Deactivating follow outgoing_status is ".
                                $relationship->outgoing_status .". ". $this->instance->network_user_id.
                                " does not follow ". $user_to_check, __METHOD__.','.__LINE__);

                            $follow_dao->deactivate($user_to_check, $this->instance->network_user_id, 'instagram');
                        }

                        //Incoming status
                        //incoming_status: followed_by
                        if ($relationship->incoming_status == 'followed_by') {
                            $this->logger->logInfo("Incoming status is ".$relationship->incoming_status. ". ".
                                $user_to_check." follows ". $this->instance->network_user_id, __METHOD__.','.__LINE__);

                            //$follow_dao->update($this->instance->network_user_id, $user_to_check, 'instagram', true);

                            //store relationship in follows table
                            if ($follow_dao->followExists($this->instance->network_user_id, $user_to_check,
                                'instagram')) {
                                $this->logger->logInfo("Update", __METHOD__.','.__LINE___);
                                // follow relationship already exists
                                $follow_dao->update($this->instance->network_user_id, $user_to_check, 'instagram');
                            } else {
                                $this->logger->logInfo("Insert", __METHOD__.','.__LINE___);
                                // follow relationship does not exist yet
                                $follow_dao->insert($this->instance->network_user_id, $user_to_check, 'instagram');
                            }
                        } else {
                            $this->logger->logInfo("Incoming status: Deactivating follow incoming_status is ".
                                $relationship->incoming_status, __METHOD__.','.__LINE__);

                            $follow_dao->deactivate($this->instance->network_user_id, $user_to_check, 'instagram');
                        }
                    } catch (APICallLimitExceededException $e) {
                        $this->logger->logInfo($e->getMessage(), __METHOD__.','.__LINE__);
                        break;
                    }
                }
            } else {
                $continue_fetching = false;
            }
        }
    }
    /**
     *  Page back through followers starting at the beginning (cursor is null) or at cursor
     *  until either
     *      A. No more next cursor
     *          if A mark archive as loaded
     *      or B. out of API calls
     *          if B store followed_by_next_cursor for pickup next time
     * @param  str $followed_by_next_cursor
     * @return void
     */
    public function pageThroughFollowers($followed_by_next_cursor = null) {
        $this->logger->logInfo("Start paging through followers", __METHOD__.','.__LINE__);
        //Retrieve followers via the Instagram API
        $user_id = $this->instance->network_user_id;
        $network = ($user_id == $this->instance->network_user_id)?$this->instance->network:'instagram';

        $api_param = null;
        if (isset($followed_by_next_cursor)) {
            $api_param = array('next_cursor'=>$followed_by_next_cursor);
        }
        try {
            $this->logger->logInfo("Make api call", __METHOD__.','.__LINE__);
            $followers = $this->api_accessor->apiRequest('followers', $api_param);
        } catch (Exception $e) {
            $this->logger->logInfo(get_class($e). " Error fetching followers from Instagram API, error was ".
                $e->getMessage(), __METHOD__.','.__LINE__);
            return;
        }

        $follows_dao = DAOFactory::getDAO('FollowDAO');

        $continue = isset($followers);
        while ($continue) {
            foreach ($followers as $follower) {
                //store user in users table
                $this->fetchUser($follower->id, 'Followers', $follower->username, $follower->full_name,
                    $follower->profile_picture );

                //store relationships in follows table
                if ($follows_dao->followExists($user_id, $follower->id, $network)) {
                    // follow relationship already exists
                    $follows_dao->update($user_id, $follower->id, $network);
                } else {
                    // follow relationship does not exist yet
                    $follows_dao->insert($user_id, $follower->id, $network);
                }
            }
            $this->logger->logInfo("Done with ".$followers->count()." followers", __METHOD__.','.__LINE__);

            $next_cursor = $followers->getNext();
            $followers = null;
            if (isset($next_cursor)) {
                $this->logger->logInfo("Next cursor is set to ".$next_cursor, __METHOD__.','.__LINE__);
                //Persist the next cursor in the instance
                $this->instance->followed_by_next_cursor = $next_cursor;
                $api_param['cursor'] = $next_cursor;
                try {
                    $followers = $this->api_accessor->apiRequest('followers', $api_param);
                    if (!isset($followers) || $followers->count() == 0) {
                        $this->logger->logInfo("No followers returned, marking archive loaded",
                            __METHOD__.','.__LINE__);
                        $this->instance->is_archive_loaded_follows = true;
                        $this->instance->followed_by_next_cursor = null;
                        $continue = false;
                    }
                } catch (Exception $e) {
                    $this->instance->followed_by_next_cursor = $next_cursor;
                    $this->logger->logInfo("Stopping due to ".get_class($e)."; saving cursor ".$next_cursor,
                        __METHOD__.','.__LINE__);
                    $continue = false;
                }
            } else {
                $this->logger->logInfo("No next cursor available", __METHOD__.','.__LINE__);
                $this->instance->is_archive_loaded_follows = true;
                $this->instance->followed_by_next_cursor = null;
                $continue = false;
            }
        }
        $this->logger->logInfo("Done paging through followers", __METHOD__.','.__LINE__);
    }
    /**
     *  Page back through friends starting at the beginning (cursor is null) or at cursor
     *  until either
     *      A. No more next cursor
     *          if A mark next cursor null
     *      or B. out of API calls
     *          if B store follows_next_cursor for pickup next time
     * @param  str $follows_next_cursor
     * @return void
     */
    public function pageThroughFriends($follows_next_cursor = null) {
        $this->logger->logInfo("Start paging through friends", __METHOD__.','.__LINE__);
        //Retrieve friends via the Instagram API
        $user_id = $this->instance->network_user_id;
        $network = ($user_id == $this->instance->network_user_id)?$this->instance->network:'instagram';

        $api_param = null;
        if (isset($follows_next_cursor)) {
            $api_param = array('next_cursor'=>$follows_next_cursor);
        }
        try {
            $this->logger->logInfo("Make api call", __METHOD__.','.__LINE__);
            $friends = $this->api_accessor->apiRequest('friends', $api_param);
        } catch (Exception $e) {
            $this->logger->logInfo(get_class($e). " Error fetching followers from Instagram API, error was ".
                $e->getMessage(), __METHOD__.','.__LINE__);
            return;
        }

        $follows_dao = DAOFactory::getDAO('FollowDAO');

        $continue = isset($friends);
        while ($continue) {
            foreach ($friends as $friend) {
                //store user in users table
                $this->fetchUser($friend->id, 'Friends', $friend->username, $friend->full_name,
                    $friend->profile_picture );

                //store relationships in follows table
                if ($follows_dao->followExists($friend->id, $user_id, $network)) {
                    // follow relationship already exists
                    $follows_dao->update( $friend->id, $user_id, $network);
                } else {
                    // follow relationship does not exist yet
                    $follows_dao->insert($friend->id, $user_id, $network);
                }
            }
            $this->logger->logInfo("Done with ".$friends->count()." followers", __METHOD__.','.__LINE__);

            $next_cursor = $friends->getNext();
            $friends = null;
            if (isset($next_cursor)) {
                $this->logger->logInfo("Next cursor is set to ".$next_cursor, __METHOD__.','.__LINE__);
                //Persist the next cursor in the instance
                $this->instance->followed_by_next_cursor = $next_cursor;
                $api_param['cursor'] = $next_cursor;
                try {
                    $friends = $this->api_accessor->apiRequest('friends', $api_param);
                    if (!isset($friends) || $friends->count() == 0) {
                        $this->logger->logInfo("No friends returned, marking archive loaded",
                            __METHOD__.','.__LINE__);
                        $this->instance->is_archive_loaded_friends = true;
                        $this->instance->follows_next_cursor = null;
                        $continue = false;
                    }
                } catch (Exception $e) {
                    $this->instance->follows_next_cursor = $next_cursor;
                    $this->logger->logInfo("Stopping due to ".get_class($e)."; saving cursor ".$next_cursor,
                        __METHOD__.','.__LINE__);
                    $continue = false;
                }
            } else {
                $this->logger->logInfo("No next cursor available", __METHOD__.','.__LINE__);
                $this->instance->is_archive_loaded_follows = true;
                $this->instance->follows_next_cursor = null;
                $continue = false;
            }
        }
        $this->logger->logInfo("Done paging through friends", __METHOD__.','.__LINE__);
    }
    /**
     * Convert a collection of profile posts into ThinkUp posts and users
     * @param Object $posts
     * @param str $network The network for the post, always 'instagram'
     * @param bool $do_fetch_comments_likes Whether or not to also fetch likes and comments for the posts
     * @return Whether or not a post (or comment), user or like was written to storage
     */
    private function processPosts(Instagram\Collection\MediaCollection $posts, $network, $do_fetch_comments_likes=true){
        $thinkup_posts = array();
        $total_added_posts = 0;

        $thinkup_users = array();
        $total_added_users = 0;

        $thinkup_likes = array();
        $total_added_likes = 0;

        //efficiency control vars
        $must_process_likes = true;
        $must_process_comments = true;
        $post_comments_added = 0;
        $post_likes_added = 0;
        $comments_difference = false;
        $likes_difference = false;

        $post_dao = DAOFactory::getDAO('PostDAO');

        foreach ($posts as $index=>$p) {
            $post_id = $p->getId();
            $this->logger->logInfo("Beginning to process ".$post_id.", post ".($index+1)." of ".($posts->count()),
                __METHOD__.','.__LINE__);

            // stream can contain posts from multiple users.  get profile for this post
            $profile = $p->getUser();
            $is_protected = 0;
            //Get likes count
            $likes_count = $p->getLikesCount();
            $comments = $p->getComments();

            $post_in_storage = $post_dao->getPost($post_id, $network);

            //Figure out if we have to process likes and comments
            if (isset($post_in_storage)) {
                $this->logger->logInfo("Post ".$post_id. " already in storage", __METHOD__.','.__LINE__);
                if ($post_in_storage->favlike_count_cache >= $likes_count ) {
                    $must_process_likes = false;
                    $this->logger->logInfo("Already have ".$likes_count." like(s) for post ".$post_id.
                    "in storage; skipping like processing", __METHOD__.','.__LINE__);
                } else  {
                    $likes_difference = $likes_count - $post_in_storage->favlike_count_cache;
                    $this->logger->logInfo($likes_difference." new like(s) to process for post ".$post_id,
                    __METHOD__.','.__LINE__);
                }

                $comments_count = $comments->count();
                if ($comments_count > 0) {
                    if ($post_in_storage->reply_count_cache >= $comments_count) {
                        $must_process_comments = false;
                        $this->logger->logInfo("Already have ".$comments_count." comment(s) for post ".$post_id.
                        "; skipping comment processing", __METHOD__.','.__LINE__);
                    } else {
                        $comments_difference = $comments_count - $post_in_storage->reply_count_cache;
                        $this->logger->logInfo($comments_difference." new comment(s) of ".$comments_count.
                        " total to process for post ".$post_id, __METHOD__.','.__LINE__);
                    }
                }
            } else {
                $this->logger->logInfo("Post ".$post_id. " not in storage", __METHOD__.','.__LINE__);
            }
            // If we dont already have this photo
            if (!isset($post_in_storage)) {
                // Photos may be posted without a caption
                // Note that if you post a photo without a caption and then reply to it with the first comment instagram
                // treats this as your caption.
                if(strlen($p->getCaption()) >0 ) {
                    $text = $p->getCaption();
                } else {
                    $text = "";
                }

                $photo_to_process = array(
                  // Post details
                  "post_id"=>$post_id,
                  "author_username"=>$profile->getUserName(),
                  "author_fullname"=>$profile->getFullName(),
                  "author_avatar"=>$profile->getProfilePicture(),
                  "author_user_id"=>$profile->getId(),
                  "post_text"=> $text,
                  "pub_date"=>DateTime::createFromFormat('U', $p->getCreatedTime())->format('Y-m-d H:i'),
                  "favlike_count_cache"=>$likes_count,
                  "in_reply_to_user_id"=>'', // assume only one recipient
                  "in_reply_to_post_id"=>'',
                  "source"=>'',
                  'network'=>$network,
                  'is_protected'=>$is_protected,
                  'location'=>'',
                  // Photo details
                  'permalink'=>$p->getLink(),
                  'standard_resolution_url'=>$p->getStandardRes()->url,
                  'low_resolution_url'=>$p->getLowRes()->url,
                  'thumbnail_url'=>$p->getThumbnail()->url,
                  'filter'=>$p->getFilter(),
                  'is_short_video' => (($p->getType() == 'video')?1:0)
                );

                $new_photo_key = $this->storePhotoAndAuthor($photo_to_process, "Owner stream");

                if ($new_photo_key !== false ) {
                    $total_added_posts++;
                    $this->instance->last_post_id = $post_id;
                }
            } else { // post already exists in storage
                if ($must_process_likes) { //update its like count only
                    $post_dao->updateFavLikeCount($post_id, $network, $likes_count);
                    $this->logger->logInfo("Updated Like count for post ".$post_id . " to ". $likes_count,
                    __METHOD__.','.__LINE__);
                }
            }

            if ($do_fetch_comments_likes && $must_process_comments) {
                if ($comments->count() > 0) {
                    $comments_captured = 0;
                    $post_comments = $comments;
                    if ($post_comments->count() > 0) {
                        foreach ($post_comments as $c) {
                            $comment_id = $c->getId();
                            //only add to queue if not already in storage
                            $comment_in_storage = $post_dao->getPost($comment_id, $network);
                            if (!isset($comment_in_storage)) {
                                $comment_author = $c->getUser();
                                $comment_to_process = array(
                                    "post_id"=>$comment_id,
                                    "author_username"=>$comment_author->getUserName(),
                                    "author_fullname"=>$comment_author->getFullName(),
                                    "author_avatar"=>$comment_author->getProfilePicture(),
                                    "author_user_id"=>$comment_author->getId(),
                                    "post_text"=>$c->getText(),
                                    "pub_date"=>DateTime::createFromFormat('U',
                                     $p->getCreatedTime())->format('Y-m-d H:i'),
                                    "in_reply_to_user_id"=>$profile->getId(),
                                    "in_reply_to_post_id"=>$post_id,
                                    "source"=>'', 'network'=>$network,
                                    'is_protected'=>$is_protected,
                                    'location'=>''
                                );
                                array_push($thinkup_posts, $comment_to_process);
                                $comments_captured = $comments_captured + 1;
                            }
                        }
                    }
                    $post_comments_added = $post_comments_added +
                    $this->storePostsAndAuthors($thinkup_posts, "Post stream comments");

                    //free up memory
                    $thinkup_posts = array();

                    if (is_int($comments_difference) && $post_comments_added >= $comments_difference) {
                        $must_process_comments = false;
                        $this->logger->logInfo("Caught up on post ".$post_id."'s balance of ".
                        $comments_difference." comments; stopping comment processing", __METHOD__.','.__LINE__);
                    }
                }
                if ($post_comments_added > 0) { //let user know
                    $this->logger->logUserSuccess("Added ".$post_comments_added." comment(s) for post ". $post_id,
                    __METHOD__.','.__LINE__);
                } else {
                    $this->logger->logInfo("Added ".$post_comments_added." comment(s) for post ". $post_id,
                    __METHOD__.','.__LINE__);
                }
                $total_added_posts = $total_added_posts + $post_comments_added;
            }

            //process "likes"
            if ($do_fetch_comments_likes && $must_process_likes) {
                if ($likes_count > 0) {
                    $likes_captured = 0;
                    $post_likes = $p->getLikes();
                    $post_likes_count = $likes_count;
                    if ($post_likes_count > 0) {
                        foreach ($post_likes as $l) {
                            //Get users
                            $user_to_add = array(
                                "user_name"=>$l->getUserName(),
                                "full_name"=>$l->getFullName(),
                                "user_id"=>$l->getId(),
                                "avatar"=>$l->getProfilePicture(),
                                "description"=>'',
                                "url"=>'',
                                "is_protected"=>0,
                                "follower_count"=>0,
                                "post_count"=>0,
                                "joined"=>'',
                                "found_in"=>"Likes",
                                "network"=>'instagram'
                            ); //Users are always set to network=instagram
                            array_push($thinkup_users, $user_to_add);

                            $fav_to_add = array(
                                "favoriter_id"=>$l->getId(),
                                "network"=>$network,
                                "author_user_id"=>$profile->getId(),
                                "post_id"=>$post_id
                            );
                            array_push($thinkup_likes, $fav_to_add);
                            $likes_captured = $likes_captured + 1;
                        }
                    }

                    $total_added_users = $total_added_users + $this->calculateNumberOfUsersStored($thinkup_users,
                    "Likes");
                    $post_likes_added = $post_likes_added + $this->storeLikes($thinkup_likes);

                    //free up memory
                    $thinkup_users = array();
                    $thinkup_likes = array();

                    if (is_int($likes_difference) && $post_likes_added >= $likes_difference) {
                        $must_process_likes = false;
                        $this->logger->logInfo("Caught up on post ".$post_id."'s balance of ".
                        $likes_difference." likes; stopping like processing", __METHOD__.','.__LINE__);
                    }
                }
                $this->logger->logInfo("Added ".$post_likes_added." like(s) for post ".$post_id,
                __METHOD__.','.__LINE__);
                $total_added_likes = $total_added_likes + $post_likes_added;
            }
            //free up memory
            $thinkup_users = array();
            $thinkup_likes = array();
            //reset control vars for next post
            $must_process_likes = true;
            $must_process_comments = true;
            $post_comments_added = 0;
            $post_likes_added = 0;
            $comments_difference = false;
            $likes_difference = false;
        }

        $this->logger->logUserSuccess("On this set, captured ".$total_added_posts." post(s), ".
        $total_added_users." user(s) and ".$total_added_likes." like(s)", __METHOD__.','.__LINE__);
        //Return whether or not something new was written to storage
        if ($total_added_posts == 0 && $total_added_users == 0 && $total_added_likes == 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Store an array of posts made on instagram in the database
     * @param  arr $posts           An array of posts to store
     * @param  str $posts_source Where the posts came from e.g. instagram
     * @return int number of posts added
     */
    private function storePostsAndAuthors($posts, $posts_source){
        $total_added_posts = 0;
        $added_post = 0;
        foreach ($posts as $post) {
            $added_post_key = $this->storePostAndAuthor($post, $posts_source);
            if ($added_post !== false) {
                $this->logger->logInfo("Added post ID ".$post["post_id"]." on ".$post["network"].
                " for ".$post["author_username"].":".substr($post["post_text"],0, 20)."...", __METHOD__.','.__LINE__);
                $total_added_posts = $total_added_posts ++;
            } else  {
                $this->logger->logInfo("Didn't add post ".$post["post_id"]." on ".$post["network"].
                " for ".$post["author_username"].":".substr($post["post_text"],0, 20)."...", __METHOD__.','.__LINE__);
            }
            $added_post = 0;
        }
        return $total_added_posts;
    }
    /**
     * Store a post in the posts table and add the author to the users table
     * @param  arr $post  An array of posts and associated users
     * @param  str $post_source Where the posts were made e.g. instagram
     * @return int id of the row the post
     */
    private function storePostAndAuthor($post, $post_source){
        $post_dao = DAOFactory::getDAO('PostDAO');
        if (isset($post['author_user_id'])) {
            $user_object = $this->fetchUser($post['author_user_id'], $post_source, $post['author_username'],
                $post['author_fullname'], $post['author_avatar'], false);
            if (isset($user_object)) {
                $post["author_username"] = $user_object->username;
                $post["author_fullname"] = $user_object->full_name;
                $post["author_avatar"] = $user_object->avatar;
                $post["location"] = $user_object->location;
            }
        }
        $added_post_key = $post_dao->addPost($post);
        return $added_post_key;
    }
    /**
     * Store a photo into the photos table and add the author to the users table
     * @param  arr $photo        An array of photos to add to the database
     * @param  str $photo_source Where the post came from e.g. instagram
     * @return int id of the row of the photo
     */
    private function storePhotoAndAuthor($photo, $photo_source){
        $photo_dao = DAOFactory::getDAO('PhotoDAO');
        if (isset($photo['author_user_id'])) {
            $user_object = $this->fetchUser($photo['author_user_id'], $photo_source, $photo['author_username'],
                $photo['author_fullname'], $photo['author_avatar'], false);
            if (isset($user_object)) {
                $photo["author_username"] = $user_object->username;
                $photo["author_fullname"] = $user_object->full_name;
                $photo["author_avatar"] = $user_object->avatar;
                $photo["location"] = $user_object->location;
            }
        }
        $added_photo_key = $photo_dao->addPhoto($photo);
        return $added_photo_key;
    }
    /**
     * Counts how many users were added to the database
     * @param  arr $users An array of users for which to check if they were stored
     * @param  str $users_source Where the users were found
     * @return int Number of users from the input array that are in the users table
     */
    private function calculateNumberOfUsersStored($users, $users_source) {
        $added_users = 0;
        if (count($users) > 0) {
            foreach ($users as $user) {
                $user_object = $this->fetchUser($user['user_id'], $users_source, $user['user_name'],
                    $user['full_name'], $user['avatar']);
                if (isset($user_object)) {
                    $added_users = $added_users + 1;
                }
            }
        }
        return $added_users;
    }
    /**
     * Stores information about users who have liked posts in the favorites table
     * @param  arr $likes An array of posts which have been liked and details about the liker
     * @return int number of likes which were added
     */
    private function storeLikes($likes) {
        $added_likes = 0;
        if (count($likes) > 0) {
            $fav_dao = DAOFactory::getDAO('FavoritePostDAO');
            foreach ($likes as $like) {
                $added_likes = $added_likes + $fav_dao->addFavorite($like['favoriter_id'], $like);
            }
        }
        return $added_likes;
    }

    /**
     * Update profiles of users who are friends of the instance user, and haven't been checked in 2 days.
     * @return void
     */
    public function updateStaleFriendsProfiles() {
        if (!isset($this->user)) {
            //Force-refresh instance user in data store
            $this->user = self::fetchUser($this->instance->network_user_id, 'Owner info',
                $this->instance->network_username, null, null, true);
        }

        if (isset($this->user)) {
            //Get stalest friends
            $follow_dao = DAOFactory::getDAO('FollowDAO');
            $stalest_friends = $follow_dao->getStalestFriends($this->user->user_id, 'instagram', $number_days_old = 2,
                $limit = 25);
            $status_message = count($stalest_friends).' friends haven\'t been updated recently.';
            $this->logger->logInfo($status_message, __METHOD__.','.__LINE__);

            while (isset($stalest_friends) && count($stalest_friends) > 0) {
                try {
                    foreach ($stalest_friends as $user) {
                        $this->fetchUser($user->user_id, "Friends stale update", $user->username, $user->full_name,
                            $user->avatar, true);
                    }
                    $stalest_friends = $follow_dao->getStalestFriends($this->user->user_id, 'instagram',
                        $number_days_old = 2, $limit = 25);
                    $status_message = count($stalest_friends).' friends haven\'t been updated recently.';
                    $this->logger->logInfo($status_message, __METHOD__.','.__LINE__);
                } catch (APICallLimitExceededException $e) {
                    $this->logger->logInfo($e->getMessage(), __METHOD__.','.__LINE__);
                    break;
                }
            }
        }
    }
}
