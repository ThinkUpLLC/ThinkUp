<?php
/**
 *
 * ThinkUp/webapp/plugins/facebook/model/class.FacebookCrawler.php
 *
 * Copyright (c) 2009-2016 Gina Trapani
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
 * Facebook Crawler
 *
 * Retrieves user data from Facebook, converts it to ThinkUp objects, and stores them in the ThinkUp database.
 * All Facebook users are inserted with the network set to 'facebook', except for page instances' corresponding user
 * (those get network='facebook page'). Comments on Facebook page posts get listed with network 'facebook page', even
 * though they are by users with network set to 'facebook'.
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2016 Gina Trapani
 */
class FacebookCrawler {
    /**
     * @var Instance
     */
    var $instance;
    /**
     * @var Logger
     */
    var $logger;
    /**
     * @var str
     */
    var $access_token;
    /**
     * @var int Maximum amount of time the crawler should spend fetching a profile or page in seconds
     */
    var $max_crawl_time;
    /**
     * Whether or not an instance Facebook Page's total likes has been recorded in the follower count table.
     * @var bool
     */
    var $page_like_count_set = false;
    /**
     * @var str Fields to request from Facebook for the user's feed
     */
    static $feed_fields =
        "comments.limit(25).summary(true),likes.limit(25).summary(true),from,message,name,link,caption,description,picture";

