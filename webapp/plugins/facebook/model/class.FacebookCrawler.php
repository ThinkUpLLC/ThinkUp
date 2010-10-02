<?php
/**
 *
 * ThinkUp/webapp/plugins/facebook/model/class.FacebookCrawler.php
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
 *
 * Facebook Crawler
 *
 * Retrieves user data from Facebook, converts it to ThinkUp objects, and stores them in the ThinkUp database.
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
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
        $user = $this->fetchUserInfo($this->instance->network_user_id, "Owner Status");
        if (isset($user)) {
            $this->logger->logStatus('Owner info set.', get_class($this));
        }
    }

    /**
     * Fetch and save a Facebook user's information.
     * @param int $uid Facebook user ID
     * @param str $found_in Where the user was found
     * @return User
     */
    public function fetchUserInfo($uid, $found_in) {
        // Get owner user details and save them to DB
        $fields = $found_in!='facebook page'?'id,name,about,location,website':'id,name,location,website';
        $user_details = FacebookGraphAPIAccessor::apiRequest('/'.$uid, $this->access_token,
        $fields);

        $user = $this->parseUserDetails($user_details);
        if (isset($user)) {
            $post_dao = DAOFactory::getDAO('PostDAO');
            $user["post_count"] = $post_dao->getTotalPostsByUser($uid, 'facebook');
            $user_object = new User($user, $found_in);
            $user_dao = DAOFactory::getDAO('UserDAO');
            $user_dao->updateUser($user_object);
            return $user_object;
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
            $ua = array();

            $ua["user_name"] = $details->name;
            $ua["full_name"] = $details->name;
            $ua["user_id"] = $details->id;
            $ua["avatar"] = 'https://graph.facebook.com/'.$details->id.'/picture';
            $ua['url'] = isset($details->website)?$details->website:'';
            $ua["follower_count"] = 0;
            $ua["location"] = isset($details->location->name)?$details->location->name:'';
            $ua["description"] = isset($details->about)?$details->about:'';
            $ua["is_protected"] = '';
            $ua["post_count"] = 0;
            $ua["joined"] = null;
            $ua["network"] = "facebook";
            return $ua;
        } else {
            return null;
        }
    }

    /**
     * Fetch a save the posts and replies on a user's profile.
     * @param int $uid
     */
    public function fetchUserPostsAndReplies($uid) {
        $stream = FacebookGraphAPIAccessor::apiRequest('/'.$uid.'/posts', $this->access_token);

        if (isset($stream->data) && is_array($stream->data) && sizeof($stream->data > 0)) {
            $this->logger->logStatus(sizeof($stream->data)." Facebook posts found for user ID $uid",
            get_class($this));

            $thinkup_data = $this->parseStream($stream);
            $posts = $thinkup_data["posts"];

            $post_dao = DAOFactory::getDAO('PostDAO');
            foreach ($posts as $post) {
                $added_posts = $post_dao->addPost($post);
                $this->logger->logStatus("Added $added_posts post for ".$post["author_username"].":".
                $post["post_text"], get_class($this));
            }

            $users = $thinkup_data["users"];
            if (count($users) > 0) {
                foreach ($users as $user) {
                    $user["post_count"] = $post_dao->getTotalPostsByUser($user['user_id'], 'facebook');
                    $found_in = 'Facebook user profile stream';
                    $user_object = new User($user, $found_in);
                    $user_dao = DAOFactory::getDAO('UserDAO');
                    $user_dao->updateUser($user_object);
                }
            }
        } else {
            $this->logger->logStatus("No Facebook posts found for user ID $uid", get_class($this));
        }

    }

    /**
     * Fetch a save the posts and replies on a Facebook page.
     * @param int $pid Page ID
     */
    public function fetchPagePostsAndReplies($pid) {
        $stream = FacebookGraphAPIAccessor::apiRequest('/'.$pid.'/posts', $this->access_token);

        if (isset($stream->data) && is_array($stream->data) && sizeof($stream->data > 0)) {
            $this->logger->logStatus(sizeof($stream->data)." Facebook posts found for page ID $pid",
            get_class($this));

            $thinkup_data = $this->parseStream($stream, 'facebook page');
            $posts = $thinkup_data["posts"];

            $post_dao = DAOFactory::getDAO('PostDAO');
            foreach ($posts as $post) {
                if ($post['author_username']== "" && isset($post['author_user_id'])) {
                    $commenter_object = $this->fetchUserInfo($post['author_user_id'], 'facebook page');
                    if (isset($commenter_object)) {
                        $post["author_username"] = $commenter_object->full_name;
                        $post["author_fullname"] = $commenter_object->full_name;
                        $post["author_avatar"] = $commenter_object->avatar;
                    }
                }

                $added_posts =$post_dao->addPost($post);
                $this->logger->logStatus("Added $added_posts post ID ".$post["post_id"]." on ".$post["network"].
                " for ".$post["author_username"].":".$post["post_text"], get_class($this));
            }

            $users = $thinkup_data["users"];
            if (count($users) > 0) {
                foreach ($users as $user) {
                    $user["post_count"] = $post_dao->getTotalPostsByUser($user['user_id'], 'facebook');
                    $found_in = 'Facebook page stream';
                    $user_object = new User($user, $found_in);
                    $user_dao = DAOFactory::getDAO('UserDAO');
                    $user_dao->updateUser($user_object);
                }
            }
        } else {
            $this->logger->logStatus("No Facebook posts found for page ID $pid", get_class($this));
        }
    }

    /**
     * Convert parsed JSON of a profile or page's posts into ThinkUp posts and users
     * @param Object $stream
     * @param str $source The network for the post; by default 'facebook'
     */
    private function parseStream($stream, $network='facebook') {
        $thinkup_posts = array();
        $thinkup_users = array();
        foreach ($stream->data as $p) {
            $post_id = explode("_", $p->id);
            $post_id = $post_id[1];
            $profile = $this->fetchUserInfo($p->from->id, $network);
            //assume profile comments are private and page posts are public
            $is_protected = ($network=='facebook')?1:0;
            $ttp = array("post_id"=>$post_id, "author_username"=>$profile->username,
            "author_fullname"=>$profile->username,"author_avatar"=>$profile->avatar, 
            "author_user_id"=>$profile->user_id, "post_text"=>isset($p->message)?$p->message:'', 
            "pub_date"=>$p->created_time, 
            "in_reply_to_user_id"=>'', "in_reply_to_post_id"=>'', "source"=>'', 'network'=>$network,
            'is_protected'=>$is_protected);
            array_push($thinkup_posts, $ttp);
            if ( isset($p->comments)) {
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
                                'is_protected'=>$is_protected);
                                array_push($thinkup_posts, $ttp);
                                //Get users
                                $ttu = array("user_name"=>$c->from->name, "full_name"=>$c->from->name,
                                "user_id"=>$c->from->id, "avatar"=>'https://graph.facebook.com/'.$c->id.'/picture', 
                                "location"=>'', "description"=>'', "url"=>'', "is_protected"=>'true',
                                "follower_count"=>0, "post_count"=>0, "joined"=>'', "found_in"=>"Comments",
                                "network"=>$network);
                                array_push($thinkup_users, $ttu);
                                $comments_captured = $comments_captured + 1;
                            }
                        }
                    }
                }
                // collapsed comment thread
                if (isset($p->comments->count) && $p->comments->count > $comments_captured) {
                    $comments_stream = FacebookGraphAPIAccessor::apiRequest('/'.$p->from->id.
                        '_'.$post_id.'/comments', $this->access_token);
                    if (isset($comments_stream) && is_array($comments_stream->data)) {
                        foreach ($comments_stream->data as $c) {
                            if (isset($c->from)) {
                                $comment_id = explode("_", $c->id);
                                $comment_id = $comment_id[2];
                                //Get posts
                                $ttp = array("post_id"=>$comment_id, "author_username"=>$c->from->name,
                                "author_fullname"=>$c->from->name, "author_avatar"=>'https://graph.facebook.com/'.
                                $c->from->id.'/picture', "author_user_id"=>$c->from->id, "post_text"=>$c->message,
                                "pub_date"=>$c->created_time, "in_reply_to_user_id"=>$profile->user_id, 
                                "in_reply_to_post_id"=>$post_id, "source"=>'', 'network'=>$network, 
                                'is_protected'=>$is_protected);
                                array_push($thinkup_posts, $ttp);
                                //Get users
                                $ttu = array("user_name"=>$c->from->name, "full_name"=>$c->from->name,
                                "user_id"=>$c->from->id, "avatar"=>'https://graph.facebook.com/'.$c->id.'/picture', 
                                "location"=>'', "description"=>'', "url"=>'', "is_protected"=>'true', 
                                "follower_count"=>0, "post_count"=>0, "joined"=>'', "found_in"=>"Comments", 
                                "network"=>$network);
                                array_push($thinkup_users, $ttu);
                            }
                        }
                    }
                }
            }
        }
        return array("posts"=>$thinkup_posts, "users"=>$thinkup_users);
    }

}