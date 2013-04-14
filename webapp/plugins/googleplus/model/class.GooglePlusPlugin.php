<?php
/**
 *
 * ThinkUp/webapp/plugins/googleplus/model/class.GooglePlusPlugin.php
 *
 * Copyright (c) 2011-2013 Gina Trapani
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
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 Gina Trapani
 */
class GooglePlusPlugin extends Plugin implements CrawlerPlugin, DashboardPlugin, PostDetailPlugin {

    public function __construct($vals=null) {
        parent::__construct($vals);
        $this->folder_name = 'googleplus';
        $this->addRequiredSetting('google_plus_client_secret');
        $this->addRequiredSetting('google_plus_client_id');
    }

    public function activate() {
    }

    public function deactivate() {
    }

    public function renderConfiguration($owner) {
        $controller = new GooglePlusPluginConfigurationController($owner, 'googleplus');
        return $controller->go();
    }

    public function renderInstanceConfiguration($owner, $instance_username, $instance_network) {
        return '';
    }

    public function crawl() {
        $logger = Logger::getInstance();
        $config = Config::getInstance();
        $instance_dao = DAOFactory::getDAO('InstanceDAO');
        $owner_instance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');

        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptionsHash('googleplus', true); //get cached

        $current_owner = $owner_dao->getByEmail(Session::getLoggedInUser());

        //crawl Google+ users
        $instances = $instance_dao->getActiveInstancesStalestFirstForOwnerByNetworkNoAuthError($current_owner,
        'google+');

        if (isset($options['google_plus_client_id']->option_value)
        && isset($options['google_plus_client_secret']->option_value)) {
            foreach ($instances as $instance) {
                $logger->setUsername(ucwords($instance->network) . ' | '.$instance->network_username );
                $logger->logUserSuccess("Starting to collect data for ".$instance->network_username."'s ".
                ucwords($instance->network), __METHOD__.','.__LINE__);

                $tokens = $owner_instance_dao->getOAuthTokens($instance->id);
                $access_token = $tokens['oauth_access_token'];
                $refresh_token = $tokens['oauth_access_token_secret'];

                $instance_dao->updateLastRun($instance->id);
                $google_plus_crawler = new GooglePlusCrawler($instance, $access_token);
                $dashboard_module_cacher = new DashboardModuleCacher($instance);
                try {
                    $google_plus_crawler->initializeInstanceUser($options['google_plus_client_id']->option_value,
                    $options['google_plus_client_secret']->option_value, $access_token, $refresh_token,
                    $current_owner->id);

                    $google_plus_crawler->fetchInstanceUserPosts();
                } catch (Exception $e) {
                    $logger->logUserError('EXCEPTION: '.$e->getMessage(), __METHOD__.','.__LINE__);
                }

                $dashboard_module_cacher->cacheDashboardModules();
                $instance_dao->save($google_plus_crawler->instance, 0, $logger);
                Reporter::reportVersion($instance);
                $logger->logUserSuccess("Finished collecting data for ".$instance->network_username."'s ".
                ucwords($instance->network), __METHOD__.','.__LINE__);
            }
        }
    }

    public function getPostDetailMenuItems($post) {
        $template_path = Utils::getPluginViewDirectory('googleplus').'googleplus.inline.view.tpl';
        $menus = array();

        if ($post->network == 'google+') {
            $likes_menu_item = new MenuItem("+1s", "", $template_path);
            //if not logged in, show only public fav'd info
            $liked_dataset = new Dataset("plus1s", 'FavoritePostDAO', "getUsersWhoFavedPost", array($post->post_id,
            $post->network, !Session::isLoggedIn()) );
            $likes_menu_item->addDataset($liked_dataset);
            $menus['plus1s'] = $likes_menu_item;
        }
        return $menus;
    }

    public function getDashboardMenuItems($instance) {
        $menus = array();


        $posts_data_tpl = Utils::getPluginViewDirectory('googleplus').'posts.tpl';
        $posts_menu_item = new MenuItem("Posts", "Post insights", $posts_data_tpl);

        $posts_menu_ds_1 = new Dataset("all_posts", 'PostDAO', "getAllPosts",
        array($instance->network_user_id, $instance->network, 3, "#page_number#"),
        'getAllPostsIterator', array($instance->network_user_id, $instance->network, GridController::getMaxRows()),
        false );
        $posts_menu_item->addDataset($posts_menu_ds_1);

        $posts_menu_ds_2 = new Dataset("most_replied_to", 'PostDAO', "getMostRepliedToPosts",
        array($instance->network_user_id, $instance->network, 3, '#page_number#'));
        $posts_menu_item->addDataset($posts_menu_ds_2);

        $posts_menu_ds_3 = new Dataset("plus_oned", 'PostDAO', "getMostFavedPosts",
        array($instance->network_user_id, $instance->network, 3, '#page_number#'));
        $posts_menu_item->addDataset($posts_menu_ds_3);

        $posts_menu_ds_4 = new Dataset("questions", 'PostDAO', "getAllQuestionPosts",
        array($instance->network_user_id, $instance->network, 3, "#page_number#"));
        $posts_menu_item->addDataset($posts_menu_ds_4);

        $menus['posts'] = $posts_menu_item;

        $gp_data_tpl = Utils::getPluginViewDirectory('googleplus').'googleplus.inline.view.tpl';

        //All tab
        $alltab = new MenuItem("All posts", 'All posts', $gp_data_tpl, 'posts');
        $alltabds = new Dataset("gplus_posts", 'PostDAO', "getAllPosts",
        array($instance->network_user_id, $instance->network, 15, "#page_number#"),
        'getAllPostsIterator', array($instance->network_user_id, $instance->network, GridController::getMaxRows()),
        false );
        $alltabds->addHelp('userguide/listings/googleplus/dashboard_all_gplus_posts');
        $alltab->addDataset($alltabds);
        $menus["posts-all"] = $alltab;

        // Most replied-to tab
        $mrttab = new MenuItem("Most discussed", "Posts with the most comments", $gp_data_tpl, 'posts');
        $mrttabds = new Dataset("gplus_posts", 'PostDAO', "getMostRepliedToPosts",
        array($instance->network_user_id, $instance->network, 15, '#page_number#'));
        $mrttabds->addHelp('userguide/listings/googleplus/dashboard_mostreplies');
        $mrttab->addDataset($mrttabds);
        $menus["posts-mostreplies"] = $mrttab;

        // Most liked posts
        $mltab = new MenuItem("Most +1'ed", "Posts with most +1s", $gp_data_tpl, 'posts');
        $mltabds = new Dataset("gplus_posts", 'PostDAO', "getMostFavedPosts",
        array($instance->network_user_id, $instance->network, 15, '#page_number#'));
        $mltabds->addHelp('userguide/listings/googleplus/dashboard_mostlikes');
        $mltab->addDataset($mltabds);
        $menus["posts-mostplusones"] = $mltab;

        //Questions tab
        $qtab = new MenuItem("Inquiries", "Inquiries, or posts with a question mark in them", $gp_data_tpl, 'posts');
        $qtabds = new Dataset("gplus_posts", 'PostDAO', "getAllQuestionPosts",
        array($instance->network_user_id, $instance->network, 15, "#page_number#"));
        $qtabds->addHelp('userguide/listings/googleplus/dashboard_questions');
        $qtab->addDataset($qtabds);
        $menus["posts-questions"] = $qtab;

        return $menus;
    }
}