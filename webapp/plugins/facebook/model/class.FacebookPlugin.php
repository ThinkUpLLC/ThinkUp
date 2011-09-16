<?php
/**
 *
 * ThinkUp/webapp/plugins/facebook/model/class.FacebookPlugin.php
 *
 * Copyright (c) 2009-2011 Gina Trapani, Mark Wilkie
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
 * @copyright 2009-2011 Gina Trapani, Mark Wilkie
 */
class FacebookPlugin implements CrawlerPlugin, DashboardPlugin, PostDetailPlugin {

    public function activate() {
    }

    public function deactivate() {
        //Pause all active Facebook user profile and page instances
        $instance_dao = DAOFactory::getDAO('InstanceDAO');
        $facebook_instances = $instance_dao->getAllInstances("DESC", true, "facebook");
        foreach ($facebook_instances as $ti) {
            $instance_dao->setActive($ti->id, false);
        }
        $facebook_instances = $instance_dao->getAllInstances("DESC", true, "facebook page");
        foreach ($facebook_instances as $ti) {
            $instance_dao->setActive($ti->id, false);
        }
    }

    public function crawl() {
        $logger = Logger::getInstance();
        $config = Config::getInstance();
        $instance_dao = DAOFactory::getDAO('InstanceDAO');
        $owner_instance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');

        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptionsHash('facebook', true); //get cached

        $max_crawl_time = isset($options['max_crawl_time']) ? $options['max_crawl_time']->option_value : 20;
        //convert to seconds
        $max_crawl_time = $max_crawl_time * 60;

        $current_owner = $owner_dao->getByEmail(Session::getLoggedInUser());

        //crawl Facebook user profiles and pages
        $profiles = $instance_dao->getAllActiveInstancesStalestFirstByNetwork('facebook');
        $pages = $instance_dao->getAllActiveInstancesStalestFirstByNetwork('facebook page');
        $instances = array_merge($profiles, $pages);

        foreach ($instances as $instance) {
            if (!$owner_instance_dao->doesOwnerHaveAccess($current_owner, $instance)) {
                // Owner doesn't have access to this instance; let's not crawl it.
                continue;
            }
            $logger->setUsername(ucwords($instance->network) . ' | '.$instance->network_username );
            $logger->logUserSuccess("Starting to collect data for ".$instance->network_username."'s ".
            ucwords($instance->network), __METHOD__.','.__LINE__);

            $tokens = $owner_instance_dao->getOAuthTokens($instance->id);
            $access_token = $tokens['oauth_access_token'];

            $instance_dao->updateLastRun($instance->id);
            $crawler = new FacebookCrawler($instance, $access_token, $max_crawl_time);
            try {
                $crawler->fetchPostsAndReplies();
            } catch (Exception $e) {
                $logger->logUserError('EXCEPTION: '.$e->getMessage(), __METHOD__.','.__LINE__);
            }

            $instance_dao->save($crawler->instance, 0, $logger);
            $logger->logUserSuccess("Finished collecting data for ".$instance->network_username."'s ".
            ucwords($instance->network), __METHOD__.','.__LINE__);
        }
    }

    public function renderConfiguration($owner) {
        $controller = new FacebookPluginConfigurationController($owner);
        return $controller->go();
    }

    public function getDashboardMenuItems($instance) {
        $fb_data_tpl = Utils::getPluginViewDirectory('facebook').'facebook.inline.view.tpl';

        $menus = array();

        //All tab
        $alltab = new MenuItem("All posts", 'All status updates', $fb_data_tpl, 'Posts');
        $alltabds = new Dataset("all_facebook_posts", 'PostDAO', "getAllPosts",
        array($instance->network_user_id, $instance->network, 15, "#page_number#"),
        'getAllPostsIterator', array($instance->network_user_id, $instance->network, GridController::getMaxRows()),
        false );
        $alltabds->addHelp('userguide/listings/facebook/dashboard_all_facebook_posts');
        $alltab->addDataset($alltabds);
        $menus["all_facebook_posts"] = $alltab;

        // Most replied-to tab
        $mrttab = new MenuItem("Most replied-to", "Posts with most replies", $fb_data_tpl);
        $mrttabds = new Dataset("most_replied_to_posts", 'PostDAO', "getMostRepliedToPosts",
        array($instance->network_user_id, $instance->network, 15, '#page_number#'));
        $mrttabds->addHelp('userguide/listings/facebook/dashboard_mostreplies');
        $mrttab->addDataset($mrttabds);
        $menus["mostreplies"] = $mrttab;

        // Most liked posts
        $mltab = new MenuItem("Most liked", "Posts with most likes", $fb_data_tpl);
        $mltabds = new Dataset("most_replied_to_posts", 'PostDAO', "getMostFavedPosts",
        array($instance->network_user_id, $instance->network, 15, '#page_number#'));
        $mltabds->addHelp('userguide/listings/facebook/dashboard_mostlikes');
        $mltab->addDataset($mltabds);
        $menus["mostlikes"] = $mltab;

        //Questions tab
        $qtab = new MenuItem("Inquiries", "Inquiries, or posts with a question mark in them",
        $fb_data_tpl);
        $qtabds = new Dataset("all_facebook_posts", 'PostDAO', "getAllQuestionPosts",
        array($instance->network_user_id, $instance->network, 15, "#page_number#"));
        $qtabds->addHelp('userguide/listings/facebook/dashboard_questions');
        $qtab->addDataset($qtabds);
        $menus["questions"] = $qtab;

        //Follower count history
        $follower_history_tpl = Utils::getPluginViewDirectory('facebook').'facebook.followercount.tpl';
        $trendtab = new MenuItem(($instance->network == 'facebook page')?'Fan count history':'Friend count history',
        'Your '.(($instance->network == 'facebook page')?'fan':'friend').' count over time', $follower_history_tpl);
        $trendtabds = new Dataset("follower_count_history_by_day", 'FollowerCountDAO', 'getHistory',
        array($instance->network_user_id, $instance->network, 'DAY', 15));
        $trendtab->addDataset($trendtabds);
        $trendtabweekds = new Dataset("follower_count_history_by_week", 'FollowerCountDAO', 'getHistory',
        array($instance->network_user_id, $instance->network, 'WEEK', 15));
        $trendtab->addDataset($trendtabweekds);
        $trendtabmonthds = new Dataset("follower_count_history_by_month", 'FollowerCountDAO', 'getHistory',
        array($instance->network_user_id, $instance->network, 'MONTH', 11));
        $trendtabmonthds->addHelp('userguide/listings/facebook/dashboard_followers-history');
        $trendtab->addDataset($trendtabmonthds);
        $menus['followers-history'] = $trendtab;

        return $menus;
    }


    public function getPostDetailMenuItems($post) {
        $facebook_data_tpl = Utils::getPluginViewDirectory('facebook').'facebook.post.likes.tpl';
        $menus = array();

        if ($post->network == 'facebook' || $post->network == 'facebook page') {
            $likes_menu_item = new MenuItem("Likes", "Those who liked this post", $facebook_data_tpl, 'Facebook');
            //if not logged in, show only public fav'd info
            $liked_dataset = new Dataset("likes", 'FavoritePostDAO', "getUsersWhoFavedPost", array($post->post_id,
            $post->network, !Session::isLoggedIn()) );
            $likes_menu_item->addDataset($liked_dataset);
            $menus['likes'] = $likes_menu_item;
        }

        return $menus;
    }
}
