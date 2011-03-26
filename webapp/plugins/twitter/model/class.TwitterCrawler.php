<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/model/class.TwitterCrawler.php
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
 * Twitter Crawler
 *
 * Retrieves tweets, replies, users, and following relationships from Twitter.com
 *
 * @TODO Complete docblocks
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2011 Gina Trapani
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
            $owner_profile = str_replace("[id]", $this->instance->network_username,
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
                    "'s details from Twitter.", __METHOD__.','.__LINE__);
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
                list($cURL_status, $twitter_data) = $this->api->apiRequest($search_results, null, false);
                if ($cURL_status == 200) {
                    $tweets = $this->api->parseJSON($twitter_data);
                    $pd = DAOFactory::getDAO('PostDAO');
                    $count = 0;
                    foreach ($tweets as $tweet) {
                        $tweet['network'] = 'twitter';

                        if ($pd->addPost($tweet) > 0) {
                            $count = $count + 1;
                            URLProcessor::processTweetURLs($this->logger, $tweet);

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
     * Capture the current instance users's tweets and store them in the database.
     */
    public function fetchInstanceUserTweets() {
        if (!isset($this->user)) {
            $this->fetchInstanceUserInfo();
        }
        if (isset($this->user)) {
            $status_message = "";
            $got_latest_page_of_tweets = false;
            $continue_fetching = true;

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

                list($cURL_status, $twitter_data) = $this->api->apiRequest($recent_tweets, $args);
                if ($cURL_status == 200) {
                    $count = 0;
                    $tweets = $this->api->parseXML($twitter_data);

                    $pd = DAOFactory::getDAO('PostDAO');
                    foreach ($tweets as $tweet) {
                        $tweet['network'] = 'twitter';

                        if ($pd->addPost($tweet, $this->user, $this->logger) > 0) {
                            $count = $count + 1;
                            $this->instance->total_posts_in_system = $this->instance->total_posts_in_system + 1;
                            //expand and insert links contained in tweet
                            URLProcessor::processTweetURLs($this->logger, $tweet);
                        }
                        if ($tweet['post_id'] > $this->instance->last_post_id)
                        $this->instance->last_post_id = $tweet['post_id'];
                    }
                    $status_message .= count($tweets)." tweet(s) found and $count saved";
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
                $status_message .= "All of ".$this->user->username. "'s tweets are in ThinkUp.";
                $this->logger->logUserSuccess($status_message, __METHOD__.','.__LINE__);
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
                $pd = DAOFactory::getDAO('PostDAO');
                foreach ($tweets as $tweet) {
                    if ($pd->addPost($tweet, $this->user, $this->logger) > 0) {
                        $status_message = 'Added replied to tweet ID '.$tid." to database.";
                        URLProcessor::processTweetURLs($this->logger, $tweet);
                    }
                }
            } elseif ($cURL_status == 404 || $cURL_status == 403) {
                $e = $this->api->parseError($twitter_data);
                $ped = DAOFactory::getDAO('PostErrorDAO');
                $ped->insertError($tid, 'twitter', $cURL_status, $e['error'], $this->user->user_id);
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

                    list($cURL_status, $twitter_data) = $this->api->apiRequest($mentions, $args);
                    if ($cURL_status > 200) {
                        $continue_fetching = false;
                    } else {
                        $count = 0;
                        $tweets = $this->api->parseXML($twitter_data);
                        if (count($tweets) == 0 && $got_newest_mentions) {# you're paged back and no new tweets
                            $this->instance->last_page_fetched_replies = 1;
                            $continue_fetching = false;
                            $this->instance->is_archive_loaded_mentions = true;
                            $status_message = 'Paged back but not finding new mentions; moving on.';
                            $this->logger->logInfo($status_message, __METHOD__.','.__LINE__);
                            $status_message = "";
                        }

                        $pd = DAOFactory::getDAO('PostDAO');
                        if (!isset($recentTweets)) {
                            $recentTweets = $pd->getAllPosts($this->user->user_id, 'twitter', 100);
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
                            if ($pd->addPost($tweet, $this->user, $this->logger) > 0) {
                                $count++;
                                //expand and insert links contained in tweet
                                URLProcessor::processTweetURLs($this->logger, $tweet);
                                if ($tweet['user_id'] != $this->user->user_id) {
                                    //don't update owner info from reply
                                    $u = new User($tweet, 'mentions');
                                    $this->user_dao->updateUser($u);
                                }
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
                        $this->fetchStatusRetweets($tweet);
                    }
                }
            }
        }
    }
    /**
     * Retrieve retweets of given status
     * @param array $status
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
                    $pd = DAOFactory::getDAO('PostDAO');
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
                        if ($pd->addPost($tweet, $user_with_retweet, $this->logger) > 0) {
                            $count++;
                            //expand and insert links contained in tweet
                            URLProcessor::processTweetURLs($this->logger, $tweet);
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

            list($cURL_status, $twitter_data) = $this->api->apiRequest($follower_ids, $args);

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
                    # add/update follow relationship
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

            # Fetch follower pages
            $continue_fetching = true;
            $updated_follow_count = 0;
            $inserted_follow_count = 0;

            while ($this->api->available && $this->api->available_api_calls_for_crawler > 0 && $continue_fetching
            && !$this->instance->is_archive_loaded_follows) {

                $follower_ids = $this->api->cURL_source['followers'];
                $args = array();
                if (!isset($next_cursor))
                $next_cursor = -1;
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

                        # add/update follow relationship
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
            # Fetch friend pages
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

                list($cURL_status, $twitter_data) = $this->api->apiRequest($friend_ids, $args);

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

                        # add/update follow relationship
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
            $pd = DAOFactory::getDAO('PostDAO');

            $continue_fetching = true;
            while ($this->api->available && $this->api->available_api_calls_for_crawler > 0 && $continue_fetching) {
                $stale_friend = $fd->getStalestFriend($this->user->user_id, 'twitter');
                if ($stale_friend != null) {
                    $this->logger->logInfo($stale_friend->username." is friend most need of update",
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

                    list($cURL_status, $twitter_data) = $this->api->apiRequest($stale_friend_tweets, $args);

                    if ($cURL_status == 200) {
                        $count = 0;
                        $tweets = $this->api->parseXML($twitter_data);

                        if (count($tweets) > 0) {
                            $stale_friend_updated_from_tweets = false;
                            foreach ($tweets as $tweet) {

                                if ($pd->addPost($tweet, $stale_friend, $this->logger) > 0) {
                                    $count++;
                                    //expand and insert links contained in tweet
                                    URLProcessor::processTweetURLs($this->logger, $tweet);
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
                        $ued = DAOFactory::getDAO('UserErrorDAO');
                        $ued->insertError($stale_friend->user_id, $cURL_status,
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
            $pd = DAOFactory::getDAO('PostDAO');
            $strays = $pd->getStrayRepliedToPosts($this->user->user_id, $this->user->network);
            $status_message = count($strays).' stray replied-to tweets to load for user ID '.
            $this->user->user_id . ' on '.$this->user->network;
            $this->logger->logInfo($status_message, __METHOD__.','.__LINE__);
            foreach ($strays as $s) {
                if ($this->api->available && $this->api->available_api_calls_for_crawler > 0) {
                    $this->fetchAndAddTweetRepliedTo($s['in_reply_to_post_id']);
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
                    $this->fetchAndAddUser($s['follower_id'], "Follower IDs");
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
                    # add/update follow relationship
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
            $ued = DAOFactory::getDAO('UserErrorDAO');
            $ued->insertError($fid, $cURL_status, $e['error'], $this->user->user_id, 'twitter');
            $status_message = 'User error saved.';
        }
        $this->logger->logInfo($status_message, __METHOD__.','.__LINE__);
        $status_message = "";
    }
    /**
     * For each API call left, grab oldest follow relationship, check if it exists, and update table.
     */
    public function cleanUpFollows() {
        $this->logger->logInfo("Working on cleanUpFollows", __METHOD__.','.__LINE__);

        $num_allowed_errors = 5;
        $num_errors = 0;

        $fd = DAOFactory::getDAO('FollowDAO');
        $continue_fetching = true;
        while ($this->api->available && $this->api->available_api_calls_for_crawler > 0 && $continue_fetching &&
        $num_errors < $num_allowed_errors) {
            $oldfollow = $fd->getOldestFollow('twitter');
            if ($oldfollow != null) {
                $friendship_call = $this->api->cURL_source['show_friendship'];
                $args = array();
                $args["source_id"] = $oldfollow["followee_id"];
                $args["target_id"] = $oldfollow["follower_id"];

                list($cURL_status, $twitter_data) = $this->api->apiRequest($friendship_call, $args);

                if ($cURL_status == 200) {
                    $friendship = $this->api->parseXML($twitter_data);
                    if ($friendship['source_follows_target'] == 'true') {
                        $fd->update($oldfollow["followee_id"], $oldfollow["follower_id"], 'twitter',
                        Utils::getURLWithParams($friendship_call, $args));
                    } else {
                        $fd->deactivate($oldfollow["followee_id"], $oldfollow["follower_id"], 'twitter',
                        Utils::getURLWithParams($friendship_call, $args));
                    }
                    if ($friendship['target_follows_source'] == 'true') {
                        $fd->update($oldfollow["follower_id"], $oldfollow["followee_id"], 'twitter',
                        Utils::getURLWithParams($friendship_call, $args));
                    } else {
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
                    $num_errors++;
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
                $pd = DAOFactory::getDAO('FavoritePostDAO');
                foreach ($tweets as $tweet) {
                    $tweet['network'] = 'twitter';

                    if ($pd->addFavorite($this->user->user_id, $tweet) > 0) {
                        $this->logger->logInfo("found new fav: " . $tweet['post_id'], __METHOD__.','.__LINE__);
                        $fcount++;
                        // the following no longer necessary -- is done within addFavorite via addPostAndEntities.
                        // $this->processTweetURLs($tweet);
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
                    $this->logger->logInfo("next will be searching for older favs: stop page: $stop_page,
                    fav <= $starting_fav_id ", __METHOD__.','.__LINE__);
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
            $pd = DAOFactory::getDAO('FavoritePostDAO');
            $status_message = "user id: " . $this->user->user_id;
            $this->logger->logInfo($status_message, __METHOD__.','.__LINE__);
            foreach ($tweets as $tweet) {
                $tweet['network'] = 'twitter';
                $this->logger->logInfo("working on fav: " . $tweet['post_id'], __METHOD__.','.__LINE__);

                if ($pd->addFavorite($this->user->user_id, $tweet) > 0) {
                    $fcount++;
                    // the following no longer necessary -- is done within addFavorite via addPostAndEntities.
                    // $this->processTweetURLs($tweet);
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
            list($tweets, $cURL_status, $twitter_data) = $this->getFavsPage($page);
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
                    // the following no longer necessary -- is done within addFavorite via addPostAndEntities.
                    // $this->processTweetURLs($fav);
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
}
