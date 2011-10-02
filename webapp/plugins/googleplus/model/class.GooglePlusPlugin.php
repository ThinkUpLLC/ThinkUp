<?php
/**
 *
 * ThinkUp/webapp/plugins/googleplus/model/class.GooglePlusPlugin.php
 *
 * Copyright (c) 2011 Gina Trapani
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
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011 Gina Trapani
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
        $instances = $instance_dao->getAllActiveInstancesStalestFirstByNetwork('google+');

        if (isset($options['google_plus_client_id']->option_value)
        && isset($options['google_plus_client_secret']->option_value)) {
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
                $refresh_token = $tokens['oauth_access_token_secret'];

                $instance_dao->updateLastRun($instance->id);
                $crawler = new GooglePlusCrawler($instance, $access_token);
                try {
                    $crawler->initializeInstanceUser($options['google_plus_client_id']->option_value,
                    $options['google_plus_client_secret']->option_value, $access_token, $refresh_token,
                    $current_owner->id);

                    $crawler->fetchInstanceUserPosts();
                } catch (Exception $e) {
                    $logger->logUserError('EXCEPTION: '.$e->getMessage(), __METHOD__.','.__LINE__);
                }

                $instance_dao->save($crawler->instance, 0, $logger);
                $logger->logUserSuccess("Finished collecting data for ".$instance->network_username."'s ".
                ucwords($instance->network), __METHOD__.','.__LINE__);
            }
        }
    }

    public function getPostDetailMenuItems($post) {
        $template_path = Utils::getPluginViewDirectory('googleplus').'googleplus.inline.view.tpl';
        $menus = array();

        if ($post->network == 'google+') {
            $likes_menu_item = new MenuItem("+1's", "Those who +1'ed this post", $template_path, 'Google+');
            //if not logged in, show only public fav'd info
            $liked_dataset = new Dataset("plus1s", 'FavoritePostDAO', "getUsersWhoFavedPost", array($post->post_id,
            $post->network, !Session::isLoggedIn()) );
            $likes_menu_item->addDataset($liked_dataset);
            $menus['plus1s'] = $likes_menu_item;
        }

        return $menus;
    }

    public function getDashboardMenuItems($instance) {
        $gp_data_tpl = Utils::getPluginViewDirectory('googleplus').'googleplus.inline.view.tpl';

        $menus = array();

        //All tab
        $alltab = new MenuItem("All posts", 'All posts', $gp_data_tpl, 'Posts');
        $alltabds = new Dataset("gplus_posts", 'PostDAO', "getAllPosts",
        array($instance->network_user_id, $instance->network, 15, "#page_number#"),
        'getAllPostsIterator', array($instance->network_user_id, $instance->network, GridController::getMaxRows()),
        false );
        $alltabds->addHelp('userguide/listings/googleplus/dashboard_all_gplus_posts');
        $alltab->addDataset($alltabds);
        $menus["all_gplus_posts"] = $alltab;

        // Most replied-to tab
        $mrttab = new MenuItem("Most discussed", "Posts with the most comments", $gp_data_tpl);
        $mrttabds = new Dataset("gplus_posts", 'PostDAO', "getMostRepliedToPosts",
        array($instance->network_user_id, $instance->network, 15, '#page_number#'));
        $mrttabds->addHelp('userguide/listings/googleplus/dashboard_mostreplies');
        $mrttab->addDataset($mrttabds);
        $menus["most_replied_to_gplus"] = $mrttab;

        // Most liked posts
        $mltab = new MenuItem("Most +1'ed", "Posts with most +1's", $gp_data_tpl);
        $mltabds = new Dataset("gplus_posts", 'PostDAO', "getMostFavedPosts",
        array($instance->network_user_id, $instance->network, 15, '#page_number#'));
        $mltabds->addHelp('userguide/listings/googleplus/dashboard_mostlikes');
        $mltab->addDataset($mltabds);
        $menus["most_plus_oned"] = $mltab;

        //Questions tab
        $qtab = new MenuItem("Inquiries", "Inquiries, or posts with a question mark in them",
        $gp_data_tpl);
        $qtabds = new Dataset("gplus_posts", 'PostDAO', "getAllQuestionPosts",
        array($instance->network_user_id, $instance->network, 15, "#page_number#"));
        $qtabds->addHelp('userguide/listings/googleplus/dashboard_questions');
        $qtab->addDataset($qtabds);
        $menus["gplus_questions"] = $qtab;
        return $menus;
    }
}