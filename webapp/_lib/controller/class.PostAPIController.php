<?php

/**
 *
 * ThinkUp/webapp/_lib/controller/class.PostAPIController.php
 *
 * Copyright (c) 2011-2013 Sam Rose
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
 * Post API Controller
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 Sam Rose
 * @author Sam Rose <samwho@lbak.co.uk>
 *
 */
class PostAPIController extends ThinkUpController {
    /**
     * The network to query. Defaults to twitter.
     * @var str
     */
    public $network = 'twitter';
    /**
     * The user ID to use. No default value. In requests that require user data, either this or username must be set.
     * @var int
     */
    public $user_id;
    /**
     * The username to use. No default value. In requests that require user data, either this or user ID must be set.
     * @var str
     */
    public $username;
    /**
     * The post ID to use. No default value.
     * @var int
     */
    public $post_id;
    /**
     * The API call type to make. Defaults to "post".
     * @var str
     */
    public $type = 'post';
    /**
     * The number of results to return. Defaults to 20.
     * @var int
     */
    public $count = 20;
    /**
     * The page of results to return. Defaults to 1 (the first page).
     * @var int
     */
    public $page = 1;
    /**
     * What to order the results by. Does not work on all calls. Defaults to "default". Different calls handle this
     * value differently.
     * @var str
     */
    public $order_by = 'default';
    /**
     * The direction to order the results in. Defaults to DESC for descending order.
     * @var str DESC or ASC
     */
    public $direction = 'DESC';
    /**
     * In time range API calls, this is the starting date. Can be a Unix timestamp or a valid time string. Defaults to
     * 0 which represents midnight on Jan 1st 1970.
     * @var mixed
     */
    public $from = 0;
    /**
     * In time range API calls, this is the end date. Can be a Unix timestamp or a valid time string.
     * @var mixed
     */
    public $until;
    /**
     * In some API calls, the reply/retweet distance is returned as an integer. This variable defines whether that
     * variable is the distance in miles ("mi") or kilometers ("km"). Defaults to "km".
     * @var str
     */
    public $unit = 'km';
    /**
     * Whether or not to include replies to each tweet. Works on all API calls. Defaults to false.
     * @var bool
     */
    public $include_replies = false;
    /**
     * Whether or not to include tweet entities (links, mentions, hashtags). Defaults to false.
     * @var bool
     */
    public $include_entities = false;
    /**
     * Whether or not to trim the user to just the user ID. Defaults to false.
     * @var bool
     */
    public $trim_user = false;
    /**
     * Some API calls (user mentions) will, by default, not include retweets as mentions. Set this to true if you wish
     * to include retweets as mentions.
     * @var bool
     */
    public $include_rts = false;
    /**
     * The keyword to use. No default value. In requests that require hashtag data must be set.
     * @var str
     */
    public $keyword;    
    /**
     * A User object set when either the user_id or username variables are set. If you are using User data at any point
     * in this class, you should use this object.
     * @var User
     */
    private $user;
    /**
     *
     * @var PostDAO
     */
    private $post_dao;
    /**
     *
     * @var UserDAO
     */
    private $user_dao;
    /**
     * A Hashtag object set when either the hashtag_id or hashtag_name variables are set.
     * If you are using Hashtag data at any point in this class, you should use this object.
     * @var hashtag
     */
    private $hashtag;
    /**
     *
     * @var HashtagDAO
     */
    private $hashtag_dao;    
    /**
     * Constructor
     *
     * @param boolean $session_started
     */
    public function __construct($session_started=false) {
        parent::__construct($session_started);
        $this->setContentType('application/json');
        $this->view_mgr->cache_lifetime = 60;

        /*
         * START READ IN OF QUERY STRING VARS
         */
        if (isset($_GET['network'])) {
            $this->network = $_GET['network'];
        }
        if (isset($_GET['post_id'])) {
            if (is_numeric($_GET['post_id'])) {
                $this->post_id = $_GET['post_id'];
            }
        }
        if (isset($_GET['user_id'])) {
            if (is_numeric($_GET['user_id'])) {
                $this->user_id = $_GET['user_id'];
            }
        }
        if (isset($_GET['type'])) {
            $this->type = $_GET['type'];
        }
        if (isset($_GET['username'])) {
            $this->username = $_GET['username'];
        }
        if (isset($_GET['count'])) {
            if (is_numeric($_GET['count'])) {
                $this->count = (int) $_GET['count'] > 200 ? 200 : (int) $_GET['count'];
            }
        }
        if (isset($_GET['page'])) {
            if (is_numeric($_GET['page'])) {
                $this->page = (int) $_GET['page'];
            }
        }
        if (isset($_GET['order_by'])) {
            $this->order_by = $this->parseOrderBy($_GET['order_by']);
        }
        if (isset($_GET['direction'])) {
            $this->direction = $_GET['direction'] == 'DESC' ? 'DESC' : 'ASC';
        }
        if (isset($_GET['from'])) {
            $this->from = $_GET['from'];
        }
        if (isset($_GET['until'])) {
            $this->until = $_GET['until'];
        }
        if (isset($_GET['unit'])) {
            $this->unit = $_GET['unit'];
        }
        if (isset($_GET['include_replies'])) {
            $this->include_replies = $this->isTrue($_GET['include_replies']);
        }
        if (isset($_GET['include_entities'])) {
            $this->include_entities = $this->isTrue($_GET['include_entities']);
        }
        if (isset($_GET['trim_user'])) {
            $this->trim_user = $this->isTrue($_GET['trim_user']);
        }
        if (isset($_GET['include_rts'])) {
            $this->include_rts = $this->isTrue($_GET['include_rts']);
        }
        if (isset($_GET['keyword'])) {
            $this->keyword = $_GET['keyword'];
        }
        
        /*
         * END READ IN OF QUERY STRING VARS
         */

        // perhaps extend this in future to allow auth to see private posts
        $this->is_public = true;
    }

