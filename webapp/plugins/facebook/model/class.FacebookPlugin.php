<?php
/**
 *
 * ThinkUp/webapp/plugins/facebook/model/class.FacebookPlugin.php
 *
 * Copyright (c) 2009-2013 Gina Trapani, Mark Wilkie
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkup.com).
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
 * @copyright 2009-2013 Gina Trapani, Mark Wilkie
 */
class FacebookPlugin extends Plugin implements CrawlerPlugin, DashboardPlugin, PostDetailPlugin {

    public function __construct($vals=null) {
        parent::__construct($vals);
        $this->folder_name = 'facebook';
        $this->addRequiredSetting('facebook_app_id');
        $this->addRequiredSetting('facebook_api_secret');
    }

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
        $profiles = $instance_dao->getActiveInstancesStalestFirstForOwnerByNetworkNoAuthError($current_owner,
        'facebook');
        $pages = $instance_dao->getActiveInstancesStalestFirstForOwnerByNetworkNoAuthError($current_owner,
        'facebook page');
        $instances = array_merge($profiles, $pages);

        foreach ($instances as $instance) {
            $logger->setUsername(ucwords($instance->network) . ' | '.$instance->network_username );
            $logger->logUserSuccess("Starting to collect data for ".$instance->network_username."'s ".
            ucwords($instance->network), __METHOD__.','.__LINE__);

            $tokens = $owner_instance_dao->getOAuthTokens($instance->id);
            $access_token = $tokens['oauth_access_token'];

            $instance_dao->updateLastRun($instance->id);
            $facebook_crawler = new FacebookCrawler($instance, $access_token, $max_crawl_time);
            $dashboard_module_cacher = new DashboardModuleCacher($instance);
            try {
                $facebook_crawler->fetchPostsAndReplies();
            } catch (APIOAuthException $e) {
                //The access token is invalid, save in owner_instances table
                $owner_instance_dao->setAuthError($current_owner->id, $instance->id, $e->getMessage());
                //Send email alert
                $this->sendInvalidOAuthEmailAlert($current_owner->email, $instance->network_username);
                $logger->logUserError('EXCEPTION: '.$e->getMessage(), __METHOD__.','.__LINE__);
            } catch (Exception $e) {
                $logger->logUserError('EXCEPTION: '.$e->getMessage(), __METHOD__.','.__LINE__);
            }
            $dashboard_module_cacher->cacheDashboardModules();

            $instance_dao->save($facebook_crawler->instance, 0, $logger);
            Reporter::reportVersion($instance);
            $logger->logUserSuccess("Finished collecting data for ".$instance->network_username."'s ".
            ucwords($instance->network), __METHOD__.','.__LINE__);
        }
    }

    /**
     * Send user email alert about invalid OAuth tokens, at most one message per week.
     * In test mode, this will only write the message body to a file in the application data directory.
     * @param str $email
     * @param str $username
     */
    private function sendInvalidOAuthEmailAlert($email, $username) {
        //Determine whether or not an email about invalid tokens was sent in the past 7 days
        $should_send_email = true;
        $option_dao = DAOFactory::getDAO('OptionDAO');
        $plugin_dao = DAOFactory::getDAO('PluginDAO');

        $plugin_id = $plugin_dao->getPluginId('facebook');
        $last_email_timestamp = $option_dao->getOptionByName(OptionDAO::PLUGIN_OPTIONS.'-'.$plugin_id,
        'invalid_oauth_email_sent_timestamp');
        if (isset($last_email_timestamp)) { //option exists, a message was sent
            //a message was sent in the past week
            if ($last_email_timestamp->option_value > strtotime('-1 week') ) {
                $should_send_email = false;
            } else {
                $option_dao->updateOption($last_email_timestamp->option_id, time());
            }
        } else {
            $option_dao->insertOption(OptionDAO::PLUGIN_OPTIONS.'-'.$plugin_id,
            'invalid_oauth_email_sent_timestamp', time());
        }

        if ($should_send_email) {
            $mailer_view_mgr = new ViewManager();
            $mailer_view_mgr->caching=false;

            $mailer_view_mgr->assign('thinkup_site_url', Utils::getApplicationURL());
            $mailer_view_mgr->assign('email', $email );
            $mailer_view_mgr->assign('faceboook_user_name', $username);
            $message = $mailer_view_mgr->fetch(Utils::getPluginViewDirectory('facebook').'_email.invalidtoken.tpl');

            Mailer::mail($email, "Please re-authorize ThinkUp to access ". $username. " on Facebook", $message);
        }
    }

    public function renderConfiguration($owner) {
        $controller = new FacebookPluginConfigurationController($owner);
        return $controller->go();
    }

    public function renderInstanceConfiguration($owner, $instance_username, $instance_network) {
        return '';
    }

    public function getDashboardMenuItems($instance) {
        $menus = array();

        $posts_data_tpl = Utils::getPluginViewDirectory('facebook').'posts.tpl';
        $posts_menu_item = new MenuItem("Posts", "Post insights", $posts_data_tpl);

        $posts_menu_ds_1 = new Dataset("all_posts", 'PostDAO', "getAllPosts",
        array($instance->network_user_id, $instance->network, 5, "#page_number#"),
        'getAllPostsIterator', array($instance->network_user_id, $instance->network, GridController::getMaxRows()),
        false );
        $posts_menu_item->addDataset($posts_menu_ds_1);

        $posts_menu_ds_2 = new Dataset("most_replied_to", 'PostDAO', "getMostRepliedToPosts",
        array($instance->network_user_id, $instance->network, 5, '#page_number#'));
        $posts_menu_item->addDataset($posts_menu_ds_2);

        $posts_menu_ds_3 = new Dataset("most_liked", 'PostDAO', "getMostFavedPosts",
        array($instance->network_user_id, $instance->network, 5, '#page_number#'));
        $posts_menu_item->addDataset($posts_menu_ds_3);

        $posts_menu_ds_4 = new Dataset("inquiries", 'PostDAO', "getAllQuestionPosts",
        array($instance->network_user_id, $instance->network, 5, "#page_number#"));
        $posts_menu_item->addDataset($posts_menu_ds_4);

        $posts_menu_ds_5 = new Dataset("wallposts", 'PostDAO', "getPostsToUser",
        array($instance->network_user_id, $instance->network, 5, '#page_number#', !Session::isLoggedIn()),
        'getPostsToUserIterator', array($instance->network_user_id, $instance->network, GridController::getMaxRows()));
        $posts_menu_item->addDataset($posts_menu_ds_5);

        $menus['posts'] = $posts_menu_item;

        $friends_data_tpl = Utils::getPluginViewDirectory('facebook').'friends.tpl';
        $friend_fan_menu_title = $instance->network == 'facebook page'?'Fans':'Friends';
        $friends_menu_item = new MenuItem($friend_fan_menu_title, "Friends insights", $friends_data_tpl);

        $friends_menu_ds_2 = new Dataset("follower_count_history_by_day", 'CountHistoryDAO', 'getHistory',
        array($instance->network_user_id, $instance->network, 'DAY', 15));
        $friends_menu_item->addDataset($friends_menu_ds_2);
        $friends_menu_ds_3 = new Dataset("follower_count_history_by_week", 'CountHistoryDAO', 'getHistory',
        array($instance->network_user_id, $instance->network, 'WEEK', 15));
        $friends_menu_item->addDataset($friends_menu_ds_3);
        $friends_menu_ds_4 = new Dataset("follower_count_history_by_month", 'CountHistoryDAO', 'getHistory',
        array($instance->network_user_id, $instance->network, 'MONTH', 11));
        $friends_menu_item->addDataset($friends_menu_ds_4);

        $menus['friends'] = $friends_menu_item;

        $fb_data_tpl = Utils::getPluginViewDirectory('facebook').'facebook.inline.view.tpl';

        //All tab
        $alltab = new MenuItem("All posts", 'All your status updates', $fb_data_tpl, 'posts' );
        $alltabds = new Dataset("all_facebook_posts", 'PostDAO', "getAllPosts",
        array($instance->network_user_id, $instance->network, 15, "#page_number#"),
        'getAllPostsIterator', array($instance->network_user_id, $instance->network, GridController::getMaxRows()),
        false);
        $alltabds->addHelp('userguide/listings/facebook/dashboard_all_facebook_posts');
        $alltab->addDataset($alltabds);
        $menus["posts-all"] = $alltab;

        // Most replied-to tab
        $mrttab = new MenuItem("Most replied-to", "Posts with most replies", $fb_data_tpl, 'posts' );
        $mrttabds = new Dataset("most_replied_to_posts", 'PostDAO', "getMostRepliedToPosts",
        array($instance->network_user_id, $instance->network, 15, '#page_number#'));
        $mrttabds->addHelp('userguide/listings/facebook/dashboard_mostreplies');
        $mrttab->addDataset($mrttabds);
        $menus["posts-mostreplies"] = $mrttab;

        // Most liked posts
        $mltab = new MenuItem("Most liked", "Posts with most likes", $fb_data_tpl, 'posts' );
        $mltabds = new Dataset("most_replied_to_posts", 'PostDAO', "getMostFavedPosts",
        array($instance->network_user_id, $instance->network, 15, '#page_number#'));
        $mltabds->addHelp('userguide/listings/facebook/dashboard_mostlikes');
        $mltab->addDataset($mltabds);
        $menus["posts-mostlikes"] = $mltab;

        //Questions tab
        $qtab = new MenuItem("Inquiries", "Inquiries, or posts with a question mark in them", $fb_data_tpl, 'posts' );
        $qtabds = new Dataset("all_facebook_posts", 'PostDAO', "getAllQuestionPosts",
        array($instance->network_user_id, $instance->network, 15, "#page_number#"));
        $qtabds->addHelp('userguide/listings/facebook/dashboard_questions');
        $qtab->addDataset($qtabds);
        $menus["posts-questions"] = $qtab;

        // Wall Posts
        $messagestab = new MenuItem("Posts On Your Wall", "Posts to your wall by other users", $fb_data_tpl, 'posts' );
        $messagestabds = new Dataset("messages_to_you", 'PostDAO', "getPostsToUser",
        array($instance->network_user_id, $instance->network, 15, '#page_number#', !Session::isLoggedIn()),
        'getPostsToUserIterator', array($instance->network_user_id, $instance->network, GridController::getMaxRows()));
        $messagestabds->addHelp('userguide/listings/facebook/dashboard-wallposts');
        $messagestab->addDataset($messagestabds);
        $menus["posts-toyou"] = $messagestab;

        return $menus;
    }

    public function getPostDetailMenuItems($post) {
        $facebook_data_tpl = Utils::getPluginViewDirectory('facebook').'facebook.post.likes.tpl';
        $menus = array();

        if ($post->network == 'facebook' || $post->network == 'facebook page') {
            $likes_menu_item = new MenuItem("Likes", "Those who liked this post", $facebook_data_tpl);
            //if not logged in, show only public fav'd info
            $liked_dataset = new Dataset("likes", 'FavoritePostDAO', "getUsersWhoFavedPost", array($post->post_id,
            $post->network, !Session::isLoggedIn()) );
            $likes_menu_item->addDataset($liked_dataset);
            $menus['likes'] = $likes_menu_item;
        }

        return $menus;
    }
}
