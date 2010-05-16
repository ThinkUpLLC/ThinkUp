<?php 
/*
 Plugin Name: Facebook
 Plugin URI: http://github.com/ginatrapani/thinktank/tree/master/webapp/plugins/facebook/
 Description: Crawler plugin pulls data from Facebook for authorized users and pages.
 Icon: assets/img/facebook_icon.png
 Version: 0.01
 Author: Gina Trapani
 */

function facebook_crawl() {
    global $THINKTANK_CFG;
    global $db;
    global $conn;
    
    $logger = new Logger($THINKTANK_CFG['log_location']);
    $id = new InstanceDAO($db, $logger);
    $oid = new OwnerInstanceDAO($db, $logger);
    
    //crawl Facebook user profiles
    $instances = $id->getAllActiveInstancesStalestFirstByNetwork('facebook');
    foreach ($instances as $i) {
        $logger->setUsername($i->network_username);
        $tokens = $oid->getOAuthTokens($i->id);
        $session_key = $tokens['oauth_access_token'];
        
        $fb = new Facebook($THINKTANK_CFG['facebook_api_key'], $THINKTANK_CFG['facebook_api_secret']);
        
        $cfg = new Config($i->network_username, $i->network_user_id);
        
        $id->updateLastRun($i->id);
        $crawler = new FacebookCrawler($i, $logger, $fb, $db);
        $crawler->fetchInstanceUserInfo($i->network_user_id, $session_key);
        $crawler->fetchUserPostsAndReplies($i->network_user_id, $session_key);
        
        $id->save($crawler->instance, $crawler->owner_object->post_count, $logger, $fb);
    }
    
    //crawl Facebook pages
    $instances = $id->getAllActiveInstancesStalestFirstByNetwork('facebook page');
    foreach ($instances as $i) {
        $logger->setUsername($i->network_username);
        $tokens = $oid->getOAuthTokens($i->id);
        $session_key = $tokens['oauth_access_token'];
        
        $fb = new Facebook($THINKTANK_CFG['facebook_api_key'], $THINKTANK_CFG['facebook_api_secret']);
        
        $cfg = new Config($i->network_username, $i->network_user_id);
        
        $id->updateLastRun($i->id);
        $crawler = new FacebookCrawler($i, $logger, $fb, $db);
        
        $crawler->fetchPagePostsAndReplies($i->network_user_id, $i->network_viewer_id, $session_key);
        $id->save($crawler->instance, 0, $logger, $fb);
        
    }
    $logger->close(); # Close logging
    
}

function facebook_webapp_configuration() {
    global $THINKTANK_CFG;
    global $db;
    global $s;
    global $od;
    global $id;
    global $cfg;
    global $owner;
    global $oid;
    
    $status = facebook_process_page_actions();
    $s->assign("info", $status["info"]);
    $s->assign("error", $status["error"]);
    $s->assign("success", $status["success"]);
    
    $logger = new Logger($THINKTANK_CFG['log_location']);
    $oid = new OwnerInstanceDAO($db);
    $user_pages = array();
    $owner_instances = $id->getByOwnerAndNetwork($owner, 'facebook');
    
    if (isset($THINKTANK_CFG['facebook_api_key']) && isset($THINKTANK_CFG['facebook_api_secret'])) {
        $facebook = new Facebook($THINKTANK_CFG['facebook_api_key'], $THINKTANK_CFG['facebook_api_secret']);
        foreach ($owner_instances as $i) {
            $crawler = new FacebookCrawler($i, $logger, $facebook, $db);
            $tokens = $oid->getOAuthTokens($i->id);
            $session_key = $tokens['oauth_access_token'];
            if ($i->network_user_id == $i->network_viewer_id) {
                $pages = $crawler->fetchPagesUserIsFanOf($i->network_user_id, $session_key);
                $keys = array_keys($pages);
                foreach ($keys as $key) {
                    $pages[$key]["json"] = json_encode($pages[$key]);
                }
                $user_pages[$i->network_user_id] = $pages;
            }
        }
    } else {
        $s->assign("error", "Please set your Facebook API key and secret in config.inc.php");
    }
    $s->assign('user_pages', $user_pages);

    
    $owner_instance_pages = $id->getByOwnerAndNetwork($owner, 'facebook page');
    $s->assign('owner_instance_pages', $owner_instance_pages);

    
    $fbconnect_link = '<a href="#" onclick="FB.Connect.requireSession(); return false;" ><img id="fb_login_image" src="http://static.ak.fbcdn.net/images/fbconnect/login-buttons/connect_light_medium_long.gif" alt="Connect"/>    </a>';
    $s->assign('fbconnect_link', $fbconnect_link);
    $s->assign('owner_instances', $owner_instances);
    if (isset($THINKTANK_CFG['facebook_api_key'])) {
        $s->assign('fb_api_key', $THINKTANK_CFG['facebook_api_key']);
    }
}

function facebook_process_page_actions() {
    global $db;
    $messages = array("error"=>'', "success"=>'', "info"=>'');
    
    //insert pages
    if (isset($_GET["action"]) && $_GET["action"] == "add page" && isset($_GET["facebook_page_id"]) && isset($_GET["viewer_id"]) && isset($_GET["owner_id"]) && isset($_GET["instance_id"])) {
        $page_data = json_decode(str_replace("\\", "", $_GET["facebook_page_id"]));
        $messages = facebook_insert_page($page_data->page_id, $_GET["viewer_id"], $_GET["owner_id"], $_GET["instance_id"], $page_data->name, $db, $messages);
    }
    
    return $messages;
}

function facebook_insert_page($fb_page_id, $viewer_id, $owner_id, $existing_instance_id, $fb_page_name, $db, $messages) {
    global $id;
    global $oid;
    
    //check if instance exists
    $i = $id->getByUserAndViewerId($fb_page_id, $viewer_id);
    if ($i == null) {
        $instance_id = $id->insert($fb_page_id, $fb_page_name, "facebook page", $viewer_id);
        if ($instance_id) {
            $messages["success"] .= "Instance ID ".$instance_id." created successfully for Facebook page ID $fb_page_id.";
        }
        $tokens = $oid->getOAuthTokens($existing_instance_id);
        $session_key = $tokens['oauth_access_token'];
        $oid->insert($owner_id, $instance_id, $session_key);
    } else {
        $messages["info"] .= "Instance ".$fb_page_id.", facebook exists.";
        $instance_id = $i->id;
    }
    return $messages;
}

$crawler->registerCallback('facebook_crawl', 'crawl');
$webapp->registerCallback('facebook_webapp_configuration', 'configuration|facebook');

?>
