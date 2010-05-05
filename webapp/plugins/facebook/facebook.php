<?php
/*
 Plugin Name: Facebook
 Plugin URI: http://github.com/ginatrapani/thinktank/tree/master/common/plugins/facebook/
 Description: Crawler plugin pulls data from Facebook for authorized users and pages.
 Icon: facebook_icon.png
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
    $icd = new InstanceChannelDAO($db);

    if (isset($THINKTANK_CFG['facebook_api_key']) && isset($THINKTANK_CFG['facebook_api_secret'])) {
        $facebook = new Facebook($THINKTANK_CFG['facebook_api_key'], $THINKTANK_CFG['facebook_api_secret']);
        foreach ($owner_instances as $i) {
            $crawler = new FacebookCrawler($i, $logger, $facebook, $db);
            $tokens = $oid->getOAuthTokens($i->id);
            $session_key = $tokens['oauth_access_token'];
            $pages = $crawler->fetchPagesUserIsFanOf($i->network_user_id, $session_key);
            $keys = array_keys($pages);
            foreach ($keys as $key) {
                $pages[$key]["json"] = json_encode($pages[$key]);
            }
            $user_pages[$i->network_user_id] = $pages;
            $channels[$i->id] = $icd->getByInstanceAndNetwork($i->id, 'facebook');
        }
    } else {
        $s->assign("error", "Please set your Facebook API key and secret in config.inc.php");
    }

    $s->assign('user_pages', $user_pages);
    $s->assign('channels', $channels);

    $fbconnect_link = '<a href="#" onclick="FB.Connect.requireSession(); return false;" ><img id="fb_login_image" src="http://static.ak.fbcdn.net/images/fbconnect/login-buttons/connect_light_medium_long.gif" alt="Connect"/>	</a>';
    $s->assign('fbconnect_link', $fbconnect_link);
    $s->assign('owner_instances', $owner_instances);
    if (isset($THINKTANK_CFG['facebook_api_key'])) {
        $s->assign('fb_api_key', $THINKTANK_CFG['facebook_api_key']);
    }
}

function facebook_process_page_actions() {
    global $db;
    $messages = array("error"=>'', "success"=>'', "info"=>'' );

    //insert pages
    if (isset($_GET["action"]) && $_GET["action"] == "add page" && isset($_GET["facebook_page_id"]) && isset($_GET["instance_id"])) {
        $page_data = json_decode($_GET["facebook_page_id"]);
        $messages = facebook_insert_page_as_channel($page_data->page_id, $_GET["instance_id"], $page_data->name, $page_data->page_url, $db, $messages);
    }

    //remove pages
    if (isset($_GET["action"]) && $_GET["action"] == "remove page" && isset($_GET["facebook_page_id"]) && isset($_GET["instance_id"])) {
        $messages = facebook_remove_page($_GET["facebook_page_id"], $_GET["instance_id"], $db, $messages);
    }

    return $messages;
}

function facebook_insert_page_as_channel($fb_page_id, $instance_id, $fb_page_name, $fb_page_url, $db, $messages){

    $cd = new ChannelDAO($db);
    //check if channel exists
    $c = $cd->getByNetworkId($fb_page_id, 'facebook');
    if ($c == null ) {
        $channel_id = $cd->insert($fb_page_name, "facebook", $fb_page_id, $fb_page_url);
        if ($channel_id) {
            $messages["success"] .= "Channel ID ". $channel_id ." created successfully.";
        }
    } else {
        $messages["info"] .= "Channel ".$fb_page_id.", facebook exists.";
        $channel_id = $c->id;
    }
    if ($channel_id) {
        $icd = new InstanceChannelDAO($db);

        $ic = $icd->get($instance_id, $channel_id);
        if ($ic == null ) {
            $icd_id = $icd->insert($instance_id, $channel_id);
            if ($icd_id) {
                $messages["success"] .= "Instance Channel ID ". $icd_id. " created successfully.";
            } else {
                $messages["error"] .= "Instance Channel creation failed.";
            }
        } else {
            $messages["info"] .= "Channel Instance for ".$instance_id.", ".$channel_id." exists.";
        }

    } else {
        $messages["error"] .= "Channel creation failed.";
    }
    return $messages;
}

function facebook_remove_page($fb_page_id, $instance_id, $db, $messages){
    $cd = new ChannelDAO($db);
    //check if channel exists
    $c = $cd->getByNetworkId($fb_page_id, 'facebook');
    if ($c != null ) {
        $channel_id = $c->id;
        if ($cd->delete($fb_page_id, "facebook")) {
            $messages["success"] .= "Channel ID ". $channel_id ." deleted successfully.";
        }
    } else {
        $messages["info"] .= "Channel ".$fb_page_id.", facebook does not exist.";
    }
    if (isset($channel_id)) {
        $icd = new InstanceChannelDAO($db);

        $ic = $icd->get($instance_id, $channel_id);
        if ($ic != null ) {
            $icd_id = $ic->id;
            if ($icd->delete($instance_id, $channel_id)) {
                $messages["success"] .= "Instance Channel ID ". $icd_id. " deleted successfully.";
            } else {
                $messages["error"] .= "Instance Channel deletion failed.";
            }
        } else {
            $messages["info"] .= "Channel Instance for ".$instance_id.", ".$channel_id." does not exist.";
        }

    } else {
        $messages["error"] .= "Channel deletion failed.";
    }
    return $messages;

}


$crawler->registerCallback('facebook_crawl', 'crawl');
$webapp->registerCallback('facebook_webapp_configuration', 'configuration|facebook');

?>
