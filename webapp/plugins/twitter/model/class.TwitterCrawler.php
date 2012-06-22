<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/model/class.TwitterCrawler.php
 *
 * Copyright (c) 2009-2012 Gina Trapani
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
 * Twitter Crawler
 *
 * Retrieves tweets, replies, users, and following relationships from Twitter.com
 *
 * @TODO Complete docblocks
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2012 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
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
    public function __construct($instance, $api) {
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
        if ($this->api->available && $this->api->available_api_calls_for_crawler > 0) {
            $owner_profile = str_replace("[id]", $this->instance->network_user_id,
            $this->api->cURL_source['show_user']);
            list($cURL_status, $twitter_data) = $this->api->apiRequest($owner_profile);
            if ($cURL_status == 200) {
                $users = $this->api->parseXML($twitter_data);
                foreach ($users as $user) {
                    $this->user = new User($user, 'Owner Status');
                }
                if (isset($this->user)) {
                    $this->user_dao->updateUser($this->user);

                    if (isset($this->user->follower_count) && $this->user->follower_count>0) {
                        $fcount_dao = DAOFactory::getDAO('FollowerCountDAO');
                        $fcount_dao->insert($this->user->user_id, 'twitter',
                        $this->user->follower_count);
                    }
                    $this->logger->logUserSuccess("Successfully fetched ".$this->user->username.
                    "'s details from Twitter. Twitter's tweet count is ". $this->user->post_count,
                    __METHOD__.','.__LINE__);
                } else {
                    $this->logger->logUserError("Twitter didn't return information for "
                    .$this->user->username, __METHOD__.','.__LINE__);
                    $this->logger->logError("Twitter payload: ".$twitter_data, __METHOD__.','.__LINE__);
                }
            }
        }
    }
    /**
     * Store Twitter search results in the database.
     * @TODO Add user logging to this method once it has been reinstated (currently it's not in use)
     * @param str $term Term to search for
     */
    public function fetchSearchResults($term) {
        if (!isset($this->user)) {
            $this->fetchInstanceUserInfo();
        }
        if (isset($this->user)) {
            $continue_fetching = true;
            $page = 1;
            while ($continue_fetching) {
                $search_results = $this->api->cURL_source['search']."?q=".urlencode($term).
                "&result_type=recent&rpp=100&page=".$page;
                try {
                    list($cURL_status, $twitter_data) = $this->api->apiRequest($search_results, null, false);
                } catch (APICallLimitExceededException $e) {
                    break;
                }
                if ($cURL_status == 200) {
                    $tweets = $this->api->parseJSON($twitter_data);
                    $post_dao = DAOFactory::getDAO('PostDAO');
                    $count = 0;
                    foreach ($tweets as $tweet) {
                        $tweet['network'] = 'twitter';
                        $inserted_post_key = $post_dao->addPost($tweet);
                        if ($inserted_post_key !== false) {
                            $count = $count + 1;
                            URLProcessor::processPostURLs($tweet['post_text'], $tweet['post_id'], 'twitter',
                            $this->logger);

                            //don't update owner info from reply
                            if ($tweet['user_id'] != $this->user->user_id) {
                                $u = new User($tweet, 'mentions');
                                $this->user_dao->updateUser($u);
                            }
                        }
                    }
                    $this->logger->logInfo(count($tweets)." tweet(s) found and $count saved", __METHOD__.','
                    .__LINE__);
                    if ( $count == 0 ) { // all tweets on the page were already saved
                        //Stop fetching when more tweets have been retrieved than were saved b/c they already existed
                        $continue_fetching = false;
                    }
                    $page = $page+1;
                } else {
                    $continue_fetching = false;
                }
            }
        }
    }

    /**
     * Delete posts from the db if needed.
     */
    private function processDeletedTweets() {
        if ($this->api->available && $this->api->available_api_calls_for_crawler > 0) {
            $recent_tweets = str_replace("[id]", $this->user->username, $this->api->cURL_source['user_timeline']);
            list($cURL_status, $twitter_data) =
            $this->api->apiRequest($recent_tweets, array('count' => '100', 'include_rts' => 'true') );
            $this->logger->logInfo("Checking for deleted tweets", __METHOD__.','.__LINE__);
            if ($cURL_status == 200) {
                $count = 0;
                $tweets = $this->api->parseXML($twitter_data);
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
                        $show_tweet = str_replace("[id]", $post->post_id, $this->api->cURL_source['show_tweet']);
                        list($cURL_status, $tweet_data)
                        = $this->api->apiRequest($show_tweet, array(), true, true);
                        if ($cURL_status == 404) {
                            $this->logger->logInfo( "Deleting post: " . $post->post_id . ' ' . $post->post_text,
                            __METHOD__.','.__LINE__);
                            $post_dao->deletePost($post->id);
                            $this->instance->total_posts_in_system--;
                        } else {
                            $this->logger->logError( "Not deleting post, still exists on Twitter, or non 404 status: "
                            . $cURL_status .  ' - ' . $post->post_id . ' ' . $post->post_text, __METHOD__.','.__LINE__);
                        }
                    }
                }

            } else {
                $this->logger->logError("Unable to fetch tweets for deletion accounting", __METHOD__.','.__LINE__);
            }
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
            $got_latest_page_of_tweets = false;
            $continue_fetching = true;
            $this->logger->logInfo("Twitter user post count:  " . $this->user->post_count .
            " and ThinkUp post count: "  . $this->instance->total_posts_in_system, __METHOD__.','.__LINE__);
            while ($this->api->available && $this->api->available_api_calls_for_crawler > 0
            && $this->user->post_count > $this->instance->total_posts_in_system && $continue_fetching) {

                $recent_tweets = str_replace("[id]", $this->user->username,
                $this->api->cURL_source['user_timeline']);
                $args = array();
                $count_arg =  (isset($this->twitter_options['tweet_count_per_call']))?
                $this->twitter_options['tweet_count_per_call']->option_value:100;
                $args["count"] = $count_arg;
                $args["include_rts"] = "true";
                $last_page_of_tweets = round($this->api->archive_limit / $count_arg) + 1;

                //set page and since_id params for API call
                if ($got_latest_page_of_tweets
                && $this->user->post_count != $this->instance->total_posts_in_system
                && $this->instance->total_posts_in_system < $this->api->archive_limit) {
                    if ($this->instance->last_page_fetched_tweets < $last_page_of_tweets) {
                        $this->instance->last_page_fetched_tweets = $this->instance->last_page_fetched_tweets + 1;
                    } else {
                        $continue_fetching = false;
                        $this->instance->last_page_fetched_tweets = 0;
                    }
                    $args["page"] = $this->instance->last_page_fetched_tweets;
                } else {
                    if (!$got_latest_page_of_tweets && $this->instance->last_post_id > 0)
                    $args["since_id"] = $this->instance->last_post_id;
                }
                try {
                    list($cURL_status, $twitter_data) = $this->api->apiRequest($recent_tweets, $args);
                } catch (APICallLimitExceededException $e) {
                    break;
                }
                if ($cURL_status == 200) {
                    $count = 0;
                    $tweets = $this->api->parseXML($twitter_data);

                    $post_dao = DAOFactory::getDAO('PostDAO');
                    $new_username = false;
                    foreach ($tweets as $tweet) {
                        $tweet['network'] = 'twitter';

                        $inserted_post_key = $post_dao->addPost($tweet, $this->user, $this->logger);
                        if ( $inserted_post_key !== false) {
                            $count = $count + 1;
                            $this->instance->total_posts_in_system = $this->instance->total_posts_in_system + 1;
                            //expand and insert links contained in tweet
                            URLProcessor::processPostURLs($tweet['post_text'], $tweet['post_id'], 'twitter',
                            $this->logger);
                        }
                        if ($tweet['post_id'] > $this->instance->last_post_id)
                        $this->instance->last_post_id = $tweet['post_id'];
                    }
                    $status_message .= ' ' . count($tweets)." tweet(s) found and $count saved";
                    $this->logger->logUserSuccess($status_message, __METHOD__.','.__LINE__);
                    $status_message = "";

                    //if you've got more than the Twitter API archive limit, stop looking for more tweets
                    if ($this->instance->total_posts_in_system >= $this->api->archive_limit) {
                        $this->instance->last_page_fetched_tweets = 1;
                        $continue_fetching = false;
                        $overage_info = "Twitter only makes ".$this->api->archive_limit.
                        " tweets available, so some of the oldest ones may be missing.";
                    } else {
                        $overage_info = "";
                    }
                    if ($this->user->post_count == $this->instance->total_posts_in_system) {
                        $this->instance->is_archive_loaded_tweets = true;
                    }
                    $status_message .= $this->instance->total_posts_in_system." tweets are in ThinkUp; ".
                    $this->user->username ." has ". $this->user->post_count." tweets according to Twitter.";
                    $this->logger->logUserInfo($status_message, __METHOD__.','.__LINE__);
                    if ($overage_info != '') {
                        $this->logger->logUserError($overage_info, __METHOD__.','.__LINE__);
                    }
                    $got_latest_page_of_tweets = true;
                }
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
     */
    private function fetchAndAddTweetRepliedTo($tid) {
        if (!isset($this->user)) {
            $this->fetchInstanceUserInfo();
        }
        if (isset($this->user)) {
            $tweet_deets = str_replace("[id]", $tid, $this->api->cURL_source['show_tweet']);
            list($cURL_status, $twitter_data) = $this->api->apiRequest($tweet_deets);
            $status_message = "";

            if ($cURL_status == 200) {
                $tweets = $this->api->parseXML($twitter_data);
                $post_dao = DAOFactory::getDAO('PostDAO');
                foreach ($tweets as $tweet) {
                    $inserted_post_key = $post_dao->addPost($tweet, $this->user, $this->logger);
                    if ($inserted_post_key !== false) {
                        $status_message = 'Added replied to tweet ID '.$tid." to database.";
                        URLProcessor::processPostURLs($tweet['post_text'], $tweet['post_id'], 'twitter', $this->logger);
                    }
                }
            } elseif ($cURL_status == 404 || $cURL_status == 403) {
                $e = $this->api->parseError($twitter_data);
                $posterror_dao = DAOFactory::getDAO('PostErrorDAO');
                $posterror_dao->insertError($tid, 'twitter', $cURL_status, $e['error'], $this->user->user_id);
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
            if ($this->api->available_api_calls_for_crawler > 0) {
                $got_newest_mentions = false;
                $continue_fetching = true;
                while ($this->api->available && $this->api->available_api_calls_for_crawler > 0 &&
                $continue_fetching) {
                    $mentions = $this->api->cURL_source['mentions'];
                    $args = array();
                    $count_arg =  (isset($this->twitter_options['tweet_count_per_call']))?
                    $this->twitter_options['tweet_count_per_call']->option_value:100;
                    $args["count"] = $count_arg;
                    $args['include_rts']='true';

                    if ($got_newest_mentions) {
                        $this->instance->last_page_fetched_replies++;
                        $args['page'] = $this->instance->last_page_fetched_replies;
                    }

                    try {
                        list($cURL_status, $twitter_data) = $this->api->apiRequest($mentions, $args);
                    } catch (APICallLimitExceededException $e) {
                        break;
                    }

                    if ($cURL_status > 200) {
                        $continue_fetching = false;
                    } else {
                        $count = 0;
                        $tweets = $this->api->parseXML($twitter_data);
                        if (count($tweets) == 0 && $got_newest_mentions) {// you're paged back and no new tweets
                            $this->instance->last_page_fetched_replies = 1;
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
                        }
                        if ($got_newest_mentions) {
                            if ( $count > 0) {
                                $status_message .= count($tweets)." mentions on page ".
                                $this->instance->last_page_fetched_replies." and $count saved";
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
                    }
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
            if ($this->api->available && $this->api->available_api_calls_for_crawler > 0) {
                $rtsofme = $this->api->cURL_source['retweets_of_me'];
                list($cURL_status, $twitter_data) = $this->api->apiRequest($rtsofme);
                if ($cURL_status == 200) {
                    $tweets = $this->api->parseXML($twitter_data);
                    foreach ($tweets as $tweet) {
                        try {
                            $this->fetchStatusRetweets($tweet);
                        } catch (APICallLimitExceededException $e) {
                            break;
                        }
                    }
                }
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
        if ($this->api->available && $this->api->available_api_calls_for_crawler > 0) {
            $rts = str_replace("[id]", $status_id, $this->api->cURL_source['retweeted_by']);
            list($cURL_status, $twitter_data) = $this->api->apiRequest($rts);
            if ($cURL_status == 200) {
                $tweets = $this->api->parseXML($twitter_data);
                foreach ($tweets as $tweet) {
                    $user_with_retweet = new User($tweet, 'retweets');
                    $this->fetchUserTimelineForRetweet($status, $user_with_retweet);
                }
            }
        }
    }
    /**
     * Retrieve a retweeting user's timeline
     * @param array $retweeted_status
     * @param User $user_with_retweet
     * @throws APICallLimitExceededException
     */
    private function fetchUserTimelineForRetweet($retweeted_status, $user_with_retweet) {
        $retweeted_status_id = $retweeted_status["post_id"];
        $status_message = "";

        if ($this->api->available && $this->api->available_api_calls_for_crawler > 0) {
            $stream_with_retweet = str_replace("[id]", $user_with_retweet->username,
            $this->api->cURL_source['user_timeline']);
            $args = array();
            $count_arg =  (isset($this->twitter_options['tweet_count_per_call']))?
            $this->twitter_options['tweet_count_per_call']->option_value:100;
            $args['count'] = $count_arg;
            $args["include_rts"]="true";

            list($cURL_status, $twitter_data) = $this->api->apiRequest($stream_with_retweet, $args);

            if ($cURL_status == 200) {
                $count = 0;
                $tweets = $this->api->parseXML($twitter_data);

                if (count($tweets) > 0) {
                    $post_dao = DAOFactory::getDAO('PostDAO');
                    foreach ($tweets as $tweet) {
                        // The parser now processes native retweet information for posts (and includes the
                        // orig post in the parsed data if there was a RT). This method can now take advantage
                        // of this additional processing.
                        // If it was detected that this tweet was a native RT during parsing of the xml, the
                        // 'in_retweet_of_post_id' value should already be set. If it is not set, go through the
                        // usual procedure to try to find it.
                        // This is just an efficiency fix, since if 'in_retweet_of_post_id' *is* set, it's not
                        // going to be unset if the retweet detector doesn't pick up on anything.
                        if (!isset($tweet['in_retweet_of_post_id']) || !$tweet['in_retweet_of_post_id']) {
                            // then try to find rt -- otherwise, information already there
                            if (RetweetDetector::isRetweet($tweet['post_text'], $this->user->username)) {
                                $this->logger->logInfo("Retweet by ".$tweet['user_name']. " found, ".
                                substr($tweet['post_text'], 0, 50)."... ", __METHOD__.','.__LINE__);
                                if ( RetweetDetector::isRetweetOfTweet($tweet["post_text"],
                                $retweeted_status["post_text"]) ){
                                    $tweet['in_retweet_of_post_id'] = $retweeted_status_id;
                                    $this->logger->logInfo("Retweet by ".$tweet['user_name']." of ".
                                    $this->user->username." original status ID found: ".$retweeted_status_id,
                                    __METHOD__.','.__LINE__);
                                } else {
                                    $this->logger->logInfo("Retweet by ".$tweet['user_name']." of ".
                                    $this->user->username." original status ID NOT found: ".
                                    $retweeted_status["post_text"]." NOT a RT of: ". $tweet["post_text"],
                                    __METHOD__.','.__LINE__);
                                }
                            }
                        }
                        // an 'else' clause (if 'in_retweet_of_post_id' WAS set) can be used to log
                        // diagnostic information. Leaving in as example for now.
                        // else {
                        //     // $rtp = $tweet['retweeted_post']['content'];
                        //     $this->logger->logDebug("Post " . $tweet['post_id'] . //", " . $tweet['post_text'] .
                        //     " from " . $tweet['user_name'] .
                        //     " is rt of " . $tweet['in_retweet_of_post_id'],// . ", ". $rtp['post_text'],
                        //     __METHOD__.','.__LINE__);
                        // }
                        $inserted_post_key = $post_dao->addPost($tweet, $user_with_retweet, $this->logger);
                        if ( $inserted_post_key !== false) {
                            $count++;
                            //expand and insert links contained in tweet
                            URLProcessor::processPostURLs($tweet['post_text'], $tweet['post_id'], 'twitter',
                            $this->logger);
                            $this->user_dao->updateUser($user_with_retweet);
                        }
                    }
                    $this->logger->logInfo(count($tweets)." tweet(s) found in usertimeline via retweet for ".
                    $user_with_retweet->username." and $count saved", __METHOD__.','.__LINE__);
                }
            } elseif ($cURL_status == 401) { //not authorized to see user timeline
                //don't set API to unavailable just because a private user retweeted
                $this->api->available = true;
                $status_message .= 'Not authorized to see '.$user_with_retweet->username."'s timeline;moving on.";
                $this->logger->logInfo($status_message, __METHOD__.','.__LINE__);
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

        if ($this->api->available && $this->api->available_api_calls_for_crawler > 0 && $continue_fetching) {
            $this->logger->logUserSuccess("Starting to fetch followers. Please wait. Working...",
            __METHOD__.','.__LINE__);
        }

        while ($this->api->available && $this->api->available_api_calls_for_crawler > 0 && $continue_fetching) {
            $args = array();
            $follower_ids = $this->api->cURL_source['followers_ids'];
            if (!isset($next_cursor)) {
                $next_cursor = -1;
            }
            $args['cursor'] = strval($next_cursor);

            try {
                list($cURL_status, $twitter_data) = $this->api->apiRequest($follower_ids, $args);
            } catch (APICallLimitExceededException $e) {
                break;
            }

            if ($cURL_status > 200) {
                $continue_fetching = false;
            } else {
                $fd = DAOFactory::getDAO('FollowDAO');
                $status_message = "Parsing XML. ";
                $status_message .= "Cursor ".$next_cursor.":";
                $ids = $this->api->parseXML($twitter_data);
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
                    if ($fd->followExists($this->instance->network_user_id, $id['id'], 'twitter')) {
                        //update it
                        if ($fd->update($this->instance->network_user_id, $id['id'], 'twitter',
                        Utils::getURLWithParams($follower_ids, $args)))
                        $updated_follow_count = $updated_follow_count + 1;
                    } else {
                        //insert it
                        if ($fd->insert($this->instance->network_user_id, $id['id'], 'twitter',
                        Utils::getURLWithParams($follower_ids, $args)))
                        $inserted_follow_count = $inserted_follow_count + 1;
                    }
                }
                $this->logger->logSuccess("Cursor at ".strval($next_cursor), __METHOD__.','.__LINE__);
            }
        }
        if ($updated_follow_count > 0 || $inserted_follow_count > 0 ){
            $status_message = $updated_follow_count ." follower(s) updated; ". $inserted_follow_count.
            " new follow(s) inserted.";
            $this->logger->logUserSuccess($status_message, __METHOD__.','.__LINE__);
        }
    }
    /**
     * Fetch instance user's followers: Page back only if more than 2% of follows are missing from database
     */
    public function fetchInstanceUserFollowers() {
        if (!isset($this->user)) {
            $this->fetchInstanceUserInfo();
        }

        if (isset($this->user) && $this->api->available_api_calls_for_crawler > 0) {
            $status_message = "";
            if ($this->instance->is_archive_loaded_follows) { //all pages have been loaded
                $this->logger->logInfo("Follower archive marked as loaded", __METHOD__.','.__LINE__);

                //find out how many new follows owner has compared to what's in db
                $new_follower_count = $this->user->follower_count - $this->instance->total_follows_in_system;
                $status_message = "New follower count is ".$this->user->follower_count." and ThinkUp has ".
                $this->instance->total_follows_in_system."; ".$new_follower_count." new follows to load";
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

            while ($this->api->available && $this->api->available_api_calls_for_crawler > 0 && $continue_fetching
            && !$this->instance->is_archive_loaded_follows) {

                $follower_ids = $this->api->cURL_source['followers'];
                $args = array();
                if (!isset($next_cursor)) {
                    $next_cursor = -1;
                }
                $args['cursor'] = strval($next_cursor);

                list($cURL_status, $twitter_data) = $this->api->apiRequest($follower_ids, $args);

                if ($cURL_status > 200) {
                    $continue_fetching = false;
                } else {
                    $fd = DAOFactory::getDAO('FollowDAO');;
                    $status_message = "Parsing XML. ";
                    $status_message .= "Cursor ".$next_cursor.":";
                    $users = $this->api->parseXML($twitter_data);
                    $next_cursor = $this->api->getNextCursor();
                    $status_message .= count($users)." followers queued to update. ";
                    $this->logger->logInfo($status_message, __METHOD__.','.__LINE__);
                    $status_message = "";

                    if (count($users) == 0) {
                        $this->instance->is_archive_loaded_follows = true;
                    }

                    foreach ($users as $u) {
                        $utu = new User($u, 'Follows');
                        $this->user_dao->updateUser($utu);

                        // add/update follow relationship
                        if ($fd->followExists($this->instance->network_user_id, $utu->user_id, 'twitter')) {
                            //update it
                            if ($fd->update($this->instance->network_user_id, $utu->user_id, 'twitter',
                            Utils::getURLWithParams($follower_ids, $args)))
                            $updated_follow_count++;
                        } else {
                            //insert it
                            if ($fd->insert($this->instance->network_user_id, $utu->user_id, 'twitter',
                            Utils::getURLWithParams($follower_ids, $args)))
                            $inserted_follow_count++;
                        }
                    }
                    $this->logger->logSuccess("Cursor at ".strval($next_cursor), __METHOD__.','.__LINE__);
                }
            }
            if ($updated_follow_count > 0 || $inserted_follow_count > 0 ){
                $status_message = $updated_follow_count ." follower(s) updated; ". $inserted_follow_count.
                " new follow(s) inserted.";
                $this->logger->logUserSuccess($status_message, __METHOD__.','.__LINE__);
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
        if (isset($this->user) && $this->api->available_api_calls_for_crawler > 0) {
            $status_message = "";
            $updated_group_member_count = 0;

            $group_member_dao = DAOFactory::getDAO('GroupMemberDAO');

            while ($this->api->available && $this->api->available_api_calls_for_crawler > 0) {
                $stale_membership = $group_member_dao->findStalestMemberships($this->instance->network_user_id,
                'twitter');
                if (empty($stale_membership)) {
                    break;
                }
                $check_group_member = $this->api->cURL_source['check_group_member'];
                $args = array();
                $args['list_id'] = $stale_membership->group_id;
                $args['user_id'] = $this->instance->network_user_id;

                try {
                    list($cURL_status, $twitter_data) = $this->api->apiRequest($check_group_member, $args);
                } catch (APICallLimitExceededException $e) {
                    break;
                }

                if ($cURL_status == 404) {
                    $status_message = sprintf('User, %s, no longer active on twitter list, %s',
                    $this->instance->network_username, $stale_membership->group_name);
                    $this->logger->logInfo($status_message, __METHOD__.','.__LINE__);
                    $group_member_dao->deactivate($this->instance->network_user_id, $stale_membership->group_id,
                    'twitter');
                    $updated_group_member_count++;
                } elseif ($cURL_status > 200) {
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
                $group_member_count_dao = DAOFactory::getDAO('GroupMembershipCountDAO');
                $status_message = $updated_group_member_count ." group membership(s) updated";
                $this->logger->logUserSuccess($status_message, __METHOD__.','.__LINE__);
                // update current number of active list membershps
                $group_member_count_dao->updateCount($this->instance->network_user_id, 'twitter');
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

        if (isset($this->user) && $this->api->available_api_calls_for_crawler > 0) {
            $status_message = "";

            // Fetch follower pages
            $continue_fetching = true;
            $updated_group_member_count = 0;
            $inserted_group_member_count = 0;

            $group_dao = DAOFactory::getDAO('GroupDAO');
            $group_member_dao = DAOFactory::getDAO('GroupMemberDAO');
            // $group_owner_dao = DAOFactory::getDAO('GroupOwnerDAO');
            $group_member_count_dao = DAOFactory::getDAO('GroupMembershipCountDAO');

            while ($this->api->available && $this->api->available_api_calls_for_crawler > 0 && $continue_fetching) {

                $groups = $this->api->cURL_source['groups'];
                $args = array();
                if (!isset($next_cursor)) {
                    $next_cursor = -1;
                }
                $args['cursor'] = strval($next_cursor);

                try {
                    list($cURL_status, $twitter_data) = $this->api->apiRequest($groups, $args);
                } catch (APICallLimitExceededException $e) {
                    break;
                }

                if ($cURL_status > 200) {
                    $continue_fetching = false;
                } else {

                    $status_message = "Parsing XML. ";
                    $status_message .= "Cursor ".$next_cursor.":";
                    $groups = $this->api->parseXML($twitter_data);
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
            $group_member_count_dao->updateCount($this->instance->network_user_id, 'twitter');
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
            $fd = DAOFactory::getDAO('FollowDAO');
            $this->instance->total_friends_in_system = $fd->countTotalFriends($this->instance->network_user_id,
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

            while ($this->api->available && $this->api->available_api_calls_for_crawler > 0 && $continue_fetching
            && !$this->instance->is_archive_loaded_friends) {
                $friend_ids = $this->api->cURL_source['following'];
                $args = array();
                if (!isset($next_cursor)) {
                    $next_cursor = -1;
                }
                $args['cursor'] = strval($next_cursor);

                try {
                    list($cURL_status, $twitter_data) = $this->api->apiRequest($friend_ids, $args);
                } catch (APICallLimitExceededException $e) {
                    break;
                }

                if ($cURL_status > 200) {
                    $continue_fetching = false;
                } else {
                    $status_message = "Parsing XML. ";
                    $status_message .= "Cursor ".$next_cursor.":";
                    $users = $this->api->parseXML($twitter_data);
                    $next_cursor = $this->api->getNextCursor();
                    $status_message .= count($users)." friends queued to update. ";
                    $this->logger->logInfo($status_message, __METHOD__.','.__LINE__);
                    $status_message = "";

                    if (count($users) == 0)
                    $this->instance->is_archive_loaded_friends = true;

                    foreach ($users as $u) {
                        $utu = new User($u, 'Friends');
                        $this->user_dao->updateUser($utu);

                        // add/update follow relationship
                        if ($fd->followExists($utu->user_id, $this->instance->network_user_id, 'twitter')) {
                            //update it
                            if ($fd->update($utu->user_id, $this->instance->network_user_id, 'twitter',
                            Utils::getURLWithParams($friend_ids, $args)))
                            $updated_follow_count++;
                        } else {
                            //insert it
                            if ($fd->insert($utu->user_id, $this->instance->network_user_id, 'twitter',
                            Utils::getURLWithParams($friend_ids, $args)))
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
     * Fetch instance users's friends tweets and friends.
     */
    public function fetchFriendTweetsAndFriends() {
        if (!isset($this->user)) {
            $this->fetchInstanceUserInfo();
        }

        if (isset($this->user)) {
            $fd = DAOFactory::getDAO('FollowDAO');
            $post_dao = DAOFactory::getDAO('PostDAO');

            $continue_fetching = true;
            while ($this->api->available && $this->api->available_api_calls_for_crawler > 0 && $continue_fetching) {
                $stale_friend = $fd->getStalestFriend($this->user->user_id, 'twitter');
                if ($stale_friend != null) {
                    $this->logger->logInfo($stale_friend->username." is friend most in need of update",
                    __METHOD__.','.__LINE__);
                    $stale_friend_tweets = str_replace("[id]", $stale_friend->username,
                    $this->api->cURL_source['user_timeline']);
                    $args = array();
                    $count_arg =  (isset($this->twitter_options['tweet_count_per_call']))?
                    $this->twitter_options['tweet_count_per_call']->option_value:100;
                    $args['count'] = $count_arg;

                    if ($stale_friend->last_post_id > 0) {
                        $args['since_id'] = $stale_friend->last_post_id;
                    }

                    try {
                        list($cURL_status, $twitter_data) = $this->api->apiRequest($stale_friend_tweets, $args);
                    } catch (APICallLimitExceededException $e) {
                        break;
                    }

                    if ($cURL_status == 200) {
                        $count = 0;
                        $tweets = $this->api->parseXML($twitter_data);

                        if (count($tweets) > 0) {
                            $stale_friend_updated_from_tweets = false;
                            foreach ($tweets as $tweet) {
                                $inserted_post_key = $post_dao->addPost($tweet, $stale_friend, $this->logger);
                                if ( $inserted_post_key !== false) {
                                    $count++;
                                    //expand and insert links contained in tweet
                                    URLProcessor::processPostURLs($tweet['post_text'], $tweet['post_id'], 'twitter',
                                    $this->logger);
                                }
                                if (!$stale_friend_updated_from_tweets) {
                                    //Update stale_friend values here
                                    $stale_friend->full_name = $tweet['full_name'];
                                    $stale_friend->avatar = $tweet['avatar'];
                                    $stale_friend->location = $tweet['location'];
                                    $stale_friend->description = $tweet['description'];
                                    $stale_friend->url = $tweet['url'];
                                    $stale_friend->is_protected = $tweet['is_protected'];
                                    $stale_friend->follower_count = $tweet['follower_count'];
                                    $stale_friend->friend_count = $tweet['friend_count'];
                                    $stale_friend->post_count = $tweet['post_count'];
                                    $stale_friend->joined = date_format(date_create($tweet['joined']), "Y-m-d H:i:s");

                                    if ($tweet['post_id'] > $stale_friend->last_post_id) {
                                        $stale_friend->last_post_id = $tweet['post_id'];
                                    }
                                    $this->user_dao->updateUser($stale_friend);
                                    $stale_friend_updated_from_tweets = true;
                                }
                            }
                        } else {
                            $this->fetchAndAddUser($stale_friend->user_id, "Friends");
                        }

                        $this->logger->logInfo(count($tweets)." tweet(s) found for ".$stale_friend->username.
                        " and ". $count." saved", __METHOD__.','.__LINE__);
                        $this->fetchUserFriendsByIDs($stale_friend->user_id, $fd);
                    } elseif ($cURL_status == 401 || $cURL_status == 404) {
                        $e = $this->api->parseError($twitter_data);
                        $usererror_dao = DAOFactory::getDAO('UserErrorDAO');
                        $usererror_dao->insertError($stale_friend->user_id, $cURL_status,
                        (isset($e['error']))?$e['error']:$twitter_data,
                        $this->user->user_id, 'twitter');
                        $this->logger->logInfo('User error saved', __METHOD__.','.__LINE__);
                    }
                } else {
                    $this->logger->logInfo('No friend staler than 1 day', __METHOD__.','.__LINE__);
                    $continue_fetching = false;
                }
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
                if ($this->api->available && $this->api->available_api_calls_for_crawler > 0) {
                    try {
                        $this->fetchAndAddTweetRepliedTo($s['in_reply_to_post_id']);
                    } catch (APICallLimitExceededException $e) {
                        break;
                    }
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
            $fd = DAOFactory::getDAO('FollowDAO');
            $strays = $fd->getUnloadedFollowerDetails($this->user->user_id, 'twitter');
            $status_message = count($strays).' unloaded follower details to load.';
            $this->logger->logInfo($status_message, __METHOD__.','.__LINE__);

            foreach ($strays as $s) {
                if ($this->api->available && $this->api->available_api_calls_for_crawler > 0) {
                    try {
                        $this->fetchAndAddUser($s['follower_id'], "Follower IDs");
                    } catch (APICallLimitExceededException $e) {
                        break;
                    }

                }
            }
        }
    }
    /**
     * Fetch instance user friends by user IDs.
     * @param int $uid
     * @param FollowDAO $fd
     */
    private function fetchUserFriendsByIDs($uid, $fd) {
        $continue_fetching = true;
        $status_message = "";
        while ($this->api->available && $this->api->available_api_calls_for_crawler > 0 && $continue_fetching) {
            $args = array();
            $friend_ids = $this->api->cURL_source['following_ids'];
            if (!isset($next_cursor)) {
                $next_cursor = -1;
            }
            $args['cursor'] = strval($next_cursor);
            $args['user_id'] = strval($uid);

            list($cURL_status, $twitter_data) = $this->api->apiRequest($friend_ids, $args);

            if ($cURL_status > 200) {
                $continue_fetching = false;
            } else {
                $status_message = "Parsing XML. ";
                $status_message .= "Cursor ".$next_cursor.":";
                $ids = $this->api->parseXML($twitter_data);
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
                    if ($fd->followExists($id['id'], $uid, 'twitter')) {
                        //update it
                        if ($fd->update($id['id'], $uid, 'twitter', Utils::getURLWithParams($friend_ids, $args))) {
                            $updated_follow_count++;
                        }
                    } else {
                        //insert it
                        if ($fd->insert($id['id'], $uid, 'twitter', Utils::getURLWithParams($friend_ids, $args))) {
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
     */
    private function fetchAndAddUser($fid, $source) {
        $status_message = "";
        $u_deets = str_replace("[id]", $fid, $this->api->cURL_source['show_user']);
        list($cURL_status, $twitter_data) = $this->api->apiRequest($u_deets);

        if ($cURL_status == 200) {
            $user_arr = $this->api->parseXML($twitter_data);
            if (isset($user_arr[0])) {
                $user = new User($user_arr[0], $source);
                $this->user_dao->updateUser($user);
                $status_message = 'Added/updated user '.$user->username." in database";
            }
        } elseif ($cURL_status == 404) {
            $e = $this->api->parseError($twitter_data);
            $usererror_dao = DAOFactory::getDAO('UserErrorDAO');
            $usererror_dao->insertError($fid, $cURL_status, $e['error'], $this->user->user_id, 'twitter');
            $status_message = 'User error saved.';
        }
        $this->logger->logInfo($status_message, __METHOD__.','.__LINE__);
        $status_message = "";
    }
    /**
     * For each API call left, grab oldest follow relationship, check if it exists, and update table.
     */
    public function cleanUpFollows() {
        // first, check that we have the resources to do work
        if (!($this->api->available && $this->api->available_api_calls_for_crawler)) {
            $this->logger->logInfo("Terminating-- no API calls available", __METHOD__.','.__LINE__);
            return true;
        }

        $fd = DAOFactory::getDAO('FollowDAO');
        $continue_fetching = true;
        while ($this->api->available && $this->api->available_api_calls_for_crawler > 0 && $continue_fetching) {
            $oldfollow = $fd->getOldestFollow('twitter');
            if ($oldfollow != null) {
                $friendship_call = $this->api->cURL_source['show_friendship'];
                $args = array();
                $args["source_id"] = $oldfollow["followee_id"];
                $args["target_id"] = $oldfollow["follower_id"];

                try {
                    $this->logger->logInfo("Checking stale follow last seen ".$oldfollow["last_seen"],
                    __METHOD__.','.__LINE__);
                    list($cURL_status, $twitter_data) = $this->api->apiRequest($friendship_call, $args);
                } catch (APICallLimitExceededException $e) {
                    break;
                }

                if ($cURL_status == 200) {
                    $friendship = $this->api->parseXML($twitter_data);
                    if ($friendship['source_follows_target'] == 'true') {
                        $this->logger->logInfo("Updating follow last seen date: ".$args["source_id"]." follows ".
                        $args["target_id"], __METHOD__.','.__LINE__);
                        $fd->update($oldfollow["followee_id"], $oldfollow["follower_id"], 'twitter',
                        Utils::getURLWithParams($friendship_call, $args));
                    } else {
                        $this->logger->logInfo("Deactivating follow: ".$args["source_id"]." does not follow ".
                        $args["target_id"], __METHOD__.','.__LINE__);
                        $fd->deactivate($oldfollow["followee_id"], $oldfollow["follower_id"], 'twitter',
                        Utils::getURLWithParams($friendship_call, $args));
                    }
                    if ($friendship['target_follows_source'] == 'true') {
                        $this->logger->logInfo("Updating follow last seen date: ".$args["target_id"]." follows ".
                        $args["source_id"], __METHOD__.','.__LINE__);
                        $fd->update($oldfollow["follower_id"], $oldfollow["followee_id"], 'twitter',
                        Utils::getURLWithParams($friendship_call, $args));
                    } else {
                        $this->logger->logInfo("Deactivating follow: ".$args["target_id"]." does not follow ".
                        $args["source_id"], __METHOD__.','.__LINE__);
                        $fd->deactivate($oldfollow["follower_id"], $oldfollow["followee_id"], 'twitter',
                        Utils::getURLWithParams($friendship_call, $args));
                    }
                } else {
                    $this->logger->logError("Got non-200 response for " .
                    Utils::getURLWithParams($friendship_call, $args), __METHOD__.','.__LINE__);
                    if ($cURL_status == 404) {
                        $this->logger->logError("Marking follow inactive due to 404 response", __METHOD__.','.__LINE__);
                        // deactivate in both directions
                        $fd->deactivate($oldfollow["followee_id"], $oldfollow["follower_id"], 'twitter',
                        Utils::getURLWithParams($friendship_call, $args));
                        $fd->deactivate($oldfollow["follower_id"], $oldfollow["followee_id"], 'twitter',
                        Utils::getURLWithParams($friendship_call, $args));
                    }
                }
            } else {
                $continue_fetching = false;
            }
        }
    }

    /**
     * This method, and the two supporting private methods 'maintFavsFetch' and 'archivingFavsFetch', provide the
     * primary crawler functionality for adding the user's favorites to the database.
     * For a given user, the process starts in 'archiving mode', by
     * working forwards from the last (oldest) page of tweets to the newest.  This archiving crawl
     * is only done once.  The crawler tries to do this all in one go, but if it exhausts the available API count,
     * it will continue where it left off in the next run.
     * Then, when page 1 is reached in archiving mode, the crawler goes into 'maintenance mode' and works
     * backwards from then on.  It first pages back until
     * it has reached the last fav it previously processed.  Then it searches back N more pages to catch any older
     * tweets that were fav'd out of chronological order, where N is determined by favs_older_pages option.
     * The bookkeeping for these two crawler stages is maintained in the in tu_instances entry for the user.
     *
     * Recently, the Twitter favorites API has developed some bugs that need to be worked around.  The comments below
     * provide more detail, but in a nutshell, these methods can not currently use information from Twitter to
     * calculate loop termination (so a bit more work may be done than necessary), and do not currently remove un-fav'd
     * tweets from the database.  Hopefully these API issues will be fixed by Twitter in future.
     */
    public function fetchInstanceFavorites() {
        // first, check that we have the resources to do work
        if (!($this->api->available && $this->api->available_api_calls_for_crawler)) {
            $this->logger->logInfo("terminating fetchInstanceFavorites-- no API calls available",
            __METHOD__.','.__LINE__);
            return true;
        }

        $status_message = "";
        //@TODO Can we get this from API?
        $page_size = 20; // number of favs per page retrieved from the API call

        $this->logger->logUserInfo("Checking for new favorites.", __METHOD__.','.__LINE__);

        $last_favorites_count = $this->instance->favorites_profile;
        $this->logger->logInfo("last favs count: $last_favorites_count", __METHOD__.','.__LINE__);
        $last_page_fetched_favorites = $this->instance->last_page_fetched_favorites;
        $last_fav_id = $this->instance->last_favorite_id;
        $curr_favs_count = $this->user->favorites_count;
        $this->logger->logInfo("curr favs count: $curr_favs_count", __METHOD__.','.__LINE__);

        $last_page_of_favs = round($this->api->archive_limit / $page_size);

        // under normal circs the latter clause below should never hold, but due to a previously-existing
        // bug that could set a negative last_page_fetched_favorites value in the db in some cases,
        // it is necessary for recovery.
        if ($last_page_fetched_favorites == "" || $last_page_fetched_favorites < 0) {
            $last_page_fetched_favorites = 0;
        }
        $this->logger->logInfo("got last_page_fetched_favorites: $last_page_fetched_favorites",
        __METHOD__.','.__LINE__);
        if ($last_fav_id == "") {
            $last_fav_id = 0;
        }

        // the owner favs count, from twitter, is currently unreliable and may be less than the actual number of
        // favs, by a large margin.  So, we still go ahead and calculate the number of 'missing' tweets based on
        // this info, but currently do not use it for fetch loop termination.
        $this->logger->logInfo("owner favs: " . $this->user->favorites_count . ", instance owner favs in system: ".
        $this->instance->owner_favs_in_system, __METHOD__.','.__LINE__);
        $favs_missing = $this->user->favorites_count - $this->instance->owner_favs_in_system;
        $this->logger->logInfo("favs missing: $favs_missing", __METHOD__.','.__LINE__);

        // figure out if we're in 'archiving' or 'maintenance' mode, via # of last_page_fetched_favorites
        $mode = 0; // default is archving/first-fetch
        if ($last_page_fetched_favorites == 1) {
            $mode = 1; // we are in maint. mode
            $new_favs_to_add = $favs_missing;
            $this->logger->logInfo("new favs to add/missing: $new_favs_to_add", __METHOD__.','.__LINE__);
            $mpage = 1;
            $starting_fav_id = $last_fav_id;
        } else {
            // we are in archiving mode.
            $new_favs_to_add = $curr_favs_count - $last_favorites_count;
            // twitter profile information is not always consistent, so ensure that this value is not negative
            if ($new_favs_to_add < 0) {
                $new_favs_to_add == 0;
            }
            $this->logger->logInfo("new favs to add: $new_favs_to_add", __METHOD__.','.__LINE__);

            // figure out start page based on where we left off last time, and how many favs added since then
            $extra_pages = ceil($new_favs_to_add / $page_size);
            $this->logger->logInfo("extra pages: $extra_pages", __METHOD__.','.__LINE__);
            $finished_first_fetch = false;
            if ($last_page_fetched_favorites == 0) {
                // if at initial starting fetch (first time favs ever crawled)
                if ($extra_pages == 0   ) {
                    $extra_pages = 1; // always check at least one page on initial fetch
                }
                $last_page_fetched_favs_start = $extra_pages + 1;
            } else {
                $last_page_fetched_favs_start = $last_page_fetched_favorites + $extra_pages;
            }
            if ($last_page_fetched_favs_start > $last_page_of_favs) {
                $last_page_fetched_favs_start = $last_page_of_favs + 1;
            }
        }

        $status_message = "total last favs count: $last_favorites_count" .
           ", last page fetched: $last_page_fetched_favorites, last fav id: $last_fav_id";
        $this->logger->logInfo($status_message, __METHOD__.','.__LINE__);
        $this->logger->logInfo("current favs count: $curr_favs_count" .
               ", new favs to add: $new_favs_to_add, last page of favs: $last_page_of_favs, mode: $mode",
        __METHOD__.','.__LINE__);

        $continue = true;
        $fcount = 0;
        $older_favs_smode = false;
        $stop_page = 0;

        $status_message = "in fetchInstanceFavorites: API available: ".$this->api->available.", avail for crawler: ".
        $this->api->available_api_calls_for_crawler;
        $this->logger->logInfo($status_message, __METHOD__.','.__LINE__);

        while ($this->api->available && $this->api->available_api_calls_for_crawler > 0 && $continue) {
            try {
                if ($mode != 0) { // in maintenance, not archiving mode
                    list($fcount, $mpage, $older_favs_smode, $stop_page, $new_favs_to_add, $last_fav_id,
                    $last_page_fetched_favorites, $continue) =
                    $this->maintFavsFetch ($starting_fav_id, $fcount, $mpage, $older_favs_smode, $stop_page,
                    $new_favs_to_add, $last_fav_id, $last_page_fetched_favorites, $continue);
                    // }
                } else { // mode 0 -- archiving mode
                    if (!$finished_first_fetch) {
                        list($fcount, $last_fav_id, $last_page_fetched_favorites, $continue) =
                        $this->archivingFavsFetch($fcount, $last_fav_id, $last_page_fetched_favs_start, $continue);
                        $finished_first_fetch = true;
                    } else {
                        list($fcount, $last_fav_id, $last_page_fetched_favorites, $continue) =
                        $this->archivingFavsFetch($fcount, $last_fav_id, $last_page_fetched_favorites, $continue);
                    }
                }
            } catch (APICallLimitExceededException $e) {
                break;
            }
        } // end while
        // update necessary instance fields
        $this->logger->logInfo("new_favs_to_add: $new_favs_to_add, fcount: $fcount", __METHOD__.','.__LINE__);
        $this->logger->logInfo("new 'last fav id': $last_fav_id", __METHOD__.','.__LINE__);

        $this->instance->last_favorite_id = $last_fav_id;
        $this->instance->last_page_fetched_favorites =$last_page_fetched_favorites;
        $this->instance->favorites_profile = $curr_favs_count;
        $this->logger->logUserSuccess("Saved $fcount new favorites.", __METHOD__.','.__LINE__);
        return true;
    }

    /**
     * maintFavsFetch implements the core of the crawler's 'maintenance fetch' for favs.  It goes into this mode
     * after the initial archving process.  In maintenance mode the crawler is just looking for new favs. It searches
     * backwards until it finds the last-stored fav, then searches further back to find any older tweets that were
     * favorited unchronologically (as might happen if the user were looking back through a particular account's
     * timeline).  The number of such pages to search back through is set in  favs_older_pages option.
     * @param int $starting_fav_id
     * @param int $fcount
     * @param int $mpage
     * @param bool $older_favs_smode
     * @param int $stop_page
     * @param int $new_favs_to_add
     * @param int $last_fav_id
     * @param int $last_page_fetched_favorites
     * @param bool $continue
     * @return array($fcount, $mpage, $older_favs_smode, $stop_page, $new_favs_to_add, $last_fav_id,
     * $last_page_fetched_favorites, $continue);
     */
    private function maintFavsFetch ($starting_fav_id, $fcount, $mpage, $older_favs_smode, $stop_page,
    $new_favs_to_add, $last_fav_id, $last_page_fetched_favorites, $continue) {
        $status_message = "";
        $older_favs_pages = 2; // default number of additional pages to check back through
        // for older favs added non-chronologically

        list($tweets, $cURL_status, $twitter_data) = $this->getFavsPage($mpage);
        if ($cURL_status == 200) {
            if ($tweets == -1) { // should not reach this
                $this->logger->logInfo("in maintFavsFetch; could not extract any tweets from response",
                __METHOD__.','.__LINE__);
                throw new Exception("could not extract any tweets from response");
            }
            if (sizeof($tweets) == 0) {
                // then done -- this should happen when we have run out of favs
                $this->logger->logInfo("It appears that we have run out of favorites to process",
                __METHOD__.','.__LINE__);
                $continue = false;
            } else {
                $post_dao = DAOFactory::getDAO('FavoritePostDAO');
                foreach ($tweets as $tweet) {
                    $tweet['network'] = 'twitter';

                    if ($post_dao->addFavorite($this->user->user_id, $tweet) > 0) {
                        URLProcessor::processPostURLs($tweet['post_text'], $tweet['post_id'], 'twitter', $this->logger);
                        $this->logger->logInfo("found new fav: " . $tweet['post_id'], __METHOD__.','.__LINE__);
                        $fcount++;
                        $this->logger->logInfo("fcount: $fcount", __METHOD__.','.__LINE__);
                        $this->logger->logInfo("added favorite: ". $tweet['post_id'], __METHOD__.','.__LINE__);
                    } else {
                        // fav was already stored, so take no action. This could happen both because some
                        // of the favs on the given page were processed last time, or because a separate process,
                        // such as a UserStream process, is also watching for and storing favs.
                        $status_message = "have already stored fav ". $tweet['post_id'];
                        $this->logger->logDebug($status_message, __METHOD__.','.__LINE__);
                    }

                    // keep track of the highest fav id we've encountered
                    if ($tweet['post_id'] > $last_fav_id) {
                        $this->logger->logInfo("fav " . $tweet['post_id'] ." > $last_fav_id",
                        __METHOD__.','.__LINE__);
                        $last_fav_id = $tweet['post_id'] + 0;
                    }
                } // end foreach
            }

            $mpage++;
            // if have gone earlier than highest fav id from last time, then switch to 'search for older favs' mode
            if ($older_favs_smode == false) {
                // last-processed tweet
                if (isset($tweet) && $tweet['post_id'] <= $starting_fav_id) {

                    // get 'favs_older_pages' plugin option value if it exists & is pos. int, otherwise use default
                    $topt = $this->twitter_options;
                    if (isset($topt['favs_older_pages'])) {
                        $conf_older_favs_pages = $topt['favs_older_pages']->option_value;
                        if (is_integer((int)$conf_older_favs_pages) && $conf_older_favs_pages > 0) {
                            $older_favs_pages = $conf_older_favs_pages;
                        }
                    }
                    $this->logger->logInfo("older_favs_pages: $older_favs_pages", __METHOD__.','.__LINE__);

                    $older_favs_smode = true;
                    $stop_page = $mpage + $older_favs_pages -1;
                    $this->logger->logInfo("next will be searching for older favs: stop page: ".$stop_page.
                    ", fav <= $starting_fav_id ", __METHOD__.','.__LINE__);
                }
            } else {// in older_favs_smode, check whether we should stop
                $this->logger->logInfo("in older favs search mode with stop page $stop_page", __METHOD__.','.__LINE__);
                // check for terminating condition, which is (for now), that we have searched N more pages back
                // or found all the add'l tweets
                // 23/10/10 making temp (?) change due to broken API.
                //           if ($mpage > $stop_page || $fcount >= $new_favs_to_add) {
                // temp change to not use the 'new favs to add' info while the api favs bug still exists-- it
                // breaks things under some circs.
                // hopefully this will be fixed again by Twitter at some point.
                if ($mpage > $stop_page ) {
                    $continue = false;
                }
            }
        } else {
            $this->logger->logError("cURL status: $cURL_status", __METHOD__.','.__LINE__);
            $this->logger->logInfo($twitter_data, __METHOD__.','.__LINE__);
            $continue = false;
        }
        return array($fcount, $mpage, $older_favs_smode, $stop_page, $new_favs_to_add, $last_fav_id,
        $last_page_fetched_favorites, $continue);
    }
    /**
     * archivingFavsFetch is used to support the favorites crawler's first 'archiving' stage,
     * in which it sucks in all the user's favorites.  It starts with the
     * largest page number (oldest favs), calculated based on the # of favs for the user as reported by twitter,
     * and searches forward (newer)
     * until it reaches page 1 or runs out of API calls.  It may need to break up this stage over several runs
     * due to API limits.
     * (This stage only happens once-- after this intitial archiving process,
     * the favs crawler switches into 'maintenance mode' as implemented by the method above,
     * and uses a limited # of API calls for each run.)
     * @param int $fcount
     * @param int $last_fav_id
     * @param int $last_page_fetched_favorites
     * @param bool $continue
     * @return array(array($fcount, $last_fav_id, $last_page_fetched_favorites, $continue);
     */
    private function archivingFavsFetch ($fcount, $last_fav_id, $last_page_fetched_favorites, $continue) {
        $status_message = "";

        // $this->logger->logInfo("in archivingFavsFetch- last_page_fetched_favorites: $last_page_fetched_favorites",
        // __METHOD__.','.__LINE__);
        list($tweets, $cURL_status, $twitter_data) = $this->getFavsPage($last_page_fetched_favorites - 1);

        if ($cURL_status == 200) {
            if ($tweets == -1 ) {
                $this->logger->logInfo("in archivingFavsFetch; could not extract any tweets from response",
                __METHOD__.','.__LINE__);
                throw new Exception("could not extract any tweets from response");
            }
            if (sizeof($tweets) == 0) {
                // then just continue to the next smaller page of favs.
                $this->logger->logInfo("received empty page of favs", __METHOD__.','.__LINE__);
                $last_page_fetched_favorites--;
                if ($last_page_fetched_favorites <= 1) { //'should' never be < 1;
                    $continue = false;
                }
                return array($fcount, $last_fav_id, $last_page_fetched_favorites, $continue);
            }
            $post_dao = DAOFactory::getDAO('FavoritePostDAO');
            $status_message = "user id: " . $this->user->user_id;
            $this->logger->logInfo($status_message, __METHOD__.','.__LINE__);
            foreach ($tweets as $tweet) {
                $tweet['network'] = 'twitter';
                $this->logger->logInfo("working on fav: " . $tweet['post_id'], __METHOD__.','.__LINE__);

                if ($post_dao->addFavorite($this->user->user_id, $tweet) > 0) {
                    URLProcessor::processPostURLs($tweet['post_text'], $tweet['post_id'], 'twitter', $this->logger);
                    $fcount++;
                    $this->logger->logInfo("added favorite: ". $tweet['post_id'], __METHOD__.','.__LINE__);
                } else {
                    $status_message = "have already stored favorite: ". $tweet['post_id'];
                    $this->logger->logDebug($status_message, __METHOD__.','.__LINE__);
                }

                // $this->logger->logInfo("current last fav id is:  $last_fav_id", __METHOD__.','.__LINE__);
                if ($tweet['post_id'] > $last_fav_id) {
                    $this->logger->logInfo("fav > $last_fav_id", __METHOD__.','.__LINE__);
                    $last_fav_id = $tweet['post_id'] + 0;
                }
            } // end foreach
            $last_page_fetched_favorites--;
            if ($last_page_fetched_favorites == 1) {
                $continue = false;
            }
        } else {
            $this->logger->logInfo("error: curl status: $cURL_status", __METHOD__.','.__LINE__);
            $this->logger->logInfo($twitter_data, __METHOD__.','.__LINE__);
            $continue = false;
        }
        return array($fcount, $last_fav_id, $last_page_fetched_favorites, $continue);
    }
    /**
     * This helper method returns the parsed favorites from a given favorites page.
     * @param int $page
     * @return array ($tweets, $cURL_status, $twitter_data)
     */
    private function getFavsPage($page) {
        $favs_call = str_replace("[id]", $this->user->username, $this->api->cURL_source['favorites']);
        $tweets = -1;
        $args = array();
        $args["page"] = $page;
        list($cURL_status, $twitter_data) = $this->api->apiRequest($favs_call, $args);
        if ($cURL_status == 200) {
            // Parse the XML file
            $tweets = $this->api->parseXML($twitter_data);
            if (!(isset($tweets) && sizeof($tweets) == 0) && $tweets == null) { // arghh, empty array evals to null.
                $this->logger->logInfo("in getFavsPage; could not extract any tweets from response",
                __METHOD__.','.__LINE__);
                throw new Exception("could not extract any tweets from response");
            }
        }
        return array($tweets, $cURL_status, $twitter_data);
    }
    /**
     * cleanUpMissedFavsUnFavs  pages back through the older pages of favs, checking for favs that are not yet in
     * the database, as well as favs that were added to the db but are no longer returned by Twitter's API.
     * However, that latter calculation, for un-fav'd tweets, is currently not reliable due to a bug on Twitter's end,
     * and so such tweets are not currently removed from the database.
     * Due to the same issue with the API, it's not clear whether all favs of older tweets are going to be actually
     * returned from Twitter (that is, it is currently not returning some actually-favorited tweets in a given range).
     * So, we may miss some older tweets that were in fact favorited, until Twitter fixes this.
     * The number of pages to page back for each run of the crawler is set by favs_cleanup_pages option.
     */
    public function cleanUpMissedFavsUnFavs() {
        // first, check that we have the resources to do work
        if (!($this->api->available && $this->api->available_api_calls_for_crawler)) {
            $this->logger->logInfo("terminating cleanUpMissedFavsUnFavs-- no API calls available",
            __METHOD__.','.__LINE__);
            return true;
        }
        $this->logger->logInfo("In cleanUpMissedFavsUnFavs", __METHOD__.','.__LINE__);
        $this->logger->logInfo("User id: " . $this->user->user_id . "\n", __METHOD__.','.__LINE__);

        $fcount = 0;
        $favs_cleanup_pages = 1; // default number of pages to process each time the crawler runs
        // get plugin option value if it exists & is positive int, otherwise use default
        $topt = $this->twitter_options;
        if (isset($topt['favs_cleanup_pages'])) {
            $conf_favs_cleanup_pages = $topt['favs_cleanup_pages']->option_value;
            $this->logger->logInfo("conf_favs_cleanup_pages: $conf_favs_cleanup_pages ", __METHOD__.','.__LINE__);
            if (is_integer((int)$conf_favs_cleanup_pages) && $conf_favs_cleanup_pages > 0) {
                $favs_cleanup_pages = $conf_favs_cleanup_pages;
            }
        }
        $this->logger->logInfo("favs_cleanup_pages: $favs_cleanup_pages ", __METHOD__.','.__LINE__);

        $fpd = DAOFactory::getDAO('FavoritePostDAO');

        $pagesize = 20; // number of favs per page retrieved from the API call... (tbd: any way to get
        //this from the API?)
        // get 'favs_older_pages' plugin option value if it exists & is pos. int.  Use it to calculate default start
        // page if set, otherwise use default value.
        $default_start_page = 2;
        $topt = $this->twitter_options;
        if (isset($topt['favs_older_pages'])) {
            $conf_older_favs_pages = $topt['favs_older_pages']->option_value;
            if (is_integer((int)$conf_older_favs_pages) && $conf_older_favs_pages > 0) {
                $default_start_page = $conf_older_favs_pages + 1;
            }
        }
        $this->logger->logInfo("default start page: $default_start_page ", __METHOD__.','.__LINE__);

        $last_page_of_favs = round($this->api->archive_limit / $pagesize);

        $last_unfav_page_checked = $this->instance->last_unfav_page_checked;
        $start_page = $last_unfav_page_checked > 0? $last_unfav_page_checked + 1 : $default_start_page;
        $this->logger->logInfo("start page: $start_page, with $favs_cleanup_pages cleanup pages",
        __METHOD__.','.__LINE__);
        $curr_favs_count = $this->user->favorites_count;

        $count = 0; $page = $start_page;
        while ($count < $favs_cleanup_pages && $this->api->available &&
        $this->api->available_api_calls_for_crawler ) {
            // get the favs from that page
            try {
                list($tweets, $cURL_status, $twitter_data) = $this->getFavsPage($page);
            } catch (APICallLimitExceededException $e) {
                break;
            }
            if ($cURL_status != 200 || $tweets == -1) {
                // todo - handle more informatively
                $this->logger->logInfo("in cleanUpMissedFavsUnFavs, error with: $twitter_data",
                __METHOD__.','.__LINE__);
                throw new Exception("in cleanUpUnFavs: error parsing favs");
            }
            if (sizeof($tweets) == 0) {
                // then done paging backwards through the favs.
                // reset pointer so that we start at the recent favs again next time through.
                $this->instance->last_unfav_page_checked = 0;
                break;
            }
            $min_tweet = $tweets[(sizeof($tweets) -1)]['post_id']; $max_tweet = $tweets[0]['post_id'];
            $this->logger->logInfo("in cleanUpUnFavs, page $page min and max: $min_tweet, $max_tweet",
            __METHOD__.','.__LINE__);
            foreach ($tweets as $fav) {
                $fav['network'] = 'twitter';
                // check whether the tweet is in the db-- if not, add it.
                if ($fpd->addFavorite($this->user->user_id, $fav) > 0) {
                    URLProcessor::processPostURLs($fav['post_text'], $fav['post_id'], 'twitter', $this->logger);
                    $this->logger->logInfo("added fav " . $fav['post_id'], __METHOD__.','.__LINE__);
                    $fcount++;
                } else {
                    $status_message = "have already stored fav ". $fav['post_id'];
                    $this->logger->logDebug($status_message, __METHOD__.','.__LINE__);
                }
            }
            // now for each favorited tweet in the database within the fetched range, check whether it's still
            // favorited. This part of the method is currently disabled due to issues with the Twitter API, which
            // is not returning all of the favorited tweets any more.  So, the fact that a previously-archived
            // tweet is not returned, no longer indicates that it was un-fav'd.
            // The method still IDs the 'missing' tweets, but no longer deletes them.  We may want to get rid of
            //  this check altogether at some point.
            $fposts = $fpd->getAllFavoritePostsUpperBound($this->user->user_id, 'twitter', $pagesize, $max_tweet + 1);
            foreach ($fposts as $old_fav) {
                $old_fav_id = $old_fav->post_id;
                if ($old_fav_id < $min_tweet) {
                    $this->logger->logInfo("Old fav $old_fav_id out of range ", __METHOD__.','.__LINE__);
                    break; // all the rest will be out of range also then
                }
                // look for the old_fav_id in the array of fetched favs
                $found = false;
                foreach ($tweets as $tweet) {
                    if ($old_fav_id == $tweet['post_id']) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) { // if it's not there...
                    // 14/10 arghh -- Twitter is suddenly (temporarily?) not returning all fav'd tweets in a
                    // sequence.
                    // skipping the delete for now, keep tabs on it.  Can check before delete with extra API
                    // request, but the point of doing it this way was to avoid the additional API request.
                    $this->logger->logInfo("Twitter claims tweet not still favorited, but this is currently ".
                        "broken, so not deleting: ". $old_fav_id, __METHOD__.','.__LINE__);
                    // 'unfavorite' by removing from favorites table
                    // $fpd->unFavorite($old_fav_id, $this->user->user_id);
                }
            }
            $this->instance->last_unfav_page_checked = $page++;
            if ($page > $last_page_of_favs) {
                $page = 0;
                break;
            }
            $count++;
        }
        $this->logger->logUserSuccess("Added $fcount older missed favorites", __METHOD__.','.__LINE__);
        return true;
    }

    public function generateInsights($number_days=3) {
        $this->logger->logUserSuccess("Calculating insights for last ".$number_days." days.",
        __METHOD__.','.__LINE__);
        self::generateInsightBaselines($number_days);
        self::generateInsightFeedItems($number_days);
    }
    /**
     * Calculate and store insight baselines for a specified number of days.
     * @param int $number_days Number of days to backfill
     */
    private function generateInsightBaselines($number_days=3) {
        $post_dao = DAOFactory::getDAO('PostDAO');
        $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');

        $days_ago = 0;
        // Generate baseline post insights for the last 7 days

        while ($days_ago < $number_days) {
            $since_date = date("Y-m-d", strtotime("-".$days_ago." day"));

            //Save average retweets over past 7 days
            $average_retweet_count_7_days = null;
            $average_retweet_count_7_days = $post_dao->getAverageRetweetCount($this->instance->network_username,
            $this->instance->network, 7, $since_date);
            if ($average_retweet_count_7_days != null ) {
                $insight_baseline_dao->insertInsightBaseline('avg_retweet_count_last_7_days', $this->instance->id,
                $average_retweet_count_7_days, $since_date);
                $this->logger->logSuccess("Averaged $average_retweet_count_7_days retweets in the 7 days before ".
                $since_date, __METHOD__.','.__LINE__);
            }
            //Save average retweets over past 30 days
            $average_retweet_count_30_days = null;
            $average_retweet_count_30_days = $post_dao->getAverageRetweetCount($this->instance->network_username,
            $this->instance->network, 30, $since_date);
            if ($average_retweet_count_30_days != null ) {
                $insight_baseline_dao->insertInsightBaseline('avg_retweet_count_last_30_days', $this->instance->id,
                $average_retweet_count_30_days, $since_date);
                $this->logger->logSuccess("Averaged $average_retweet_count_30_days retweets in the 30 days before ".
                $since_date, __METHOD__.','.__LINE__);
            }

            //Save retweet high for last 7 days
            $high_retweet_count_7_days = $post_dao->getAllPostsByUsernameOrderedBy($this->instance->network_username,
            $network="twitter", 1, 'retweets', 7, $iterator = false, $is_public = false, $since=$since_date);
            if ($high_retweet_count_7_days != null ) {
                $high_retweet_count_7_days = $high_retweet_count_7_days[0]->all_retweets;
                $insight_baseline_dao->insertInsightBaseline('high_retweet_count_last_7_days', $this->instance->id,
                $high_retweet_count_7_days, $since_date);
                $this->logger->logSuccess("High of $high_retweet_count_7_days retweets in the 7 days before ".
                $since_date, __METHOD__.','.__LINE__);
            }

            //Save retweet high for last 30 days
            $high_retweet_count_30_days = $post_dao->getAllPostsByUsernameOrderedBy($this->instance->network_username,
            $network="twitter", 1, 'retweets', 30, $iterator = false, $is_public = false, $since=$since_date);
            if ($high_retweet_count_30_days != null ) {
                $high_retweet_count_30_days = $high_retweet_count_30_days[0]->all_retweets;
                $insight_baseline_dao->insertInsightBaseline('high_retweet_count_last_30_days', $this->instance->id,
                $high_retweet_count_30_days, $since_date);
                $this->logger->logSuccess("High of $high_retweet_count_30_days retweets in the 30 days before ".
                $since_date, __METHOD__.','.__LINE__);
            }

            //Save retweet high for last 365 days
            $high_retweet_count_365_days = $post_dao->getAllPostsByUsernameOrderedBy($this->instance->network_username,
            $network="twitter", 1, 'retweets', 365, $iterator = false, $is_public = false, $since=$since_date);
            if ($high_retweet_count_365_days != null ) {
                $high_retweet_count_365_days = $high_retweet_count_365_days[0]->all_retweets;
                $insight_baseline_dao->insertInsightBaseline('high_retweet_count_last_365_days', $this->instance->id,
                $high_retweet_count_365_days, $since_date);
                $this->logger->logSuccess("High of $high_retweet_count_365_days retweets in the 365 days before ".
                $since_date, __METHOD__.','.__LINE__);
            }

            $days_ago++;
        }
    }
    /**
     * Calculate and store insights for a specified number of days.
     * @param int $number_days Number of days to backfill
     */
    private function generateInsightFeedItems($number_days=3) {
        $post_dao = DAOFactory::getDAO('PostDAO');
        $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $insight_dao = DAOFactory::getDAO('InsightDAO');

        // Get retweeted posts for last 7 days
        $posts = $post_dao->getMostRetweetedPostsInLastWeek($this->instance->network_username,
        $this->instance->network, 40, $is_public = false);
        $posts = $post_dao->getAllPostsByUsernameOrderedBy($this->instance->network_username, $network="twitter",
        $count=0, $order_by="pub_date", $in_last_x_days = $number_days, $iterator = false, $is_public = false);

        $baseline_date = null;
        // foreach post
        foreach ($posts as $post) {
            $simplified_post_date = date('Y-m-d', strtotime($post->pub_date));

            if ($simplified_post_date != $baseline_date) { //need to get baselines
                $average_retweet_count_7_days =
                $insight_baseline_dao->getInsightBaseline('avg_retweet_count_last_7_days', $this->instance->id,
                $simplified_post_date);

                $average_retweet_count_30_days =
                $insight_baseline_dao->getInsightBaseline('avg_retweet_count_last_30_days', $this->instance->id,
                $simplified_post_date);

                $high_retweet_count_7_days =
                $insight_baseline_dao->getInsightBaseline('high_retweet_count_last_7_days', $this->instance->id,
                $simplified_post_date);

                $high_retweet_count_30_days =
                $insight_baseline_dao->getInsightBaseline('high_retweet_count_last_30_days', $this->instance->id,
                $simplified_post_date);

                $high_retweet_count_365_days =
                $insight_baseline_dao->getInsightBaseline('high_retweet_count_last_365_days', $this->instance->id,
                $simplified_post_date);

                $baseline_date = $post->pub_date;
            }
            if (isset($high_retweet_count_365_days->value)
            && $post->all_retweets >= $high_retweet_count_365_days->value) {
                $insight_dao->insertInsight('retweet_high_365_day_'.$post->id, $this->instance->id,
                $simplified_post_date, "New 365-day high! ".$post->all_retweets." people retweeted your tweet.",
                Insight::EMPHASIS_HIGH, serialize($post));

                $insight_dao->deleteInsight('retweet_high_30_day_'.$post->id, $this->instance->id,
                $simplified_post_date);
                $insight_dao->deleteInsight('retweet_high_7_day_'.$post->id, $this->instance->id,
                $simplified_post_date);
                $insight_dao->deleteInsight('retweet_spike_30_day_'.$post->id, $this->instance->id,
                $simplified_post_date);
                $insight_dao->deleteInsight('retweet_spike_7_day_'.$post->id, $this->instance->id,
                $simplified_post_date);
            } elseif (isset($high_retweet_count_30_days->value)
            && $post->all_retweets >= $high_retweet_count_30_days->value) {
                $insight_dao->insertInsight('retweet_high_30_day_'.$post->id, $this->instance->id,
                $simplified_post_date, "New 30-day high! ".$post->all_retweets." people retweeted your tweet.",
                Insight::EMPHASIS_HIGH, serialize($post));

                $insight_dao->deleteInsight('retweet_high_7_day_'.$post->id, $this->instance->id,
                $simplified_post_date);
                $insight_dao->deleteInsight('retweet_spike_30_day_'.$post->id, $this->instance->id,
                $simplified_post_date);
                $insight_dao->deleteInsight('retweet_spike_7_day_'.$post->id, $this->instance->id,
                $simplified_post_date);
            } elseif (isset($high_retweet_count_7_days->value)
            && $post->all_retweets >= $high_retweet_count_7_days->value) {
                $insight_dao->insertInsight('retweet_high_7_day_'.$post->id, $this->instance->id, $simplified_post_date,
                "New 7-day high! ".$post->all_retweets." people retweeted your tweet.",
                Insight::EMPHASIS_HIGH, serialize($post));

                $insight_dao->deleteInsight('retweet_high_30_day_'.$post->id, $this->instance->id,
                $simplified_post_date);
                $insight_dao->deleteInsight('retweet_spike_30_day_'.$post->id, $this->instance->id,
                $simplified_post_date);
                $insight_dao->deleteInsight('retweet_spike_7_day_'.$post->id, $this->instance->id,
                $simplified_post_date);
            } elseif (isset($average_retweet_count_30_days->value)
            && $post->all_retweets > ($average_retweet_count_30_days->value*2)) {
                $multiplier = floor($post->all_retweets/$average_retweet_count_30_days->value);
                $insight_dao->insertInsight('retweet_spike_30_day_'.$post->id, $this->instance->id,
                $simplified_post_date, "Retweet spike! ".$post->all_retweets.
                " people reshared your tweet, more than ".$multiplier. "x your 30-day average.", Insight::EMPHASIS_LOW,
                serialize($post));

                $insight_dao->deleteInsight('retweet_high_30_day_'.$post->id, $this->instance->id,
                $simplified_post_date);
                $insight_dao->deleteInsight('retweet_high_7_day_'.$post->id, $this->instance->id,
                $simplified_post_date);
                $insight_dao->deleteInsight('retweet_spike_7_day_'.$post->id, $this->instance->id,
                $simplified_post_date);
            } elseif (isset($average_retweet_count_7_days->value)
            && $post->all_retweets > ($average_retweet_count_7_days->value*2)) {
                $multiplier = floor($post->all_retweets/$average_retweet_count_7_days->value);
                $insight_dao->insertInsight('retweet_spike_7_day_'.$post->id, $this->instance->id,
                $simplified_post_date, "Retweet spike! ".$post->all_retweets." people reshared your tweet, more than "
                .$multiplier. "x your 7-day average.", Insight::EMPHASIS_LOW, serialize($post));

                $insight_dao->deleteInsight('retweet_high_30_day_'.$post->id, $this->instance->id,
                $simplified_post_date);
                $insight_dao->deleteInsight('retweet_high_7_day_'.$post->id, $this->instance->id,
                $simplified_post_date);
                $insight_dao->deleteInsight('retweet_spike_30_day_'.$post->id, $this->instance->id,
                $simplified_post_date);
            }

            //If not a reply or retweet and geoencoded, show the map in the stream
            if (!isset($post->$in_reply_to_user_id) && !isset($post->$in_reply_to_post_id)
            && !isset($post->in_retweet_of_post_id) && $post->reply_count_cache > 5) {
                $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
                $options = $plugin_option_dao->getOptionsHash('geoencoder', true);
                if (isset($options['gmaps_api_key']->option_value) && $post->is_geo_encoded == 1) {
                    $insight_dao->insertInsight('geoencoded_replies', $this->instance->id, $simplified_post_date,
                   "Going global! Your post got replies and retweets from locations all over the map.",
                    Insight::EMPHASIS_LOW, serialize($post));
                }
            }

            //If more than 20 replies, let user know most-frequently mentioned words are available
            if ($post->reply_count_cache >= 20) {
                if (!isset($config)) {
                    $config = Config::getInstance();
                }
                $insight_dao->insertInsight('replies_frequent_words_'.$post->id, $this->instance->id,
                $simplified_post_date,
               'Your post got '.$post->reply_count_cache.' replies! See <a href="'.$config->getValue('site_root_path').
                'post/?t='.$post->post_id.'&n='.$post->network.'">the most frequently-mentioned reply words</a>.',
                Insight::EMPHASIS_HIGH, serialize($post));
            }
        }

        //Generate least likely followers insights
        $follow_dao = DAOFactory::getDAO('FollowDAO');
        $days_ago = 0;
        while ($days_ago < $number_days) {
            //For each of the past 7 days (remove this later & just do day by day?)
            //get least likely followers for that day
            $least_likely_followers = $follow_dao->getLeastLikelyFollowersByDay($this->instance->network_user_id,
            'twitter', $days_ago, 3);
            if (sizeof($least_likely_followers) > 0 ) { //if not null, store insight
                //If followers have more followers than half of what the instance has, jack up emphasis
                $emphasis = Insight::EMPHASIS_LOW;
                foreach ($least_likely_followers as $least_likely_follower) {
                    if ($least_likely_follower->follower_count > ($this->user->follower_count/2)) {
                        $emphasis = Insight::EMPHASIS_HIGH;
                    }
                }

                $insight_date = new DateTime();
                //Not PHP 5.2 compatible
                //$insight_date->sub(new DateInterval('P'.$days_ago.'D'));
                $insight_date->modify('-'.$days_ago.' day');
                $insight_date = $insight_date->format('Y-m-d');
                if (sizeof($least_likely_followers) > 1) {
                    $insight_dao->insertInsight('least_likely_followers', $this->instance->id, $insight_date,
                    "Good people: ".sizeof($least_likely_followers)." interesting users followed you.",
                    $emphasis, serialize($least_likely_followers));
                } else {
                    $insight_dao->insertInsight('least_likely_followers', $this->instance->id, $insight_date,
                    "An interesting user followed you.",
                    $emphasis, serialize($least_likely_followers));
                }
            }
            $days_ago++;
        }

        //Generate new list membership insights
        $group_membership_dao = DAOFactory::getDAO('GroupMemberDAO');
        $days_ago = 0;
        while ($days_ago < $number_days) {
            $insight_date = new DateTime();
            $insight_date->modify('-'.$days_ago.' day');
            $insight_date = $insight_date->format('Y-m-d');
            $this->logger->logInfo("Getting new group memberships for ".$insight_date, __METHOD__.','
            .__LINE__);
            //get new group memberships per day
            $new_groups = $group_membership_dao->getNewMembershipsByDate('twitter', $this->instance->network_user_id,
            $insight_date);
            if (sizeof($new_groups) > 0 ) { //if not null, store insight
                $group_membership_count_dao = DAOFactory::getDAO('GroupMembershipCountDAO');
                $list_membership_count_history_by_day = $group_membership_count_dao->getHistory(
                $this->instance->network_user_id, 'twitter', 'DAY', 15);
                if (sizeof($new_groups) > 1) {
                    $group_name_list = '';
                    foreach ($new_groups as $group) {
                        if ($group == end($new_groups)) {
                            $group_name_list .= " and ";
                        } else {
                            if ($group_name_list != '') {
                                $group_name_list .= ", ";
                            }
                        }
                        $group->setMetadata();
                        $group_name_list .= '<a href="'.$group->url.'">'.$group->keyword.'</a>';
                    }
                    $insight_dao->insertInsight('new_group_memberships', $this->instance->id, $insight_date,
                    "You got added to ".sizeof($new_groups)." lists: ".$group_name_list.
                    ", bringing your total to ".number_format(end($list_membership_count_history_by_day['history'])).
                    ".", Insight::EMPHASIS_LOW, serialize($list_membership_count_history_by_day));
                } else {
                    $new_groups[0]->setMetadata();
                    $insight_dao->insertInsight('new_group_memberships', $this->instance->id, $insight_date,
                    "You got added to a new list, ".'<a href="'.$new_groups[0]->url.'">'.$new_groups[0]->keyword.
                    "</a>, bringing your total to ".
                    number_format(end($list_membership_count_history_by_day['history'])).
                    ".", Insight::EMPHASIS_LOW, serialize($list_membership_count_history_by_day));
                }
            }
            $days_ago++;
        }

        //Follower count history milestone
        $days_ago = $number_days;
        while ($days_ago > -1) {
            $insight_date = new DateTime();
            $insight_date->modify('-'.$days_ago.' day');
            $insight_day_of_week = (int) $insight_date->format('w');
            $this->logger->logInfo("Insight day of week is ".$insight_day_of_week, __METHOD__.','
            .__LINE__);
            $insight_date_formatted = $insight_date->format('Y-m-d');

            if ($insight_day_of_week == 0) { //it's Sunday
                $follower_count_dao = DAOFactory::getDAO('FollowerCountDAO');
                //by week
                $follower_count_history_by_week = $follower_count_dao->getHistory($this->instance->network_user_id,
                $this->instance->network, 'WEEK', 15, $insight_date_formatted);
                $this->logger->logInfo($insight_date_formatted." is Sunday; Count by week stats are ".
                Utils::varDumpToString($follower_count_history_by_week) , __METHOD__.','
                .__LINE__);
                if ( isset($follower_count_history_by_week['milestone'])
                && $follower_count_history_by_week["milestone"]["will_take"] > 0
                && $follower_count_history_by_week["milestone"]["next_milestone"] > 0 ) {
                    $insight_text = "Upcoming milestone: ";
                    $insight_text .= $follower_count_history_by_week['milestone']['will_take'].' week';
                    if ($follower_count_history_by_week['milestone']['will_take'] > 1) {
                        $insight_text .= 's';
                    }
                    $insight_text .= ' till you reach '.
                    number_format($follower_count_history_by_week['milestone']['next_milestone']);
                    $insight_text .= ' followers at your current growth rate.';
                    $this->logger->logInfo("Storing insight ".$insight_text, __METHOD__.','
                    .__LINE__);

                    $insight_dao->insertInsight('follower_count_history_by_week_milestone', $this->instance->id,
                    $insight_date_formatted, $insight_text, Insight::EMPHASIS_HIGH,
                    serialize($follower_count_history_by_week));
                }
            }

            $insight_day_of_month = (int) $insight_date->format('j');
            if ($insight_day_of_month == 1) { //it's the first day of the month
                $follower_count_dao = DAOFactory::getDAO('FollowerCountDAO');
                //by month
                $follower_count_history_by_month = $follower_count_dao->getHistory($this->instance->network_user_id,
                $this->instance->network, 'MONTH', 15, $insight_date_formatted);
                if ( isset($follower_count_history_by_month['milestone'])
                && $follower_count_history_by_month["milestone"]["will_take"] > 0
                && $follower_count_history_by_month["milestone"]["next_milestone"] > 0) {
                    $insight_text = "Upcoming milestone: ";
                    $insight_text .= $follower_count_history_by_month['milestone']['will_take'].' month';
                    if ($follower_count_history_by_month['milestone']['will_take'] > 1) {
                        $insight_text .= 's';
                    }
                    $insight_text .= ' till you reach '.
                    number_format($follower_count_history_by_month['milestone']['next_milestone']);
                    $insight_text .= ' followers at your current growth rate.';

                    $insight_dao->insertInsight('follower_count_history_by_month_milestone', $this->instance->id,
                    $insight_date_formatted, $insight_text, Insight::EMPHASIS_HIGH,
                    serialize($follower_count_history_by_month));
                }
            }

            $years_to_rewind = 6;
            $existing_insight = $insight_dao->getInsight("posts_on_this_day_flashback", $this->instance->id,
            $insight_date_formatted);
            if (!isset($existing_insight)) {
                //Generate flashback post list
                $flashback_posts = $post_dao->getOnThisDayFlashbackPosts($this->instance->network_user_id, 'twitter',
                $insight_date_formatted);
                if (isset($flashback_posts) && sizeof($flashback_posts) > 0 ) {
                    $oldest_post_year = date(date( 'Y' , strtotime($flashback_posts[0]->pub_date)));
                    $current_year = date('Y');
                    $number_of_years_ago = $current_year - $oldest_post_year;
                    $plural = ($number_of_years_ago > 1 )?'s':'';
                    $insight_dao->insertInsight("posts_on_this_day_flashback", $this->instance->id,
                    $insight_date_formatted, $oldest_post_year." flashback: ".$number_of_years_ago." year".
                    $plural. " ago today, you posted: ", Insight::EMPHASIS_MED, serialize($flashback_posts));
                }
            }

            $days_ago--;
        }
    }
}
