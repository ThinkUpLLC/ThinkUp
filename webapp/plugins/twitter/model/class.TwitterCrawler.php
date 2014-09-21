<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/model/class.TwitterCrawler.php
 *
 * Copyright (c) 2009-2013 Gina Trapani
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
 * Twitter Crawler
 *
 * Retrieves tweets, replies, users, and following relationships from Twitter.com
 *
 * @TODO Complete docblocks
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class TwitterCrawler {
    /**
     * @var Instance ThinkUp network account instance, ie, @thinkupapp on Twitter
     */
    var $instance;
    /**
     * @var CrawlerTwitterAPIAccessorOAuth API accessor object
     */
    var $api;
    /**
     * @var User The instance network user
     */
    var $user;
    /**
     * @var UserDAO User data access object
     */
    var $user_dao;
    /**
     * @var Logger
     */
    var $logger;
    /*
     * @var array PluginOption objects
     */
    var $twitter_options;

    /**
     * Constructor
     * @param Instance $instance
     * @param CrawlerTwitterAPIAccessorOAuth $api
     * @return TwitterCrawler
     */
    public function __construct(Instance $instance, CrawlerTwitterAPIAccessorOAuth $api) {
        $this->instance = $instance;
        $this->api = $api;
        $this->logger = Logger::getInstance();
        $this->logger->setUsername($instance->network_username);
        $this->user_dao = DAOFactory::getDAO('UserDAO');
        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
        $this->twitter_options = $plugin_option_dao->getOptionsHash('twitter');
    }
    /**
     * Get owner user details and save them to the database.
     */
    public function fetchInstanceUserInfo() {
        $endpoint = $this->api->endpoints['show_user'];
        list($http_status, $payload) = $this->api->apiRequest($endpoint, array(), $this->instance->network_user_id);
        if ($http_status == 200) {
            $user = $this->api->parseJSONUser($payload);
            $this->user = new User($user, 'Owner Status');
            if (isset($this->user)) {
                $this->user_dao->updateUser($this->user);

                if (isset($this->user->follower_count) && $this->user->follower_count>0) {
                    $count_dao = DAOFactory::getDAO('CountHistoryDAO');
                    $count_dao->insert($this->user->user_id, 'twitter',
                    $this->user->follower_count, null, 'followers');
                }
                $this->logger->logUserSuccess("Successfully fetched ".$this->user->username.
                "'s details from Twitter. Twitter's tweet count is ". number_format($this->user->post_count) .".",
                __METHOD__.','.__LINE__);
            } else {
                $this->logger->logUserError("Twitter didn't return information for " .$this->user->username,
                __METHOD__.','.__LINE__);
                $this->logger->logError("Twitter payload: ".$payload, __METHOD__.','.__LINE__);
            }
        }
    }
    /**
     * Delete posts from the db if needed.
     */
    private function processDeletedTweets() {
        $endpoint = $this->api->endpoints['user_timeline'];
        $args = array('count'=>'100', 'include_rts'=>'true', 'screen_name'=>$this->user->username);
        try {
            list($http_status, $payload) = $this->api->apiRequest($endpoint, $args);
            $this->logger->logInfo("Checking for deleted tweets", __METHOD__.','.__LINE__);
            if ($http_status == 200) {
                $count = 0;
                $tweets = $this->api->parseJSONTweets($payload);
                $tweets_array = array();
                $last_id = 0;
                foreach($tweets as $tweet) {
                    $tweets_array[$tweet['post_id'] . ''] =  $tweet;
                    $last_id = $tweet['post_id'];
                }
                $post_dao = DAOFactory::getDAO('PostDAO');
                $db_posts = $post_dao->getAllPosts($this->instance->network_user_id, 'twitter',
                count($tweets), 1,true, 'pub_date', 'DESC', false);

                foreach($db_posts as $post) {
                    if (!isset($tweets_array[ $post->post_id . '']) ) {
                        // verify this tweet does not exists
                        $endpoint = $this->api->endpoints['show_tweet'];
                        try {
                            list($http_status, $tweet_data) = $this->api->apiRequest($endpoint, array(), $post->post_id,
                            true, true);
                        } catch (APICallLimitExceededException $e) {
                            $this->logger->logInfo($e->getMessage(), __METHOD__.','.__LINE__);
                        }
                        if ($http_status == 404) {
                            $this->logger->logInfo( "Deleting post: " . $post->post_id . ' ' . $post->post_text,
                            __METHOD__.','.__LINE__);
                            $post_dao->deletePost($post->id);
                            $this->instance->total_posts_in_system--;
                        } else {
                            $this->logger->logError( "Not deleting post, still exists on Twitter, or non 404 status: "
                            . $http_status .  ' - ' . $post->post_id . ' ' . $post->post_text, __METHOD__.','.__LINE__);
                        }
                    }
                }
            } else {
                $this->logger->logError("Unable to fetch tweets for deletion accounting", __METHOD__.','.__LINE__);
            }
        } catch (APICallLimitExceededException $e) {
            $this->logger->logInfo($e->getMessage(), __METHOD__.','.__LINE__);
        }
    }
    /**
     * Capture the current instance users's tweets and store them in the database.
     */
    public function fetchInstanceUserTweets() {
        if (!isset($this->user)) {
            $this->fetchInstanceUserInfo();
        }
        if (isset($this->user)) {
            // check for deletes
            if ($this->instance->total_posts_in_system >= $this->user->post_count) {
                $this->processDeletedTweets();
                return;
            }
            $status_message = "";
            $continue_fetching = true;
            $this->logger->logInfo("Twitter user post count:  " . $this->user->post_count .
            " and ThinkUp post count: "  . $this->instance->total_posts_in_system, __METHOD__.','.__LINE__);

            // Set up endpoint and unchanging args
            $endpoint = $this->api->endpoints['user_timeline'];
            $args = array();
            $count_arg =  (isset($this->twitter_options['tweet_count_per_call']))?
            $this->twitter_options['tweet_count_per_call']->option_value:100;
            $args["count"] = $count_arg;
            $args["include_rts"] = "true";
            $args["screen_name"] = $this->user->username;

            $max_id = "";
            //have we fetching latest tweets with no max_id once?
            $got_latest_tweets = false;
            //are we fetching the archive using the max_id?
            $fetching_archive = false;
            while ($this->user->post_count > $this->instance->total_posts_in_system && $continue_fetching) {
                if ($got_latest_tweets) {
                    $max_id = $this->instance->last_post_id;
                    if ($max_id !== "") {
                        $args["max_id"] = $max_id;
                        $fetching_archive = true;
                    }
                }

                try {
                    list($http_status, $payload) = $this->api->apiRequest($endpoint, $args);
                    if ($http_status == 200) {
                        $count = 0;
                        $tweets = $this->api->parseJSONTweets($payload);

                        $post_dao = DAOFactory::getDAO('PostDAO');
                        $new_username = false;
                        $link_dao = DAOFactory::getDAO('LinkDAO');
                        foreach ($tweets as $tweet) {
                            $tweet['network'] = 'twitter';

                            $inserted_post_key = $post_dao->addPost($tweet, $this->user, $this->logger);
                            if ( $inserted_post_key !== false) {
                                $count = $count + 1;
                                $this->instance->total_posts_in_system = $this->instance->total_posts_in_system + 1;
                                // Expand and insert links contained in tweet
                                $extracted_urls = Post::extractURLs($tweet['post_text']);
                                $urls = array();
                                // Skip over URLs where we are extracting image media
                                foreach ($extracted_urls as $url) {
                                    $add_url = true;
                                    if (!empty($tweet['photos'])) {
                                        foreach ($tweet['photos'] as $media) {
                                            if ($media->display_url == $url || $media->url == $url) {
                                                $add_url = false;
                                                continue;
                                            }
                                        }
                                    }
                                    if ($add_url) {
                                        $urls[] = $url;
                                    }
                                }
                                if (count($urls)) {
                                    URLProcessor::processPostURLs($tweet['post_text'], $tweet['post_id'], 'twitter',
                                        $this->logger, $urls);
                                }
                                if (!empty($tweet['photos'])) {
                                    foreach ($tweet['photos'] as $photo) {
                                        $link = new Link(array(
                                            'url' => $photo->url,
                                            'expanded_url' => $photo->expanded_url,
                                            'image_src' => $photo->media_url,
                                            'post_key' => $inserted_post_key
                                        ));
                                        try {
                                            $link_dao->insert($link);
                                            $this->logger->logSuccess("Inserted $photo->url into links table",
                                                __METHOD__.','.__LINE__);
                                        } catch (DuplicateLinkException $e) {
                                            $this->logger->logInfo($photo->url." already exists in links table",
                                                __METHOD__.','.__LINE__);
                                        } catch (DataExceedsColumnWidthException $e) {
                                            $this->logger->logInfo($photo->url."data exceeds table column width",
                                                __METHOD__.','.__LINE__);
                                        }
                                    }
                                }
                            }

                            if ($this->instance->last_post_id == "" || $fetching_archive) {
                                $this->instance->last_post_id = $tweet['post_id'];
                            }
                        }
                        $got_latest_tweets = true;

                        if (count($tweets) > 0 || $count > 0) {
                            $status_message .= ' ' . count($tweets)." tweet(s) found and $count saved";
                            $this->logger->logUserSuccess($status_message, __METHOD__.','.__LINE__);
                            $status_message = "";
                        } else {
                            $continue_fetching = false;
                        }

                        //if you've got more than the Twitter API archive limit, stop looking for more tweets
                        if ($this->instance->total_posts_in_system >= $this->api->archive_limit) {
                            $continue_fetching = false;
                            $overage_info = "Twitter only makes ".number_format($this->api->archive_limit).
                            " tweets available, so some of the oldest ones may be missing.";
                        } else {
                            $overage_info = "";
                        }
                        if ($this->user->post_count == $this->instance->total_posts_in_system) {
                            $this->instance->is_archive_loaded_tweets = true;
                            $continue_fetching = false;
                        }

                        if ($max_id !== "" && $this->instance->last_post_id !== ""
                        && $max_id == $this->instance->last_post_id) {
                            $continue_fetching = false;
                        }
                    } else {
                        $continue_fetching = false;
                    }
                } catch (APICallLimitExceededException $e) {
                    $this->logger->logInfo($e->getMessage(), __METHOD__.','.__LINE__);
                    break;
                }
            }
            $status_message .= number_format($this->instance->total_posts_in_system)." tweets are in ThinkUp; ".
            $this->user->username ." has ". number_format($this->user->post_count)." tweets according to Twitter.";
            $this->logger->logUserInfo($status_message, __METHOD__.','.__LINE__);
            if (isset($overage_info) && $overage_info != '') {
                $this->logger->logUserError($overage_info, __METHOD__.','.__LINE__);
            }

            if ($this->instance->total_posts_in_system >= $this->user->post_count) {
                $status_message = "All of ".$this->user->username. "'s tweets are in ThinkUp.";
                $this->logger->logUserSuccess($status_message, __METHOD__.','.__LINE__);
            }

            if (isset($this->user->username) && $this->user->username != $this->instance->network_username) {
                // User has changed their username, so update instance and posts data
                $instance_dao = DAOFactory::getDAO('InstanceDAO');
                $instance_dao->updateUsername($this->instance->id,$this->user->username);
                $post_dao = DAOFactory::getDAO('PostDAO');
                $post_dao->updateAuthorUsername($this->instance->network_user_id, 'twitter', $this->user->username);
            }
        }
    }
    /**
     * Fetch a replied-to tweet and add it and any URLs it contains to the database.
     * @param int $tid
     * @throws APICallLimitExceededException
     */
    private function fetchAndAddTweetRepliedTo($tid) {
        if (!isset($this->user)) {
            $this->fetchInstanceUserInfo();
        }
        if (isset($this->user)) {
            $endpoint = $this->api->endpoints['show_tweet'];
            list($http_status, $payload) = $this->api->apiRequest($endpoint, array(), $tid);
            $status_message = "";
            if ($http_status == 200) {
                $tweet = $this->api->parseJSONTweet($payload);

                $post_dao = DAOFactory::getDAO('PostDAO');

                $user_replied_to = new User($tweet, 'replies');
                $this->user_dao->updateUser($user_replied_to);
                $inserted_post_key = $post_dao->addPost($tweet, $user_replied_to, $this->logger);
                if ($inserted_post_key !== false) {
                    $status_message = 'Added replied to tweet ID '.$tid." to database.";
                    URLProcessor::processPostURLs($tweet['post_text'], $tweet['post_id'], 'twitter', $this->logger);
                }
            } elseif ($http_status == 404 || $http_status == 403) {
                $e = $this->api->parseJSONError($payload);
                $posterror_dao = DAOFactory::getDAO('PostErrorDAO');
                $posterror_dao->insertError($tid, 'twitter', $http_status, $e['error'], $this->user->user_id);
                $status_message = 'Error saved to tweets.';
            }
            $this->logger->logInfo($status_message, __METHOD__.','.__LINE__);
        }
    }
    /**
     * Fetch the current instance user's mentions from Twitter and store in the database.
     * Detect whether or not a mention is a retweet and store as such.
     */
    public function fetchInstanceUserMentions() {
        if (!isset($this->user)) {
            $this->fetchInstanceUserInfo();
        }
        if (isset($this->user)) {
            $status_message = "";

            $endpoint = $this->api->endpoints['mentions'];
            $args = array();
            $count_arg =  (isset($this->twitter_options['tweet_count_per_call']))?
            $this->twitter_options['tweet_count_per_call']->option_value:100;
            $args["count"] = $count_arg;

            $got_newest_mentions = false;
            $continue_fetching = true;
            while ($continue_fetching) {
                if ($got_newest_mentions && $this->instance->last_reply_id !== "") {
                    $args['max_id'] = $this->instance->last_reply_id;
                }

                try {
                    list($http_status, $payload) = $this->api->apiRequest($endpoint, $args);
                    if ($http_status > 200) {
                        $continue_fetching = false;
                    } else {
                        $count = 0;
                        $tweets = $this->api->parseJSONTweets($payload);
                        if (count($tweets) == 0 && $got_newest_mentions) {// you're paged back and no new tweets
                            $continue_fetching = false;
                            $this->instance->is_archive_loaded_mentions = true;
                            $status_message = 'Paged back but not finding new mentions; moving on.';
                            $this->logger->logInfo($status_message, __METHOD__.','.__LINE__);
                            $status_message = "";
                        }

                        $post_dao = DAOFactory::getDAO('PostDAO');
                        $mention_dao = DAOFactory::getDAO('MentionDAO');
                        if (!isset($recentTweets)) {
                            $recentTweets = $post_dao->getAllPosts($this->user->user_id, 'twitter', 100);
                        }
                        $count = 0;
                        foreach ($tweets as $tweet) {
                            // Figure out if the mention is a retweet
                            if (RetweetDetector::isRetweet($tweet['post_text'], $this->user->username)) {
                                $this->logger->logInfo("Retweet found, ".substr($tweet['post_text'], 0, 50).
                                    "... ", __METHOD__.','.__LINE__);
                                // if did find retweet, add in_rt_of_user_id info
                                // even if can't find original post id
                                $tweet['in_rt_of_user_id'] = $this->user->user_id;
                                $originalTweetId = RetweetDetector::detectOriginalTweet($tweet['post_text'],
                                $recentTweets);
                                if ($originalTweetId != false) {
                                    $tweet['in_retweet_of_post_id'] = $originalTweetId;
                                    $this->logger->logInfo("Retweet original status ID found: ".$originalTweetId,
                                    __METHOD__.','.__LINE__);
                                }
                            }
                            $inserted_post_key = $post_dao->addPost($tweet, $this->user, $this->logger);
                            if ( $inserted_post_key !== false ) {
                                $count++;
                                //expand and insert links contained in tweet
                                URLProcessor::processPostURLs($tweet['post_text'], $tweet['post_id'], 'twitter',
                                $this->logger);
                                if ($tweet['user_id'] != $this->user->user_id) {
                                    //don't update owner info from reply
                                    $u = new User($tweet, 'mentions');
                                    $this->user_dao->updateUser($u);
                                }
                                $mention_dao->insertMention($this->user->user_id, $this->user->username,
                                $tweet['post_id'], $tweet['author_user_id'], 'twitter');
                            }

                            if ($this->instance->last_reply_id == "" || $got_newest_mentions) {
                                $this->instance->last_reply_id = $tweet['post_id'];
                            }
                        }
                        if ($got_newest_mentions) {
                            if ( $count > 0) {
                                $status_message .= count($tweets)." mentions past reply ID ".
                                $this->instance->last_reply_id." and $count saved";
                                $this->logger->logUserSuccess($status_message, __METHOD__.','.__LINE__);
                                $status_message = "";
                            }
                        } else {
                            if ($count == 0) {
                                $status_message = "No new mentions found.";
                                $this->logger->logUserInfo($status_message, __METHOD__.','.__LINE__);
                            } else {
                                $status_message .= count($tweets)." mentions found and $count saved";
                                $this->logger->logUserSuccess($status_message, __METHOD__.','.__LINE__);
                            }
                            $status_message = "";
                        }

                        $got_newest_mentions = true;

                        if ($got_newest_mentions && $this->instance->is_archive_loaded_replies) {
                            $continue_fetching = false;
                            $status_message .= 'Retrieved newest mentions; Archive loaded; Stopping reply fetch.';
                            $this->logger->logInfo($status_message, __METHOD__.','.__LINE__);
                            $status_message = "";
                        }

                        if (isset($args['max_id']) && $args['max_id'] !== "" && $this->instance->last_reply_id !== ""
                        && $args['max_id'] == $this->instance->last_reply_id) {
                            $continue_fetching = false;
                        }
                    }
                } catch (APICallLimitExceededException $e) {
                    $this->logger->logInfo($e->getMessage(), __METHOD__.','.__LINE__);
                    break;
                }
            }
        }
    }
    /**
     * Retrieve recent retweets of the instance user and add them to the database.
     */
    public function fetchRetweetsOfInstanceUser() {
        if (!isset($this->user)) {
            $this->fetchInstanceUserInfo();
        }
        if (isset($this->user)) {
            $status_message = "";
            $endpoint = $this->api->endpoints['retweets_of_me'];
            $args = array();
            $args['include_entities'] = 'false';
            $args['include_user_entities'] = 'false';
            try {
                list($http_status, $payload) = $this->api->apiRequest($endpoint, $args);
                if ($http_status == 200) {
                    $tweets = $this->api->parseJSONTweets($payload);
                    $continue = true;
                    foreach ($tweets as $tweet) {
                        if ($continue) {
                            try {
                                $this->fetchStatusRetweets($tweet);
                            } catch (APICallLimitExceededException $e) {
                                $this->logger->logInfo($e->getMessage(), __METHOD__.','.__LINE__);
                                $continue = false;
                            }
                        }
                    }
                }
            } catch (APICallLimitExceededException $e) {
                $this->logger->logInfo($e->getMessage(), __METHOD__.','.__LINE__);
            }
        }
    }
    /**
     * Retrieve retweets of given status
     * @param array $status
     * @throws APICallLimitExceededException
     */
    private function fetchStatusRetweets($status) {
        $status_id = $status["post_id"];
        $status_message = "";
        $endpoint = $this->api->endpoints['retweeted_by'];
        list($http_status, $payload) = $this->api->apiRequest($endpoint, array(), $status_id);
        if ($http_status == 200) {
            $tweets = $this->api->parseJSONTweets($payload);
            $post_dao = DAOFactory::getDAO('PostDAO');
            foreach ($tweets as $tweet) {
                $user_with_retweet = new User($tweet, 'retweets');
                $tweet['network'] = 'twitter';
                $inserted_post_key = $post_dao->addPost($tweet, $user_with_retweet, $this->logger);
            }
        }
    }
    /**
     * Fetch instance users's followers by their User IDs
     */
    private function fetchInstanceUserFollowersByIDs() {
        $continue_fetching = true;
        $status_message = "";

        $updated_follow_count = 0;
        $inserted_follow_count = 0;

        if ($continue_fetching) {
            $this->logger->logUserSuccess("Starting to fetch followers...", __METHOD__.','.__LINE__);
        }
        $follow_dao = DAOFactory::getDAO('FollowDAO');

        while ( $continue_fetching) {
            $args = array();
            $endpoint = $this->api->endpoints['followers_ids'];
            if (!isset($next_cursor)) {
                $next_cursor = -1;
            }
            $args['cursor'] = strval($next_cursor);
            $args['stringify_ids'] = 'true';

            try {
                list($http_status, $payload) = $this->api->apiRequest($endpoint, $args);
                if ($http_status > 200) {
                    $continue_fetching = false;
                } else {
                    $status_message = "Parsing JSON. ";
                    $status_message .= "Cursor ".$next_cursor.":";
                    $ids = $this->api->parseJSONIDs($payload);
                    $next_cursor = $this->api->getNextCursor();
                    $status_message .= count($ids)." follower IDs queued to update. ";
                    $this->logger->logInfo($status_message, __METHOD__.','.__LINE__);
                    $status_message = "";
                    if (count($ids) == 0) {
                        $this->instance->is_archive_loaded_follows = true;
                        $continue_fetching = false;
                    }
                    foreach ($ids as $id) {
                        // add/update follow relationship
                        if ($follow_dao->followExists($this->instance->network_user_id, $id['id'], 'twitter')) {
                            //update it
                            if ($follow_dao->update($this->instance->network_user_id, $id['id'], 'twitter')) {
                                $updated_follow_count = $updated_follow_count + 1;
                            }
                        } else {
                            // insert it
                            if ($follow_dao->insert($this->instance->network_user_id, $id['id'], 'twitter')) {
                                $inserted_follow_count = $inserted_follow_count + 1;
                            }
                        }
                    }
                    $this->logger->logSuccess("Cursor at ".strval($next_cursor), __METHOD__.','.__LINE__);
                }
                if ($updated_follow_count > 0 || $inserted_follow_count > 0 ){
                    $status_message = $updated_follow_count ." follower(s) updated; ". $inserted_follow_count.
                        " new follow(s) inserted.";
                    $this->logger->logUserSuccess($status_message, __METHOD__.','.__LINE__);
                }
            } catch (APICallLimitExceededException $e) {
                $this->logger->logInfo($e->getMessage(), __METHOD__.','.__LINE__);
                break;
            }
        }
    }
    /**
     * Fetch instance user's followers: Page back only if more than 2% of follows are missing from database
     */
    public function fetchInstanceUserFollowers() {
        if (!isset($this->user)) {
            $this->fetchInstanceUserInfo();
        }

        if (isset($this->user) ) {
            $status_message = "";
            if ($this->instance->is_archive_loaded_follows) { //all pages have been loaded
                $this->logger->logInfo("Follower archive marked as loaded", __METHOD__.','.__LINE__);

                //find out how many new follows owner has compared to what's in db
                $new_follower_count = $this->user->follower_count - $this->instance->total_follows_in_system;
                $status_message = "New follower count is ".number_format($this->user->follower_count).
                " and ThinkUp has ". number_format($this->instance->total_follows_in_system).
                "; ".number_format($new_follower_count)." follows to update.";
                $this->logger->logUserInfo($status_message, __METHOD__.','.__LINE__);

                if ($new_follower_count > 0) {
                    $this->logger->logInfo("Fetching follows via IDs", __METHOD__.','.__LINE__);
                    $this->fetchInstanceUserFollowersByIDs();
                }
            } else {
                $this->logger->logInfo("Follower archive is not loaded; fetch should begin.", __METHOD__.','.__LINE__);
            }

            // Fetch follower pages
            $continue_fetching = true;
            $updated_follow_count = 0;
            $inserted_follow_count = 0;

            $follow_dao = DAOFactory::getDAO('FollowDAO');;

            while ($continue_fetching && !$this->instance->is_archive_loaded_follows) {
                $endpoint = $this->api->endpoints['followers'];
                $args = array();
                $args['skip_status'] = 'true';
                $args['include_entities'] = 'false';
                if (!isset($next_cursor)) {
                    $next_cursor = -1;
                }
                $args['cursor'] = strval($next_cursor);

                try {
                    list($http_status, $payload) = $this->api->apiRequest($endpoint, $args);
                    if ($http_status > 200) {
                        $continue_fetching = false;
                    } else {
                        $status_message = "Parsing JSON. ";
                        $status_message .= "Cursor ".$next_cursor.":";
                        $users = $this->api->parseJSONUsers($payload);
                        $next_cursor = $this->api->getNextCursor();
                        $status_message .= count($users)." followers queued to update. ";
                        $this->logger->logInfo($status_message, __METHOD__.','.__LINE__);
                        $status_message = "";

                        if (count($users) == 0) {
                            $this->instance->is_archive_loaded_follows = true;
                        }

                        foreach ($users as $u) {
                            $user_to_update = new User($u, 'Follows');
                            $this->user_dao->updateUser($user_to_update);
                            // add/update follow relationship
                            $does_follow_exist =
                            $follow_dao->followExists($this->instance->network_user_id, $user_to_update->user_id,
                            'twitter');
                            if ($does_follow_exist) {
                                //update it
                                if ($follow_dao->update($this->instance->network_user_id, $user_to_update->user_id,
                                'twitter'))
                                $updated_follow_count++;
                            } else {
                                //insert it
                                if ($follow_dao->insert($this->instance->network_user_id, $user_to_update->user_id,
                                'twitter'))
                                $inserted_follow_count++;
                            }
                        }
                        $this->logger->logSuccess("Cursor at ".strval($next_cursor), __METHOD__.','.__LINE__);
                    }
                    if ($updated_follow_count > 0 || $inserted_follow_count > 0 ){
                        $status_message = $updated_follow_count ." follower(s) updated; ". $inserted_follow_count.
                        " new follow(s) inserted.";
                        $this->logger->logUserSuccess($status_message, __METHOD__.','.__LINE__);
                    }
                } catch (APICallLimitExceededException $e) {
                    $this->logger->logInfo($e->getMessage(), __METHOD__.','.__LINE__);
                    $continue_fetching = false;
                    break;
                }
            }
        }
    }

    /** Find group memberships that are probably no longer active, verify, and
     * deactivate or update
     */
    public function updateStaleGroupMemberships() {
        if (!isset($this->user)) {
            $this->fetchInstanceUserInfo();
        }
        if (isset($this->user) ) {
            $status_message = "";
            $updated_group_member_count = 0;

            $group_member_dao = DAOFactory::getDAO('GroupMemberDAO');
            $continue = true;
            while ($continue ) {
                $stale_membership = $group_member_dao->findStalestMemberships($this->instance->network_user_id,
                'twitter');
                if (empty($stale_membership)) {
                    break;
                }
                $endpoint = $this->api->endpoints['check_group_member'];
                $args = array();
                $args['list_id'] = $stale_membership->group_id;
                $args['user_id'] = $this->instance->network_user_id;

                try {
                    list($http_status, $payload) = $this->api->apiRequest($endpoint, $args);
                } catch (APICallLimitExceededException $e) {
                    $this->logger->logInfo($e->getMessage(), __METHOD__.','.__LINE__);
                    break;
                }

                if ($http_status == 404) {
                    $status_message = sprintf('User, %s, no longer active on twitter list, %s',
                    $this->instance->network_username, $stale_membership->group_name);
                    $this->logger->logInfo($status_message, __METHOD__.','.__LINE__);
                    $group_member_dao->deactivate($this->instance->network_user_id, $stale_membership->group_id,
                    'twitter');
                    $updated_group_member_count++;
                } elseif ($http_status > 200) {
                    break;
                } else {
                    $status_message = sprintf('Updating active member, %s, on twitter list, %s',
                    $this->instance->network_username, $stale_membership->group_name);
                    $this->logger->logInfo($status_message, __METHOD__.','.__LINE__);
                    $group_member_dao->update($this->instance->network_user_id, $stale_membership->group_id, 'twitter');
                    $updated_group_member_count++;
                }
            }
            if ($updated_group_member_count > 0) {
                $count_history_dao = DAOFactory::getDAO('CountHistoryDAO');
                $status_message = $updated_group_member_count ." group membership(s) updated";
                $this->logger->logUserSuccess($status_message, __METHOD__.','.__LINE__);
                // update current number of active list membershps
                $count_history_dao->updateGroupMembershipCount($this->instance->network_user_id, 'twitter');
            }
        }
    }

    /**
     * Fetch instance user's lists: Page back only if more than 2% of follows are missing from database
     */
    public function fetchInstanceUserGroups() {
        if (!isset($this->user)) {
            $this->fetchInstanceUserInfo();
        }

        if (isset($this->user) ) {
            $status_message = "";

            // Fetch follower pages
            $continue_fetching = true;
            $updated_group_member_count = 0;
            $inserted_group_member_count = 0;

            $group_dao = DAOFactory::getDAO('GroupDAO');
            $group_member_dao = DAOFactory::getDAO('GroupMemberDAO');
            // $group_owner_dao = DAOFactory::getDAO('GroupOwnerDAO');
            $count_history_dao = DAOFactory::getDAO('CountHistoryDAO');

            while ($continue_fetching) {

                $endpoint = $this->api->endpoints['groups'];
                $args = array();
                if (!isset($next_cursor)) {
                    $next_cursor = -1;
                }
                $args['cursor'] = strval($next_cursor);

                try {
                    list($http_status, $payload) = $this->api->apiRequest($endpoint, $args);
                } catch (APICallLimitExceededException $e) {
                    $this->logger->logInfo($e->getMessage(), __METHOD__.','.__LINE__);
                    break;
                }

                if ($http_status > 200) {
                    $continue_fetching = false;
                } else {

                    $status_message = "Parsing JSON. ";
                    $status_message .= "Cursor ".$next_cursor.":";
                    $groups = $this->api->parseJSONLists($payload);
                    $next_cursor = $this->api->getNextCursor();
                    if (!isset($next_cursor)) {
                        $continue_fetching = false;
                    }
                    $status_message .= count($groups)." groups queued to update. ";
                    $this->logger->logInfo($status_message, __METHOD__.','.__LINE__);
                    $status_message = "";

                    foreach ($groups as $group_vals) {
                        $group_vals['is_active'] = 1;
                        $group_vals['last_seen'] = date('Y-m-d H:i:s');
                        $group_vals['first_seen'] = date('Y-m-d H:i:s');
                        $group = new Group($group_vals);
                        // if group exists, update it; otherwise, insert group
                        $group_dao->updateOrInsertGroup($group);

                        // add/update group membership/ownership
                        if ($group_member_dao->isGroupMemberInStorage($this->instance->network_user_id,
                        $group->group_id, 'twitter')) {
                            if ($group_member_dao->update($this->instance->network_user_id, $group->group_id,
                            'twitter')) {
                            $updated_group_member_count++;
                            }
                        } else {
                            if ($group_member_dao->insert($this->instance->network_user_id, $group->group_id,
                            'twitter')) {
                            $inserted_group_member_count++;
                            }
                        }
                    }
                    $this->logger->logSuccess("Cursor at ".strval($next_cursor), __METHOD__.','.__LINE__);
                }
            }
            if ($updated_group_member_count > 0 || $inserted_group_member_count > 0 ) {
                $status_message = $updated_group_member_count ." group membership(s) updated; ".
                $inserted_group_member_count. " new group membership(s) inserted.";
                $this->logger->logUserSuccess($status_message, __METHOD__.','.__LINE__);
            }
            // update current number of active list membershps
            $count_history_dao->updateGroupMembershipCount($this->instance->network_user_id, 'twitter');
        }
    }

    /**
     * Fetch the instance user's friends' tweets.
     */
    public function fetchInstanceUserFriends() {
        if (!isset($this->user)) {
            $this->fetchInstanceUserInfo();
        }

        if (isset($this->user)) {
            $follow_dao = DAOFactory::getDAO('FollowDAO');
            $this->instance->total_friends_in_system = $follow_dao->countTotalFriends($this->instance->network_user_id,
            'twitter');

            if ($this->instance->total_friends_in_system < $this->user->friend_count) {
                $this->instance->is_archive_loaded_friends = false;
                $this->logger->logUserInfo($this->instance->total_friends_in_system." friends in system, ".
                $this->user->friend_count." friends according to Twitter; Friend archive is not loaded",
                __METHOD__.','.__LINE__);
            } else {
                $this->instance->is_archive_loaded_friends = true;
                $this->logger->logInfo("Friend archive loaded", __METHOD__.','.__LINE__);
            }

            $status_message = "";
            // Fetch friend pages
            $continue_fetching = true;
            $updated_follow_count = 0;
            $inserted_follow_count = 0;

            while ($continue_fetching && !$this->instance->is_archive_loaded_friends) {
                $endpoint = $this->api->endpoints['following'];
                $args = array();
                if (!isset($next_cursor)) {
                    $next_cursor = -1;
                }
                $args['cursor'] = strval($next_cursor);
                $args['skip_status'] = 'true';
                $args['include_user_entities'] = 'false';

                try {
                    list($http_status, $payload) = $this->api->apiRequest($endpoint, $args);
                } catch (APICallLimitExceededException $e) {
                    $this->logger->logInfo($e->getMessage(), __METHOD__.','.__LINE__);
                    break;
                }

                if ($http_status > 200) {
                    $continue_fetching = false;
                } else {
                    $status_message = "Parsing JSON. ";
                    $status_message .= "Cursor ".$next_cursor.":";
                    $users = $this->api->parseJSONUsers($payload);
                    $next_cursor = $this->api->getNextCursor();
                    $status_message .= count($users)." friends queued to update. ";
                    $this->logger->logInfo($status_message, __METHOD__.','.__LINE__);
                    $status_message = "";

                    if (count($users) == 0)
                    $this->instance->is_archive_loaded_friends = true;

                    foreach ($users as $u) {
                        $user_to_update = new User($u, 'Friends');
                        $this->user_dao->updateUser($user_to_update);

                        // add/update follow relationship
                        $does_follow_exist = $follow_dao->followExists($user_to_update->user_id,
                        $this->instance->network_user_id, 'twitter');
                        if ($does_follow_exist) {
                            //update it
                            if ($follow_dao->update($user_to_update->user_id, $this->instance->network_user_id,
                            'twitter'))
                            $updated_follow_count++;
                        } else {
                            //insert it
                            if ($follow_dao->insert($user_to_update->user_id, $this->instance->network_user_id,
                            'twitter'))
                            $inserted_follow_count++;
                        }
                    }

                    $this->logger->logSuccess("Cursor at ".strval($next_cursor), __METHOD__.','.__LINE__);
                }
            }
            if ($updated_follow_count > 0 || $inserted_follow_count > 0 ){
                $status_message = $updated_follow_count ." people ".$this->instance->network_username.
                " follows updated; ". $inserted_follow_count." new follow(s) inserted.";
                $this->logger->logUserSuccess($status_message, __METHOD__.','.__LINE__);
            }
        }
    }
    /**
     * Fetch stray replied-to tweets.
     */
    public function fetchStrayRepliedToTweets() {
        if (!isset($this->user)) {
            $this->fetchInstanceUserInfo();
        }
        if (isset($this->user)) {
            $post_dao = DAOFactory::getDAO('PostDAO');
            $strays = $post_dao->getStrayRepliedToPosts($this->user->user_id, $this->user->network);
            $status_message = count($strays).' stray replied-to tweets to load for user ID '.
            $this->user->user_id . ' on '.$this->user->network;
            $this->logger->logInfo($status_message, __METHOD__.','.__LINE__);
            foreach ($strays as $s) {
                try {
                    $this->fetchAndAddTweetRepliedTo($s['in_reply_to_post_id']);
                } catch (APICallLimitExceededException $e) {
                    $this->logger->logInfo($e->getMessage(), __METHOD__.','.__LINE__);
                    break;
                }
            }
        }
    }
    /**
     * Fetch unloaded follower details.
     */
    public function fetchUnloadedFollowerDetails() {
        if (!isset($this->user)) {
            $this->fetchInstanceUserInfo();
        }
        if (isset($this->user)) {
            $follow_dao = DAOFactory::getDAO('FollowDAO');
            $strays = $follow_dao->getUnloadedFollowerDetails($this->user->user_id, 'twitter');
            $status_message = count($strays).' unloaded follower details to load.';
            $this->logger->logInfo($status_message, __METHOD__.','.__LINE__);

            foreach ($strays as $s) {
                try {
                    $this->fetchAndAddUser($s['follower_id'], "Follower IDs");
                } catch (APICallLimitExceededException $e) {
                    $this->logger->logInfo($e->getMessage(), __METHOD__.','.__LINE__);
                    break;
                }
            }
        }
    }
    /**
     * Fetch instance user friends by user IDs.
     * @param int $uid
     * @param FollowDAO $follow_dao
     */
    private function fetchUserFriendsByIDs($uid, $follow_dao) {
        $continue_fetching = true;
        $status_message = "";
        while ($continue_fetching) {
            $args = array();
            $endpoint = $this->api->endpoints['following_ids'];
            if (!isset($next_cursor)) {
                $next_cursor = -1;
            }
            $args['cursor'] = strval($next_cursor);
            $args['user_id'] = strval($uid);

            try {
                list($http_status, $payload) = $this->api->apiRequest($endpoint, $args);
            } catch (APICallLimitExceededException $e) {
                $this->logger->logInfo($e->getMessage(), __METHOD__.','.__LINE__);
                break;
            }

            if ($http_status > 200) {
                $continue_fetching = false;
            } else {
                $status_message = "Parsing JSON. ";
                $status_message .= "Cursor ".$next_cursor.":";
                $ids = $this->api->parseJSONIDs($payload);
                $next_cursor = $this->api->getNextCursor();
                $status_message .= count($ids)." friend IDs queued to update. ";
                $this->logger->logInfo($status_message, __METHOD__.','.__LINE__);
                $status_message = "";

                if (count($ids) == 0) {
                    $continue_fetching = false;
                }

                $updated_follow_count = 0;
                $inserted_follow_count = 0;
                foreach ($ids as $id) {
                    // add/update follow relationship
                    if ($follow_dao->followExists($id['id'], $uid, 'twitter')) {
                        //update it
                        if ($follow_dao->update($id['id'], $uid, 'twitter')) {
                            $updated_follow_count++;
                        }
                    } else {
                        //insert it
                        if ($follow_dao->insert($id['id'], $uid, 'twitter')) {
                            $inserted_follow_count++;
                        }
                    }
                }
                $status_message .= "$updated_follow_count existing follows updated; ".$inserted_follow_count.
                " new follows inserted.";
                $this->logger->logUserInfo($status_message, __METHOD__.','.__LINE__);
            }
        }
    }
    /**
     * Fetch user from Twitter and add to DB
     * @param int $fid
     * @param str $source Place where user was found
     * @throws APICallLimitExceededException
     */
    private function fetchAndAddUser($fid, $source) {
        $status_message = "";
        $endpoint = $this->api->endpoints['show_user'];
        list($http_status, $payload) = $this->api->apiRequest($endpoint, array(), $fid);
        if ($http_status == 200) {
            $user_arr = $this->api->parseJSONUser($payload);
            if (isset($user_arr)) {
                $user = new User($user_arr, $source);
                $this->user_dao->updateUser($user);
                $status_message = 'Added/updated user '.$user->username." in database";
            }
        } elseif ($http_status == 404) {
            $e = $this->api->parseJSONError($payload);
            $usererror_dao = DAOFactory::getDAO('UserErrorDAO');
            $usererror_dao->insertError($fid, $http_status, $e['error'], $this->user->user_id, 'twitter');
            $status_message = 'User error saved.';
        }
        $this->logger->logInfo($status_message, __METHOD__.','.__LINE__);
        $status_message = "";
    }
    /**
     * For each API call left, grab oldest follow relationship, check if it exists, and update table.
     */
    public function cleanUpFollows() {
        $follow_dao = DAOFactory::getDAO('FollowDAO');
        $continue_fetching = true;
        while ( $continue_fetching) {
            $old_follow = $follow_dao->getOldestFollow('twitter');
            if ($old_follow != null) {
                $endpoint = $this->api->endpoints['show_friendship'];
                $args = array();
                $args["source_id"] = $old_follow["followee_id"];
                $args["target_id"] = $old_follow["follower_id"];

                $debug_api_call = $http_status;
                try {
                    $this->logger->logInfo("Checking stale follow last seen ".$old_follow["last_seen"],
                        __METHOD__.','.__LINE__);
                    list($http_status, $payload) = $this->api->apiRequest($endpoint, $args);
                    if ($http_status == 200) {
                        $friendship = $this->api->parseJSONRelationship($payload);
                        if ($friendship['source_follows_target'] == 'true') {
                            $this->logger->logInfo("Updating follow last seen date: ".$args["source_id"]." follows ".
                                $args["target_id"], __METHOD__.','.__LINE__);
                            $follow_dao->update($old_follow["followee_id"], $old_follow["follower_id"], 'twitter',
                                true, $debug_api_call);
                        } else {
                            $this->logger->logInfo("Deactivating follow: ".$args["source_id"]." does not follow ".
                                $args["target_id"], __METHOD__.','.__LINE__);
                            $follow_dao->deactivate($old_follow["followee_id"], $old_follow["follower_id"], 'twitter',
                                $debug_api_call);
                        }
                        if ($friendship['target_follows_source'] == 'true') {
                            $this->logger->logInfo("Updating follow last seen date: ".$args["target_id"]." follows ".
                                $args["source_id"], __METHOD__.','.__LINE__);
                            $follow_dao->update($old_follow["follower_id"], $old_follow["followee_id"], 'twitter',
                                true, $debug_api_call);
                        } else {
                            $this->logger->logInfo("Deactivating follow: ".$args["target_id"]." does not follow ".
                            $args["source_id"], __METHOD__.','.__LINE__);
                            $follow_dao->deactivate($old_follow["follower_id"], $old_follow["followee_id"], 'twitter',
                                $debug_api_call);
                        }
                    } else {
                        $this->logger->logError("Got non-200 response for " .$endpoint->getShortPath(),
                            __METHOD__.','.__LINE__);
                        $error_code = $this->api->parseJSONErrorCodeAPI($payload);
                        if ($http_status == 403 && $error_code['error'] == 163) {
                            $this->logger->logError("Marking follow inactive due to 403 Source User Not Found ".
                                "error response with API 163 error", __METHOD__.','.__LINE__);
                            // deactivate in both directions
                            $follow_dao->deactivate($old_follow["followee_id"], $old_follow["follower_id"], 'twitter',
                                $debug_api_call);
                            $follow_dao->deactivate($old_follow["follower_id"], $old_follow["followee_id"], 'twitter',
                                $debug_api_call);
                        }
                        if ($http_status == 404) {
                            $this->logger->logError("Marking follow inactive due to 404 response",
                                __METHOD__.','.__LINE__);
                            // deactivate in both directions
                            $follow_dao->deactivate($old_follow["followee_id"], $old_follow["follower_id"], 'twitter',
                                $debug_api_call);
                            $follow_dao->deactivate($old_follow["follower_id"], $old_follow["followee_id"], 'twitter',
                                $debug_api_call);
                        }
                    }
                } catch (APICallLimitExceededException $e) {
                    $this->logger->logInfo($e->getMessage(), __METHOD__.','.__LINE__);
                    break;
                }
            } else {
                $continue_fetching = false;
            }
        }
    }
    /**
     * Fetch instance user's favorites since the last favorite stored.
     */
    public function fetchInstanceUserFavorites() {
        if (!isset($this->user)) {
            $this->fetchInstanceUserInfo();
        }

        $this->logger->logUserInfo("Checking for new favorites.", __METHOD__.','.__LINE__);

        $last_fav_id = $this->instance->last_favorite_id;
        $this->logger->logInfo("Owner favs: " . $this->user->favorites_count . ", instance owner favs in system: ".
        $this->instance->owner_favs_in_system, __METHOD__.','.__LINE__);

        $continue = true;
        while ($continue) {
            list($tweets, $http_status, $payload) = $this->getFavorites($last_fav_id);
            if ($http_status == 200) {
                if (sizeof($tweets) == 0) {
                    // then done -- this should happen when we have run out of favs
                    $this->logger->logInfo("It appears that we have run out of favorites to process",
                    __METHOD__.','.__LINE__);
                    $continue = false;
                } else {
                    $post_dao = DAOFactory::getDAO('FavoritePostDAO');
                    $fav_count = 0;
                    foreach ($tweets as $tweet) {
                        $tweet['network'] = 'twitter';
                        if ($post_dao->addFavorite($this->user->user_id, $tweet) > 0) {
                            URLProcessor::processPostURLs($tweet['post_text'], $tweet['post_id'], 'twitter',
                            $this->logger);
                            $this->logger->logInfo("Found new fav: " . $tweet['post_id'], __METHOD__.','.__LINE__);
                            $fav_count++;
                            $this->logger->logInfo("Fav count: $fav_count", __METHOD__.','.__LINE__);
                            $this->logger->logInfo("Added favorite: ". $tweet['post_id'], __METHOD__.','.__LINE__);
                        } else {
                            // fav was already stored, so take no action. This could happen both because some
                            // of the favs on the given page were processed last time, or because a separate process,
                            // such as a UserStream process, is also watching for and storing favs.
                            //$status_message = "Have already stored fav ". $tweet['post_id'];
                            //$this->logger->logDebug($status_message, __METHOD__.','.__LINE__);
                        }

                        // keep track of the highest fav id we've encountered
                        if ($tweet['post_id'] > $last_fav_id) {
                            $last_fav_id = $tweet['post_id'];
                        }
                    } // end foreach
                }
            } else {
                $continue = false;
            }
        }
    }
    /**
     * This helper method returns the parsed favorites from a given favorites page.
     * @param int $since_id
     * @return array ($tweets, $http_status, $payload)
     */
    private function getFavorites($since_id) {
        $endpoint = $this->api->endpoints['favorites'];
        $args = array();
        $args["screen_name"] = $this->user->username;
        $args["include_entities"] = "false";
        $args["count"] = 100;
        if ($since_id != "") {
            $args["since_id"] = $since_id;
        }
        //init vars
        $tweets = null;
        $http_status = null;
        $payload = null;
        try {
            list($http_status, $payload) = $this->api->apiRequest($endpoint, $args);
            if ($http_status == 200) {
                // Parse the JSON file
                $tweets = $this->api->parseJSONTweets($payload);
                if (!(isset($tweets) && sizeof($tweets) == 0) && $tweets == null) { // arghh, empty array evals to null
                    $this->logger->logInfo("in getFavorites; could not extract any tweets from response",
                    __METHOD__.','.__LINE__);
                    throw new Exception("could not extract any tweets from response");
                }
            }
        } catch (APICallLimitExceededException $e) {
            $this->logger->logInfo($e->getMessage(), __METHOD__.','.__LINE__);
        }
        return array($tweets, $http_status, $payload);
    }
    /**
     * Retrieve tweets in search results for a keyword/hashtag.
     * @param InstanceHashtag $instance_hashtag
     * @return void
     */
    public function fetchInstanceHashtagTweets($instance_hashtag) {
        if (isset($this->instance)){
            $status_message = "";
            $continue_fetching = true;
            $since_id = 0;
            $max_id = 0;
            $instance_hashtag_dao = DAOFactory::getDAO('InstanceHashtagDAO');
            $post_dao = DAOFactory::getDAO('PostDAO');
            $user_dao = DAOFactory::getDAO('UserDAO');
            $hashtagpost_dao = DAOFactory::getDAO('HashtagPostDAO');
            $hashtag_dao = DAOFactory::getDAO('HashtagDAO');

            //Get hashtag
            $hashtag = $hashtag_dao->getHashtagByID($instance_hashtag->hashtag_id);

            while ($continue_fetching) {
                $endpoint = $this->api->endpoints['search_tweets'];
                $args = array();
                $args["q"] = $hashtag->hashtag;
                $count_arg =  (isset($this->twitter_options['tweet_count_per_call']))?
                $this->twitter_options['tweet_count_per_call']->option_value:100;
                $args["count"] = $count_arg;
                $args["include_entities"] = "true";

                if ($since_id == 0) {
                    $since_id = $instance_hashtag->last_post_id;
                }
                if ($since_id > 0) {
                    $args["since_id"] = $since_id;
                }
                if ($max_id > $since_id) {
                    $args["max_id"] = $max_id;
                }

                try {
                    list($http_status, $payload) = $this->api->apiRequest($endpoint, $args);
                } catch (APICallLimitExceededException $e) {
                    $this->logger->logInfo($e->getMessage(), __METHOD__.','.__LINE__);
                    break;
                }
                if ($http_status == 200) {
                    $this->logger->logDebug('Search tweets 200 '.$endpoint->getPath(),__METHOD__.','.__LINE__);
                    $count = 0;
                    $user_count = 0;
                    $tweets = $this->api->parseJSONTweetsFromSearch($payload);
                    foreach ($tweets as $tweet) {
                        $this->logger->logDebug('Processing '.$tweet['post_id'],__METHOD__.','.__LINE__);
                        $this->logger->logDebug('Processing '.Utils::varDumpToString($tweet),__METHOD__.','.__LINE__);
                        $inserted_post_key = $post_dao->addPost($tweet, $this->user, $this->logger);
                        //We need to check if post exists before add relationship between post and hashtag
                        if ( $post_dao->isPostInDB($tweet['post_id'],'twitter')) {
                            if (!$hashtagpost_dao->isHashtagPostInStorage($hashtag->id,$tweet['post_id'], 'twitter')) {
                                $count = $count + 1;
                                $hashtagpost_dao->insertHashtagPost($hashtag->hashtag, $tweet['post_id'], 'twitter');
                                $user = new User($tweet);
                                $rows_updated = $user_dao->updateUser($user);
                                if ($rows_updated > 0) {
                                    $user_count = $user_count + $rows_updated;
                                }
                                $this->logger->logDebug('User has been updated',__METHOD__.','.__LINE__);

                                if (isset($tweet['retweeted_post']) && isset($tweet['retweeted_post']['content'])) {
                                    $this->logger->logDebug('Retweeted post info set', __METHOD__.','.__LINE__);
                                    if (!$hashtagpost_dao->isHashtagPostInStorage($hashtag->id,
                                    $tweet['retweeted_post']['content']['post_id'], 'twitter')) {
                                        $this->logger->logDebug('Retweeted post not in storage',
                                        __METHOD__.','.__LINE__);
                                        $count++;
                                        $hashtagpost_dao->insertHashtagPost($hashtag->hashtag,
                                        $tweet['retweeted_post']['content']['post_id'], 'twitter');
                                        $user_retweet = new User($tweet['retweeted_post']['content']);
                                        $rows_retweet_updated = $user_dao->updateUser($user_retweet);
                                        if ($rows_retweet_updated > 0) {
                                            $user_count = $user_count + $rows_retweet_updated;
                                        }
                                    } else {
                                        $this->logger->logDebug('Retweeted post in storage',__METHOD__.','.__LINE__);
                                    }
                                } else {
                                    $this->logger->logDebug('Retweeted post info not set', __METHOD__.','.__LINE__);
                                }
                                $this->logger->logDebug('About to process URLs',__METHOD__.','.__LINE__);
                                URLProcessor::processPostURLs($tweet['post_text'], $tweet['post_id'], 'twitter',
                                $this->logger);
                                $this->logger->logDebug('URLs have been processed',__METHOD__.','.__LINE__);
                            }
                        }
                        if ($tweet['post_id'] > $instance_hashtag->last_post_id) {
                            $instance_hashtag->last_post_id = $tweet['post_id'];
                        }
                        if ($instance_hashtag->earliest_post_id == 0
                        || $tweet['post_id'] < $instance_hashtag->earliest_post_id) {
                            $instance_hashtag->earliest_post_id = $tweet['post_id'];
                        }
                        if ($max_id == 0 || $tweet['post_id'] < $max_id) {
                            $max_id = $tweet['post_id'];
                        }
                        $this->logger->logDebug('Instance hashtag markers updated',__METHOD__.','.__LINE__);
                    }

                    //Status message for tweets and users
                    $status_message = ' ' . count($tweets)." tweet(s) found and $count saved";
                    $this->logger->logUserSuccess($status_message, __METHOD__.','.__LINE__);
                    $status_message = ' ' . count($tweets)." tweet(s) found and $user_count users saved";
                    $this->logger->logUserSuccess($status_message, __METHOD__.','.__LINE__);

                    //Save instance_hashtag important values
                    if ($instance_hashtag->last_post_id > 0) {
                        $instance_hashtag_dao->updateLastPostID($instance_hashtag->instance_id,
                        $instance_hashtag->hashtag_id, $instance_hashtag->last_post_id);
                    }
                    if ($instance_hashtag->earliest_post_id > 0) {
                        $instance_hashtag_dao->updateEarliestPostID($instance_hashtag->instance_id,
                        $instance_hashtag->hashtag_id, $instance_hashtag->earliest_post_id);
                    }

                    //Not to continue fetching if search not return the maxim number of tweets
                    if  (count($tweets) < $count_arg) {
                        $continue_fetching = false;
                    }
                } else {
                    $status_message = "Stop fetching tweets. cURL_status = " . $cURL_status;
                    $this->logger->logUserSuccess($status_message, __METHOD__.','.__LINE__);
                    $continue_fetching = false;
                }
            }
        }
    }

    /**
     * Update profiles of users who are friends of the instance user, and haven't been checked in 1 day.
     * @return void
     */
    public function updateFriendsProfiles() {
        if (!isset($this->user)) {
            $this->fetchInstanceUserInfo();
        }
        if (isset($this->user)) {
            //Get stalest friends
            $follow_dao = DAOFactory::getDAO('FollowDAO');
            $stalest_friends = $follow_dao->getStalestFriends($this->user->user_id, 'twitter', $number_days_old = 1);
            $status_message = count($stalest_friends).' friends haven\'t been updated recently.';
            $this->logger->logInfo($status_message, __METHOD__.','.__LINE__);

            foreach ($stalest_friends as $user) {
                try {
                    $this->fetchAndAddUser($user->user_id, "Friends stale update");
                } catch (APICallLimitExceededException $e) {
                    $this->logger->logInfo($e->getMessage(), __METHOD__.','.__LINE__);
                    break;
                }
            }
        }
    }
}
