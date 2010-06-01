<?php
class FacebookPlugin implements CrawlerPlugin, WebappPlugin {
    public function crawl() {
        global $db;
        global $conn;

        $logger = Logger::getInstance();
        $config = Config::getInstance();
        $id = DAOFactory::getDAO('InstanceDAO');
        $oid = new OwnerInstanceDAO($db, $logger);

        //crawl Facebook user profiles
        $instances = $id->getAllActiveInstancesStalestFirstByNetwork('facebook');
        foreach ($instances as $instance) {
            $logger->setUsername($instance->network_username);
            $tokens = $oid->getOAuthTokens($instance->id);
            $session_key = $tokens['oauth_access_token'];

            $fb = new Facebook($config->getValue('facebook_api_key'), $config->getValue('facebook_api_secret'));

            $id->updateLastRun($instance->id);
            $crawler = new FacebookCrawler($instance, $fb, $db);
            $crawler->fetchInstanceUserInfo($instance->network_user_id, $session_key);
            $crawler->fetchUserPostsAndReplies($instance->network_user_id, $session_key);

            $id->save($crawler->instance, $crawler->owner_object->post_count, $logger, $fb);
        }

        //crawl Facebook pages
        $instances = $id->getAllActiveInstancesStalestFirstByNetwork('facebook page');
        foreach ($instances as $instance) {
            $logger->setUsername($instance->network_username);
            $tokens = $oid->getOAuthTokens($instance->id);
            $session_key = $tokens['oauth_access_token'];

            $fb = new Facebook($config->getValue('facebook_api_key'), $config->getValue('facebook_api_secret'));

            $id->updateLastRun($instance->id);
            $crawler = new FacebookCrawler($instance, $fb, $db);

            $crawler->fetchPagePostsAndReplies($instance->network_user_id, $instance->network_viewer_id, $session_key);
            $id->save($crawler->instance, 0, $logger, $fb);

        }
        $logger->close(); # Close logging

    }

    public function renderConfiguration() {
        global $db;
        global $s;
        global $od;
        global $id;
        global $owner;
        global $oid;

        $config = Config::getInstance();
        $status = self::facebook_process_page_actions();
        $s->assign("info", $status["info"]);
        $s->assign("error", $status["error"]);
        $s->assign("success", $status["success"]);

        $logger = Logger::getInstance();
        $oid = new OwnerInstanceDAO($db);
        $user_pages = array();
        $owner_instances = $id->getByOwnerAndNetwork($owner, 'facebook');

        $api_key = $config->getValue('facebook_api_key');
        $api_secret = $config->getValue('facebook_api_secret');

        if (isset($api_key) && isset($api_secret)) {
            $facebook = new Facebook($api_key, $api_secret);
            foreach ($owner_instances as $instance) {
                $crawler = new FacebookCrawler($instance, $facebook, $db);
                $tokens = $oid->getOAuthTokens($instance->id);
                $session_key = $tokens['oauth_access_token'];
                if ($instance->network_user_id == $instance->network_viewer_id) {
                    $pages = $crawler->fetchPagesUserIsFanOf($instance->network_user_id, $session_key);
                    $keys = array_keys($pages);
                    foreach ($keys as $key) {
                        $pages[$key]["json"] = json_encode($pages[$key]);
                    }
                    $user_pages[$instance->network_user_id] = $pages;
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
        if (isset($api_key)) {
            $s->assign('fb_api_key', $api_key);
        }
    }

    function facebook_process_page_actions() {
        global $db;
        $messages = array("error"=>'', "success"=>'', "info"=>'');

        //insert pages
        if (isset($_GET["action"]) && $_GET["action"] == "add page" && isset($_GET["facebook_page_id"]) && isset($_GET["viewer_id"]) && isset($_GET["owner_id"]) && isset($_GET["instance_id"])) {
            $page_data = json_decode(str_replace("\\", "", $_GET["facebook_page_id"]));
            $messages = self::facebook_insert_page($page_data->page_id, $_GET["viewer_id"], $_GET["owner_id"], $_GET["instance_id"], $page_data->name, $db, $messages);
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
            $instance_id = $instance->id;
        }
        return $messages;
    }

    public function getChildTabsUnderPosts($instance) {
        global $pd;

        $fb_data_tpl = Utils::getPluginViewDirectory('facebook').'facebook.inline.view.tpl';

        $child_tabs = array();

        //All tab
        $alltab = new WebappTab("all_facebook_posts", "All", '', $fb_data_tpl);
        $alltabds = new WebappTabDataset("all_facebook_posts", $pd, "getAllPosts", array($instance->network_user_id, 15));
        $alltab->addDataset($alltabds);
        array_push($child_tabs, $alltab);
        return $child_tabs;
    }

    public function getChildTabsUnderReplies($instance) {
        global $pd;

        $fb_data_tpl = Utils::getPluginViewDirectory('facebook').'facebook.inline.view.tpl';
        $child_tabs = array();

        //All Replies
        $artab = new WebappTab("all_facebook_replies", "Replies", "Replies to your Facebook posts", $fb_data_tpl);
        $artabds = new WebappTabDataset("all_facebook_replies", $pd, "getAllReplies", array($instance->network_user_id, 15));
        $artab->addDataset($artabds);
        array_push($child_tabs, $artab);
        return $child_tabs;
    }

    public function getChildTabsUnderFriends($instance) {
        global $fd;

        $fb_data_tpl = Utils::getPluginViewDirectory('facebook').'facebook.inline.view.tpl';
        $child_tabs = array();

        //Popular friends
        $poptab = new WebappTab("friends_mostactive", 'Popular', '', $fb_data_tpl);
        $poptabds = new WebappTabDataset("facebook_users", $fd, "getMostFollowedFollowees", array($instance->network_user_id, 15));
        $poptab->addDataset($poptabds);
        array_push($child_tabs, $poptab);

        return $child_tabs;
    }

    public function getChildTabsUnderFollowers($instance) {
        global $fd;

        $fb_data_tpl = Utils::getPluginViewDirectory('facebook').'facebook.inline.view.tpl';
        $child_tabs = array();

        //Most followed
        $mftab = new WebappTab("followers_mostfollowed", 'Most-followed', 'Followers with most followers', $fb_data_tpl);
        $mftabds = new WebappTabDataset("facebook_users", $fd, "getMostFollowedFollowers", array($instance->network_user_id, 15));
        $mftab->addDataset($mftabds);
        array_push($child_tabs, $mftab);

        return $child_tabs;
    }

    public function getChildTabsUnderLinks($instance) {
        global $ld;

        $fb_data_tpl = Utils::getPluginViewDirectory('facebook').'facebook.inline.view.tpl';
        $child_tabs = array();

        //Links from friends
        $fltab = new WebappTab("links_from_friends", 'Links', 'Links posted on your wall', $fb_data_tpl);
        $fltabds = new WebappTabDataset("links_from_friends", $ld, "getLinksByFriends", array($instance->network_user_id));
        $fltab->addDataset($fltabds);
        array_push($child_tabs, $fltab);

        return $child_tabs;
    }


}
?>
