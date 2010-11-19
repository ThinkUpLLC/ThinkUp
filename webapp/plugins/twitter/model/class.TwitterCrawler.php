<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/model/class.TwitterCrawler.php
 *
 * Copyright (c) 2009-2010 Gina Trapani
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
 */
/**
 * Twitter Crawler
 *
 * Retrieves tweets, replies, users, and following relationships from Twitter.com
 *
 * @TODO Complete docblocks
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
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
                            $this->processTweetURLs($tweet);

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
                $args["count"] = 200;
                $args["include_rts"] = "true";
                $last_page_of_tweets = round($this->api->archive_limit / 200) + 1;

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
                            $this->processTweetURLs($tweet);
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
     * Insert links in a tweet in the links table.
     * If it's an image (Twitpic/Twitgoo/Yfrog/Flickr for now) insert direct path to thumb as expanded url.
     * Otherwise, just expand.
     * @TODO Abstract out this image thumbnail link expansion into a plugin modeled after the Flickr Thumbnails plugin
     * @param str $tweet
     */
    private function processTweetURLs($tweet) {
        $ld = DAOFactory::getDAO('LinkDAO');
        $urls = Post::extractURLs($tweet['post_text']);
        foreach ($urls as $u) {
            $is_image = 0;
            $title = '';
            $eurl = '';
            if (substr($u, 0, strlen('http://twitpic.com/')) == 'http://twitpic.com/') {
                $eurl = 'http://twitpic.com/show/thumb/'.substr($u, strlen('http://twitpic.com/'));
                $is_image = 1;
            } elseif (substr($u, 0, strlen('http://yfrog.com/')) == 'http://yfrog.com/') {
                $eurl = $u.'.th.jpg';
                $is_image = 1;
            } elseif (substr($u, 0, strlen('http://twitgoo.com/')) == 'http://twitgoo.com/') {
                $eurl = 'http://twitgoo.com/show/thumb/'.substr($u, strlen('http://twitgoo.com/'));
                $is_image = 1;
            } elseif (substr($u, 0, strlen('http://flic.kr/')) == 'http://flic.kr/') {
                $is_image = 1;
            }
            if ($ld->insert($u, $eurl, $title, $tweet['post_id'], 'twitter', $is_image)) {
                $this->logger->logSuccess("Inserted ".$u." (".$eurl.", ".$is_image."), into links table",
                get_class($this));
            } else {
                $this->logger->logError("Did NOT insert ".$u." (".$eurl.") into links table", __METHOD__.','.__LINE__);
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

            if ($cURL_status == 200) {
                $tweets = $this->api->parseXML($twitter_data);
                $pd = DAOFactory::getDAO('PostDAO');
                foreach ($tweets as $tweet) {
                    if ($pd->addPost($tweet, $this->user, $this->logger) > 0) {
                        $status_message = 'Added replied to tweet ID '.$tid." to database.";
                        $this->processTweetURLs($tweet);
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
                    $args['count'] = 200;
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
                                $originalTweetId = RetweetDetector::detectOriginalTweet($tweet['post_text'],
                                $recentTweets);
                                if ($originalTweetId != false) {
                                    $tweet['in_retweet_of_post_id'] = $originalTweetId;
                                    $this->logger->logInfo("Retweet original status ID found: ".$originalTweetId,
                                    get_class($this));
                                }
                            }
                            if ($pd->addPost($tweet, $this->user, $this->logger) > 0) {
                                $count++;
                                //expand and insert links contained in tweet
                                $this->processTweetURLs($tweet);
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
            $args["count"] = 200;
            $args["include_rts"]="true";

            list($cURL_status, $twitter_data) = $this->api->apiRequest($stream_with_retweet, $args);

            if ($cURL_status == 200) {
                $count = 0;
                $tweets = $this->api->parseXML($twitter_data);

                if (count($tweets) > 0) {
                    $pd = DAOFactory::getDAO('PostDAO');
                    foreach ($tweets as $tweet) {
                        if (RetweetDetector::isRetweet($tweet['post_text'], $this->user->username)) {
                            $this->logger->logInfo("Retweet by ".$tweet['user_name']. " found, ".
                            substr($tweet['post_text'], 0, 50)."... ", __METHOD__.','.__LINE__);
                            if ( RetweetDetector::isRetweetOfTweet($tweet["post_text"],
                            $retweeted_status["post_text"]) ){
                                $tweet['in_retweet_of_post_id'] = $retweeted_status_id;
                                $this->logger->logInfo("Retweet by ".$tweet['user_name']." of ".
                                $this->user->username." original status ID found: ".$retweeted_status_id,
                                get_class($this));
                            } else {
                                $this->logger->logInfo("Retweet by ".$tweet['user_name']." of ".
                                $this->user->username." original status ID NOT found: ".
                                $retweeted_status["post_text"]." NOT a RT of: ". $tweet["post_text"],
                                get_class($this));
                            }
                        }
                        if ($pd->addPost($tweet, $user_with_retweet, $this->logger) > 0) {
                            $count++;
                            //expand and insert links contained in tweet
                            $this->processTweetURLs($tweet);
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
                    $args["count"] = 200;

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
                                    $this->processTweetURLs($tweet);
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
                        $ued->insertError($stale_friend->user_id, $cURL_status, $e['error'],
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
     * @param inte $fid
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
        $fd = DAOFactory::getDAO('FollowDAO');
        $continue_fetching = true;
        while ($this->api->available && $this->api->available_api_calls_for_crawler > 0 && $continue_fetching) {
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
                    $continue_fetching = false;
                }
            } else {
                $continue_fetching = false;
            }
        }
    }
}