<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/model/class.TwitterCrawler.php
 *
 * Copyright (c) 2009-2010 Gina Trapani, Christoffer Viken
 *
 * LICENSE:
 *
 * This file is part of ThinkUp.
 * 
 * ThinkUp is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * ThinkUp is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with ThinkUp.  If not, see <http://www.gnu.org/licenses/>.
 *
*/
/**
 * Twitter Crawler
 *
 * Retrieves tweets, replies, users, and following relationships from Twitter.com
 *
 * @TODO Complete docblocks
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani, Christoffer Viken
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TwitterCrawler {
    var $instance;
    var $api;
    var $owner_object;
    var $user_dao;
    var $logger;

    public function __construct($instance, $api) {
        $this->instance = $instance;
        $this->api = $api;
        $this->logger = Logger::getInstance();
        $this->logger->setUsername($instance->network_username);
        $this->user_dao = DAOFactory::getDAO('UserDAO');
    }

    public function fetchInstanceUserInfo() {
        // Get owner user details and save them to DB
        if ($this->api->available && $this->api->available_api_calls_for_crawler > 0) {
            $status_message = "";
            $owner_profile = str_replace("[id]", $this->instance->network_username,
            $this->api->cURL_source['show_user']);
            list($cURL_status, $twitter_data) = $this->api->apiRequest($owner_profile);

            if ($cURL_status == 200) {
                try {
                    $users = $this->api->parseXML($twitter_data);
                    foreach ($users as $user) {
                        $this->owner_object = new User($user, 'Owner Status');
                    }

                    if (isset($this->owner_object)) {
                        $status_message = 'Owner info set.';
                        $this->user_dao->updateUser($this->owner_object);

                        if (isset($this->owner_object->follower_count) && $this->owner_object->follower_count>0) {
                            $fcount_dao = DAOFactory::getDAO('FollowerCountDAO');
                            $fcount_dao->insert($this->owner_object->user_id, 'twitter',
                            $this->owner_object->follower_count);
                        }

                    } else {
                        $status_message = 'Owner was not set.';
                    }
                }
                catch(Exception $e) {
                    $status_message = 'Could not parse profile XML for $this->owner_object->username';
                }
            } else {
                $status_message = 'cURL status is not 200';
            }
            $this->logger->logStatus($status_message, get_class($this));
        }
    }

    public function fetchSearchResults($term) {
        if (!isset($this->owner_object)) {
            $this->fetchInstanceUserInfo();
        }
        if (isset($this->owner_object)) {
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
                            if ($tweet['user_id'] != $this->owner_object->user_id) {
                                $u = new User($tweet, 'mentions');
                                $this->user_dao->updateUser($u);
                            }
                        }
                    }
                    $this->logger->logStatus(count($tweets)." tweet(s) found and $count saved", get_class($this));
                    if ( $count == 0 ) { // all tweets on the page were already saved
                        //Stop fetching when more tweets have been retrieved than were saved b/c they already existed
                        $continue_fetching = false;
                    }
                    $page = $page+1;
                } else {
                    $this->logger->logStatus("cURL status $cURL_status", get_class($this));
                    $continue_fetching = false;
                }
            }
        } else {
            $this->logger->logStatus("Cannot fetch search results; Owner object has not been set.", get_class($this));
        }
    }

    public function fetchInstanceUserTweets() {
        if (!isset($this->owner_object)) {
            $this->fetchInstanceUserInfo();
        }
        if (isset($this->owner_object)) {
            // Get owner's tweets
            $status_message = "";
            $got_latest_page_of_tweets = false;
            $continue_fetching = true;

            while ($this->api->available && $this->api->available_api_calls_for_crawler > 0
            && $this->owner_object->post_count > $this->instance->total_posts_in_system && $continue_fetching) {

                $recent_tweets = str_replace("[id]", $this->owner_object->username,
                $this->api->cURL_source['user_timeline']);
                $args = array();
                $args["count"] = 200;
                $args["include_rts"] = "true";
                $last_page_of_tweets = round($this->api->archive_limit / 200) + 1;

                //set page and since_id params for API call
                if ($got_latest_page_of_tweets
                && $this->owner_object->post_count != $this->instance->total_posts_in_system
                && $this->instance->total_posts_in_system < $this->api->archive_limit) {
                    if ($this->instance->last_page_fetched_tweets < $last_page_of_tweets)
                    $this->instance->last_page_fetched_tweets = $this->instance->last_page_fetched_tweets + 1;
                    else {
                        $continue_fetching = false;
                        $this->instance->last_page_fetched_tweets = 0;
                    }
                    $args["page"] = $this->instance->last_page_fetched_tweets;

                } else {
                    if (!$got_latest_page_of_tweets && $this->instance->last_status_id > 0)
                    $args["since_id"] = $this->instance->last_status_id;
                }

                list($cURL_status, $twitter_data) = $this->api->apiRequest($recent_tweets, $args);
                if ($cURL_status == 200) {
                    # Parse the XML file
                    try {
                        $count = 0;
                        $tweets = $this->api->parseXML($twitter_data);

                        $pd = DAOFactory::getDAO('PostDAO');
                        foreach ($tweets as $tweet) {
                            $tweet['network'] = 'twitter';

                            if ($pd->addPost($tweet, $this->owner_object, $this->logger) > 0) {
                                $count = $count + 1;
                                $this->instance->total_posts_in_system = $this->instance->total_posts_in_system + 1;

                                //expand and insert links contained in tweet
                                $this->processTweetURLs($tweet);

                            }
                            if ($tweet['post_id'] > $this->instance->last_status_id)
                            $this->instance->last_status_id = $tweet['post_id'];

                        }
                        $status_message .= count($tweets)." tweet(s) found and $count saved";
                        $this->logger->logStatus($status_message, get_class($this));
                        $status_message = "";

                        //if you've got more than the Twitter API archive limit, stop looking for more tweets
                        if ($this->instance->total_posts_in_system >= $this->api->archive_limit) {
                            $this->instance->last_page_fetched_tweets = 1;
                            $continue_fetching = false;
                            $status_message = "More than Twitter cap of ".$this->api->archive_limit.
                        " already in system, moving on.";
                            $this->logger->logStatus($status_message, get_class($this));
                            $status_message = "";
                        }


                        if ($this->owner_object->post_count == $this->instance->total_posts_in_system)
                        $this->instance->is_archive_loaded_tweets = true;

                        $status_message .= $this->instance->total_posts_in_system." in system; ".
                        $this->owner_object->post_count." by owner";
                        $this->logger->logStatus($status_message, get_class($this));
                        $status_message = "";

                    }
                    catch(Exception $e) {
                        $status_message = 'Could not parse tweet XML for $this->network_username';
                        $this->logger->logStatus($status_message, get_class($this));
                        $status_message = "";

                    }

                    $got_latest_page_of_tweets = true;
                }
            }

            if ($this->owner_object->post_count == $this->instance->total_posts_in_system)
            $status_message .= "All of ".$this->owner_object->username.
            "'s tweets are in the system; Stopping tweet fetch.";


            $this->logger->logStatus($status_message, get_class($this));
            $status_message = "";
        } else {
            $this->logger->logStatus("Cannot fetch search results; Owner object has not been set.", get_class($this));
        }
    }

    private function processTweetURLs($tweet) {
        $ld = DAOFactory::getDAO('LinkDAO');

        $urls = Post::extractURLs($tweet['post_text']);
        foreach ($urls as $u) {
            //if it's an image (Twitpic/Twitgoo/Yfrog/Flickr for now)
            //insert direct path to thumb as expanded url, otherwise, just expand
            //set defaults
            $is_image = 0;
            $title = '';
            $eurl = '';
            //TODO Abstract out this image thumbnail link expansion into an Image Thumbnail plugin
            //modeled after the Flickr Thumbnails plugin
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
                $this->logger->logStatus("Inserted ".$u." (".$eurl.", ".$is_image."), into links table",
                get_class($this));
            } else {
                $this->logger->logStatus("Did NOT insert ".$u." (".$eurl.") into links table", get_class($this));
            }
        }
    }

    private function fetchAndAddTweetRepliedTo($tid, $pd) {
        if (!isset($this->owner_object)) {
            $this->fetchInstanceUserInfo();
        }

        if (isset($this->owner_object)) {
            //fetch tweet from Twitter and add to DB
            $status_message = "";
            $tweet_deets = str_replace("[id]", $tid, $this->api->cURL_source['show_tweet']);
            list($cURL_status, $twitter_data) = $this->api->apiRequest($tweet_deets);

            if ($cURL_status == 200) {
                try {
                    $tweets = $this->api->parseXML($twitter_data);
                    foreach ($tweets as $tweet) {
                        if ($pd->addPost($tweet, $this->owner_object, $this->logger) > 0) {
                            $status_message = 'Added replied to tweet ID '.$tid." to database.";
                            //expand and insert links contained in tweet
                            $this->processTweetURLs($tweet);
                        }
                    }
                }
                catch(Exception $e) {
                    $status_message = 'Could not parse tweet XML for $id';
                }
            } elseif ($cURL_status == 404 || $cURL_status == 403) {
                try {
                    $e = $this->api->parseError($twitter_data);
                    $ped = DAOFactory::getDAO('PostErrorDAO');
                    $ped->insertError($tid, 'twitter', $cURL_status, $e['error'], $this->owner_object->user_id);
                    $status_message = 'Error saved to tweets.';
                }
                catch(Exception $e) {
                    $status_message = 'Could not parse tweet XML for $tid';
                }
            }
            $this->logger->logStatus($status_message, get_class($this));
            $status_message = "";
        } else {
            $this->logger->logStatus("Cannot fetch search results; Owner object has not been set.", get_class($this));
        }
    }

    public function fetchInstanceUserMentions() {
        if (!isset($this->owner_object)) {
            $this->fetchInstanceUserInfo();
        }

        if (isset($this->owner_object)) {
            $status_message = "";
            // Get owner's mentions
            if ($this->api->available_api_calls_for_crawler > 0) {
                $got_newest_mentions = false;
                $continue_fetching = true;

                while ($this->api->available && $this->api->available_api_calls_for_crawler > 0 && $continue_fetching) {
                    # Get the most recent mentions
                    $mentions = $this->api->cURL_source['mentions'];
                    $args = array();
                    $args['count'] = 200;
                    $args['include_rts']='true';

                    if ($got_newest_mentions) {
                        $this->last_page_fetched_mentions++;
                        $args['page'] = $this->last_page_fetched_mentions;
                    }

                    list($cURL_status, $twitter_data) = $this->api->apiRequest($mentions, $args);
                    if ($cURL_status > 200) {
                        $continue_fetching = false;
                    } else {
                        try {
                            $count = 0;
                            $tweets = $this->api->parseXML($twitter_data);
                            if (count($tweets) == 0 && $got_newest_mentions) {# you're paged back and no new tweets
                                $this->last_page_fetched_mentions = 1;
                                $continue_fetching = false;
                                $this->instance->is_archive_loaded_mentions = true;
                                $status_message = 'Paged back but not finding new mentions; moving on.';
                                $this->logger->logStatus($status_message, get_class($this));
                                $status_message = "";
                            }


                            $pd = DAOFactory::getDAO('PostDAO');
                            if (!isset($recentTweets)) {
                                $recentTweets = $pd->getAllPosts($this->owner_object->user_id, 'twitter', 100);
                            }
                            $count = 0;
                            foreach ($tweets as $tweet) {
                                // Figure out if the mention is a retweet
                                if (RetweetDetector::isRetweet($tweet['post_text'], $this->owner_object->username)) {
                                    $this->logger->logStatus("Retweet found, ".substr($tweet['post_text'], 0, 50).
                                    "... ", get_class($this));
                                    $originalTweetId = RetweetDetector::detectOriginalTweet($tweet['post_text'],
                                    $recentTweets);
                                    if ($originalTweetId != false) {
                                        $tweet['in_retweet_of_post_id'] = $originalTweetId;
                                        $this->logger->logStatus("Retweet original status ID found: ".$originalTweetId,
                                        get_class($this));
                                    }
                                }

                                if ($pd->addPost($tweet, $this->owner_object, $this->logger) > 0) {
                                    $count++;
                                    //expand and insert links contained in tweet
                                    $this->processTweetURLs($tweet);
                                    if ($tweet['user_id'] != $this->owner_object->user_id) {
                                        //don't update owner info from reply
                                        $u = new User($tweet, 'mentions');
                                        $this->user_dao->updateUser($u);
                                    }

                                }

                            }
                            $status_message .= count($tweets)." mentions found and $count saved";
                            $this->logger->logStatus($status_message, get_class($this));
                            $status_message = "";

                            $got_newest_mentions = true;

                            $this->logger->logStatus($status_message, get_class($this));
                            $status_message = "";

                            if ($got_newest_mentions && $this->instance->is_archive_loaded_replies) {
                                $continue_fetching = false;
                                $status_message .= 'Retrieved newest mentions; Archive loaded; Stopping reply fetch.';
                                $this->logger->logStatus($status_message, get_class($this));
                                $status_message = "";
                            }

                        }
                        catch(Exception $e) {
                            $status_message = 'Could not parse mentions XML for $this->owner_object->username';
                            $this->logger->logStatus($status_message, get_class($this));
                            $status_message = "";
                        }
                    }

                }
            } else {
                $status_message = 'Crawler API error: either call limit exceeded or API returned an error.';
            }

            $this->logger->logStatus($status_message, get_class($this));
            $status_message = "";
        } else {
            $this->logger->logStatus("Cannot fetch search results; Owner object has not been set.", get_class($this));
        }
    }


    /**
     * Retrieve recent retweets and add them to the database
     */
    public function fetchRetweetsOfInstanceUser() {
        if (!isset($this->owner_object)) {
            $this->fetchInstanceUserInfo();
        }

        if (isset($this->owner_object)) {
            $status_message = "";
            // Get owner's mentions
            if ($this->api->available && $this->api->available_api_calls_for_crawler > 0) {
                # Get the most recent retweets
                $rtsofme = $this->api->cURL_source['retweets_of_me'];
                list($cURL_status, $twitter_data) = $this->api->apiRequest($rtsofme);
                if ($cURL_status == 200) {
                    try {
                        $tweets = $this->api->parseXML($twitter_data);
                        foreach ($tweets as $tweet) {
                            $this->fetchStatusRetweets($tweet);
                        }
                    } catch(Exception $e) {
                        $status_message = 'Could not parse retweets_of_me XML for $this->owner_object->username';
                        $this->logger->logStatus($status_message, get_class($this));
                        $status_message = "";
                    }
                } else {
                    $status_message .= 'API returned error code '. $cURL_status;
                }
            } else {
                $status_message .= 'Crawler API error: either call limit exceeded or API returned an error.';
            }

            $this->logger->logStatus($status_message, get_class($this));
            $status_message = "";
        } else {
            $this->logger->logStatus("Cannot fetch search results; Owner object has not been set.", get_class($this));
        }
    }

    /**
     * Retrieve retweets of given status
     * @param array $status
     */
    private function fetchStatusRetweets($status) {
        $status_id = $status["post_id"];
        $status_message = "";
        // Get owner's mentions
        if ($this->api->available && $this->api->available_api_calls_for_crawler > 0) {
            # Get the most recent mentions
            $rts = str_replace("[id]", $status_id, $this->api->cURL_source['retweeted_by']);
            list($cURL_status, $twitter_data) = $this->api->apiRequest($rts);
            if ($cURL_status == 200) {
                try {
                    $tweets = $this->api->parseXML($twitter_data);
                    foreach ($tweets as $tweet) {
                        $user_with_retweet = new User($tweet, 'retweets');
                        $this->fetchUserTimelineForRetweet($status, $user_with_retweet);
                    }
                } catch (Exception $e) {
                    $status_message = 'Could not parse retweeted_by XML for $this->owner_object->username';
                    $this->logger->logStatus($status_message, get_class($this));
                    $status_message = "";
                }
            } else {
                $status_message .= 'API returned error code '. $cURL_status;
            }
        } else {
            $status_message .= 'Crawler API error: either call limit exceeded or API returned an error.';
        }
        $this->logger->logStatus($status_message, get_class($this));
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
                try {
                    $count = 0;
                    $tweets = $this->api->parseXML($twitter_data);

                    if (count($tweets) > 0) {
                        $pd = DAOFactory::getDAO('PostDAO');
                        foreach ($tweets as $tweet) {
                            if (RetweetDetector::isRetweet($tweet['post_text'], $this->owner_object->username)) {
                                $this->logger->logStatus("Retweet by ".$tweet['user_name']. " found, ".
                                substr($tweet['post_text'], 0, 50)."... ", get_class($this));
                                if ( RetweetDetector::isRetweetOfTweet($tweet["post_text"],
                                $retweeted_status["post_text"]) ){
                                    $tweet['in_retweet_of_post_id'] = $retweeted_status_id;
                                    $this->logger->logStatus("Retweet by ".$tweet['user_name']." of ".
                                    $this->owner_object->username." original status ID found: ".$retweeted_status_id,
                                    get_class($this));
                                } else {
                                    $this->logger->logStatus("Retweet by ".$tweet['user_name']." of ".
                                    $this->owner_object->username." original status ID NOT found: ".
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
                        $this->logger->logStatus(count($tweets)." tweet(s) found in usertimeline via retweet for ".
                        $user_with_retweet->username." and $count saved", get_class($this));
                    }
                } catch(Exception $e) {
                    $this->logger->logStatus($e->getMessage(), get_class($this));
                    $this->logger->logStatus('Could not parse timeline for retweets XML for '.
                    $user_with_retweet->username, get_class($this));
                }
            } elseif ($cURL_status == 401) { //not authorized to see user timeline
                //don't set API to unavailable just because a private user retweeted
                $this->api->available = true;
                $status_message .= 'Not authorized to see '.$user_with_retweet->username."'s timeline;moving on.";
            } else {
                $status_message .= 'API returned error code '. $cURL_status;
            }
        } else {
            $status_message .= 'Crawler API error: either call limit exceeded or API returned an error.';
        }
        $this->logger->logStatus($status_message, get_class($this));
    }

    private function fetchInstanceUserFollowersByIDs() {
        $continue_fetching = true;
        $status_message = "";

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

                try {
                    $status_message = "Parsing XML. ";
                    $status_message .= "Cursor ".$next_cursor.":";
                    $ids = $this->api->parseXML($twitter_data);
                    $next_cursor = $this->api->getNextCursor();
                    $status_message .= count($ids)." follower IDs queued to update. ";
                    $this->logger->logStatus($status_message, get_class($this));
                    $status_message = "";


                    if (count($ids) == 0) {
                        $this->instance->is_archive_loaded_follows = true;
                        $continue_fetching = false;
                    }

                    $updated_follow_count = 0;
                    $inserted_follow_count = 0;
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

                    $status_message .= "$updated_follow_count existing follows updated; ".$inserted_follow_count.
                    " new follows inserted.";
                }
                catch(Exception $e) {
                    $status_message = 'Could not parse follower ID XML for $crawler_twitter_username';
                }
                $this->logger->logStatus($status_message, get_class($this));
                $status_message = "";

            }

            $this->logger->logStatus($status_message, get_class($this));
            $status_message = "";

        }

    }

    public function fetchInstanceUserFollowers() {
        if (!isset($this->owner_object)) {
            $this->fetchInstanceUserInfo();
        }

        if (isset($this->owner_object)) {
            $status_message = "";
            // Get owner's followers: Page back only if more than 2% of follows are missing from database
            // See how many are missing from last run
            if ($this->instance->is_archive_loaded_follows) { //all pages have been loaded
                $this->logger->logStatus("Follower archive marked as loaded", get_class($this));

                //find out how many new follows owner has compared to what's in db
                $new_follower_count = $this->owner_object->follower_count - $this->instance->total_follows_in_system;
                $status_message = "New follower count is ".$this->owner_object->follower_count." and system has ".
                $this->instance->total_follows_in_system."; ".$new_follower_count." new follows to load";
                $this->logger->logStatus($status_message, get_class($this));

                if ($new_follower_count > 0) {
                    $this->logger->logStatus("Fetching follows via IDs", get_class($this));
                    $this->fetchInstanceUserFollowersByIDs();
                }
            } else {
                $this->logger->logStatus("Follower archive is not loaded; fetch should begin.", get_class($this));
            }

            # Fetch follower pages
            $continue_fetching = true;
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

                    try {
                        $status_message = "Parsing XML. ";
                        $status_message .= "Cursor ".$next_cursor.":";
                        $users = $this->api->parseXML($twitter_data);
                        $next_cursor = $this->api->getNextCursor();
                        $status_message .= count($users)." followers queued to update. ";
                        $this->logger->logStatus($status_message, get_class($this));
                        $status_message = "";

                        if (count($users) == 0)
                        $this->instance->is_archive_loaded_follows = true;

                        $updated_follow_count = 0;
                        $inserted_follow_count = 0;
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

                        $status_message .= "$updated_follow_count existing follows updated; ".$inserted_follow_count.
                    " new follows inserted.";
                    }
                    catch(Exception $e) {
                        $status_message = 'Could not parse followers XML for $crawler_twitter_username';
                    }
                    $this->logger->logStatus($status_message, get_class($this));
                    $status_message = "";

                }

                $this->logger->logStatus($status_message, get_class($this));
                $status_message = "";
            }
        } else {
            $this->logger->logStatus("Cannot fetch search results; Owner object has not been set.", get_class($this));
        }

    }

    public function fetchInstanceUserFriends() {
        if (!isset($this->owner_object)) {
            $this->fetchInstanceUserInfo();
        }

        if (isset($this->owner_object)) {
            $fd = DAOFactory::getDAO('FollowDAO');
            $this->instance->total_friends_in_system = $fd->countTotalFriends($this->instance->network_user_id,
            'twitter');

            if ($this->instance->total_friends_in_system
            < $this->owner_object->friend_count) {
                $this->instance->is_archive_loaded_friends = false;
                $this->logger->logStatus($this->instance->total_friends_in_system." friends in system, ".
                $this->owner_object->friend_count." friends according to Twitter; Friend archive is not loaded",
                get_class($this));
            } else {
                $this->instance->is_archive_loaded_friends = true;
                $this->logger->logStatus("Friend archive loaded", get_class($this));
            }

            $status_message = "";
            # Fetch friend pages
            $continue_fetching = true;
            while ($this->api->available && $this->api->available_api_calls_for_crawler > 0 && $continue_fetching
            && !$this->instance->is_archive_loaded_friends) {

                $friend_ids = $this->api->cURL_source['following'];
                $args = array();
                if (!isset($next_cursor))
                $next_cursor = -1;
                $args['cursor'] = strval($next_cursor);

                list($cURL_status, $twitter_data) = $this->api->apiRequest($friend_ids, $args);

                if ($cURL_status > 200) {
                    $continue_fetching = false;
                } else {

                    try {
                        $status_message = "Parsing XML. ";
                        $status_message .= "Cursor ".$next_cursor.":";
                        $users = $this->api->parseXML($twitter_data);
                        $next_cursor = $this->api->getNextCursor();
                        $status_message .= count($users)." friends queued to update. ";
                        $this->logger->logStatus($status_message, get_class($this));
                        $status_message = "";

                        $updated_follow_count = 0;
                        $inserted_follow_count = 0;

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

                        $status_message .= "$updated_follow_count existing friends updated; ".$inserted_follow_count.
                    " new friends inserted.";
                    }
                    catch(Exception $e) {
                        $status_message = 'Could not parse friends XML for $crawler_twitter_username';
                    }
                    $this->logger->logStatus($status_message, get_class($this));
                    $status_message = "";

                }

                $this->logger->logStatus($status_message, get_class($this));
                $status_message = "";
            }
        } else {
            $this->logger->logStatus("Cannot fetch search results; Owner object has not been set.", get_class($this));
        }
    }

    public function fetchFriendTweetsAndFriends() {
        if (!isset($this->owner_object)) {
            $this->fetchInstanceUserInfo();
        }

        if (isset($this->owner_object)) {
            $fd = DAOFactory::getDAO('FollowDAO');
            $pd = DAOFactory::getDAO('PostDAO');

            $continue_fetching = true;
            while ($this->api->available && $this->api->available_api_calls_for_crawler > 0 && $continue_fetching) {
                $stale_friend = $fd->getStalestFriend($this->owner_object->user_id, 'twitter');
                if ($stale_friend != null) {
                    $this->logger->logStatus($stale_friend->username." is friend most need of update",
                    get_class($this));
                    $stale_friend_tweets = str_replace("[id]", $stale_friend->username,
                    $this->api->cURL_source['user_timeline']);
                    $args = array();
                    $args["count"] = 200;

                    if ($stale_friend->last_post_id > 0) {
                        $args['since_id'] = $stale_friend->last_post_id;
                    }

                    list($cURL_status, $twitter_data) = $this->api->apiRequest($stale_friend_tweets, $args);

                    if ($cURL_status == 200) {
                        try {
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

                            $this->logger->logStatus(count($tweets)." tweet(s) found for ".$stale_friend->username.
                            " and ". $count." saved", get_class($this));
                        }
                        catch(Exception $e) {
                            $this->logger->logStatus('Could not parse friends XML for $stale_friend->username',
                            get_class($this));
                        }
                        $this->fetchUserFriendsByIDs($stale_friend->user_id, $fd);
                    } elseif ($cURL_status == 401 || $cURL_status == 404) {
                        try {
                            $e = $this->api->parseError($twitter_data);
                            $ued = DAOFactory::getDAO('UserErrorDAO');
                            $ued->insertError($stale_friend->user_id, $cURL_status, $e['error'],
                            $this->owner_object->user_id, 'twitter');
                            $this->logger->logStatus('User error saved', get_class($this));
                        }
                        catch(Exception $e) {
                            $this->logger->logStatus('Could not parse timeline error for $stale_friend->username',
                            get_class($this));
                        }
                    }
                } else {
                    $this->logger->logStatus('No friend staler than 1 day', get_class($this));
                    $continue_fetching = false;
                }
            }
        } else {
            $this->logger->logStatus("Cannot fetch search results; Owner object has not been set.", get_class($this));
        }
    }

    public function fetchStrayRepliedToTweets() {
        if (!isset($this->owner_object)) {
            $this->fetchInstanceUserInfo();
        }

        if (isset($this->owner_object)) {
            $pd = DAOFactory::getDAO('PostDAO');
            $strays = $pd->getStrayRepliedToPosts($this->owner_object->user_id, $this->owner_object->network);
            $status_message = count($strays).' stray replied-to tweets to load for user ID '.$this->owner_object->user_id .
        ' on '.$this->owner_object->network;
            $this->logger->logStatus($status_message, get_class($this));

            foreach ($strays as $s) {
                if ($this->api->available && $this->api->available_api_calls_for_crawler > 0)
                $this->fetchAndAddTweetRepliedTo($s['in_reply_to_post_id'], $pd);
            }
        } else {
            $this->logger->logStatus("Cannot fetch search results; Owner object has not been set.", get_class($this));
        }
    }

    public function fetchUnloadedFollowerDetails() {
        if (!isset($this->owner_object)) {
            $this->fetchInstanceUserInfo();
        }

        if (isset($this->owner_object)) {
            $fd = DAOFactory::getDAO('FollowDAO');
            $strays = $fd->getUnloadedFollowerDetails($this->owner_object->user_id, 'twitter');
            $status_message = count($strays).' unloaded follower details to load.';
            $this->logger->logStatus($status_message, get_class($this));

            foreach ($strays as $s) {
                if ($this->api->available && $this->api->available_api_calls_for_crawler > 0)
                $this->fetchAndAddUser($s['follower_id'], "Follower IDs");
            }
        } else {
            $this->logger->logStatus("Cannot fetch search results; Owner object has not been set.", get_class($this));
        }
    }

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

                try {

                    $status_message = "Parsing XML. ";
                    $status_message .= "Cursor ".$next_cursor.":";
                    $ids = $this->api->parseXML($twitter_data);
                    $next_cursor = $this->api->getNextCursor();
                    $status_message .= count($ids)." friend IDs queued to update. ";
                    $this->logger->logStatus($status_message, get_class($this));
                    $status_message = "";


                    if (count($ids) == 0)
                    $continue_fetching = false;

                    $updated_follow_count = 0;
                    $inserted_follow_count = 0;
                    foreach ($ids as $id) {

                        # add/update follow relationship
                        if ($fd->followExists($id['id'], $uid, 'twitter')) {
                            //update it
                            if ($fd->update($id['id'], $uid, 'twitter', Utils::getURLWithParams($friend_ids, $args)))
                            $updated_follow_count++;
                        } else {
                            //insert it
                            if ($fd->insert($id['id'], $uid, 'twitter', Utils::getURLWithParams($friend_ids, $args)))
                            $inserted_follow_count++;
                        }
                    }

                    $status_message .= "$updated_follow_count existing follows updated; ".$inserted_follow_count.
                    " new follows inserted.";
                }
                catch(Exception $e) {
                    $status_message = 'Could not parse follower ID XML for $uid';
                }
                $this->logger->logStatus($status_message, get_class($this));
                $status_message = "";

            }

            $this->logger->logStatus($status_message, get_class($this));
            $status_message = "";
        }
    }

    private function fetchAndAddUser($fid, $source) {
        //fetch user from Twitter and add to DB
        $status_message = "";
        $u_deets = str_replace("[id]", $fid, $this->api->cURL_source['show_user']);
        list($cURL_status, $twitter_data) = $this->api->apiRequest($u_deets);

        if ($cURL_status == 200) {
            try {
                $user_arr = $this->api->parseXML($twitter_data);
                $user = new User($user_arr[0], $source);
                $this->user_dao->updateUser($user);
                $status_message = 'Added/updated user '.$user->username." in database";
            }
            catch(Exception $e) {
                $status_message = 'Could not parse tweet XML for $uid';
            }
        } elseif ($cURL_status == 404) {
            try {
                $e = $this->api->parseError($twitter_data);
                $ued = DAOFactory::getDAO('UserErrorDAO');
                $ued->insertError($fid, $cURL_status, $e['error'], $this->owner_object->user_id, 'twitter');
                $status_message = 'User error saved.';

            }
            catch(Exception $e) {
                $status_message = 'Could not parse tweet XML for $uid';
            }

        }
        $this->logger->logStatus($status_message, get_class($this));
        $status_message = "";

    }

    // For each API call left, grab oldest follow relationship, check if it exists, and update table
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
                    try {
                        $friendship = $this->api->parseXML($twitter_data);
                        if ($friendship['source_follows_target'] == 'true')
                        $fd->update($oldfollow["followee_id"], $oldfollow["follower_id"], 'twitter',
                        Utils::getURLWithParams($friendship_call, $args));
                        else
                        $fd->deactivate($oldfollow["followee_id"], $oldfollow["follower_id"], 'twitter',
                        Utils::getURLWithParams($friendship_call, $args));

                        if ($friendship['target_follows_source'] == 'true')
                        $fd->update($oldfollow["follower_id"], $oldfollow["followee_id"], 'twitter',
                        Utils::getURLWithParams($friendship_call, $args));
                        else
                        $fd->deactivate($oldfollow["follower_id"], $oldfollow["followee_id"], 'twitter',
                        Utils::getURLWithParams($friendship_call, $args));


                    }
                    catch(Exception $e) {
                        $status_message = 'Could not parse friendship XML';
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