    /**
     * Extended max_crawl_time.
     * If crawler has never run before, and max_crawl_time is shorter than this, extend it to this.
     * @var integer
     */
    const MAX_CRAWL_TIME_EXTENDED = 240;
    /**
     * @param Instance $instance
     * @return FacebookCrawler
     */
    public function __construct($instance, $access_token, $max_crawl_time) {
        $this->instance = $instance;
        $this->logger = Logger::getInstance();
        $this->access_token = $access_token;
        $this->max_crawl_time = $max_crawl_time;

        //If crawler has never run before, and max_crawl_time is short, extend it
        if ($max_crawl_time < self::MAX_CRAWL_TIME_EXTENDED) {
            $user_dao = DAOFactory::getDAO('UserDAO');
            //If user is not in storage then crawler has not run before
            if (!$user_dao->isUserInDB($instance->network_user_id, $instance->network)) {
                $this->max_crawl_time = self::MAX_CRAWL_TIME_EXTENDED;
            }
        }
    }
    /**
     * If user doesn't exist in the datastore, fetch details from Facebook API and insert into the datastore.
     * If $reload_from_facebook is true, update existing user details in store with data from Facebook API.
     * @param int $user_id Facebook user ID
     * @param str $found_in Where the user was found
     * @param bool $reload_from_facebook Defaults to false; if true will query Facebook API and update existing user
     * @return User
     */
    public function fetchUser($user_id, $found_in, $force_reload_from_facebook=false) {
        //assume all users except the instance user is a facebook profile, not a page
        //@TODO: Start supporting users of type 'facebook page'
        $network = ($user_id == $this->instance->network_user_id)?$this->instance->network:'facebook';
        $user_dao = DAOFactory::getDAO('UserDAO');
        $user_object = null;
        if ($force_reload_from_facebook || !$user_dao->isUserInDB($user_id, $network)) {
            // Get owner user details and save them to DB
            $fields = $network!='facebook page'?'id,name,is_verified,updated_time':null;
            $user_details = FacebookGraphAPIAccessor::apiRequest($user_id, $this->access_token, null, $fields);
            if (isset($user_details)) {
                $user_details->network = $network;
            }

            $user = $this->parseUserDetails($user_details);
            if (isset($user)) {
                $user_object = new User($user, $found_in);
                $user_dao->updateUser($user_object);
            }

            if ($this->instance->network_user_id == $user_id && $user['updated_time']) {
                $this->instance->profile_updated = $user['updated_time'];
            }

            // Record the current number of page likes in follower count table
            if ($network == 'facebook page' && isset($user_details->likes) && !$this->page_like_count_set) {
                $count_dao = DAOFactory::getDAO('CountHistoryDAO');
                $count_dao->insert($this->instance->network_user_id, 'facebook page', $user_details->likes, null,
                'followers');
                $this->page_like_count_set = true;
            }

            if (isset($user_object)) {
                $this->logger->logSuccess("Successfully fetched ".$user_id. " ".$network."'s details from Facebook",
                __METHOD__.','.__LINE__);
            } else {
                //@TODO: Most of these errors occur because TU doesn't yet support users of type 'facebook page'
                //We just assume every user is a vanilla FB user. However, we can't retrieve page details using
                //a vanilla user call here
                $this->logger->logInfo("Error fetching ".$user_id." ". $network."'s details from Facebook API, ".
                "response was ".Utils::varDumpToString($user_details), __METHOD__.','.__LINE__);
            }
        }
        return $user_object;
    }
    /**
     * Convert decoded JSON data from Facebook into a ThinkUp user object.
     * @param arr $details
     * @return arr $user_vals
     */
    private function parseUserDetails($details) {
        if (isset($details->name) && isset($details->id)) {
            $user_vals = array();

            $user_vals["user_name"] = $details->name;
            $user_vals["full_name"] = $details->name;
            $user_vals["user_id"] = $details->id;
            $user_vals["gender"] = $details->gender;
            // We only want to store valid full birthdays
            if (substr_count($details->birthday, '/') > 1) {
                $birth_ts = strtotime($details->birthday);
                // This check may become invalid as modern medicine improves
                if ($birth_ts >= (time() - (60*60*24*365*130))) {
                    $user_vals["birthday"] = date('Y-m-d', $birth_ts);
                }
            }
            $user_vals["avatar"] = 'https://graph.facebook.com/'.$details->id.'/picture';
            $user_vals['url'] = isset($details->website)?$details->website:'';

            if (isset($details->subscribers->summary->total_count)) {
                $follower_count = $details->subscribers->summary->total_count;
            } else {
                $follower_count = 0;
            }
            $user_vals["follower_count"] = $follower_count;
            $user_vals["location"] = isset($details->location->name)?$details->location->name:'';
            $user_vals["description"] = isset($details->about)?$details->about:'';
            $user_vals["is_verified"] = $details->is_verified;
            $user_vals["is_protected"] = 1; //for now, assume a Facebook user is private
            $user_vals["post_count"] = 0;
            $user_vals["joined"] = ''; //Column 'joined' cannot be null
            $user_vals["network"] = $details->network;
            //this will help us in getting correct range of posts
            $user_vals["updated_time"] = isset($details->updated_time)?$details->updated_time:0;
            return $user_vals;
        }
    }
    /**
     * Fetch and save the posts and replies for the crawler's instance. This function will loop back through the
     * user's or pages archive of posts.
     * @return void
     * @throws APIOAuthException
     */
    public function fetchPostsAndReplies() {
        $id = $this->instance->network_user_id;
        $network = $this->instance->network;

        $fetch_next_page = true;
        $current_page_number = 1;
        $next_api_request = $id.'/feed';
        $fields = self::$feed_fields;

        //Cap crawl time for very busy pages with thousands of likes/comments
        $fetch_stop_time = time() + $this->max_crawl_time;

        $api_request_params = null;

        $use_full_api_url = false;

        $dig_into_archives = false;

        while ($fetch_next_page) {
            if (!$use_full_api_url) {
                $stream = FacebookGraphAPIAccessor::apiRequest($next_api_request, $this->access_token,
                    $api_request_params, $fields);
                $api_request_params = null;
            } else {
                //Use full paging URL
                $stream = FacebookGraphAPIAccessor::apiRequestFullURL($next_api_request, $this->access_token);
            }
            if (isset($stream->data) && is_array($stream->data) && sizeof($stream->data) > 0) {
                $this->logger->logInfo(sizeof($stream->data)." Facebook posts found on page ".$current_page_number,
                    __METHOD__.','.__LINE__);

                $total_added_posts = $this->processStream($stream, $network, $current_page_number);

                if ($total_added_posts == 0) { //No new posts were found, try going back into the archives
                    if (!$dig_into_archives) {
                        $dig_into_archives = true;

                        //Determine 'since', datetime of oldest post in datastore
                        $post_dao = DAOFactory::getDAO('PostDAO');
                        $since_post = $post_dao->getAllPosts($id, $network, 1, 1, true, 'pub_date', 'ASC');
                        $since = isset($since_post[0])?$since_post[0]->pub_date:0;
                        $since = strtotime($since);

                        $this->logger->logInfo("No Facebook posts found for $id here, digging into archives since ".
                            $since_post[0]->pub_date. " strtotime ". $since, __METHOD__.','.__LINE__);

                        $api_request_params = array('since'=>$since);
                        $use_full_api_url = false;
                        $next_api_request = $id.'/feed';
                    } else {
                        if (isset($stream->paging->next)) {
                            $next_api_request = $stream->paging->next;
                            $use_full_api_url = true;
                            //DEBUG
                            $this->logger->logInfo("Dug into archives, next page API request is ".$next_api_request,
                                __METHOD__.','.__LINE__);
                            $current_page_number++;
                        } else {
                            $fetch_next_page = false;
                        }
                    }
                } else {
                    if (isset($stream->paging->next)) {
                        $next_api_request = $stream->paging->next;
                        $use_full_api_url = true;
                        //DEBUG
                        $this->logger->logInfo("Next page API request is ".$next_api_request,
                            __METHOD__.','.__LINE__);
                        $current_page_number++;
                    } else {
                        $fetch_next_page = false;
                    }
                }
            } elseif (isset($stream->error->type) && ($stream->error->type == 'OAuthException')) {
                throw new APIOAuthException($stream->error->message);
            } else {
                $this->logger->logInfo("No Facebook posts found for ID $id", __METHOD__.','.__LINE__);
                $fetch_next_page = false;
            }
            if (time() > $fetch_stop_time) {
                $fetch_next_page = false;
                $this->logger->logUserInfo("Stopping this service user's crawl because it has exceeded max time of ".
                ($this->max_crawl_time/60)." minute(s). ",__METHOD__.','.__LINE__);
            }
        }
    }
    /**
     * Convert parsed JSON of a profile or page's posts into ThinkUp posts and users
     * @param Object $stream
     * @param str $source The network for the post, either 'facebook' or 'facebook page'
     * @param int Page number being processed
     * @return int $total_added_posts How many posts (excluding comments) got added to the data store
     */
    private function processStream($stream, $network, $page_number) {
        $thinkup_posts = array();
        $total_added_posts = 0;
        $total_added_comments = 0;

        $thinkup_users = array();
        $total_added_users = 0;

        $thinkup_links = array();
        $total_links_added = 0;

        $thinkup_likes = array();
        $total_added_likes = 0;

        $profiles = array();

        //efficiency control vars
        $must_process_likes = true;
        $must_process_comments = true;
        $post_comments_added = 0;
        $post_likes_added = 0;
        $comments_difference = false;
        $likes_difference = false;

        $post_dao = DAOFactory::getDAO('PostDAO');

        foreach ($stream->data as $index=>$p) {
            $post_id = explode("_", $p->id);
            $post_id = $post_id[1];
            $this->logger->logInfo("Beginning to process ".$post_id.", post ".($index+1)." of ".count($stream->data).
                " on page ".$page_number, __METHOD__.','.__LINE__);

            // stream can contain posts from multiple users.  get profile for this post
            $profile = null;
            if (!empty($profiles[$p->from->id])) {
                $profile = $profiles[$p->from->id];
            } else {
                $profile = $this->fetchUser($p->from->id, 'Post stream', true);
                $profiles[$p->from->id] = $profile;
            }

            //Assume profile comments are private and page posts are public
            $is_protected = ($network=='facebook')?1:0;
            //Get likes count
            $likes_count = 0;
            //Normalize likes to be one array
            if (isset($p->likes)) {
                $likes_count = $p->likes->summary->total_count;
                $p->likes = $this->normalizeLikes($p->likes);
            }

            // Normalize comments to be one array
            $comments_count = 0;
            if (isset($p->comments)) {
                $comments_count = $p->comments->summary->total_count;
                $p->comments = $this->normalizeComments($p->comments);
            }

            $post_in_storage = $post_dao->getPost($post_id, $network);

            //Figure out if we have to process likes and comments
            if (isset($post_in_storage)) {
                $this->logger->logInfo("Post ".$post_id. " already in storage", __METHOD__.','.__LINE__);
                if ($post_in_storage->favlike_count_cache >= $likes_count ) {
                    $must_process_likes = false;
                    $this->logger->logInfo("Already have ".$likes_count." like(s) for post ".$post_id.
                        " in storage; skipping like processing", __METHOD__.','.__LINE__);
                } else  {
                    $likes_difference = $likes_count - $post_in_storage->favlike_count_cache;
                    $this->logger->logInfo($likes_difference." new like(s) to process for post ".$post_id,
                    __METHOD__.','.__LINE__);
                }

                if (isset($p->comments->summary->total_count)) {
                    if ($post_in_storage->reply_count_cache >= $p->comments->summary->total_count) {
                        $must_process_comments = false;
                        $this->logger->logInfo("Already have ".$post_in_storage->reply_count_cache
                            ." comment(s) for post ".$post_id.
                            "; skipping comment processing", __METHOD__.','.__LINE__);
                    } else {
                        $comments_difference = $p->comments->summary->total_count - $post_in_storage->reply_count_cache;
                        $this->logger->logInfo($comments_difference." new comment(s) of "
                            .$p->comments->summary->total_count.
                            " total to process for post ".$post_id, __METHOD__.','.__LINE__);
                    }
                }
            } else {
                $this->logger->logInfo("Post ".$post_id. " not in storage", __METHOD__.','.__LINE__);
            }

            if (!isset($profile) ) {
                $this->logger->logError("No profile set", __METHOD__.','.__LINE__);
            } else {
                if (!isset($post_in_storage)) {
                    $this->logger->logInfo("Post ".$post_id. " has ".$comments_count." comments",
                        __METHOD__.','.__LINE__);
                    $post_to_process = array(
                      "post_id"=>$post_id,
                      "author_username"=>$profile->username,
                      "author_fullname"=>$profile->username,
                      "author_avatar"=>$profile->avatar,
                      "author_user_id"=>$p->from->id,
                      "post_text"=>isset($p->message)?$p->message:'',
                      "pub_date"=>$p->created_time,
                      "favlike_count_cache"=>$likes_count,
                      "reply_count_cache"=>$comments_count,
                       // assume only one recipient
                      "in_reply_to_user_id"=> isset($p->to->data[0]->id) ? $p->to->data[0]->id : '',
                      "in_reply_to_post_id"=>'',
                      "source"=>'',
                      'network'=>$network,
                      'is_protected'=>$is_protected,
                      'location'=>''
                    );

                    $new_post_key = $this->storePostAndAuthor($post_to_process, "Owner stream");

                    if ($new_post_key !== false ) {
                        $total_added_posts++;
                    }

                    if (isset($p->source) || isset($p->link)) { // there's a link to store
                        $link_url = (isset($p->source))?$p->source:$p->link;
                        $link = new Link(array(
                          "url"=>$link_url,
                          "expanded_url"=>'',
                          "image_src"=>(isset($p->picture))?$p->picture:'',
                          "caption"=>(isset($p->caption))?$p->caption:'',
                          "description"=>(isset($p->description))?$p->description:'',
                          "title"=>(isset($p->name))?$p->name:'',
                          "post_key"=>$new_post_key
                        ));
                        array_push($thinkup_links, $link);
                    }
                    $total_links_addded = $total_links_added + $this->storeLinks($thinkup_links);
                    if ($total_links_added > 0 ) {
                        $this->logger->logUserSuccess("Collected $total_links_added new links",
                        __METHOD__.','.__LINE__);
                    }
                    //free up memory
                    $thinkup_links  = array();
                } else { // post already exists in storage
                    if ($must_process_likes) { //update its like count only
                        $post_dao->updateFavLikeCount($post_id, $network, $likes_count);
                        $this->logger->logInfo("Updated Like count for post ".$post_id . " to ". $likes_count,
                        __METHOD__.','.__LINE__);
                    }
                }

                if ($must_process_comments) {
                    if (isset($p->comments)) {
                        $comments_captured = 0;
                        if (isset($p->comments->data)) {
                            $post_comments = $p->comments->data;
                            $post_comments_count = isset($post_comments)?sizeof($post_comments):0;
                            if (is_array($post_comments) && sizeof($post_comments) > 0) {
                                foreach ($post_comments as $c) {
                                    if (isset($c->from)) {
                                        // Sometimes the id is parent_poster_postId
                                        // sometimes it's just parent_postId
                                        $comment_id = explode("_", $c->id);
                                        if (count($comment_id) == 3) {
                                            $comment_id = $comment_id[2];
                                        } else {
                                            $comment_id = $comment_id[1];
                                        }
                                        //only add to queue if not already in storage
                                        $comment_in_storage = $post_dao->getPost($comment_id, $network);
                                        if (!isset($comment_in_storage)) {
                                            $comment_to_process = array("post_id"=>$comment_id,
                                              "author_username"=>$c->from->name,
                                              "author_fullname"=>$c->from->name,
                                              "author_gender"=>$c->from->gender,
                                              "author_birthday"=>$c->from->birthday,
                                              "author_avatar"=>'https://graph.facebook.com/'.$c->from->id.'/picture',
                                              "author_user_id"=>$c->from->id,
                                              "post_text"=>$c->message,
                                              "pub_date"=>$c->created_time,
                                              "in_reply_to_user_id"=>$profile->user_id,
                                              "in_reply_to_post_id"=>$post_id,
                                              "source"=>'', 'network'=>$network,
                                              'is_protected'=>$is_protected,
                                              'location'=>'');
                                            array_push($thinkup_posts, $comment_to_process);
                                            $comments_captured = $comments_captured + 1;
                                        }
                                    }
                                }
                            }
                        }
                        $post_comments_added = $post_comments_added +
                            $this->storePostsAndAuthors($thinkup_posts, "Post stream comments");

                        //free up memory
                        $thinkup_posts = array();

                        if (is_int($comments_difference) && $post_comments_added >= $comments_difference) {
                            $must_process_comments = false;
                            if (isset($comments_stream->paging->next)) {
                                $this->logger->logInfo("Caught up on post ".$post_id."'s balance of ".
                                    $comments_difference." comments; stopping comment processing",
                                    __METHOD__.','.__LINE__);
                            }
                        }
                        // collapsed comment thread
                        if (isset($p->comments->summary->total_count)
                            && $p->comments->summary->total_count > $comments_captured
                            && $must_process_comments) {

                            if (is_int($comments_difference)) {
                                $offset = $p->comments->summary->total_count - $comments_difference;
                                $offset_arr = array('offset'=>$offset, 'limit'=>$comments_difference);
                            } else {
                                $offset_arr = null;
                            }
                            $api_call = $p->from->id.'_'.$post_id. '/comments';
                            do {
                                $comments_stream = FacebookGraphAPIAccessor::apiRequest($api_call, $this->access_token,
                                    $offset_arr);
                                if (isset($comments_stream) && isset($comments_stream->data)
                                    && is_array($comments_stream->data)) {

                                    foreach ($comments_stream->data as $c) {
                                        if (isset($c->from)) {
                                            $comment_id = explode("_", $c->id);
                                            $comment_id = $comment_id[sizeof($comment_id)-1];
                                            //only add to queue if not already in storage
                                            $comment_in_storage = $post_dao->getPost($comment_id, $network);
                                            if (!isset($comment_in_storage)) {
                                                $comment_to_process = array(
                                                    "post_id"=>$comment_id,
                                                    "author_username"=>$c->from->name,
                                                    "author_fullname"=>$c->from->name,
                                                    "author_avatar"=>'https://graph.facebook.com/'
                                                        .$c->from->id.'/picture',
                                                    "author_user_id"=>$c->from->id,
                                                    "post_text"=>$c->message,
                                                    "pub_date"=>$c->created_time,
                                                    "in_reply_to_user_id"=>$profile->user_id,
                                                    "in_reply_to_post_id"=>$post_id,
                                                    "source"=>'',
                                                    'network'=>$network,
                                                    'is_protected'=>$is_protected,
                                                    'location'=>''
                                                );
                                                array_push($thinkup_posts, $comment_to_process);
                                            }
                                        }
                                    }

                                    $post_comments_added = $post_comments_added +
                                    $this->storePostsAndAuthors($thinkup_posts, "Posts stream comments collapsed");

                                    if (is_int($comments_difference) && $post_comments_added >= $comments_difference) {
                                        $must_process_comments = false;
                                        if (isset($comments_stream->paging->next)) {
                                            $this->logger->logInfo("Caught up on post ".$post_id."'s balance of ".
                                                $comments_difference." comments; stopping comment processing",
                                                __METHOD__.','.__LINE__);
                                        }
                                    }

                                    //free up memory
                                    $thinkup_posts = array();
                                    if (isset($comments_stream->paging->next) ) {
                                        $api_call = str_replace('\u00257C', '|', $comments_stream->paging->next);
                                    }
                                } else {
                                    // no comments (pun intended)
                                    break;
                                }
                            } while (isset($comments_stream->paging->next) && $must_process_comments);
                        }
                    }
                    if ($post_comments_added > 0) { //let user know
                        $this->logger->logUserSuccess("Added ".$post_comments_added." comment(s) for post ". $post_id,
                            __METHOD__.','.__LINE__);
                    } else {
                        $this->logger->logInfo("Added ".$post_comments_added." comment(s) for post ". $post_id,
                            __METHOD__.','.__LINE__);
                    }
                    $total_added_comments = $total_added_comments + $post_comments_added;
                }
                //Inserting comments also increments the original post's reply_count_cache; reset it here
                $post_dao->updateReplyCount($post_id, $network, $comments_count);

                //process "likes"
                if ($must_process_likes) {
                    if (isset($p->likes)) {
                        $likes_captured = 0;
                        if (isset($p->likes->data)) {
                            $post_likes = $p->likes->data;
                            $post_likes_count = isset($post_likes)?sizeof($post_likes):0;
                            if (is_array($post_likes) && sizeof($post_likes) > 0) {
                                foreach ($post_likes as $l) {
                                    if (isset($l->name) && isset($l->id)) {
                                        //Get users
                                        $user_to_add = array(
                                            "user_name"=>$l->name,
                                            "full_name"=>$l->name,
                                            "user_id"=>$l->id,
                                            "avatar"=>'https://graph.facebook.com/'.$l->id.'/picture',
                                            "location"=>'',
                                            "description"=>'',
                                            "url"=>'',
                                            "is_protected"=>1,
                                            "follower_count"=>0,
                                            "post_count"=>0,
                                            "joined"=>'',
                                            "found_in"=>"Likes",
                                            "network"=>'facebook'
                                        ); //Users are always set to network=facebook
                                        array_push($thinkup_users, $user_to_add);

                                        $fav_to_add = array("favoriter_id"=>$l->id, "network"=>$network,
                                        "author_user_id"=>$profile->user_id, "post_id"=>$post_id);
                                        array_push($thinkup_likes, $fav_to_add);
                                        $likes_captured = $likes_captured + 1;
                                    }
                                }
                            }
                        }

                        $total_added_users = $total_added_users + $this->storeUsers($thinkup_users, "Likes");
                        $post_likes_added = $post_likes_added + $this->storeLikes($thinkup_likes);

                        //free up memory
                        $thinkup_users = array();
                        $thinkup_likes = array();

                        if (is_int($likes_difference) && $post_likes_added >= $likes_difference) {
                            $must_process_likes = false;
                            if (isset($likes_stream->paging->next)) {
                                $this->logger->logInfo("Caught up on post ".$post_id."'s balance of ".
                                    $likes_difference." likes; stopping like processing", __METHOD__.','.__LINE__);
                            }
                        }

                        // collapsed likes
                        if (isset($p->likes->count) && $p->likes->count > $likes_captured && $must_process_likes) {
                            if (is_int($likes_difference)) {
                                $offset = $p->likes->count - $likes_difference;
                                $offset_arr = array('offset'=>$offset);
                            } else {
                                $offset_arr = null;
                            }

                            $api_call = $p->from->id.'_'.$post_id.'/likes';
                            do {
                                $likes_stream = FacebookGraphAPIAccessor::apiRequest($api_call, $this->access_token,
                                    $offset_arr);
                                if (isset($likes_stream) && is_array($likes_stream->data)) {
                                    foreach ($likes_stream->data as $l) {
                                        if (isset($l->name) && isset($l->id)) {
                                            //Get users
                                            $user_to_add = array(
                                                "user_name"=>$l->name,
                                                "full_name"=>$l->name,
                                                "user_id"=>$l->id,
                                                "avatar"=>'https://graph.facebook.com/'.$l->id.'/picture',
                                                "is_protected"=>1,
                                                "location"=>'',
                                                "description"=>'',
                                                "url"=>'',
                                                "follower_count"=>0,
                                                "post_count"=>0,
                                                "joined"=>'',
                                                "found_in"=>"Likes",
                                                "network"=>'facebook'
                                            ); //Users are always set to network=facebook
                                            array_push($thinkup_users, $user_to_add);

                                            $fav_to_add = array("favoriter_id"=>$l->id, "network"=>$network,
                                                "author_user_id"=>$p->from->id, "post_id"=>$post_id);
                                            array_push($thinkup_likes, $fav_to_add);
                                            $likes_captured = $likes_captured + 1;
                                        }
                                    }

                                    $total_added_users = $total_added_users + $this->storeUsers($thinkup_users,
                                        "Likes");
                                    $post_likes_added = $post_likes_added + $this->storeLikes($thinkup_likes);

                                    //free up memory
                                    $thinkup_users = array();
                                    $thinkup_likes = array();

                                    if (is_int($likes_difference) && $post_likes_added >= $likes_difference) {
                                        $must_process_likes = false;
                                        if (isset($likes_stream->paging->next)) {
                                            $this->logger->logInfo("Caught up on post ".$post_id."'s balance of ".
                                                $likes_difference." likes; stopping like processing",
                                                __METHOD__.','.__LINE__);
                                        }
                                    }

                                    if (isset($likes_stream->paging->next)) {
                                        $api_call = str_replace('\u00257C', '|', $likes_stream->paging->next);
                                    }
                                } else {
                                    // no likes
                                    break;
                                }
                            } while (isset($likes_stream->paging->next ) && $must_process_likes);
                        }
                    }
                    $this->logger->logInfo("Added ".$post_likes_added." like(s) for post ".$post_id,
                        __METHOD__.','.__LINE__);
                    $total_added_likes = $total_added_likes + $post_likes_added;
                }
                //free up memory
                $thinkup_users = array();
                $thinkup_likes = array();
            }
            //reset control vars for next post
            $must_process_likes = true;
            $must_process_comments = true;
            $post_comments_added = 0;
            $post_likes_added = 0;
            $comments_difference = false;
            $likes_difference = false;
        }

        $this->logger->logUserSuccess("On page ".$page_number.", captured ".$total_added_posts." post(s), "
            .$total_added_comments." comment(s), "
            .$total_added_users." user(s) and ".$total_added_likes." like(s)", __METHOD__.','.__LINE__);
        return $total_added_posts;
    }

