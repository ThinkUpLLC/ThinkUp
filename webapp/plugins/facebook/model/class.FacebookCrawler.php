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
     *
     * @param Instance $instance
     * @return FacebookCrawler
     */
    public function __construct($instance, $access_token) {
        $this->instance = $instance;
        $this->logger = Logger::getInstance();
        $this->logger->setUsername($instance->network_username);
        $this->access_token = $access_token;
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
     * @param int $uid Facebook user ID
     * @param str $network Either 'facebook page' or 'facebook'
     * @param str $found_in Where the user was found
     * @return User
     */
    public function fetchUserInfo($uid, $network, $found_in) {
        // Get owner user details and save them to DB
        $fields = $network!='facebook page'?'id,name,about,location,website':'id,name,location,website';
<<<<<<< HEAD
        $user_details = FacebookGraphAPIAccessor::apiRequest('/'.$uid, $this->access_token);
=======
        //$user_details = FacebookGraphAPIAccessor::apiRequest('/'.$uid, $this->access_token,
        //$fields);
		$user_details = FacebookGraphAPIAccessor::apiRequest('/'.$uid, $this->access_token);
>>>>>>> d27175998a2f0f2752e9fa97e691c9f2254b1b3f
        $user_details->network = $network;

        $user = $this->parseUserDetails($user_details);
		//var_dump($user);exit;
        if (isset($user)) {
            $post_dao = DAOFactory::getDAO('PostDAO');
            $user["post_count"] = $post_dao->getTotalPostsByUser($user['user_name'], 'facebook');
            $user_object = new User($user, $found_in);
<<<<<<< HEAD
            // storing Facebook 'updated_time' in other space in User object, in case it might interfer with other code
            $user_object->updated_time = $user['updated_time'];
=======
			// storing Facebook 'updated_time' in other space in User object, in case it might interfer with other code
			$user_object->updated_time = $user['updated_time'];
>>>>>>> d27175998a2f0f2752e9fa97e691c9f2254b1b3f
            $user_dao = DAOFactory::getDAO('UserDAO');
            $user_dao->updateUser($user_object);
			//var_dump($user_object);
            return $user_object; exit;
        } else {
            return null;
        }
    }

    /**
     * Convert decoded JSON data from Facebook into a ThinkUp user object.
     * @param array $details
     */
    private function parseUserDetails($details) {
	    //var_dump($details);
        if (isset($details->name) && isset($details->id)) {
<<<<<<< HEAD
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
            $user_vals["updated_time"] = isset($details->updated_time)?$details->updated_time:0; // this will help us in getting correct range of posts
            return $user_vals;
=======
            $ua = array();

            $ua["user_name"] = $details->name;
            $ua["full_name"] = $details->name;
            $ua["user_id"] = $details->id;
            $ua["avatar"] = 'https://graph.facebook.com/'.$details->id.'/picture';
            $ua['url'] = isset($details->website)?$details->website:'';
            $ua["follower_count"] = 0;
            $ua["location"] = isset($details->location->name)?$details->location->name:'';
            $ua["description"] = isset($details->about)?$details->about:'';
            $ua["is_protected"] = 1; //for now, assume a Facebook user is private
            $ua["post_count"] = 0;
            $ua["joined"] = null;
            $ua["network"] = $details->network;
			$ua["updated_time"] = isset($details->updated_time)?$details->updated_time:''; // this will help us in getting correct range of posts
            return $ua;
>>>>>>> d27175998a2f0f2752e9fa97e691c9f2254b1b3f
        } else {
            return null;
        }
    }

    /**
     * Fetch a save the posts and replies on a user's profile or page.
     * @param int $id Facebook user or page ID.
     * @param bool $is_page If true then this is a Facebook page, else it's a user profile
     */
    public function fetchPostsAndReplies($id, $is_page) {
<<<<<<< HEAD
		// 'since' is the datetime of the last post in ThinkUp DB. 'until' is the last post in stream, according to Facebook
		$post_dao = DAOFactory::getDAO('PostDAO');
		$sincePost = $post_dao->getAllPosts($id, "facebook", 1, true, 'pub_date', 'DESC');
		$since = $sincePost[0]->pub_date;
		$since = strtotime($since) - (60 * 60 * 24); // last post minus one day, just to be safe
		$profile = $this->fetchUserInfo($id, 'facebook', 'Post stream');
		$until = $profile->other["updated_time"];
		
		$keepLooping = TRUE;
		$i = 0;
		$rawNextRequest = 'https://graph.facebook.com/' .$id. '/posts?access_token=' .$this->access_token;
		
		while ($keepLooping) {
			$i++;
			$stream = FacebookGraphAPIAccessor::rawApiRequest($rawNextRequest, TRUE);
			if (isset($stream->data) && is_array($stream->data) && sizeof($stream->data > 0)) {
				$this->logger->logInfo(sizeof($stream->data)." Facebook posts found.",
				__METHOD__.','.__LINE__);

				$thinkup_data = $this->processStream($stream, (($is_page)?'facebook page':'facebook'));
				
				//get the next page for the loop
				$rawNextRequest = $stream->paging->next;
			} else {
				$this->logger->logInfo("No Facebook posts found for ID $id", __METHOD__.','.__LINE__);
				$keepLooping = FALSE;
			}
			
			if ($i > 10) {
			    $keepLooping = FALSE; //failsafe to keep from looping forever
			}
		}
		
		
=======
        //$stream = FacebookGraphAPIAccessor::apiRequest('/'.$id.'/posts', $this->access_token);
		// 'since' is the datetime of the last post in DB. 'until' is the last post in stream, according to Facebook
		
		$post_dao = DAOFactory::getDAO('PostDAO');
		//$sincePost = $post_dao->getAllPostsByUsernameOrderedBy($id, "facebook", 1, "pub_date");
		//$sincePost = $post_dao->getAllPostsByUserID($id, "facebook", 1, $order_by="pub_date", $direction="DESC");
		$sincePost = $post_dao->getAllPostsIterator($id, "facebook", 1, true, 'pub_date', 'DESC');
		
		var_dump($sincePost); exit; 
		
        $stream = FacebookGraphAPIAccessor::apiRequest('/'.$id.'/posts', $this->access_token, null, array('since' =>'0'));
        if (isset($stream->data) && is_array($stream->data) && sizeof($stream->data > 0)) {
            $this->logger->logInfo(sizeof($stream->data)." Facebook posts found.",
            __METHOD__.','.__LINE__);

            $thinkup_data = $this->processStream($stream, (($is_page)?'facebook page':'facebook'));
        } else {
            $this->logger->logInfo("No Facebook posts found for ID $id", __METHOD__.','.__LINE__);
        }
>>>>>>> d27175998a2f0f2752e9fa97e691c9f2254b1b3f
    }

    /**
     * Convert parsed JSON of a profile or page's posts into ThinkUp posts and users
     * @param Object $stream
     * @param str $source The network for the post; by default 'facebook'
     */
    private function processStream($stream, $network) {
        $thinkup_posts = array();
        $total_added_posts = 0;

        $thinkup_users = array();
        $total_added_users = 0;

        $thinkup_links = array();
        $total_links_added = 0;

        $thinkup_likes = array();
        $total_added_likes = 0;

        $profile = null;
        foreach ($stream->data as $p) {
            $post_id = explode("_", $p->id);
            $post_id = $post_id[1];
            if ($profile==null) {
                $profile = $this->fetchUserInfo($p->from->id, $network, 'Post stream');
            }
			//var_dump($profile);
            //assume profile comments are private and page posts are public
            $is_protected = ($network=='facebook')?1:0;
            //get likes count
            $likes_count = 0;
            if (isset($p->likes)) {
                if (is_int($p->likes)) {
                    $likes_count = $p->likes;
                } elseif (isset($p->likes->count) && is_int($p->likes->count) )  {
                    $likes_count = $p->likes->count;
                }
            }
            if (isset($profile)) {
                $ttp = array("post_id"=>$post_id, "author_username"=>$profile->username,
                "author_fullname"=>$profile->username,"author_avatar"=>$profile->avatar, 
                "author_user_id"=>$p->from->id, "post_text"=>isset($p->message)?$p->message:'', 
                "pub_date"=>$p->created_time, "favlike_count_cache"=>$likes_count,
                "in_reply_to_user_id"=>'', "in_reply_to_post_id"=>'', "source"=>'', 'network'=>$network,
                'is_protected'=>$is_protected, 'location'=>$profile->location);

                array_push($thinkup_posts, $ttp);
                $total_added_posts = $total_added_posts + $this->storePosts($thinkup_posts);
            }

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
                $this->logger->logUserSuccess("Collected $total_links_added new links", __METHOD__.','.__LINE__);
            }
            //free up memory
            $thinkup_links  = array();

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
                                //Get posts
                                $ttp = array("post_id"=>$comment_id, "author_username"=>$c->from->name,
                                "author_fullname"=>$c->from->name,
                                "author_avatar"=>'https://graph.facebook.com/'.$c->from->id.'/picture', 
                                "author_user_id"=>$c->from->id, "post_text"=>$c->message, 
                                "pub_date"=>$c->created_time, "in_reply_to_user_id"=>$profile->user_id, 
                                "in_reply_to_post_id"=>$post_id, "source"=>'', 'network'=>$network, 
                                'is_protected'=>$is_protected, 'location'=>'');
                                array_push($thinkup_posts, $ttp);
                                //Get users
                                $ttu = array("user_name"=>$c->from->name, "full_name"=>$c->from->name,
                                "user_id"=>$c->from->id, "avatar"=>'https://graph.facebook.com/'.$c->from->id.
                                '/picture', "location"=>'', "description"=>'', "url"=>'', "is_protected"=>1,
                                "follower_count"=>0, "post_count"=>0, "joined"=>'', "found_in"=>"Comments",
                                "network"=>'facebook', 'location'=>''); //Users are always set to network=facebook
                                array_push($thinkup_users, $ttu);
                                $comments_captured = $comments_captured + 1;
                            }
                        }
                    }
                }
                $total_added_posts = $total_added_posts + $this->storePosts($thinkup_posts);
                $total_added_users = $total_added_users + $this->storeUsers($thinkup_users);
                //free up memory
                $thinkup_posts = array();
                $thinkup_users = array();

                // collapsed comment thread
                if (isset($p->comments->count) && $p->comments->count > $comments_captured) {
                    $api_call = 'https://graph.facebook.com/'.$p->from->id.'_'.$post_id.'/comments?access_token='.
                    $this->access_token;
                    do {
                        $comments_stream = FacebookGraphAPIAccessor::rawApiRequest($api_call);
                        if (isset($comments_stream) && is_array($comments_stream->data)) {
                            foreach ($comments_stream->data as $c) {
                                if (isset($c->from)) {
                                    $comment_id = explode("_", $c->id);
                                    $comment_id = $comment_id[sizeof($comment_id)-1];
                                    //Get posts
                                    $ttp = array("post_id"=>$comment_id, "author_username"=>$c->from->name,
                                    "author_fullname"=>$c->from->name, "author_avatar"=>'https://graph.facebook.com/'.
                                    $c->from->id.'/picture', "author_user_id"=>$c->from->id, "post_text"=>$c->message,
                                    "pub_date"=>$c->created_time, "in_reply_to_user_id"=>$profile->user_id,
                                    "in_reply_to_post_id"=>$post_id, "source"=>'', 'network'=>$network,
                                    'is_protected'=>$is_protected, 'location'=>'');
                                    array_push($thinkup_posts, $ttp);
                                    //Get users
                                    $ttu = array("user_name"=>$c->from->name, "full_name"=>$c->from->name,
                                    "user_id"=>$c->from->id, "avatar"=>'https://graph.facebook.com/'.$c->from->id.
                                    '/picture', "location"=>'', "description"=>'', "url"=>'', "is_protected"=>1,
                                    "follower_count"=>0, "post_count"=>0, "joined"=>'', "found_in"=>"Comments",
                                    "network"=>'facebook', 'location'=>''); //Users are always set to network=facebook
                                    array_push($thinkup_users, $ttu);
                                }
                            }

                            $total_added_posts = $total_added_posts + $this->storePosts($thinkup_posts);
                            $total_added_users = $total_added_users + $this->storeUsers($thinkup_users);
                            //free up memory
                            $thinkup_posts = array();
                            $thinkup_users = array();
                            if (isset($comments_stream->paging->next)) {
                                $api_call = str_replace('\u00257C', '|', $comments_stream->paging->next);
                            }
                        } else {
                            // no comments (pun intended)
                            break;
                        }
                    }
                    while (isset($comments_stream->paging->next));
                }
            }

            //process "likes"
            if (isset($p->likes)) {
                $likes_captured = 0;
                if (isset($p->likes->data)) {
                    $post_likes = $p->likes->data;
                    $post_likes_count = isset($post_likes)?sizeof($post_likes):0;
                    if (is_array($post_likes) && sizeof($post_likes) > 0) {
                        foreach ($post_likes as $l) {
                            if (isset($l->name) && isset($l->id)) {
                                //Get users
                                $ttu = array("user_name"=>$l->name, "full_name"=>$l->name,
                                "user_id"=>$l->id, "avatar"=>'https://graph.facebook.com/'.$l->id.
                                '/picture', "location"=>'', "description"=>'', "url"=>'', "is_protected"=>1,
                                "follower_count"=>0, "post_count"=>0, "joined"=>'', "found_in"=>"Likes",
                                "network"=>'facebook'); //Users are always set to network=facebook
                                array_push($thinkup_users, $ttu);

                                $fav_to_add = array("favoriter_id"=>$l->id, "network"=>$network,
                                "author_user_id"=>$profile->user_id, "post_id"=>$post_id);
                                array_push($thinkup_likes, $fav_to_add);
                                $likes_captured = $likes_captured + 1;
                            }
                        }
                    }
                }

                $total_added_users = $total_added_users + $this->storeUsers($thinkup_users);
                $total_added_likes = $total_added_likes + $this->storeLikes($thinkup_likes);
                //free up memory
                $thinkup_users = array();
                $thinkup_likes = array();

                // collapsed likes
                if (isset($p->likes->count) && $p->likes->count > $likes_captured) {
                    $api_call = 'https://graph.facebook.com/'.$p->from->id.'_'.$post_id.'/likes?access_token='.
                    $this->access_token;
                    do {
                        $likes_stream = FacebookGraphAPIAccessor::rawApiRequest($api_call);
                        if (isset($likes_stream) && is_array($likes_stream->data)) {
                            foreach ($likes_stream->data as $l) {
                                if (isset($l->name) && isset($l->id)) {
                                    //Get users
                                    $ttu = array("user_name"=>$l->name, "full_name"=>$l->name,
                                    "user_id"=>$l->id, "avatar"=>'https://graph.facebook.com/'.$l->id.
                                    '/picture', "location"=>'', "description"=>'', "url"=>'', "is_protected"=>1,
                                    "follower_count"=>0, "post_count"=>0, "joined"=>'', "found_in"=>"Likes",
                                    "network"=>'facebook'); //Users are always set to network=facebook
                                    array_push($thinkup_users, $ttu);

                                    $fav_to_add = array("favoriter_id"=>$l->id, "network"=>$network,
                                    "author_user_id"=>$p->from->id, "post_id"=>$post_id);
                                    array_push($thinkup_likes, $fav_to_add);
                                    $likes_captured = $likes_captured + 1;
                                }
                            }

                            $total_added_posts = $total_added_posts + $this->storePosts($thinkup_posts);
                            $total_added_users = $total_added_users + $this->storeUsers($thinkup_users);
                            //free up memory
                            $thinkup_posts = array();
                            $thinkup_users = array();

                            if (isset($likes_stream->paging->next)) {
                                $api_call = str_replace('\u00257C', '|', $likes_stream->paging->next);
                            }
                        } else {
                            // no likes
                            break;
                        }
                    }
                    while (isset($likes_stream->paging->next));
                }
            }
            $total_added_users = $total_added_users + $this->storeUsers($thinkup_users);
            $total_added_likes = $total_added_likes + $this->storeLikes($thinkup_likes);
            //free up memory
            $thinkup_users = array();
            $thinkup_likes = array();
        }

        if ($total_added_posts > 0 ) {
            $this->logger->logUserSuccess("Collected $total_added_posts posts", __METHOD__.','.__LINE__);
        } else {
            $this->logger->logUserInfo("No new posts found.", __METHOD__.','.__LINE__);
        }
        if ($total_added_users > 0 ) {
            $this->logger->logUserSuccess("Collected $total_added_users users", __METHOD__.','.__LINE__);
        } else {
            $this->logger->logUserInfo("No new users found.", __METHOD__.','.__LINE__);
        }
        if ($total_added_likes > 0 ) {
            $this->logger->logUserSuccess("Collected $total_added_likes likes", __METHOD__.','.__LINE__);
        } else {
            $this->logger->logUserInfo("No new likes found.", __METHOD__.','.__LINE__);
        }
    }

    private function storePosts($posts){
        $added_posts = 0;
        $post_dao = DAOFactory::getDAO('PostDAO');
        foreach ($posts as $post) {
            if (isset($post['author_user_id'])) {
                $user_object = $this->fetchUserInfo($post['author_user_id'], 'facebook', 'Facebook page comments');
                if (isset($user_object)) {
                    $post["author_username"] = $user_object->full_name;
                    $post["author_fullname"] = $user_object->full_name;
                    $post["author_avatar"] = $user_object->avatar;
                    $post["location"] = $user_object->location;
                }
            }
            $added_posts = $added_posts + $post_dao->addPost($post);
            if ($added_posts == 0 && isset($post['favlike_count_cache'])) {
                //post already exists in storage, so update its like count only
                $post_dao->updateFavLikeCount($post['post_id'], $post['network'], $post['favlike_count_cache']);
            }

            $this->logger->logInfo("Added post ID ".$post["post_id"]." on ".$post["network"].
            " for ".$post["author_username"].":".$post["post_text"], __METHOD__.','.__LINE__);
        }
        return $added_posts;
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

    private function storeUsers($users) {
        $added_users = 0;
        if (count($users) > 0) {
            foreach ($users as $user) {
                $user_object = $this->fetchUserInfo($user['user_id'], 'facebook', 'Facebook stream');
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
