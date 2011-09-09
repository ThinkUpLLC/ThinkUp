<?php
/**
 *
 * ThinkUp/webapp/plugins/facebook/model/class.FacebookCrawler.php
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
 * Facebook Crawler
 *
 * Retrieves user data from Facebook, converts it to ThinkUp objects, and stores them in the ThinkUp database.
 * All Facebook users are inserted with the network set to 'facebook', except for page instances' corresponding user
 * (those get network='facebook page'). Comments on Facebook page posts get listed with network 'facebook page', even
 * though they are by users with network set to 'facebook'.
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2011 Gina Trapani
 */
class FacebookCrawler {
    /**
     *
     * @var Instance
     */
    var $instance;
    /**
     *
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
     *
     * @param Instance $instance
     * @return FacebookCrawler
     */
    public function __construct($instance, $access_token, $max_crawl_time) {
        $this->instance = $instance;
        $this->logger = Logger::getInstance();
        //$this->logger->setUsername(ucwords($instance->network). ' | '.$instance->network_username );
        $this->access_token = $access_token;
        $this->max_crawl_time = $max_crawl_time;
    }

    /**
     * Fetch and save the instance user's information.
     */
    public function fetchInstanceUserInfo() {
        $user = $this->fetchUserInfo($this->instance->network_user_id, $this->instance->network, "Owner Status");
        if (isset($user)) {
            $this->logger->logUserSuccess("Successfully fetched ".$this->instance->network_username.
            "'s details from Facebook", __METHOD__.','.__LINE__);
        }
    }

