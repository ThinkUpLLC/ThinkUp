<?php
/**
 *
 * ThinkUp/webapp/plugins/twitterrealtime/model/class.TwitterJSONStreamParser.php
 *
 * Copyright (c) 2011-2012 Amy Unruh
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
 * Twitter JSON Stream Parser
 * The methods of this class take JSON strings generated from the UserStream API.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2012 Amy Unruh
 * @author Amy Unruh
 */

// currently, since RetweetDectector lives in the twitter plugin, it will not be loaded unless this plugin is active.
// since it is needed for the twitter realtime plugin as well, we load it explicitly here.
// @TODO - do we need a 'shared' location for code used with multiple plugins?
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.RetweetDetector.php';

class TwitterJSONStreamParser {
    /**
     * @var Logger
     */
    var $logger;

    public function __construct() {
        $this->logger = Logger::getInstance('stream_log_location');
    }

    /**
     * @param  $data
     * @return bool
     */
    public function parseJSON($data) {
        $status = true;

        $content = json_decode($data, true); //@TODO what does the 2nd argument do?
        $logger = Logger::getInstance('stream_log_location');

        if (!is_array($content)) {
            return false;
        }
        // route depending upon content
        if (isset($content['friends'])) {
            $logger->logDebug("Friends list: $content", __METHOD__.','.__LINE__);
            // the list of the friends of the auth'd user.
            // @TODO - currently, not doing anything with this information.  Should probably use it
            // to update the database.
        } elseif (isset($content['delete'])) {
            // a tweet deletion
            $post_dao = DAOFactory::getDAO('PostDAO');
            $post = $post_dao->getPost($content['delete']['status']['id_str'], 'twitter');
            if ($post) {
                $delstr =  "deleted post: " . $post->post_id . ": " . $post->post_text . " , by: " .
                $post->author_username . ", id: " . $post->id . "\n";
                $this->logger->logInfo($delstr, __METHOD__.','.__LINE__);
                $post_dao->deletePost($post->id);
            }
        } elseif (isset($content['event'])) {
            // have an event such as a follow or a favorite
            if ($content["event"] == "follow") {
                $logger->logDebug("****Follow event: new follower " . $content["source"]["id"] .
                " of: " . $content["target"]["id"], __METHOD__.','.__LINE__);
                //@TODO handle, update friends info
            } elseif ($content["event"] == "favorite") {
                $target_id = $content['target']['id'];
                $source_id = $content['source']['id'];
                $target_name = $content['target']['screen_name'];
                $source_name = $content['source']['screen_name'];
                $logger->logDebug("favorite event with source $source_id, $source_name " .
                "and target $target_id, $target_name", __METHOD__.','.__LINE__);
                $this->addFavorite($content);
            } else {
                $logger->logError("currently unhandled event: " . $content['event'], __METHOD__.','.__LINE__);
            }
        } elseif (isset($content['text'])) {
            // a 'regular' post, perhaps a reply, perhaps a retweet
            $this->addPost($content);
        } else {
            $logger->logDebug("not processing content: $content", __METHOD__.','.__LINE__);
            $status = false;
        }
        return $status;
    }

    /**
     * @param  $content
     * @return int
     */
    private function addPost($content) {
        list($post, $entities, $user_array) = $this->parsePost($content);
        $post_dao = DAOFactory::getDAO('PostDAO');
        $post_dao->setLoggerInstance($this->logger);
        $inserted_post_key = $post_dao->addPostAndAssociatedInfo($post, $entities, $user_array);
        return $inserted_post_key;
    }

