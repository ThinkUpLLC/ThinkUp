<?php
/**
 *
 * ThinkUp/webapp/plugins/facebook/model/class.FacebookPlugin.php
 *
 * Copyright (c) 2009-2010 Gina Trapani, Mark Wilkie
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
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Mark Wilkie <mark[at]bitterpill[dot]org>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani, Mark Wilkie
 */
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
            $access_token = $tokens['oauth_access_token'];

            $id->updateLastRun($instance->id);
            $crawler = new FacebookCrawler($instance, $access_token);
            try {
                $crawler->fetchInstanceUserInfo();
                $crawler->fetchUserPostsAndReplies($instance->network_user_id);
            } catch (Exception $e) {
                $logger->logStatus('PROFILE EXCEPTION: '.$e->getMessage(), get_class($this));
            }

            $id->save($crawler->instance, 0, $logger);
        }

        //crawl Facebook pages
        $instances = $id->getAllActiveInstancesStalestFirstByNetwork('facebook page');
        foreach ($instances as $instance) {
            $logger->setUsername($instance->network_username);
            $tokens = $oid->getOAuthTokens($instance->id);
            $access_token = $tokens['oauth_access_token'];

            $id->updateLastRun($instance->id);
            $crawler = new FacebookCrawler($instance, $access_token);

            try {
                $crawler->fetchPagePostsAndReplies($instance->network_user_id);
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
        array($instance->network_user_id, $instance->network, 15, "#page_number#"),
        'getAllPostsIterator', array($instance->network_user_id, $instance->network, GridController::MAX_ROWS), false );
        $alltab->addDataset($alltabds);
        array_push($child_tabs, $alltab);

        // Most replied-to tab
        $mrttab = new WebappTab("mostreplies", "Most replied-to", "Posts with most replies", $fb_data_tpl);
        $mrttabds = new WebappTabDataset("most_replied_to_posts", 'PostDAO', "getMostRepliedToPosts",
        array($instance->network_user_id, $instance->network, 15, '#page_number#'));
        $mrttab->addDataset($mrttabds);
        array_push($child_tabs, $mrttab);

        //Questions tab
        $qtab = new WebappTab("questions", "Inquiries", "Inquiries, or posts with a question mark in them",
        $fb_data_tpl);
        $qtabds = new WebappTabDataset("all_facebook_posts", 'PostDAO', "getAllQuestionPosts",
        array($instance->network_user_id, $instance->network, 15, "#page_number#"));
        $qtab->addDataset($qtabds);
        array_push($child_tabs, $qtab);

        return $child_tabs;
    }

    public function getChildTabsUnderReplies($instance) {
        $child_tabs = array();
        return $child_tabs;
    }

    public function getChildTabsUnderFriends($instance) {
        $fb_data_tpl = Utils::getPluginViewDirectory('facebook').'facebook.inline.view.tpl';
        $child_tabs = array();

        //Popular friends
        //        $poptab = new WebappTab("friends_mostactive", 'Popular', '', $fb_data_tpl);
        //        $poptabds = new WebappTabDataset("facebook_users", 'FollowDAO', "getMostFollowedFollowees",
        //        array($instance->network_user_id, 15));
        //        $poptab->addDataset($poptabds);
        //        array_push($child_tabs, $poptab);

        return $child_tabs;
    }

    public function getChildTabsUnderFollowers($instance) {
        $fb_data_tpl = Utils::getPluginViewDirectory('facebook').'facebook.inline.view.tpl';
        $child_tabs = array();

        //Most followed
        //        $mftab = new WebappTab("followers_mostfollowed", 'Most-followed', 'Followers with most followers',
        //        $fb_data_tpl);
        //        $mftabds = new WebappTabDataset("facebook_users", 'FollowDAO', "getMostFollowedFollowers",
        //        array($instance->network_user_id, 15));
        //        $mftab->addDataset($mftabds);
        //        array_push($child_tabs, $mftab);

        return $child_tabs;
    }

    public function getChildTabsUnderLinks($instance) {
        $fb_data_tpl = Utils::getPluginViewDirectory('facebook').'facebook.inline.view.tpl';
        $child_tabs = array();

        //Links from friends
        //        $fltab = new WebappTab("links_from_friends", 'Links', 'Links posted on your wall', $fb_data_tpl);
        //        $fltabds = new WebappTabDataset("links_from_friends", 'LinkDAO', "getLinksByFriends",
        //        array($instance->network_user_id, 'facebook'));
        //        $fltab->addDataset($fltabds);
        //        array_push($child_tabs, $fltab);

        return $child_tabs;
    }
}
