<?php 
class FacebookCrawler {
    var $instance;
    var $logger;
    var $facebook;
    var $owner_object;
    var $ud;
    var $db;
    
    function FacebookCrawler($instance, $logger, $facebook, $db) {
        $this->instance = $instance;
        $this->facebook = $facebook;
        $this->db = $db;
        $this->logger = $logger;
        $this->ud = new UserDAO($this->db, $this->logger);
    }
    
    function fetchUserPostsAndReplies($uid, $session_key) {
        $stream = $this->facebook->api_client->stream_get($uid, $uid, '', '', 10, $session_key, '');
        
        if (sizeof($stream['posts'] > 0)) {
            $this->logger->logStatus(sizeof($stream["posts"])." Facebook posts found for user ID $uid with session key $session_key", get_class($this));
            //print_r($stream['posts']);
            
            $posts = $this->parsePosts($stream['posts']);
            
            $pd = new PostDAO($this->db, $this->logger);
            foreach ($posts as $post) {
                $pd->addPost($post);
            }
        } else {
            $this->logger->logStatus("No Facebook posts found for user ID $uid", get_class($this));
        }
        
    }
    
    private function parsePosts($fbdata) {
        $thinktank_posts = array();
        foreach ($fbdata as $p) {
            $post_id = split("_", $p["post_id"]);
            $post_id = $post_id[1];
            $ttp = array("post_id"=>$post_id, "author_username"=>'', "author_fullname"=>'', "author_avatar"=>'', "user_id"=>$p['actor_id'], "post_text"=>$p['message'], "pub_date"=>date('Y-m-d H:i:s',  $p['created_time'] ), "in_reply_to_user_id"=>'', "in_reply_to_post_id"=>'', "user_name"=>'', "full_name"=>'', "avatar"=>'', "source"=>'', 'network'=>'facebook');
            array_push($thinktank_posts, $ttp);
        }
        return $thinktank_posts;
    }
}
?>
