<?php
/**
 *
 * ThinkUp/webapp/plugins/facebook/model/class.FacebookCrawler.php
 *
 * Copyright (c) 2009-2010 Gina Trapani
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
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
*/
class FacebookCrawler {
    var $instance;
    var $logger;
    var $facebook;
    var $owner_object;
    var $user_dao;
    var $pd;

    public function __construct($instance, $facebook) {
        $this->instance = $instance;
        $this->facebook = $facebook;
        $this->logger = Logger::getInstance();
        $this->logger->setUsername($instance->network_username);
        $this->user_dao = DAOFactory::getDAO('UserDAO');
        $this->pd = DAOFactory::getDAO('PostDAO');
    }

    public function fetchInstanceUserInfo($uid, $session_key) {
        $user = $this->fetchUserInfo($uid, $session_key, "Owner Status");
        $this->owner_object = $user;
        if (isset($this->owner_object)) {
            $status_message = 'Owner info set.';
        } else {
            $status_message = 'Owner was not set.';
        }
        $this->logger->logStatus($status_message, get_class($this));
    }

    public function fetchUserInfo($uid, $session_key, $found_in) {
        // Get owner user details and save them to DB
        $user_details = $this->facebook->api_client->users_getInfo($uid,
        'first_name,last_name,current_location,username,website,pic_square,about_me');

        /*
         $serialized = serialize($user_details);
         echo "SERIALIZED USER DETAILS FOR $uid STARTING NOW:\n".$serialized."\n SERIALIZING ENDING NOW";
         print_r($user_details);
         */

        $user = $this->parseUserDetails($user_details);
        if (isset($user)) {
            $user["post_count"] = $this->pd->getTotalPostsByUser($uid, 'facebook');
            $user_object = new User($user, $found_in);
            $this->user_dao->updateUser($user_object);
            return $user_object;
        } else {
            return null;
        }
    }

    public function fetchPagesUserIsFanOf($uid, $session_key) {
        $q = "SELECT page_id, name, page_url, pic_square FROM page WHERE page_id IN ";
        $q .= "(SELECT page_id FROM page_fan WHERE uid=".$uid.")";
        try{
            $pages = $this->facebook->api_client->fql_query($q);

            /*
             $serialized = serialize($pages);
             echo "SERIALIZED PAGES FOR $uid STARTING NOW:\n".$serialized."\n SERIALIZING ENDING NOW";
             print_r($pages);
             */
             
        } catch (Exception $e){
            $this->logger->logStatus("Exception '".$e->getMessage()."' thrown when trying to retrieve pages for $uid",
            get_class($this));
            $pages = false;
        }
        return $pages;
    }

    private function parseUserDetails($details) {
        if (isset($details[0])) {
            $ua = array();

            $ua["user_name"] = $details[0]["username"];
            $ua["full_name"] = $details[0]["first_name"]." ".$details[0]["last_name"];
            $ua["user_id"] = $details[0]["uid"];
            $ua["avatar"] = $details[0]["pic_square"];
            $ua["follower_count"] = 0;
            $current_location = '';
            $larr = $details[0]["current_location"];
            if (count($larr) > 0) {
                if (isset($larr["city"])) {
                    $current_location .= $larr["city"];
                }
                if (isset($larr["state"])) {
                    $current_location .= $larr["state"];
                }
                if (isset($larr["country"])) {
                    $current_location .= $larr["country"];
                }
            }
            $ua["location"] = $current_location;
            $ua["url"] = $details[0]["website"];
            $ua["description"] = $details[0]["about_me"];
            $ua["is_protected"] = '';
            $ua["post_count"] = 0;
            $ua["joined"] = null;
            $ua["network"] = "facebook";
            return $ua;
        } else {
            return null;
        }
    }


    public function fetchUserPostsAndReplies($uid, $session_key) {
        $stream = $this->facebook->api_client->stream_get($uid, $uid, '', '', 10, $session_key, '');

        /*
         $serialized = serialize($stream);
         echo "SERIALIZED STREAM FOR $uid STARTING NOW:\n".$serialized."\n SERIALIZING ENDING NOW";
         print_r($stream);
         */

        if (isset($stream['posts']) && is_array($stream['posts']) && sizeof($stream['posts'] > 0)) {
            $this->logger->logStatus(sizeof($stream["posts"]).
            " Facebook posts found for user ID $uid with session key $session_key", get_class($this));

            $thinkup_data = $this->parseStream($stream);
            $posts = $thinkup_data["posts"];

            foreach ($posts as $post) {
                $added_posts = $this->pd->addPost($post);
                $this->logger->logStatus("Added $added_posts post for ".$post["author_username"].":".$post["post_text"],
                get_class($this));
            }

            $users = $thinkup_data["users"];
            if (count($users) > 0) {
                foreach ($users as $user) {
                    $this->fetchUserInfo($user["user_id"], $session_key, "Comments");
                }
            }
        } else {
            $this->logger->logStatus("No Facebook posts found for user ID $uid", get_class($this));
        }

    }

