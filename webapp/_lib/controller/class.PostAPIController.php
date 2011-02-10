<?php

/**
 *
 * ThinkUp/webapp/_lib/controller/class.PostAPIController.php
 *
 * Copyright (c) 2009-2011 Gina Trapani, Sam Rose
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
 *
 * Post API Controller
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani, Sam Rose
 * @author Sam Rose <samwho@lbak.co.uk>
 *
 */
class PostAPIController extends ThinkUpController {

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
     * Constructor
     *
     * @param boolean $session_started
     */
    public function __construct($session_started=false) {
        parent::__construct($session_started);
        $this->view_mgr->cache_lifetime = 60;
    }

    /**
     * This is an override of the normal getCacheKeyString function. It is overridden to use only the variables
     * from the $_GET array that are used in the ThinkUp Post API to avoid people doing a sort of pseudo cache
     * reset by introducing a random new variable into the $_GET array.
     * 
     * @return string
     */
    public function getCacheKeyString() {
        $cache_key = '';
        foreach ($this->parseQueryString() as $key => $value) {
            $cache_key .= $value.'-';
        }
        return $cache_key;
    }

    /**
     * This function is to make some of the order_by option a little bit more readable.
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
            case 'post_id': $order_by = 'p.post_id';
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

            default: $order_by = 'default';
                break;
        }

        return $order_by;
    }

    /**
     * Determines whether the given value represents true or not. Used for the boolean $_GET values such as
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

    public function parseQueryString() {
        /*
         * START READ IN OF QUERY STRING VARS
         */
        $network = isset($_GET['network']) ? $_GET['network'] : 'twitter';

        $user_id = isset($_GET['user_id']) ? (is_numeric($_GET['user_id']) ? $_GET['user_id'] : null) : null;
        $username = isset($_GET['username']) ? $_GET['username'] : null;

        $post_id = isset($_GET['post_id']) ? (is_numeric($_GET['post_id']) ? $_GET['post_id'] : null) : null;

        $type = isset($_GET['type']) ? $_GET['type'] : 'post';

        $count = isset($_GET['count']) ? (is_numeric($_GET['count']) ? (int) $_GET['count'] : 20) : 20;
        $page = isset($_GET['page']) ? (is_numeric($_GET['page']) ? (int) $_GET['page'] : 1) : 1;

        $order_by = isset($_GET['order_by']) ? $this->parseOrderBy($_GET['order_by']) : 'default';
        $direction = isset($_GET['direction']) ? ($_GET['direction'] == 'DESC' ? 'DESC' : 'ASC') : 'DESC';

        $from = isset($_GET['from']) ? $_GET['from'] : 0;
        $until = isset($_GET['until']) ? $_GET['until'] : null;

        $unit = isset($_GET['unit']) ? $_GET['unit'] : 'km';

        $include_replies = isset($_GET['include_replies']) ? $this->isTrue($_GET['include_replies']) : false;
        $include_entities = isset($_GET['include_entities']) ? $this->isTrue($_GET['include_entities']) : false;
        $trim_user = isset($_GET['trim_user']) ? $this->isTrue($_GET['trim_user']) : false;
        $include_rts = isset($_GET['include_rts']) ? $this->isTrue($_GET['include_rts']) : false;

        /*
         * END READ IN OF QUERY STRING VARS
         */

        // perhaps extend this in future to allow auth to see private posts
        $is_public = true;

