<?php 
class Facebook {

    public $api_client;
    
    public function __construct($api_key, $secret, $generate_session_secret = false) {
        $this->api_client = new FacebookRestClient($api_key, $secret, null);
    }
    
}

class FacebookRestClient {

    public function & stream_get($viewer_id = null, $source_ids = null, $start_time = 0, $end_time = 0, $limit = 30, $filter_key = '', $exportable_only = false, $metadata = null, $post_ids = null, $query = null, $everyone_stream = false) {
    
        switch ($viewer_id) {
            case '606837591': //return two posts with no comments
                $stream = unserialize('a:2:{s:5:"posts";a:2:{i:0;a:21:{s:7:"post_id";s:25:"606837591_108956622464235";s:9:"viewer_id";s:9:"606837591";s:9:"source_id";s:9:"606837591";s:4:"type";s:2:"46";s:6:"app_id";s:0:"";s:11:"attribution";s:0:"";s:8:"actor_id";s:9:"606837591";s:9:"target_id";s:0:"";s:7:"message";s:50:"The Pacific is really good. Can\'t wait for part 3.";s:10:"attachment";a:1:{s:11:"description";s:0:"";}s:8:"app_data";s:0:"";s:12:"action_links";s:0:"";s:8:"comments";a:4:{s:10:"can_remove";s:1:"1";s:8:"can_post";s:1:"1";s:5:"count";s:1:"0";s:12:"comment_list";s:0:"";}s:5:"likes";a:6:{s:4:"href";s:82:"http://www.facebook.com/social_graph.php?node_id=108956622464235&class=LikeManager";s:5:"count";s:1:"0";s:6:"sample";s:0:"";s:7:"friends";s:0:"";s:10:"user_likes";s:1:"0";s:8:"can_like";s:1:"1";}s:7:"privacy";a:6:{s:11:"description";s:12:"Only Friends";s:5:"value";s:11:"ALL_FRIENDS";s:7:"friends";s:0:"";s:8:"networks";s:0:"";s:5:"allow";s:0:"";s:4:"deny";s:0:"";}s:12:"updated_time";s:10:"1269488627";s:12:"created_time";s:10:"1269488627";s:10:"tagged_ids";s:0:"";s:9:"is_hidden";s:1:"0";s:10:"filter_key";s:34:"f8c16f44b43083fc2545a46d-606837591";s:9:"permalink";s:82:"http://www.facebook.com/profile.php?v=feed&story_fbid=108956622464235&id=606837591";}i:1;a:21:{s:7:"post_id";s:25:"606837591_107266209295210";s:9:"viewer_id";s:9:"606837591";s:9:"source_id";s:9:"606837591";s:4:"type";s:2:"46";s:6:"app_id";s:0:"";s:11:"attribution";s:0:"";s:8:"actor_id";s:9:"606837591";s:9:"target_id";s:0:"";s:7:"message";s:23:"SHAKE IT LIKE A POM-POM";s:10:"attachment";a:1:{s:11:"description";s:0:"";}s:8:"app_data";s:0:"";s:12:"action_links";s:0:"";s:8:"comments";a:4:{s:10:"can_remove";s:1:"1";s:8:"can_post";s:1:"1";s:5:"count";s:1:"0";s:12:"comment_list";s:0:"";}s:5:"likes";a:6:{s:4:"href";s:82:"http://www.facebook.com/social_graph.php?node_id=107266209295210&class=LikeManager";s:5:"count";s:1:"0";s:6:"sample";s:0:"";s:7:"friends";s:0:"";s:10:"user_likes";s:1:"0";s:8:"can_like";s:1:"1";}s:7:"privacy";a:6:{s:11:"description";s:12:"Only Friends";s:5:"value";s:11:"ALL_FRIENDS";s:7:"friends";s:0:"";s:8:"networks";s:0:"";s:5:"allow";s:0:"";s:4:"deny";s:0:"";}s:12:"updated_time";s:10:"1269411918";s:12:"created_time";s:10:"1269411918";s:10:"tagged_ids";s:0:"";s:9:"is_hidden";s:1:"0";s:10:"filter_key";s:34:"f8c16f44b43083fc2545a46d-606837591";s:9:"permalink";s:82:"http://www.facebook.com/profile.php?v=feed&story_fbid=107266209295210&id=606837591";}}s:8:"profiles";a:1:{i:0;a:5:{s:2:"id";s:9:"606837591";s:3:"url";s:0:"";s:4:"name";s:12:"Gina Trapani";s:10:"pic_square";s:60:"http://profile.ak.fbcdn.net/v222/1942/94/q606837591_9678.jpg";s:4:"type";s:4:"user";}}}');
                return $stream;
                break;
            case '6068375911': //return two posts, one with one comment
                $stream = unserialize('a:2:{s:5:"posts";a:2:{i:0;a:21:{s:7:"post_id";s:25:"606837591_108956622464235";s:9:"viewer_id";s:9:"606837591";s:9:"source_id";s:9:"606837591";s:4:"type";s:2:"46";s:6:"app_id";s:0:"";s:11:"attribution";s:0:"";s:8:"actor_id";s:9:"606837591";s:9:"target_id";s:0:"";s:7:"message";s:50:"The Pacific is really good. Can\'t wait for part 3.";s:10:"attachment";a:1:{s:11:"description";s:0:"";}s:8:"app_data";s:0:"";s:12:"action_links";s:0:"";s:8:"comments";a:4:{s:10:"can_remove";s:1:"1";s:8:"can_post";s:1:"1";s:5:"count";s:1:"1";s:12:"comment_list";a:1:{i:0;a:4:{s:6:"fromid";s:9:"606837591";s:4:"time";s:10:"1269548986";s:4:"text";s:5:"Yes. ";s:2:"id";s:32:"606837591_108956622464235_157806";}}}s:5:"likes";a:6:{s:4:"href";s:82:"http://www.facebook.com/social_graph.php?node_id=108956622464235&class=LikeManager";s:5:"count";s:1:"0";s:6:"sample";s:0:"";s:7:"friends";s:0:"";s:10:"user_likes";s:1:"0";s:8:"can_like";s:1:"1";}s:7:"privacy";a:6:{s:11:"description";s:12:"Only Friends";s:5:"value";s:11:"ALL_FRIENDS";s:7:"friends";s:0:"";s:8:"networks";s:0:"";s:5:"allow";s:0:"";s:4:"deny";s:0:"";}s:12:"updated_time";s:10:"1269548986";s:12:"created_time";s:10:"1269488627";s:10:"tagged_ids";s:0:"";s:9:"is_hidden";s:1:"0";s:10:"filter_key";s:34:"f8c16f44b43083fc2545a46d-606837591";s:9:"permalink";s:82:"http://www.facebook.com/profile.php?v=feed&story_fbid=108956622464235&id=606837591";}i:1;a:21:{s:7:"post_id";s:25:"606837591_107266209295210";s:9:"viewer_id";s:9:"606837591";s:9:"source_id";s:9:"606837591";s:4:"type";s:2:"46";s:6:"app_id";s:0:"";s:11:"attribution";s:0:"";s:8:"actor_id";s:9:"606837591";s:9:"target_id";s:0:"";s:7:"message";s:23:"SHAKE IT LIKE A POM-POM";s:10:"attachment";a:1:{s:11:"description";s:0:"";}s:8:"app_data";s:0:"";s:12:"action_links";s:0:"";s:8:"comments";a:4:{s:10:"can_remove";s:1:"1";s:8:"can_post";s:1:"1";s:5:"count";s:1:"0";s:12:"comment_list";s:0:"";}s:5:"likes";a:6:{s:4:"href";s:82:"http://www.facebook.com/social_graph.php?node_id=107266209295210&class=LikeManager";s:5:"count";s:1:"0";s:6:"sample";s:0:"";s:7:"friends";s:0:"";s:10:"user_likes";s:1:"0";s:8:"can_like";s:1:"1";}s:7:"privacy";a:6:{s:11:"description";s:12:"Only Friends";s:5:"value";s:11:"ALL_FRIENDS";s:7:"friends";s:0:"";s:8:"networks";s:0:"";s:5:"allow";s:0:"";s:4:"deny";s:0:"";}s:12:"updated_time";s:10:"1269411918";s:12:"created_time";s:10:"1269411918";s:10:"tagged_ids";s:0:"";s:9:"is_hidden";s:1:"0";s:10:"filter_key";s:34:"f8c16f44b43083fc2545a46d-606837591";s:9:"permalink";s:82:"http://www.facebook.com/profile.php?v=feed&story_fbid=107266209295210&id=606837591";}}s:8:"profiles";a:1:{i:0;a:5:{s:2:"id";s:9:"606837591";s:3:"url";s:0:"";s:4:"name";s:12:"Gina Trapani";s:10:"pic_square";s:60:"http://profile.ak.fbcdn.net/v222/1942/94/q606837591_9678.jpg";s:4:"type";s:4:"user";}}}');
                return $stream;
                break;
            default:
                return null;
                break;
        }
    }
    public function & users_getInfo($uid, $fields) {
        switch ($uid) {
            case '606837591': //return user profile
                $details = unserialize('a:1:{i:0;a:8:{s:8:"about_me";s:0:"";s:16:"current_location";s:0:"";s:10:"first_name";s:4:"Gina";s:9:"last_name";s:7:"Trapani";s:3:"uid";s:9:"606837591";s:10:"pic_square";s:60:"http://profile.ak.fbcdn.net/v222/1942/94/q606837591_9678.jpg";s:8:"username";s:11:"ginatrapani";s:7:"website";s:0:"";}}');
                return $details;
                break;
            default:
                return null;
                break;
        }
    }

    
}



?>