    /**
     * Fetch and save a Facebook user's information.
     * @param int $user_id Facebook user ID
     * @param str $network Either 'facebook page' or 'facebook'
     * @param str $found_in Where the user was found
     * @param bool $reload_from_facebook Defaults to false; if true will always query Facebook API for update
     * @return User
     */
    public function fetchUserInfo($user_id, $network, $found_in, $reload_from_facebook=false) {
        $user_dao = DAOFactory::getDAO('UserDAO');
        if ($reload_from_facebook || !$user_dao->isUserInDB($user_id, $network)) {
            // Get owner user details and save them to DB
            $fields = $network!='facebook page'?'id,name,about,location,website':'id,name,location,website';
            $user_details = FacebookGraphAPIAccessor::apiRequest('/'.$user_id, $this->access_token, $fields);
            $user_details->network = $network;

            $user = $this->parseUserDetails($user_details);
            if (isset($user)) {
                $user_object = new User($user, $found_in);
                $user_dao->updateUser($user_object);
                return $user_object;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * Convert decoded JSON data from Facebook into a ThinkUp user object.
     * @param array $details
     */
    private function parseUserDetails($details) {
        if (isset($details->name) && isset($details->id)) {
            $user_vals = array();

            $user_vals["user_name"] = $details->name;
            $user_vals["full_name"] = $details->name;
            $user_vals["user_id"] = $details->id;
            $user_vals["avatar"] = 'https://graph.facebook.com/'.$details->id.'/picture';
            $user_vals['url'] = isset($details->website)?$details->website:'';
            $user_vals["follower_count"] = 0;
            $user_vals["location"] = isset($details->location->name)?$details->location->name:'';
            $user_vals["description"] = isset($details->about)?$details->about:'';
            $user_vals["is_protected"] = 1; //for now, assume a Facebook user is private
            $user_vals["post_count"] = 0;
            $user_vals["joined"] = null;
            $user_vals["network"] = $details->network;
            //this will help us in getting correct range of posts
            $user_vals["updated_time"] = isset($details->updated_time)?$details->updated_time:0;
            return $user_vals;
        }
    }

    /**
     * Fetch and save the posts and replies on a user's profile or page. This function will loop back through a
     * user's or pages archive of posts.
     * @param int $id Facebook user or page ID.
     * @param bool $is_page If true then this is a Facebook page, else it's a user profile
     */
    public function fetchPostsAndReplies($id, $is_page) {
        $fetch_next_page = true;
        $current_page_number = 1;
        $next_api_request = 'https://graph.facebook.com/' .$id. '/posts?access_token=' .$this->access_token;

        //Cap crawl time for very busy pages with thousands of likes/comments
        $fetch_stop_time = time() + $this->max_crawl_time;

        while ($fetch_next_page) {
            $stream = FacebookGraphAPIAccessor::rawApiRequest($next_api_request, true);
            if (isset($stream->data) && is_array($stream->data) && sizeof($stream->data > 0)) {
                $this->logger->logInfo(sizeof($stream->data)." Facebook posts found on page ".$current_page_number,
                __METHOD__.','.__LINE__);

                $this->processStream($stream, (($is_page)?'facebook page':'facebook'), $current_page_number);

                if (isset($stream->paging->next)) {
                    if ($current_page_number == 1) { // Determine 'since', datetime of oldest post in datastore
                        $post_dao = DAOFactory::getDAO('PostDAO');
                        $since_post = $post_dao->getAllPosts($id, (($is_page)?'facebook page':'facebook'), 1, 1,
                        true, 'pub_date', 'ASC');
                        $since = isset($since_post[0])?$since_post[0]->pub_date:0;
                        $since = strtotime($since) - (60 * 60 * 24); // last post minus one day, just to be safe
                        ($since < 0)?$since=0:$since=$since;
                    }
                    $next_api_request = $stream->paging->next . '&since=' . $since;
                    $current_page_number++;
                } else {
                    $fetch_next_page = false;
                }
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
     * @param str $source The network for the post; by default 'facebook'
     * @param int Page number being processed
     */
    private function processStream($stream, $network, $page_number) {
        $thinkup_posts = array();
        $total_added_posts = 0;

        $thinkup_users = array();
        $total_added_users = 0;

        $thinkup_links = array();
        $total_links_added = 0;

        $thinkup_likes = array();
        $total_added_likes = 0;

        $profile = null;

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
            if ($profile==null) {
                $profile = $this->fetchUserInfo($p->from->id, $network, 'Post stream', true);
            }

            //Assume profile comments are private and page posts are public
            $is_protected = ($network=='facebook')?1:0;
            //Get likes count
            $likes_count = 0;
            if (isset($p->likes)) {
                if (is_int($p->likes)) {
                    $likes_count = $p->likes;
                } elseif (isset($p->likes->count) && is_int($p->likes->count) )  {
                    $likes_count = $p->likes->count;
                }
            }

            $post_in_storage = $post_dao->getPost($post_id, $network);

            //Figure out if we have to process likes and comments
            if (isset($post_in_storage)) {
                if ($post_in_storage->favlike_count_cache >= $likes_count ) {
                    $must_process_likes = false;
                    $this->logger->logInfo("Already have ".$likes_count." like(s) for post ".$post_id.
                    "in storage; skipping like processing", __METHOD__.','.__LINE__);
                } else  {
                    $likes_difference = $likes_count - $post_in_storage->favlike_count_cache;
                    $this->logger->logInfo($likes_difference." new like(s) to process for post ".$post_id,
                    __METHOD__.','.__LINE__);
                }

                if (isset($p->comments->count)) {
                    if ($post_in_storage->reply_count_cache >= $p->comments->count) {
                        $must_process_comments = false;
                        $this->logger->logInfo("Already have ".$p->comments->count." comment(s) for post ".$post_id.
                        "; skipping comment processing", __METHOD__.','.__LINE__);
                    } else {
                        $comments_difference = $p->comments->count - $post_in_storage->reply_count_cache;
                        $this->logger->logInfo($comments_difference." new comment(s) of ".$p->comments->count.
                        " total to process for post ".$post_id, __METHOD__.','.__LINE__);
                    }
                }
            }

            if (isset($profile) ) {
                if (!isset($post_in_storage)) {
                    $post_to_process = array("post_id"=>$post_id, "author_username"=>$profile->username,
                    "author_fullname"=>$profile->username,"author_avatar"=>$profile->avatar, 
                    "author_user_id"=>$p->from->id, "post_text"=>isset($p->message)?$p->message:'', 
                    "pub_date"=>$p->created_time, "favlike_count_cache"=>$likes_count,
                    "in_reply_to_user_id"=>'', "in_reply_to_post_id"=>'', "source"=>'', 'network'=>$network,
                    'is_protected'=>$is_protected, 'location'=>$profile->location);

                    array_push($thinkup_posts, $post_to_process);
                    $total_added_posts = $total_added_posts + $this->storePostsAndAuthors($thinkup_posts,
                    "Owner stream");
                    //free up memory
                    $thinkup_posts = array();

                    if (isset($p->source) || isset($p->link)) { // there's a link to store
                        $link_url = (isset($p->source))?$p->source:$p->link;
                        $link = new Link(array(
                        "url"=>$link_url, 
                        "expanded_url"=>$link_url, 
                        "image_src"=>(isset($p->picture))?$p->picture:'',
                        "caption"=>(isset($p->caption))?$p->caption:'', 
                        "description"=>(isset($p->description))?$p->description:'',
                        "title"=>(isset($p->name))?$p->name:'', 
                        "network"=>$network, "post_id"=>$post_id 
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
                                        $comment_id = explode("_", $c->id);
                                        $comment_id = $comment_id[2];
                                        //only add to queue if not already in storage
                                        $comment_in_storage = $post_dao->getPost($comment_id, $network);
                                        if (!isset($comment_in_storage)) {
                                            $comment_to_process = array("post_id"=>$comment_id,
                                            "author_username"=>$c->from->name, "author_fullname"=>$c->from->name,
                                            "author_avatar"=>'https://graph.facebook.com/'.$c->from->id.'/picture', 
                                            "author_user_id"=>$c->from->id, "post_text"=>$c->message, 
                                            "pub_date"=>$c->created_time, "in_reply_to_user_id"=>$profile->user_id, 
                                            "in_reply_to_post_id"=>$post_id, "source"=>'', 'network'=>$network, 
                                            'is_protected'=>$is_protected, 'location'=>'');
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
                                $comments_difference." comments; stopping comment processing", __METHOD__.','.__LINE__);
                            }
                        }
                        // collapsed comment thread
                        if (isset($p->comments->count) && $p->comments->count > $comments_captured
                        && $must_process_comments) {
                            if (is_int($comments_difference)) {
                                $offset = $p->comments->count - $comments_difference;
                                $offset_str = "&offset=".$offset."&limit=".$comments_difference;
                            } else {
                                $offset_str = "";
                            }
                            $api_call = 'https://graph.facebook.com/'.$p->from->id.'_'.$post_id.
                            '/comments?access_token='. $this->access_token.$offset_str;
                            //$this->logger->logInfo("API call ".$api_call, __METHOD__.','.__LINE__);
                            do {
                                $comments_stream = FacebookGraphAPIAccessor::rawApiRequest($api_call);
                                if (isset($comments_stream) && is_array($comments_stream->data)) {
                                    foreach ($comments_stream->data as $c) {
                                        if (isset($c->from)) {
                                            $comment_id = explode("_", $c->id);
                                            $comment_id = $comment_id[sizeof($comment_id)-1];
                                            //only add to queue if not already in storage
                                            $comment_in_storage = $post_dao->getPost($comment_id, $network);
                                            if (!isset($comment_in_storage)) {
                                                $comment_to_process = array("post_id"=>$comment_id,
                                                "author_username"=>$c->from->name, "author_fullname"=>$c->from->name,
                                                "author_avatar"=>'https://graph.facebook.com/'.
                                                $c->from->id.'/picture', "author_user_id"=>$c->from->id,
                                                "post_text"=>$c->message, "pub_date"=>$c->created_time,
                                                "in_reply_to_user_id"=>$profile->user_id,
                                                "in_reply_to_post_id"=>$post_id, "source"=>'', 'network'=>$network,
                                                'is_protected'=>$is_protected, 'location'=>'');
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
                        $this->logger->logUserInfo("Added ".$post_comments_added." comment(s) for post ". $post_id,
                        __METHOD__.','.__LINE__);
                    } else {
                        $this->logger->logInfo("Added ".$post_comments_added." comment(s) for post ". $post_id,
                        __METHOD__.','.__LINE__);
                    }
                    $total_added_posts = $total_added_posts + $post_comments_added;
                }

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
                                        $user_to_add = array("user_name"=>$l->name, "full_name"=>$l->name,
                                        "user_id"=>$l->id, "avatar"=>'https://graph.facebook.com/'.$l->id.
                                        '/picture', "location"=>'', "description"=>'', "url"=>'', "is_protected"=>1,
                                        "follower_count"=>0, "post_count"=>0, "joined"=>'', "found_in"=>"Likes",
                                        "network"=>'facebook'); //Users are always set to network=facebook
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
                                $offset_str = "&offset=".$offset;
                            } else {
                                $offset_str = "";
                            }

                            $api_call = 'https://graph.facebook.com/'.$p->from->id.'_'.$post_id.'/likes?access_token='.
                            $this->access_token.$offset_str;
                            do {
                                $likes_stream = FacebookGraphAPIAccessor::rawApiRequest($api_call);
                                if (isset($likes_stream) && is_array($likes_stream->data)) {
                                    foreach ($likes_stream->data as $l) {
                                        if (isset($l->name) && isset($l->id)) {
                                            //Get users
                                            $user_to_add = array("user_name"=>$l->name, "full_name"=>$l->name,
                                            "user_id"=>$l->id, "avatar"=>'https://graph.facebook.com/'.$l->id.
                                            '/picture', "location"=>'', "description"=>'', "url"=>'', "is_protected"=>1,
                                            "follower_count"=>0, "post_count"=>0, "joined"=>'', "found_in"=>"Likes",
                                            "network"=>'facebook'); //Users are always set to network=facebook
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
                    $this->logger->logUserInfo("Added ".$post_likes_added." like(s) for post ".$post_id,
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

        $this->logger->logUserSuccess("On page ".$page_number.", captured ".$total_added_posts." post(s), ".
        $total_added_users." user(s) and ".$total_added_likes." like(s)", __METHOD__.','.__LINE__);
    }

    private function storePostsAndAuthors($posts, $posts_source){
        $total_added_posts = 0;
        $added_posts = 0;
        $post_dao = DAOFactory::getDAO('PostDAO');
        foreach ($posts as $post) {
            if (isset($post['author_user_id'])) {
                $user_object = $this->fetchUserInfo($post['author_user_id'], 'facebook', $posts_source);
                if (isset($user_object)) {
                    $post["author_username"] = $user_object->full_name;
                    $post["author_fullname"] = $user_object->full_name;
                    $post["author_avatar"] = $user_object->avatar;
                    $post["location"] = $user_object->location;
                }
            }
            $added_posts = $post_dao->addPost($post);
            if ($added_posts > 0) {
                $this->logger->logInfo("Added post ID ".$post["post_id"]." on ".$post["network"].
                " for ".$post["author_username"].":".substr($post["post_text"],0, 20)."...", __METHOD__.','.__LINE__);
            }
            $total_added_posts = $total_added_posts + $added_posts;
            $added_posts = 0;
        }
        return $total_added_posts;
    }

    private function storeLinks($links) {
        $total_links_added = 0;
        $link_dao = DAOFactory::getDAO('LinkDAO');
        foreach ($links as $link) {
            $added_links = $link_dao->insert($link);
            $total_links_added = $total_links_added + (($added_links)?1:0);
        }
        return $total_links_added;
    }

    private function storeUsers($users, $users_source) {
        $added_users = 0;
        if (count($users) > 0) {
            foreach ($users as $user) {
                $user_object = $this->fetchUserInfo($user['user_id'], 'facebook', $users_source);
                if (isset($user_object)) {
                    $added_users = $added_users + 1;
                }
            }
        }
        return $added_users;
    }

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
}