    public function fetchPagePostsAndReplies($pid, $uid, $session_key) {
        $stream = $this->facebook->api_client->stream_get($uid, $pid, '', '', 2, $session_key, '');

        /*
         $serialized = serialize($stream);
         echo "SERIALIZED STREAM FOR PAGE $pid STARTING NOW:\n".$serialized."\n SERIALIZING ENDING NOW";
         print_r($stream);
         */

        if (isset($stream['posts']) && is_array($stream['posts']) && sizeof($stream['posts'] > 0)) {
            $this->logger->logStatus(sizeof($stream["posts"]).
            " Facebook posts found for page ID $pid with session key $session_key", get_class($this));

            $thinkup_data = $this->parseStream($stream, 'facebook page');
            $posts = $thinkup_data["posts"];

            foreach ($posts as $post) {
                if ($post['author_username']== "" && isset($post['author_user_id'])) {
                    $commenter_object = $this->fetchUserInfo($post['author_user_id'], $session_key, 'Comments');
                    if (isset($commenter_object)) {
                        $post["author_username"] = $commenter_object->full_name;
                        $post["author_fullname"] = $commenter_object->full_name;
                        $post["author_avatar"] = $commenter_object->avatar;
                    }
                }

                $added_posts = $this->pd->addPost($post);
                $this->logger->logStatus("Added $added_posts post ID ".$post["post_id"]." on ".$post["network"].
                " for ".$post["author_username"].":".$post["post_text"],
                get_class($this));
            }

            $users = $thinkup_data["users"];
            if (count($users) > 0) {
                foreach ($users as $user) {
                    $this->fetchUserInfo($user["user_id"], $session_key, "Comments");
                }
            }
        } else {
            $this->logger->logStatus("No Facebook posts found for page ID $pid", get_class($this));
        }
    }

    private function parseStream($stream, $source='facebook') {
        $thinkup_posts = array();
        $thinkup_users = array();
        foreach ($stream["posts"] as $p) {
            $post_id = explode("_", $p["post_id"]);
            $post_id = $post_id[1];
            $profile = $this->getProfile($p['actor_id'], $stream["profiles"]);
            //assume profile comments are private and page posts are public
            $is_protected = ($source=='facebook')?1:0;
            $ttp = array("post_id"=>$post_id, "author_username"=>$profile["name"], "author_fullname"=>$profile["name"],
            "author_avatar"=>$profile["pic_square"], "author_user_id"=>$profile['id'], "post_text"=>$p['message'], 
            "pub_date"=>date('Y-m-d H:i:s', $p['created_time']), "in_reply_to_user_id"=>'', "in_reply_to_post_id"=>'', 
            "source"=>'', 'network'=>$source, 'is_protected'=>$is_protected);
            array_push($thinkup_posts, $ttp);
            $post_comments = $p["comments"]["comment_list"];
            $post_comments_count = isset($p["comments"]["count"])?$p["comments"]["count"]:0;
            if (is_array($post_comments) && sizeof($post_comments) > 0) {
                foreach ($post_comments as $c) {
                    $comment_id = explode("_", $c["id"]);
                    $comment_id = $comment_id[2];
                    $commenter = $this->getProfile($c['fromid'], $stream["profiles"]);
                    //Get posts
                    $ttp = array("post_id"=>$comment_id, "author_username"=>$commenter["name"],
                    "author_fullname"=>$commenter["name"], "author_avatar"=>$commenter["pic_square"], 
                    "author_user_id"=>$commenter["id"], 
                    "post_text"=>$c['text'], "pub_date"=>date('Y-m-d H:i:s', $c['time']), 
                    "in_reply_to_user_id"=>$profile['id'], "in_reply_to_post_id"=>$post_id, "source"=>'', 
                    'network'=>$source, 'is_protected'=>$is_protected);
                    array_push($thinkup_posts, $ttp);
                    //Get users
                    $ttu = array("user_name"=>$commenter["name"], "full_name"=>$commenter["name"],
                    "user_id"=>$c['fromid'], "avatar"=>$commenter["pic_square"], "location"=>'', 
                    "description"=>'', 
                    "url"=>'', "is_protected"=>'true', "follower_count"=>0, "post_count"=>0, "joined"=>'', 
                    "found_in"=>"Comments", "network"=>$source);
                    array_push($thinkup_users, $ttu);
                }
            }
            // collapsed comment thread
            if ($p["comments"]["count"] > 0 && $p["comments"]["count"] > sizeof($post_comments)) {
                $comments_stream = $this->facebook->api_client->fql_query(
                'SELECT xid, fromid, time, text, id FROM comment WHERE object_id='.$post_id);
                /*
                 $serialized = serialize($comments_stream);
                 echo "SERIALIZED STREAM FOR POST $post_id STARTING NOW:\n".$serialized."\n SERIALIZING ENDING NOW\n";
                 print_r($comments_stream);
                 */
                if (isset($comments_stream) && is_array($comments_stream)) {
                    foreach ($comments_stream as $c) {
                        $comment_id = explode("_", $c["id"]);
                        $comment_id = $comment_id[1];
                        //Get posts
                        $ttp = array("post_id"=>$comment_id, "author_username"=>'',
                        "author_fullname"=>'', "author_avatar"=>'', 
                        "author_user_id"=>$c["fromid"], 
                        "post_text"=>$c['text'], "pub_date"=>date('Y-m-d H:i:s', $c['time']), 
                        "in_reply_to_user_id"=>$profile['id'], "in_reply_to_post_id"=>$post_id, "source"=>'', 
                        'network'=>$source);
                        array_push($thinkup_posts, $ttp);
                    }
                }
            }
        }
        return array("posts"=>$thinkup_posts, "users"=>$thinkup_users);
    }

    private function getProfile($userid, $profiles) {
        foreach ($profiles as $p) {
            if ($p['id'] == $userid) {
                return $p;
            }
        }
        return null;
    }
}