        return get_defined_vars();
    }

    public function control() {
        if ($this->view_mgr->isViewCached()) {
            if ($this->view_mgr->is_cached('json.tpl', $this->getCacheKeyString())) {
                // set the json data to keep the ThinkUpController happy.
                $this->setJsonData(array());
                return $this->generateView();
            }
        }

        // fetch the correct PostDAO and UserDAO from the DAOFactory
        $this->post_dao = DAOFactory::getDAO('PostDAO');
        $this->user_dao = DAOFactory::getDAO('UserDAO');

        // get parsed query string values
        extract($this->parseQueryString());
        
        /*
         * Use the information gathered from the query string to retrieve a
         * User object. This will be the standard object with which to get
         * User information from in API calls.
         */
        if ($user_id != null) {
            $user = $this->user_dao->getDetails($user_id, $network);
        } else if ($username != null) {
            $user = $this->user_dao->getUserByName($username, $network);
        } else {
            $user = null;
        }

        /**
         * This switch statement is the main part of this function. It decides
         * what type of posts will be fetched depending on the "type" GET
         * variable and use the PostDAO to fetch the appropriate posts from
         * the database.
         *
         * If a required field is missing it will create an error field to
         * output in JSON.
         */
        switch ($type) {
            /**
             * Gets a post.
             *
             * Required arguments: post_id
             *
             * Optional arguments: network, include_entities, include_replies, trim_user
             */
            case 'post':
                if (is_null($post_id)) {
                    $data = new stdClass();
                    $data->error->type = 'RequiredArgumentMissingException';
                    $data->error->message = 'A request of type "' . $type . '" requires a post_id
to be specified.';
                } else {
                    $data = $this->post_dao->getPost($post_id, $network, $is_public);
                }
                break;

            /**
             * Gets all retweets to a post.
             *
             * Required arguments: post_id
             *
             * Optional arguments: network, order_by, unit, count, page, include_entities, include_replies, trim_user
             */
            case 'post_retweets':
                if (is_null($post_id)) {
                    $data = new stdClass();
                    $data->error->type = 'RequiredArgumentMissingException';
                    $data->error->message = 'A request of type "' . $type . '" requires a post_id
to be specified.';
                } else {
                    $data = $this->post_dao->getRetweetsOfPost($post_id, $network, $order_by, $unit, $is_public,
                                    $count, $page);
                }
                break;

            /**
             * Gets replies to a post.
             *
             * Required arguments: post_id
             *
             * Optional arguments: network, order_by, unit, count, page, include_entities, include_replies, trim_user
             *
             * Ordering can only be done by either location or follower count.
             */
            case 'post_replies':
                if (is_null($post_id)) {
                    $data = new stdClass();
                    $data->error->type = 'RequiredArgumentMissingException';
                    $data->error->message = 'A request of type "' . $type . '" requires a post_id
to be specified.';
                } else {
                    $data = $this->post_dao->getRepliesToPost($post_id, $network, $order_by, $unit, $is_public,
                                    $count, $page);
                }
                break;

            /*
             * Get posts related to a post (replies to it, retweets of it).
             *
             * Required arguments: post_id
             *
             * Optional arguments: network, count, page, geo_encoded_only, include_original_post, include_entities,
             * include_replies, trim_user
             */
            case 'related_posts':
                if (is_null($post_id)) {
                    $data = new stdClass();
                    $data->error->type = 'RequiredArgumentMissingException';
                    $data->error->message = 'A request of type "' . $type . '" requires a post_id
to be specified.';
                } else {
                    $data = $this->post_dao->getRelatedPosts($post_id, $network, $is_public, $count, $page,
                                    $geo_encoded_only = false, $include_original_post = false);
                }
                break;

            /*
             * Gets the user's most replied to posts.
             *
             * Required arguments: user_id or username
             *
             * Optional arguments: network, count, page, include_entities, include_replies, trim_user
             */
            case 'user_posts_most_replied_to':
                if (is_null($user)) {
                    // Check why the User object is null. Could be missing required fields or not found.
                    if (is_null($user_id) && is_null($username)) {
                        $data = new stdClass();
                        $data->error->type = 'RequiredArgumentMissingException';
                        $data->error->message = 'A request of type "' . $type . '" requires a user_id ';
                        $data->error->message .= 'or username to be specified.';
                    } else {
                        $data = new stdClass();
                        $data->error->type = 'UserNotFoundException';
                        $data->error->message = 'The user that you specified could not be found in our database.';
                    }
                } else {
                    $data = $this->post_dao->getMostRepliedToPosts($user->user_id, $network, $count, $page, $is_public);
                }
                break;

            /*
             * Gets the user's most retweeted posts.
             *
             * Required arguments: user_id or username
             *
             * Optional arguments: network, count, page, include_entities, include_replies, trim_user
             */
            case 'user_posts_most_retweeted':
                if (is_null($user)) {
                    // Check why the User object is null. Could be missing required fields or not found.
                    if (is_null($user_id) && is_null($username)) {
                        $data = new stdClass();
                        $data->error->type = 'RequiredArgumentMissingException';
                        $data->error->message = 'A request of type "' . $type . '" requires a user_id ';
                        $data->error->message .= 'or username to be specified.';
                    } else {
                        $data = new stdClass();
                        $data->error->type = 'UserNotFoundException';
                        $data->error->message = 'The user that you specified could not be found in our database.';
                    }
                } else {
                    $data = $this->post_dao->getMostRetweetedPosts($user->user_id, $network, $count, $page, $is_public);
                }
                break;

            /*
             * Gets posts a user has made.
             *
             * Required arguments: user_id or username
             *
             * Optional arguments: network, count, page, order_by, direction, include_entities, include_replies,
             * trim_user
             */
            case 'user_posts':
                if (is_null($user)) {
                    // Check why the User object is null. Could be missing required fields or not found.
                    if (is_null($user_id) && is_null($username)) {
                        $data = new stdClass();
                        $data->error->type = 'RequiredArgumentMissingException';
                        $data->error->message = 'A request of type "' . $type . '" requires a user_id ';
                        $data->error->message .= 'or username to be specified.';
                    } else {
                        $data = new stdClass();
                        $data->error->type = 'UserNotFoundException';
                        $data->error->message = 'The user that you specified could not be found in our database.';
                    }
                } else {
                    $data = $this->post_dao->getAllPosts($user->user_id, $network, $count, $page, false, $order_by,
                                    $direction, $is_public);
                }
                break;

            /*
             * Gets posts a user has made.
             *
             * Required arguments: user_id or username, from and until
             *
             * Optional arguments: network, order_by, direction, include_entities, include_replies,
             * trim_user
             */
            case 'user_posts_in_range':
                if (is_null($user) || is_null($from) || is_null($until)) {
                    // Check why the User object is null. Could be missing required fields or not found.
                    if (is_null($user_id) && is_null($username)) {
                        $data = new stdClass();
                        $data->error->type = 'RequiredArgumentMissingException';
                        $data->error->message = 'A request of type "' . $type . '" requires a user_id ';
                        $data->error->message .= 'or username to be specified.';
                    } else if (is_null($from) || is_null($until)) {
                        $data = new stdClass();
                        $data->error->type = 'RequiredArgumentMissingException';
                        $data->error->message = 'A request of type "' . $type . '" requires valid from and until ';
                        $data->error->message .= 'parameters to be specified.';
                    } else {
                        $data = new stdClass();
                        $data->error->type = 'UserNotFoundException';
                        $data->error->message = 'The user that you specified could not be found in our database.';
                    }
                } else {
                    $data = $this->post_dao->getPostsByUserInRange($user->user_id, $network, $from, $until,
                            $order_by, $direction, $iterator=false, $is_public);
                }
                break;

            /*
             * Gets posts a user is mentioned in.
             *
             * Required arguments: user_id or username
             *
             * Optional arguments: network, count, page, include_rts, include_entities, include_replies, trim_user
             */
            case 'user_mentions':
                if (is_null($user)) {
                    // Check why the User object is null. Could be missing required fields or not found.
                    if (is_null($user_id) && is_null($username)) {
                        $data = new stdClass();
                        $data->error->type = 'RequiredArgumentMissingException';
                        $data->error->message = 'A request of type "' . $type . '" requires a user_id ';
                        $data->error->message .= 'or username to be specified.';
                    } else {
                        $data = new stdClass();
                        $data->error->type = 'UserNotFoundException';
                        $data->error->message = 'The user that you specified could not be found in our database.';
                    }
                } else {
                    $data = $this->post_dao->getAllMentions($user->username, $count, $network, $page, $is_public,
                                    $include_rts, $order_by, $direction);
                }
                break;

            /*
             * Gets question posts a user has made.
             *
             * Required arguments: user_id or username
             *
             * Optional arguments: network, count, page, order_by, direction, include_entities, include_replies,
             * trim_user
             */
            case 'user_questions':
                if (is_null($user)) {
                    // Check why the User object is null. Could be missing required fields or not found.
                    if (is_null($user_id) && is_null($username)) {
                        $data = new stdClass();
                        $data->error->type = 'RequiredArgumentMissingException';
                        $data->error->message = 'A request of type "' . $type . '" requires a user_id ';
                        $data->error->message .= 'or username to be specified.';
                    } else {
                        $data = new stdClass();
                        $data->error->type = 'UserNotFoundException';
                        $data->error->message = 'The user that you specified could not be found in our database.';
                    }
                } else {
                    $data = $this->post_dao->getAllQuestionPosts($user->user_id, $network, $count, $page, $order_by,
                                    $direction, $is_public);
                }
                break;

            /*
             * Gets replies to a user.
             *
             * Required arguments: user_id or username
             *
             * Optional arguments: network, count, page, order_by, direction, include_entities, include_replies,
             * trim_user
             */
            case 'user_replies':
                if (is_null($user)) {
                    // Check why the User object is null. Could be missing required fields or not found.
                    if (is_null($user_id) && is_null($username)) {
                        $data = new stdClass();
                        $data->error->type = 'RequiredArgumentMissingException';
                        $data->error->message = 'A request of type "' . $type . '" requires a user_id ';
                        $data->error->message .= 'or username to be specified.';
                    } else {
                        $data = new stdClass();
                        $data->error->type = 'UserNotFoundException';
                        $data->error->message = 'The user that you specified could not be found in our database.';
                    }
                } else {
                    $data = $this->post_dao->getAllReplies($user->user_id, $network, $count, $page, $order_by,
                                    $direction, $is_public);
                }
                break;


            /*
             * Generate an error because the API call type was not recognised.
             */
            default:
                $data = new stdClass();
                $data->error->type = 'APICallTypeNotRecognised';
                $data->error->message = 'Your API call type "' . $type . '" was not recognised.';
                break;
        }

        /**
         * If the $data variable is null, issue an appropriate error.
         */
        if (is_null($data) || empty($data)) {
            $data = new stdClass();
            $data->error->type = 'NotFoundException';
            $data->error->message = 'No posts could be found for your request.';
        } else if (isset($data->error)) {
            /*
             * If the $data variable has an error inside it, the posts will not
             * need to be parsed into a Twitter style format so just enter
             * this block and pass over it, ignoring the else clause to this
             * if block.
             */
        } else {
            /**
             * The $data variable can contain either an array of posts or a
             * single post. This section of code handles that appropriately.
             *
             * Posts get run through a method of this class that converts them
             * to a Twitter-like API format. It also gives you the option to
             * include replies to each tweet. The replies function is recursive
             * so all replies to all replies will be fetched.
             *
             * If you are going to extend this API to other services, this part here
             * is where you should use a method to convert the look of the original
             * posts to whatever service you're implementing.
             */
            switch ($network) {
                case 'twitter':
                    if (is_array($data)) {
                        foreach ($data as $key => $post) {
                            $data[$key] = $this->convertPostToTweet($post, $include_replies, $include_entities,
                                            $trim_user, $order_by, $unit, $is_public);
                        }
                    } else {
                        $data = $this->convertPostToTweet($data, $include_replies, $include_entities, $trim_user,
                                        $order_by, $unit, $is_public);
                    }
                    break;

                case 'facebook':
                    // write a function here to convert to Facebook API style
                    break;

                default: break;
            }
        }

        $this->setJsonData($data);
        return $this->generateView();
    }

    /**
     * Converts the post as it is returned from the database to how it looks
     * when output by the Twitter API. Also adds the replies into the post
     * with the index "replies".
     *
     * If the $post parameter is not a Post object, the function returns null.
     *
     * @param Post $post The post object.
     * @param bool $include_replies The replies object.
     * @param bool $include_entities Whether or not to include Tweet entities.
     * @param bool $trim_user Whether or not to trim the user object down to just the ID.
     * @param string $order_by What database column to order by.
     * @param string $unit What unit to display distance info in.
     * @param bool $is_public Whether or not the data is public.
     * @return stdObject The post formatted to look like the Twitter API.
     */
    private function convertPostToTweet($post, $include_replies = false, $include_entities = false, $trim_user = false,
            $order_by = 'default', $unit = 'km', $is_public = true) {

        if (!is_a($post, 'Post')) {
            return null;
        }

        if ($include_replies) {
            /*
             * Get all replies to the post. The limit is set to ten
             * million as a kind of "no limit" argument. If there is a no
             * limit argument, please change this.
             *
             * TODO: Implement a "no limit" argument for getRepliesToPost()
             */
            $replies = $this->post_dao->getRepliesToPost($post->post_id, $post->network,
                            $order_by, $unit, $is_public, 10000000);

            // if replies exist for this post
            if ($replies) {
                // recursively scan through the post replies, converting them
                foreach ($replies as $reply) {
                    $reply = $this->convertPostToTweet($reply, $include_replies, $include_entities, $trim_user,
                                    $order_by, $unit, $is_public);
                }

                // add the replies to the post
                $post->replies = $replies;
            }
        }

        /*
         * The following code is basically just chopping and changing the
         * data that has been fetched from the database to look more like the
         * official Twitter API.
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
            $post->coordinates->coordinates = $coordinates;
        }

        /*
         * SET THINKUP METADATA
         */
        $post->thinkup = new stdClass();
        $post->thinkup->retweet_count_cache = $post->retweet_count_cache;
        $post->thinkup->reply_count_cache = $post->reply_count_cache;
        $post->thinkup->old_retweet_count_cache = $post->old_retweet_count_cache;
        $post->thinkup->is_geo_encoded = $post->is_geo_encoded;

        $user = $this->user_dao->getUserByName($post->author_username, $post->network);

        /*
         * Occasionally you run into users you haven't fetched yet. Bypass this code
         * if you find one of them. 
         */
        if ($user != null) {
            if (!$trim_user) {
                $post->user = $this->convertUserToStdClass($user);

                $post->user->id = $post->user->user_id;
                $post->user->followers_count = $post->user->follower_count;
                $post->user->profile_image_url = $post->user->avatar;
                $post->user->name = $post->user->full_name;
                $post->user->screen_name = $post->user->username;
                $post->user->statuses_count = $post->user->post_count;
                $post->user->created_at = strftime('%a %b %d %T %z %Y', strtotime($post->user->joined));
                $post->user->favourites_count = $post->user->favorites_count;
                $post->user->utc_offset = Config::getInstance()->getGMTOffset() * 3600;

                if (isset($post->user->other->avg_tweets_per_day)) {
                    $post->user->avg_tweets_per_day = $post->user->other->avg_tweets_per_day;
                }

                if (isset($post->user->other->last_updated)) {
                    $post->user->last_updated = $post->user->other->last_updated;
                }

                $post->user->thinkup = new stdClass();

                $post->user->thinkup->last_post = $post->user->last_post;
                $post->user->thinkup->last_post_id = $post->user->last_post_id;
                $post->user->thinkup->found_in = $post->user->found_in;

            } else {
                $post->user->id = $user->user_id;
            }
        }


        if ($include_entities) {
            /*
             * Gather the links and format them into a Tweet entity.
             *
             * As part of this conditional, a search for the link in the post text
             * is made because it seemed occasionally that unrelated links were
             * finding their way into entities. I don't know why.
             */
            $post->entities->urls = array();
            if (!is_null($post->link)) {
                if (!is_null($post->link->url) && !empty($post->link->url)
                        && stripos($post->link->url, $post->text) !== false) {
                    $link = new stdClass();
                    $link->url = stripslashes($post->link->url);
                    $link->expanded_url = $post->link->expanded_url == "" ? null : $post->link->expanded_url;
                    $link->indices = array();
                    $link->indices[] = stripos($post->text, $link->url);
                    $link->indices[] = strlen($link->url) + $link->indices[0];
                    $post->entities->urls[] = $link;
                }
            }

            /*
             * Gather hashtags and format them into a Tweet entity.
             */
            $extracted_hashtags = Post::extractHashtags($post->text);

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
                         */
                        $user_api_call = json_decode(Utils::getURLContents(
                                                'https://api.twitter.com/1/users/show.json?screen_name=' . $username));

                        $mention->name = $user_api_call->name;
                        $mention->id = $user_api_call->id;
                        $mention->screen_name = $user_api_call->screen_name;
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
            $post->retweeted_status = $this->convertPostToTweet($post->retweeted_status,
                            $include_replies, $include_entities, $trim_user, $order_by, $unit, $is_public);
        }

        /*
         * Unset no longer used variabled in this post.
         *
         * This is mostly variables that have been moved to more
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
     * Converts a User object into a stdClass object. This was necessary
     * because of the overloaded __set() method on the User object.
     * 
     * @param User $user A User object.
     * @return stdClass A stdClass object with all of the same vars as the
     * User object that was passed in.
     */
    private function convertUserToStdClass($user) {
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