    /**
     * @param  $content
     * @return void
     */
    private function addFavorite($content) {
        $logger = Logger::getInstance('stream_log_location');
        $target_id = $content['target']['id'];
        $source_id = $content['source']['id'];
        $fav_post = $content['target_object'];
        list($post, $entities, $user_array) = $this->parsePost($fav_post);
        $source_user = $this->parseUser($content['source']);
        $logger->logDebug("source user: " . print_r($source_user, true), __METHOD__.','.__LINE__);

        $fd = DAOFactory::getDAO('FavoritePostDAO');
        $fd->setLoggerInstance($this->logger);
        if ($source_user) {
            $ud = DAOFactory::getDAO('UserDAO');
            $ud->setLoggerInstance($this->logger);
            $u = new User($source_user);
            $logger->logDebug("in addFavorite, adding or updating source user: " . $u->user_id, __METHOD__.','.
            __LINE__);
            $ud->updateUser($u);
        } else {
            $logger->logDebug("in addFavorite--could not get source user information-- should have been available."
            . $u->user_id, __METHOD__.','.__LINE__);
        }
        $retval = $fd->addFavorite($source_id, $post, $entities, $user_array);
        if ($retval) {
            $this->logger->logInfo("found new fav: " . $post['post_id'], __METHOD__.','.__LINE__);
        }
    }

    /**
     * @param  $user
     * @param null $last_post
     * @return array
     */
    private function parseUser($user, $last_post = null) {
        $user_array = array();

        $user_array['user_id'] = $user['id'];
        $user_array['user_name'] = $user['screen_name'];
        $user_array['full_name'] = $user['name'];
        $user_array['avatar'] = $user['profile_image_url'];
        $user_array['follower_count'] = $user['followers_count'];
        $user_array['is_protected'] = $user['protected'];
        $user_array['location'] = $user['location'];
        $user_array['description'] = $user['description'];
        $user_array['friend_count'] = $user['friends_count'];
        $user_array['post_count'] = $user['statuses_count'];
        $user_array['joined'] = gmdate("Y-m-d H:i:s", strToTime($user['created_at']));
        $user_array['url'] = $user['url'];
        $user_array['network'] = 'twitter';

        if ($last_post) {
            $user_array['last_post'] = $last_post;
        }

        return $user_array;
    }