    /**
     * Store posts and authors.
     * @param  arr $posts
     * @param  str $posts_source Where posts were found
     * @return int Total posts stored.
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
     * Store post and author.
     * @param  arr $post
     * @param  str $post_source Where post was found.
     * @return int Internal unique ID of post stored.
     */
    private function storePostAndAuthor($post, $post_source){
        $post_dao = DAOFactory::getDAO('PostDAO');
        if (isset($post['author_user_id'])) {
            $user_object = $this->fetchUser($post['author_user_id'], $post_source);
            if (isset($user_object)) {
                $post["author_username"] = $user_object->full_name;
                $post["author_fullname"] = $user_object->full_name;
                $post["author_avatar"] = $user_object->avatar;
                $post["location"] = $user_object->location;
            }
        }
        $added_post_key = $post_dao->addPost($post);
        return $added_post_key;
    }

    /**
     * Store links.
     * @param  arr $links
     * @return int Total links stored
     */
    private function storeLinks($links) {
        $total_links_added = 0;
        $link_dao = DAOFactory::getDAO('LinkDAO');
        foreach ($links as $link) {
            try {
                $added_links = $link_dao->insert($link);
                $total_links_added = $total_links_added + (($added_links)?1:0);
            } catch (DuplicateLinkException $e) {
                $this->logger->logInfo($link->url." already exists in links table",
                __METHOD__.','.__LINE__);
            } catch (DataExceedsColumnWidthException $e) {
                $this->logger->logInfo($link->url."  data exceeds table column width",
                __METHOD__.','.__LINE__);
            }

        }
        return $total_links_added;
    }