    /**
     * Convert the order_by option to database column.
     *
     * For example, 'date' gets converted into the appropriate database colum name: 'pub_date'.
     *
     * @param string $order_by The value from $_GET['order_by']
     * @return string A valid database column.
     */
    private function parseOrderBy($order_by) {
        switch ($order_by) {
            case 'date': $order_by = 'pub_date';
            break;
            case 'location': $order_by = 'location';
            break;
            case 'source': $order_by = 'source';
            break;
            case 'follower_count': $order_by = 'author_follower_count';
            break;
            case 'post_text': $order_by = 'post_text';
            break;
            case 'author_username': $order_by = 'author_username';
            break;

            default: $order_by = $this->order_by;
            break;
        }

        return $order_by;
    }

    /**
     * Determine whether the given value represents true or not. Used for the boolean $_GET values such as
     * trim_user and include_entities.
     *
     * @param string $var The value to determine.
     * @return bool True if $var is 't', 'true' or '1'.
     */
    private function isTrue($var) {
        if (isset($var) && !is_null($var)) {
            return $var == 'true' || $var == 't' || $var == '1';
        } else {
            return false;
        }
    }

    public function control() {
        /*
         * Check if the view is cached and, if it is, return the cached version before any of the application login
         * is executed.
         */
        if ($this->view_mgr->isViewCached()) {
            if ($this->view_mgr->is_cached('json.tpl', $this->getCacheKeyString())) {
                // set the json data to keep the ThinkUpController happy.
                $this->setJsonData(array());
                return $this->generateView();
            }
        }

        /*
         * Check if the API is disabled and, if it is, throw the appropriate exception.
         *
         * Docs: http://thinkup.com/docs/userguide/api/errors/apidisabled.html
         */
        $is_api_disabled = Config::getInstance()->getValue('is_api_disabled');
        if ($is_api_disabled) {
            throw new APIDisabledException();
        }

        // fetch the correct PostDAO and UserDAO from the DAOFactory
        $this->post_dao = DAOFactory::getDAO('PostDAO');
        $this->user_dao = DAOFactory::getDAO('UserDAO');
        $this->hashtag_dao = DAOFactory::getDAO('HashtagDAO');

        /*
         * Use the information gathered from the query string to retrieve a
         * User object. This will be the standard object with which to get
         * User information from in API calls.
         */
        if ($this->user_id != null) {
            $this->user = $this->user_dao->getDetails($this->user_id, $this->network);
        } else if ($this->username != null) {
            $this->user = $this->user_dao->getUserByName($this->username, $this->network);
        } else {
            $this->user = null;
        }
        
       /*
        * Use the information gathered from the query string to retrieve a
        * Hashtag object. This will be the standard object with which to get
        * Hashtag information from in API calls.
        */
        if (!is_null($this->keyword) && !is_null($this->network)) {
            $this->hashtag = $this->hashtag_dao->getHashtag($this->keyword,$this->network);
        } else {
            $this->hashtag = null;
        }
        
        //Privacy checks
        if (substr($this->type, 0, 4)=='user') { //user-related API call
            if (is_null($this->user)) {
                // Check why the User object is null. Could be missing required fields or not found.
                if (is_null($this->user_id) && is_null($this->username)) {
                    $m = 'A request of type ' . $this->type . ' requires a user_id or username to be specified.';
                    throw new RequiredArgumentMissingException($m);
                } else {
                    throw new UserNotFoundException();
                }
            } elseif ($this->user->is_protected) { //user is protected on originating network
                throw new UserNotFoundException();
            } else {
                $instance_dao = DAOFactory::getDAO('InstanceDAO');
                $instance = $instance_dao->getByUsernameOnNetwork($this->user->username, $this->user->network);
                if (isset($instance)) {
                    if (!$instance->is_public) { //user is protected on ThinkUp
                        throw new UserNotFoundException();
                    }
                }
            }
        } else { //post-related API call
            if ($this->network == "facebook") {
                //assume all Facebook posts are private
                throw new PostNotFoundException();
            }
        }

        /*
         * This switch statement is the main part of this function. It decides
         * what type of posts will be fetched depending on the "type" GET
         * variable and use the PostDAO to fetch the appropriate posts from
         * the database.
         *
         * If a required field is missing it will create an error field to
         * output in JSON.
         */
        switch ($this->type) {
            /*
             * Gets a post.
             *
             * Required arguments: post_id
             *
             * Optional arguments: network, include_entities, include_replies, trim_user
             *
             * Docs: http://thinkup.com/docs/userguide/api/posts/post.html
             */
            case 'post':
                if (is_null($this->post_id)) {
                    $m = 'A request of type ' . $this->type . ' requires a post_id to be specified.';
                    throw new RequiredArgumentMissingException($m);
                } else {
                    $data = $this->post_dao->getPost($this->post_id, $this->network, $this->is_public);
                }
                break;

                /*
                 * Gets all retweets to a post.
                 *
                 * Required arguments: post_id
                 *
                 * Optional arguments: network, order_by, unit, count, page, include_entities, include_replies,
                 * trim_user
                 *
                 * Docs: http://thinkup.com/docs/userguide/api/posts/post_retweets.html
                 */
            case 'post_retweets':
                if (is_null($this->post_id)) {
                    $m = 'A request of type ' . $this->type . ' requires a post_id to be specified.';
                    throw new RequiredArgumentMissingException($m);
                } else {
                    $data = $this->post_dao->getRetweetsOfPost($this->post_id, $this->network, $this->order_by,
                    $this->unit, $this->is_public, $this->count, $this->page);
                }
                break;

                /**
                 * Gets replies to a post.
                 *
                 * Required arguments: post_id
                 *
                 * Optional arguments: network, order_by, unit, count, page, include_entities, include_replies,
                 * trim_user
                 *
                 * Ordering can only be done by either location or follower count.
                 *
                 * Docs: http://thinkup.com/docs/userguide/api/posts/post_replies.html
                 */
            case 'post_replies':
                if (is_null($this->post_id)) {
                    $m = 'A request of type ' . $this->type . ' requires a post_id to be specified.';
                    throw new RequiredArgumentMissingException($m);
                } else {
                    $data = $this->post_dao->getRepliesToPost($this->post_id, $this->network, $this->order_by,
                    $this->unit, $this->is_public, $this->count, $this->page);
                }
                break;


                /*
                 * Gets replies to a post within a date range.
                 *
                 * Required arguments: post_id, from and until
                 *
                 * Optional arguments: network, order_by, unit, count, page, include_entities, include_replies,
                 * trim_user
                 *
                 * Ordering can only be done by either location or follower count.
                 *
                 * Docs: http://thinkup.com/docs/userguide/api/posts/post_replies.html
                 */
            case 'post_replies_in_range':
                if (is_null($this->post_id) || is_null($this->from) || is_null($this->until)) {
                    $m = 'A request of type ' . $this->type . ' requires a post_id to be specified.';
                    throw new RequiredArgumentMissingException($m);
                } else {
                    $data = $this->post_dao->getRepliesToPostInRange($this->post_id, $this->network, $this->from,
                    $this->until, $this->order_by, $this->unit, $this->is_public, $this->count, $this->page);
                }
                break;

                /*
                 * Get posts related to a post (replies to it, retweets of it).
                 *
                 * Required arguments: post_id
                 *
                 * Optional arguments: network, count, page, geo_encoded_only, include_original_post, include_entities,
                 * include_replies, trim_user
                 *
                 * Docs: http://thinkup.com/docs/userguide/api/posts/related_posts.html
                 */
            case 'related_posts':
                if (is_null($this->post_id)) {
                    $m = 'A request of type ' . $this->type . ' requires a post_id to be specified.';
                    throw new RequiredArgumentMissingException($m);
                } else {
                    $data = $this->post_dao->getRelatedPosts($this->post_id, $this->network, $this->is_public,
                    $this->count, $this->page, $geo_encoded_only = false, $include_original_post = false);
                }
                break;

                /*
                 * Gets the user's most replied to posts.
                 *
                 * Required arguments: user_id or username
                 *
                 * Optional arguments: network, count, page, include_entities, include_replies, trim_user
                 *
                 * Docs: http://thinkup.com/docs/userguide/api/posts/user_posts_most_replied_to.html
                 */
            case 'user_posts_most_replied_to':
                $data = $this->post_dao->getMostRepliedToPosts($this->user->user_id, $this->network, $this->count,
                $this->page, $this->is_public);
                break;

                /*
                 * Gets the user's most retweeted posts.
                 *
                 * Required arguments: user_id or username
                 *
                 * Optional arguments: network, count, page, include_entities, include_replies, trim_user
                 *
                 * Docs: http://thinkup.com/docs/userguide/api/posts/user_posts_most_retweeted.html
                 */
            case 'user_posts_most_retweeted':
                $data = $this->post_dao->getMostRetweetedPosts($this->user->user_id, $this->network, $this->count,
                $this->page, $this->is_public);
                break;

                /*
                 * Gets posts a user has made.
                 *
                 * Required arguments: user_id or username
                 *
                 * Optional arguments: network, count, page, order_by, direction, include_entities, include_replies,
                 * trim_user
                 *
                 * Docs: http://thinkup.com/docs/userguide/api/posts/user_posts.html
                 */
            case 'user_posts':
                $data = $this->post_dao->getAllPosts($this->user->user_id, $this->network, $this->count,
                $this->page, true, $this->order_by, $this->direction, $this->is_public);
                break;

                /*
                 * Gets posts a user has made.
                 *
                 * Required arguments: user_id or username, from and until
                 *
                 * Optional arguments: network, order_by, direction, include_entities, include_replies,
                 * trim_user
                 *
                 * Docs: http://thinkup.com/docs/userguide/api/posts/user_posts_in_range.html
                 */
            case 'user_posts_in_range':
                if (is_null($this->from) || is_null($this->until)) {
                    $m = 'A request of type ' . $this->type . ' requires valid from and until parameters to be ';
                    $m .= 'specified.';
                    throw new RequiredArgumentMissingException($m);
                } else {
                    $data = $this->post_dao->getPostsByUserInRange($this->user->user_id, $this->network, $this->from,
                    $this->until, $this->order_by, $this->direction, $iterator=false, $this->is_public);
                }
                break;

                /*
                 * Gets posts a user is mentioned in.
                 *
                 * Required arguments: user_id or username
                 *
                 * Optional arguments: network, count, page, include_rts, include_entities, include_replies, trim_user
                 *
                 * Docs: http://thinkup.com/docs/userguide/api/posts/user_mentions.html
                 */
            case 'user_mentions':
                $data = $this->post_dao->getAllMentions($this->user->username, $this->count, $this->network,
                $this->page, $this->is_public, $this->include_rts, $this->order_by, $this->direction);
                break;


                /*
                 * Gets posts a user is mentioned in.within a date range
                 *
                 * Required arguments: user_id or username, from and until
                 *
                 * Optional arguments: network, count, page, include_rts, include_entities, include_replies, trim_user
                 */
            case 'user_mentions_in_range':
                if (is_null($this->from) || is_null($this->until)) {
                    $m = 'A request of type ' . $this->type . ' requires valid from and until parameters to be ';
                    $m .= 'specified.';
                    throw new RequiredArgumentMissingException($m);
                } else {
                    $data = $this->post_dao->getAllMentionsInRange($this->user->username, $this->count, $this->network,
                    $this->from, $this->until, $this->page, $this->is_public, $this->include_rts,$this->order_by,
                    $this->direction);
                }
                break;

                /*
                 * Gets question posts a user has made.
                 *
                 * Required arguments: user_id or username
                 *
                 * Optional arguments: network, count, page, order_by, direction, include_entities, include_replies,
                 * trim_user
                 *
                 * Docs: http://thinkup.com/docs/userguide/api/posts/user_questions.html
                 */
            case 'user_questions':
                $data = $this->post_dao->getAllQuestionPosts($this->user->user_id, $this->network, $this->count,
                $this->page, $this->order_by, $this->direction, $this->is_public);
                break;

                /*
                 * Gets question posts a user has made within a date range
                 *
                 * Required arguments: user_id or username, from and until
                 *
                 * Optional arguments: network, count, page, order_by, direction, include_entities, include_replies,
                 * trim_user
                 *
                 * Docs: http://thinkup.com/docs/userguide/api/posts/user_questions.html
                 */
            case 'user_questions_in_range':
                $data = $this->post_dao->getAllQuestionPostsInRange($this->user->user_id, $this->network, $this->count,
                $this->from, $this->until, $this->page, $this->order_by, $this->direction, $this->is_public);
                break;

                /*
                 * Gets replies to a user.
                 *
                 * Required arguments: user_id or username
                 *
                 * Optional arguments: network, count, page, order_by, direction, include_entities, include_replies,
                 * trim_user
                 *
                 * http://thinkup.com/docs/userguide/api/posts/user_replies.html
                 */
            case 'user_replies':
                $data = $this->post_dao->getAllReplies($this->user->user_id, $this->network, $this->count,
                $this->page, $this->order_by, $this->direction, $this->is_public);
                break;

                /*
                 * Gets replies to a user within a date range.
                 *
                 * Required arguments: user_id or username, from and until
                 *
                 * Optional arguments: network, count, page, order_by, direction, include_entities, include_replies,
                 * trim_user
                 *
                 * http://thinkup.com/docs/userguide/api/posts/user_replies.html
                 */
            case 'user_replies_in_range':
                $data = $this->post_dao->getAllRepliesInRange($this->user->user_id, $this->network, $this->count,
                $this->from, $this->until, $this->page, $this->order_by, $this->direction, $this->is_public);
                break;
                
                /*
                 * Gets posts that contains a Keyword.
                *
                * Required arguments: keyword and network
                *
                * Optional arguments: count, page, order_by, direction, include_entities, trim_user
                *
                * Docs: http://thinkup.com/docs/userguide/api/posts/keyword_posts.html
                */
            case 'keyword_posts':
                if (is_null($this->keyword) || is_null($this->network)) {
                    $m = 'A request of type ' . $this->type . ' requires valid keyword and network ';
                    $m .= 'parameters to be specified.';
                    throw new RequiredArgumentMissingException($m);
                }
                elseif (is_null($this->hashtag) && !is_null($this->keyword) && !is_null($this->network)) {
                    throw new KeywordNotFoundException();
                }
                else {
                    $data = $this->post_dao->getAllPostsByHashtagId($this->hashtag->id, $this->network,
                            $this->count, $this->order_by, $this->direction, $this->page, $this->is_public);
                }
                break;

                /*
                 * Generate an error because the API call type was not recognized.
                 *
                 * Docs: http://thinkup.com/docs/userguide/api/errors/apicalltypenotrecognised.html
                 */
            default:
                throw new APICallTypeNotRecognizedException($this->type);
                break;
        }

        if (is_null($data) ) {
            throw new PostNotFoundException();
        }

        switch ($this->network) {
            case 'twitter':
                if (is_array($data)) {
                    foreach ($data as $key => $post) {
                        $data[$key] = $this->convertPostToTweet($post);
                    }
                } else {
                    $data = $this->convertPostToTweet($data);
                }
                break;

            case 'facebook':
                // TODO: write a function here to convert to Facebook API style
                break;

            default:
                break;
        }

        // if no posts were found, $data is null. Set it to an empty array.
        if (is_null($data)) {
            $data = array();
        }

        $this->setJsonData($data);
        return $this->generateView();
    }