    /**
     * @param  $content
     * @return array
     */
    private function parsePost($content) {
        $logger = Logger::getInstance('stream_log_location');
        $rt_string = "RT @";
        $post = array();
        $mentions = array();
        $urls = array();
        $hashtags = array();
        $entities = array();
        $user_array = array();

        try {
            $post['is_rt'] = false;
            $post['in_rt_of_user_id'] = '';
            $user = $content['user'];

            // parse info into user and post arrays
            $post['post_id'] = $content['id_str'];
            $post['author_user_id'] = $user['id_str'];
            $post['author_username'] = $user['screen_name'];
            $post['author_fullname'] = $user['name'];
            $post['author_avatar'] = $user['profile_image_url'];
            $post['author_follower_count'] = $user['followers_count'];
            $post['post_text'] = $content['text'];
            $post['is_protected'] = $user['protected'];
            $post['source'] = $content['source'];
            $post['location'] = $user['location'];
            $post['description'] = $user['description'];
            $post['url'] = $user['url'];
            $post['author_friends_count'] = $user['friends_count'];
            $post['author_post_count'] = $user['statuses_count'];
            $post['author_joined'] = gmdate("Y-m-d H:i:s", strToTime($user['created_at']));
            $post['favorited'] = $content['favorited'];
            $user_array['url'] = $user['url'];
            // for now, retain existing 'place' handling, where a place is set in the post.
            // Set new place_id field as well, and add point coord information if it exists
            $logger->logDebug("point coords: " . print_r($content['coordinates'], true), __METHOD__.','.__LINE__);
            $place = $content['place'];
            if ($place != null) {
                $post['place'] = $place['full_name'];
                $post['place_id'] = $place['id'];
                if (isset($content['coordinates'])) {
                    $place['point_coords'] = $content['coordinates'];
                }
            } else {
                $post['place'] = null;
                $post['place_id'] = null;
                // it's possible for the point coords to be set even if the place is not.
                if (isset($content['coordinates'])) {
                    $place = array();
                    $place['point_coords'] = $content['coordinates'];
                }
            }

            $post['pub_date'] = gmdate("Y-m-d H:i:s", strToTime($content['created_at']));
            $post['in_reply_to_user_id'] = $content['in_reply_to_user_id_str'];
            $post['in_reply_to_post_id'] = $content['in_reply_to_status_id_str'];
            $post['network'] = 'twitter';
            $post['reply_count_cache'] = 0;

            if (isset($content['entities'])) {
                foreach ($content['entities']['user_mentions'] as $m) {
                    $mention_info = array();
                    $mention_info['user_id'] = $m['id_str'];
                    $mention_info['user_name'] = $m['screen_name'];
                    $mentions[] = $mention_info;
                }
                // get urls
                foreach ($content['entities']['urls'] as $u) {
                    // This block broken under 0.11
                    /*
                    $url_info = array();
                    $url_info['url']= $u['url'];
                    if (isset($u['expanded_url'])) {
                    $url_info['expanded_url'] = $u['expanded_url'];
                    print "expanded url for: " . $url_info['url'] . ": " . $url_info['expanded_url'] . "\n";
                    } else {
                    $url_info['expanded_url'] = '';
                    }
                    $urls[] = $url_info;
                    */
                    // just need an array of urls now...
                    if ( isset($u['expanded_url']) ) {
                        array_push($urls, $u['expanded_url']);
                    } else if (isset($u['url'])) {
                        array_push($urls, $u['url']);
                    }
                }
                // get hashtags
                foreach ($content['entities']['hashtags'] as $h) {
                    $hashtags[] = $h['text'];
                }
            }

            $logger->logDebug($post['post_text'] . " -- " . $post['author_username'], __METHOD__.','.__LINE__);
            if ( !isset($content['retweeted_status'])) {
                if (isset($content['retweet_count'])) {
                    // do this only for the original post (rt will have rt count too)
                    $retweet_count_api = $content['retweet_count'];
                    $pos = strrpos($content['retweet_count'], '+');
                    if ($pos != false) {
                        // remove '+', e.g. '100+' -- so currently 100 is max that can be indicated
                        $retweet_count_api = substr($content['retweet_count'], 0, $pos) ;
                    }
                    $post['retweet_count_api'] = $retweet_count_api;
                    $this->logger->logDebug($content['id_str'] . " is not a retweet but orig., count is: " .
                    $content['retweet_count'] . "/ ".
                    $retweet_count_api, __METHOD__.','.__LINE__);
                }
                // // parse to see if 'old-style' retweet "RT @..." for first 'mention'-- if so, set that information
                if (sizeof($mentions) > 0) {
                    $first_mention = $mentions[0]['user_name'];
                    $logger->logDebug("first mention: $first_mention", __METHOD__.','.__LINE__);
                    if (RetweetDetector::isRetweet($post['post_text'], $first_mention)) {
                        $post['is_rt'] = true;
                        $post['in_rt_of_user_id'] = $mentions[0]['user_id'];
                        $logger->logDebug("detected retweet of: " . $post['in_rt_of_user_id'] . ", " .
                        $first_mention, __METHOD__.','.__LINE__);
                    }
                }
            } else {
                // then this is a retweet.
                // Process its original too.
                $this->logger->logInfo("this is a retweet, will first process original post " .
                $content['retweeted_status']['id_str'] .
                   " from user " . $content['retweeted_status']['user']['id_str'], __METHOD__.','.__LINE__);
                list($orig_post, $orig_entities, $orig_user_array) = $this->parsePost($content['retweeted_status']);
                $rtp = array();
                $rtp['content'] = $orig_post;
                $rtp['entities'] = $orig_entities;
                $rtp['user_array'] = $orig_user_array;
                $post['retweeted_post'] = $rtp;
                $post['in_retweet_of_post_id'] = $content['retweeted_status']['id_str'];
                $post['in_rt_of_user_id'] = $content['retweeted_status']['user']['id_str'];
            }

            $user_array = $this->parseUser($user, $post['pub_date']);

        } catch (Exception $e) {
            $logger->logErrro("exception: $e", __METHOD__.','.__LINE__);
        }
        $entities['urls'] = $urls; $entities['mentions'] = $mentions; $entities['hashtags'] = $hashtags;
        $entities['place'] = $place; // add 'place' object to entities array; may be null

        return array($post, $entities, $user_array);
    }
}