    /**
     * Store users.
     * @param  arr $users
     * @param  str $users_source Where user was found.
     * @return int Total users stored
     */
    private function storeUsers($users, $users_source) {
        $added_users = 0;
        if (count($users) > 0) {
            foreach ($users as $user) {
                $user_object = $this->fetchUser($user['user_id'], $users_source);
                if (isset($user_object)) {
                    $added_users = $added_users + 1;
                }
            }
        }
        return $added_users;
    }

    /**
     * Store likes.
     * @param  arr $likes
     * @return int Total likes added
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
     * Take a list of comments from a page or a post, run through pagination
     * and add a count member to the object.
     * @param object $comments Comments Object structure from Facebook API
     * @return object
     */
    private function normalizeComments($comments) {
        $output = (object)array('count' => 0, 'data' => array());
        while ($comments !== null) {
            foreach ($comments->data as $comment) {
                $output->data[] = $comment;
                $output->count++;
            }
            if (!empty($comments->paging->next)) {
                $next_url = $comments->paging->next;
                $comments = FacebookGraphAPIAccessor::apiRequestFullURL($next_url, $this->access_token);
            } else {
                $comments = null;
            }
        }
        return $output;
    }

    /**
     * Take a list of likes from a page or a post, run through pagination and add a count member to the object.
     * @param  object $likes Likes Object structure from Facebook API
     * @return object
     */
    private function normalizeLikes($likes) {
        $output = (object) array('count' => 0, 'data' => array());
        // Just in case we get an object with the legacy layout
        if (!isset($likes->data)) {
            if (is_int($likes)) {
                $output->count = $likes;
            } elseif (is_object($likes) && isset($likes->count) && is_int($likes->count)) {
                $output->count = $likes->count;
            }
            return $output;
        }

        while ($likes !== null) {
            foreach ($likes->data as $like) {
                $output->data[] = $like;
                $output->count++;
            }
            if (!empty($likes->paging->next)) {
                $next_url = $likes->paging->next;
                //DEBUG
                //$this->logger->logInfo("Next likes url ".$next_url, __METHOD__.','.__LINE__);
                $likes = FacebookGraphAPIAccessor::apiRequestFullURL($next_url, $this->access_token);
            } else {
                $likes = null;
            }
        }
        return $output;
    }
}
