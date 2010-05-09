<?php
class FacebookCrawler {
    var $instance;
    var $logger;
    var $facebook;
    var $owner_object;
    var $ud;
    var $pd;
    var $db;

    function FacebookCrawler($instance, $logger, $facebook, $db) {
        $this->instance = $instance;
        $this->facebook = $facebook;
        $this->db = $db;
        $this->logger = $logger;
        $this->ud = new UserDAO($this->db, $this->logger);
        $this->pd = new PostDAO($this->db, $this->logger);
    }

    function fetchInstanceUserInfo($uid, $session_key) {
        $user = $this->fetchUserInfo($uid, $session_key, "Owner Status");
        $this->owner_object = $user;
        if (isset($this->owner_object)) {
            $status_message = 'Owner info set.';
        } else {
            $status_message = 'Owner was not set.';
        }
        $this->logger->logStatus($status_message, get_class($this));
    }

    function fetchUserInfo($uid, $session_key, $found_in) {
        // Get owner user details and save them to DB
        $user_details = $this->facebook->api_client->users_getInfo($uid, 'first_name,last_name,current_location,username,website,pic_square,about_me');

        /*
         $serialized = serialize($user_details);
         echo "SERIALIZED USER DETAILS FOR $uid STARTING NOW:\n".$serialized."\n SERIALIZING ENDING NOW";
         print_r($user_details);*/


        $user = $this->parseUserDetails($user_details);
        $user["post_count"] = $this->pd->getTotalPostsByUser($uid);
        $user_object = new User($user, $found_in);
        $this->ud->updateUser($user_object);
        return $user_object;
    }

    function fetchPagesUserIsFanOf($uid, $session_key) {
        $query = "SELECT page_id, name, page_url FROM page WHERE page_id IN (SELECT page_id FROM page_fan WHERE uid = ".$uid.")";
        try{
            $pages = $this->facebook->api_client->fql_query($query);
             
            //         $serialized = serialize($pages);
            //         echo "SERIALIZED PAGES FOR $uid STARTING NOW:\n".$serialized."\n SERIALIZING ENDING NOW";
            //         print_r($pages);
             
        } catch (Exception $e){
            $this->logger->logStatus("Exception '".$e->getMessage()."' thrown when trying to retrieve pages for $uid", get_class($this));
            $pages = false;
        }
        return $pages;
    }

    private function parseUserDetails($details) {
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
    }


    function fetchUserPostsAndReplies($uid, $session_key) {
        $stream = $this->facebook->api_client->stream_get($uid, $uid, '', '', 10, $session_key, '');

        /*$serialized = serialize($stream);
         echo "SERIALIZED STREAM FOR $uid STARTING NOW:\n".$serialized."\n SERIALIZING ENDING NOW";
         print_r($stream);*/

        if (is_array($stream['posts']) && sizeof($stream['posts'] > 0)) {
            $this->logger->logStatus(sizeof($stream["posts"])." Facebook posts found for user ID $uid with session key $session_key", get_class($this));

            $thinktank_data = $this->parseStream($stream);
            $posts = $thinktank_data["posts"];

            foreach ($posts as $post) {
                $added_posts = $this->pd->addPost($post);
                $this->logger->logStatus("Added $added_posts post for ".$post["user_name"].":".$post["post_text"], get_class($this));
            }

            $users = $thinktank_data["users"];
            if (count($users) > 0) {
                foreach ($users as $user) {
                    $this->fetchUserInfo($user["user_id"], $session_key, "Comments");
                }
            }
        } else {
            $this->logger->logStatus("No Facebook posts found for user ID $uid", get_class($this));
        }

    }

    function fetchPagePostsAndReplies($pid, $uid, $session_key) {
        $stream = $this->facebook->api_client->stream_get($uid, $pid, '', '', 2, $session_key, '');

        //        $serialized = serialize($stream);
        //        echo "SERIALIZED STREAM FOR PAGE $pid STARTING NOW:\n".$serialized."\n SERIALIZING ENDING NOW";
        //        print_r($stream);

        if (is_array($stream['posts']) && sizeof($stream['posts'] > 0)) {
            $this->logger->logStatus(sizeof($stream["posts"])." Facebook posts found for page ID $pid with session key $session_key", get_class($this));

            $thinktank_data = $this->parseStream($stream);
            $posts = $thinktank_data["posts"];

            foreach ($posts as $post) {
                $added_posts = $this->pd->addPost($post);
                $this->logger->logStatus("Added $added_posts post for ".$post["user_name"].":".$post["post_text"], get_class($this));
            }

            $users = $thinktank_data["users"];
            if (count($users) > 0) {
                foreach ($users as $user) {
                    $this->fetchUserInfo($user["user_id"], $session_key, "Comments");
                }
            }
        } else {
            $this->logger->logStatus("No Facebook posts found for page ID $pid", get_class($this));
        }

    }


    private function parseStream($stream) {
        $thinktank_posts = array();
        $thinktank_users = array();
        foreach ($stream["posts"] as $p) {
            $post_id = split("_", $p["post_id"]);
            $post_id = $post_id[1];
            $profile = $this->getProfile($p['actor_id'], $stream["profiles"]);
            $ttp = array("post_id"=>$post_id, "user_name"=>$profile["name"], "full_name"=>$profile["name"], "avatar"=>$profile["pic_square"], "user_id"=>$profile['id'], "post_text"=>$p['message'], "pub_date"=>date('Y-m-d H:i:s', $p['created_time']), "in_reply_to_user_id"=>'', "in_reply_to_post_id"=>'', "source"=>'', 'network'=>'facebook');
            array_push($thinktank_posts, $ttp);
            $post_comments = $p["comments"]["comment_list"];
            if (is_array($post_comments) && sizeof($post_comments) > 0) {
                foreach ($post_comments as $c) {
                    $comment_id = split("_", $c["id"]);
                    $comment_id = $comment_id[2];
                    $commenter = $this->getProfile($c['fromid'], $stream["profiles"]);
                    //Get posts
                    $ttp = array("post_id"=>$comment_id, "user_name"=>$commenter["name"], "full_name"=>$commenter["name"], "avatar"=>$commenter["pic_square"], "user_id"=>$commenter["id"], "post_text"=>$c['text'], "pub_date"=>date('Y-m-d H:i:s', $c['time']), "in_reply_to_user_id"=>$profile['id'], "in_reply_to_post_id"=>$post_id, "source"=>'', 'network'=>'facebook');
                    array_push($thinktank_posts, $ttp);
                    //Get users
                    $ttu = array("user_name"=>$commenter["name"], "full_name"=>$commenter["name"], "user_id"=>$c['fromid'], "avatar"=>$commenter["pic_square"], "location"=>'', "description"=>'', "url"=>'', "is_protected"=>'true', "follower_count"=>0, "post_count"=>0, "joined"=>'', "found_in"=>"Comments", "network"=>"facebook");
                    array_push($thinktank_users, $ttu);
                }
            }
        }
        return array("posts"=>$thinktank_posts, "users"=>$thinktank_users);
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

?>
