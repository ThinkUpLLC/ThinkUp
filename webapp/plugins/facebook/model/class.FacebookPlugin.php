<?php
class FacebookPlugin implements CrawlerPlugin, WebappPlugin {
    public function crawl() {
        $logger = Logger::getInstance();
        $config = Config::getInstance();
        $id = DAOFactory::getDAO('InstanceDAO');
        $oid = DAOFactory::getDAO('OwnerInstanceDAO');
        $od = DAOFactory::getDAO('OwnerDAO');

        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptionsHash('facebook', true); //get cached

        $current_owner = $od->getByEmail(Session::getLoggedInUser());

        //crawl Facebook user profiles
        $instances = $id->getAllActiveInstancesStalestFirstByNetwork('facebook');
        foreach ($instances as $instance) {
            if (!$oid->doesOwnerHaveAccess($current_owner, $instance)) {
                // Owner doesn't have access to this instance; let's not crawl it.
                continue;
            }
            $logger->setUsername($instance->network_username);
            $tokens = $oid->getOAuthTokens($instance->id);
            $session_key = $tokens['oauth_access_token'];

            $fb = new Facebook($options['facebook_api_key']->option_value,
            $options['facebook_api_secret']->option_value);

            $id->updateLastRun($instance->id);
            $crawler = new FacebookCrawler($instance, $fb);
            try {
                $crawler->fetchInstanceUserInfo($instance->network_user_id, $session_key);
                $crawler->fetchUserPostsAndReplies($instance->network_user_id, $session_key);
            } catch (Exception $e) {
                $logger->logStatus('PROFILE EXCEPTION: '.$e->getMessage(), get_class($this));
            }

            $id->save($crawler->instance, $crawler->owner_object->post_count, $logger);
        }

        //crawl Facebook pages
        $instances = $id->getAllActiveInstancesStalestFirstByNetwork('facebook page');
        foreach ($instances as $instance) {
            $logger->setUsername($instance->network_username);
            $tokens = $oid->getOAuthTokens($instance->id);
            $session_key = $tokens['oauth_access_token'];

            $fb = new Facebook($options['facebook_api_key']->option_value,
            $options['facebook_api_secret']->option_value);

            $id->updateLastRun($instance->id);
            $crawler = new FacebookCrawler($instance, $fb);

            try {
                $crawler->fetchPagePostsAndReplies($instance->network_user_id, $instance->network_viewer_id, $session_key);
            } catch (Exception $e) {
                $logger->logStatus('PAGE EXCEPTION: '.$e->getMessage(), get_class($this));
            }
            $id->save($crawler->instance, 0, $logger);

        }
        $logger->close(); # Close logging

    }

    public function renderConfiguration($owner) {
        $controller = new FacebookPluginConfigurationController($owner);
        return $controller->go();
    }

    public function getChildTabsUnderPosts($instance) {
        $fb_data_tpl = Utils::getPluginViewDirectory('facebook').'facebook.inline.view.tpl';

        $child_tabs = array();

        //All tab
        $alltab = new WebappTab("all_facebook_posts", "All", '', $fb_data_tpl);
        $alltabds = new WebappTabDataset("all_facebook_posts", 'PostDAO', "getAllPosts",
        array($instance->network_user_id, 'facebook', 15, false));
        $alltab->addDataset($alltabds);
        array_push($child_tabs, $alltab);
        return $child_tabs;
    }

    public function getChildTabsUnderReplies($instance) {
        $fb_data_tpl = Utils::getPluginViewDirectory('facebook').'facebook.inline.view.tpl';
        $child_tabs = array();

        //All Replies
        $artab = new WebappTab("all_facebook_replies", "Replies", "Replies to your Facebook posts", $fb_data_tpl);
        $artabds = new WebappTabDataset("all_facebook_replies", 'PostDAO', "getAllReplies",
        array($instance->network_user_id, 'facebook', 15));
        $artab->addDataset($artabds);
        array_push($child_tabs, $artab);
        return $child_tabs;
    }

    public function getChildTabsUnderFriends($instance) {
        $fb_data_tpl = Utils::getPluginViewDirectory('facebook').'facebook.inline.view.tpl';
        $child_tabs = array();

        //Popular friends
        $poptab = new WebappTab("friends_mostactive", 'Popular', '', $fb_data_tpl);
        $poptabds = new WebappTabDataset("facebook_users", 'FollowDAO', "getMostFollowedFollowees",
        array($instance->network_user_id, 15));
        $poptab->addDataset($poptabds);
        array_push($child_tabs, $poptab);

        return $child_tabs;
    }

    public function getChildTabsUnderFollowers($instance) {
        $fb_data_tpl = Utils::getPluginViewDirectory('facebook').'facebook.inline.view.tpl';
        $child_tabs = array();

        //Most followed
        $mftab = new WebappTab("followers_mostfollowed", 'Most-followed', 'Followers with most followers',
        $fb_data_tpl);
        $mftabds = new WebappTabDataset("facebook_users", 'FollowDAO', "getMostFollowedFollowers",
        array($instance->network_user_id, 15));
        $mftab->addDataset($mftabds);
        array_push($child_tabs, $mftab);

        return $child_tabs;
    }

    public function getChildTabsUnderLinks($instance) {
        $fb_data_tpl = Utils::getPluginViewDirectory('facebook').'facebook.inline.view.tpl';
        $child_tabs = array();

        //Links from friends
        $fltab = new WebappTab("links_from_friends", 'Links', 'Links posted on your wall', $fb_data_tpl);
        $fltabds = new WebappTabDataset("links_from_friends", 'LinkDAO', "getLinksByFriends",
        array($instance->network_user_id, 'facebook'));
        $fltab->addDataset($fltabds);
        array_push($child_tabs, $fltab);

        return $child_tabs;
    }
}
