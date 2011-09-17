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
class GooglePlusPlugin implements CrawlerPlugin, DashboardPlugin {

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
            $crawler = new GooglePlusCrawler($instance, $access_token);
            try {
                //@TODO Make this fetchPostsAndReplies when that's ready
                $crawler->fetchUser($instance->network_user_id, 'google+', true);
            } catch (Exception $e) {
                $logger->logUserError('EXCEPTION: '.$e->getMessage(), __METHOD__.','.__LINE__);
            }

            $instance_dao->save($crawler->instance, 0, $logger);
            $logger->logUserSuccess("Finished collecting data for ".$instance->network_username."'s ".
            ucwords($instance->network), __METHOD__.','.__LINE__);
        }
    }

    public function getPostDetailMenuItems($post) {
        $template_path = Utils::getPluginViewDirectory('googleplus').'googleplus.inline.view.tpl';
        $menu_items = array();

        //Define a menu item
        $hello_menu_item_1 = new MenuItem("Data vis 1", "First data visualization", $template_path,
        'Hello ThinkUp Plugin Menu Header');
        //Define a dataset to be displayed when that menu item is selected
        $hello_menu_item_dataset_1 = new Dataset("replies_1", 'PostDAO', "getRepliesToPost",
        array($post->post_id, $post->network, 'location') );
        //Associate dataset with menu item
        $hello_menu_item_1->addDataset($hello_menu_item_dataset_1);
        //Add menu item to menu items array
        $menu_items['data_vis_1'] = $hello_menu_item_1;

        //Define a menu item
        $hello_menu_item_2 = new MenuItem("Data vis 2", "Second data visualization", $template_path);
        //Define a dataset to be displayed when that menu item is selected
        $hello_menu_item_dataset_2 = new Dataset("replies_2", 'PostDAO', "getRepliesToPost",
        array($post->post_id, $post->network, 'location') );
        //Associate dataset with menu item
        $hello_menu_item_2->addDataset($hello_menu_item_dataset_2);
        //Add menu item to menu items array
        $menu_items['data_vis_2'] = $hello_menu_item_2;

        return $menu_items;
    }


    public function getDashboardMenuItems($instance) {
        $gp_data_tpl = Utils::getPluginViewDirectory('googleplus').'googleplus.inline.view.tpl';

        $menus = array();

        //All tab
        $alltab = new MenuItem("All posts", 'All posts', $gp_data_tpl, 'Posts');
        $alltabds = new Dataset("all_gplus_posts", 'PostDAO', "getAllPosts",
        array($instance->network_user_id, $instance->network, 15, "#page_number#"),
        'getAllPostsIterator', array($instance->network_user_id, $instance->network, GridController::getMaxRows()),
        false );
        $alltabds->addHelp('userguide/listings/googleplus/dashboard_all_gplus_posts');
        $alltab->addDataset($alltabds);
        $menus["all_gplus_posts"] = $alltab;
        /* @TODO
         // Most replied-to tab
         $mrttab = new MenuItem("Most replied-to", "Posts with most replies", $gp_data_tpl);
         $mrttabds = new Dataset("most_replied_to_posts", 'PostDAO', "getMostRepliedToPosts",
         array($instance->network_user_id, $instance->network, 15, '#page_number#'));
         $mrttabds->addHelp('userguide/listings/facebook/dashboard_mostreplies');
         $mrttab->addDataset($mrttabds);
         $menus["mostreplies"] = $mrttab;

         // Most liked posts
         $mltab = new MenuItem("Most liked", "Posts with most likes", $gp_data_tpl);
         $mltabds = new Dataset("most_replied_to_posts", 'PostDAO', "getMostFavedPosts",
         array($instance->network_user_id, $instance->network, 15, '#page_number#'));
         $mltabds->addHelp('userguide/listings/facebook/dashboard_mostlikes');
         $mltab->addDataset($mltabds);
         $menus["mostlikes"] = $mltab;

         //Questions tab
         $qtab = new MenuItem("Inquiries", "Inquiries, or posts with a question mark in them",
         $gp_data_tpl);
         $qtabds = new Dataset("all_facebook_posts", 'PostDAO', "getAllQuestionPosts",
         array($instance->network_user_id, $instance->network, 15, "#page_number#"));
         $qtabds->addHelp('userguide/listings/facebook/dashboard_questions');
         $qtab->addDataset($qtabds);
         $menus["questions"] = $qtab;
         */
        return $menus;
    }
}