    /**
     * Convert the post as it is returned from the database to how it looks when output by the Twitter API.
     * Also, add the replies into the post with the index "replies".
     *
     * If the $post parameter is not a Post object, the function returns null.
     *
     * @param Post $post The post object.
     * @return stdObject The post formatted to look like the Twitter API.
     */
    private function convertPostToTweet($post) {
        if (!($post instanceof Post)) {
            return null;
        }

        if ($this->include_replies) {
            /*
             * Get all replies to the post. The limit is set to 0 because if the count is not greater than 0,
             * the method returns all replies.
             */
            $replies = $this->post_dao->getRepliesToPost($post->post_id, $post->network,
            $this->order_by, $this->unit, $this->is_public, 0);

            // if replies exist for this post
            if ($replies) {
                // recursively scan through the post replies, converting them
                foreach ($replies as $reply) {
                    $reply = $this->convertPostToTweet($reply);
                }

                // add the replies to the post
                $post->replies = $replies;
            }
        }

        /*
         * Chop and changing the data fetched from the database to look more like the official Twitter API.
         */
        $post->text = $post->post_text;
        $post->created_at = strftime('%a %b %d %T %z %Y', strtotime($post->pub_date));
        $post->id = $post->post_id;
        $post->favorited = $post->favorited ? true : false;
        $post->annotations = null; // to be implemented at some point
        $post->truncated = false; // always false
        $post->protected = $post->is_protected == 0 ? false : true;

        if ($post->geo != null) {
            $coordinates = preg_split('/(, |,| )/', $post->geo);
            $post->geo = new stdClass();
            $post->geo->coordinates = $coordinates;
            if (!isset($post->coordinates)) {
                $post->coordinates = new stdClass();
            }
            $post->coordinates->coordinates = $coordinates;
        }

        /*
         * SET THINKUP METADATA
         */
        $post->thinkup = new stdClass();
        $post->thinkup->retweet_count_cache = $post->retweet_count_cache;
        $post->thinkup->retweet_count_api = $post->retweet_count_api;
        $post->thinkup->reply_count_cache = $post->reply_count_cache;
        $post->thinkup->old_retweet_count_cache = $post->old_retweet_count_cache;
        $post->thinkup->is_geo_encoded = $post->is_geo_encoded;

        $user = $this->user_dao->getUserByName($post->author_username, $post->network);

        /*
         * Occasionally you run into users you haven't fetched yet. Bypass this code if you find one of them.
         */
        if ($user != null) {
            if (!$this->trim_user) {
                $post->user = $this->convertUserToStdClass($user);

                $post->user->id = $post->user->user_id;
                $post->user->followers_count = $post->user->follower_count;
                $post->user->profile_image_url = $post->user->avatar;
                $post->user->name = $post->user->full_name;
                $post->user->screen_name = $post->user->username;
                $post->user->statuses_count = $post->user->post_count;
                $post->user->created_at = strftime('%a %b %d %T %z %Y', strtotime($post->user->joined));
                $post->user->favorites_count = $post->user->favorites_count;

                if (isset($post->user->other)) {
                    if (isset($post->user->other['avg_tweets_per_day'])) {
                        $post->user->avg_tweets_per_day = $post->user->other['avg_tweets_per_day'];
                    }
                }

                $post->user->thinkup = new stdClass();

                $post->user->thinkup->last_post = $post->user->last_post;
                $post->user->thinkup->last_post_id = $post->user->last_post_id;
                $post->user->thinkup->found_in = $post->user->found_in;

            } else {
                $post->user = new stdClass();
                $post->user->id = $user->user_id;
            }
        }

        if ($this->include_entities) {
            /*
             * Gather hashtags and format them into a Tweet entity.
             */
            $extracted_hashtags = Post::extractHashtags($post->text);

            if (!isset($post->entities)) {
                $post->entities = new stdClass();
            }
            $post->entities->hashtags = array();
            if (!empty($extracted_hashtags)) {
                foreach ($extracted_hashtags as $hashtag_text) {
                    $hashtag = new stdClass();
                    $hashtag->text = str_replace('#', '', $hashtag_text);
                    $hashtag->indices[] = stripos($post->text, $hashtag_text);
                    $hashtag->indices[] = strlen($hashtag_text) + $hashtag->indices[0];

                    $post->entities->hashtags[] = $hashtag;
                }
            }

            /*
             * Gather mentions and format them into a Tweet entity.
             */
            $mentions = Post::extractMentions($post->text);

            if (!isset($post->entities)) {
                $post->entities = new stdClass();
            }
            $post->entities->user_mentions = array();
            if (!empty($mentions)) {
                foreach ($mentions as $username) {
                    $mentioned_user = $this->user_dao->getUserByName(str_replace('@', '', $username),
                    $user->network);
                    $mention = new stdClass();

                    if (is_null($mentioned_user)) {
                        // skip this for now, probably not a good idea
                        continue;

                        /*
                         * If the user is not in our own ThinkUp database, a Twitter API call needs to be
                         * made to fill in the missing details.
                         *
                         * Not 100% sure if this is a good idea but it works.
                         * Update, Feb 2013: This doesn't work with Twitter API v 1.1, commenting out.

                         $user_api_call = json_decode(Utils::getURLContents(
                         'https://api.twitter.com/1/users/show.json?screen_name=' . $username));

                         $mention->name = $user_api_call->name;
                         $mention->id = $user_api_call->id;
                         $mention->screen_name = $user_api_call->screen_name;
                         */
                    } else {
                        $mention->name = $mentioned_user->full_name;
                        $mention->id = $mentioned_user->user_id;
                        $mention->screen_name = $mentioned_user->username;
                    }

                    $mention->indices = array();
                    $mention->indices[] = stripos($post->text, $username);
                    $mention->indices[] = strlen($username) + $mention->indices[0];

                    $post->entities->user_mentions[] = $mention;
                }
            }
        }

        if ($post->in_retweet_of_post_id != null) {
            $post->retweeted_status = $this->post_dao->getPost($post->in_retweet_of_post_id, $user->network);
            $post->retweeted_status = $this->convertPostToTweet($post->retweeted_status);
        }

        /*
         * Unset no-longer-used variables in this post; mostly variables that have been moved to more
         * Twtter like locations / naming conventions.
         */
        unset(
        $post->post_id,
        $post->pub_date,
        $post->network,
        $post->post_text,
        $post->author,
        $post->author_fullname,
        $post->author_username,
        $post->author_user_id,
        $post->author_avatar,
        $post->adj_pub_date,
        $post->user->follower_count,
        $post->user->is_protected,
        $post->user->network,
        $post->user->avatar,
        $post->user->full_name,
        $post->user->username,
        $post->user->user_id,
        $post->user->post_count,
        $post->user->joined,
        $post->user->favorites_count,
        $post->user->other,
        $post->link,
        $post->in_retweet_of_post_id,
        $post->in_retweet_of_user_id,
        $post->retweet_count_cache,
        $post->reply_count_cache,
        $post->old_retweet_count_cache,
        $post->is_geo_encoded,
        $post->rt_threshold,
        $post->is_protected,
        $post->user->last_post,
        $post->user->last_post_id,
        $post->user->found_in
        );
        return $post;
    }

    /**
     * Convert a User object into a stdClass object. This was necessary because of the overloaded __set() method on
     * the User object.
     *
     * @param User $user A User object.
     * @return stdClass A stdClass object with all of the same vars as the User object that was passed in.
     */
    private function convertUserToStdClass(User $user) {
        if (is_object($user)) {
            $return = new stdClass();
            foreach (get_object_vars($user) as $key => $val) {
                $return->$key = $val;
            }
            return $return;
        }
        else {
            return null;
        }
    }
}
