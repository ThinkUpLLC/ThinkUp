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
class GooglePlusPlugin implements CrawlerPlugin, PostDetailPlugin {

    public function activate() {
    }

    public function deactivate() {
    }

    public function renderConfiguration($owner) {
        $controller = new GooglePlusPluginConfigurationController($owner, 'googleplus');
        return $controller->go();
    }

    public function crawl() {
        //echo "GooglePlus crawler plugin is running now.";
        /**
        * When crawling, make sure you only work on objects the current Owner has access to.
        *
        * Example:
        *
        *	$od = DAOFactory::getDAO('OwnerDAO');
        *	$oid = DAOFactory::getDAO('OwnerInstanceDAO');
        *
        * $current_owner = $od->getByEmail(Session::getLoggedInUser());
        *
        * $instances = [...]
        * foreach ($instances as $instance) {
        *	    if (!$oid->doesOwnerHaveAccess($current_owner, $instance)) {
        *	        // Owner doesn't have access to this instance; let's not crawl it.
        *	        continue;
        *	    }
        *	    [...]
        * }
        *
        */
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
}