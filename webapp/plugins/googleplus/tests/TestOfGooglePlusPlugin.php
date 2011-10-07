<?php
/**
 *
 * ThinkUp/webapp/plugins/googleplus/tests/TestOfGooglePlusPlugin.php
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
 *
 * Test of TestOfGooglePlusPlugin
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';
require_once THINKUP_ROOT_PATH.'tests/classes/class.ThinkUpBasicUnitTestCase.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/googleplus/model/class.GooglePlusPlugin.php';


class TestOfGooglePlusPlugin extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
        $webapp = Webapp::getInstance();
        $webapp->registerPlugin('google+', 'GooglePlusPlugin');
        $webapp->setActivePlugin('google+');
    }

    public function tearDown(){
        parent::tearDown();
    }

    public function testConstructor() {
        $plugin = new GooglePlusPlugin();
        $this->assertNotNull($plugin);
        $this->assertIsA($plugin, 'GooglePlusPlugin');
        $this->assertEqual(count($plugin->required_settings), 2);
        $this->assertFalse($plugin->isConfigured());
    }

    public function testMenuItemRegistration() {
        $webapp = Webapp::getInstance();
        $logger = Logger::getInstance();
        $pd = DAOFactory::getDAO('PostDAO');
        $instance = new Instance();
        $instance->network_user_id = 1;

        $menus = $webapp->getDashboardMenu($instance);
        $posts_menu = $menus["posts-all"];

        $this->assertEqual(sizeof($menus), 5);

        $post_tab = $menus['posts-all'];
        $this->assertEqual($post_tab->name, "All posts");
        $this->assertEqual($post_tab->description, "All posts");
        $post_tab_datasets = $post_tab->getDatasets();
        $post_tab_dataset = $post_tab_datasets[0];
        $this->assertEqual($post_tab_dataset->name, "gplus_posts");
        $this->assertEqual($post_tab_dataset->dao_name, 'PostDAO');
        $this->assertEqual($post_tab_dataset->dao_method_name, "getAllPosts");

        $post_tab = $menus['posts-mostreplies'];
        $this->assertEqual($post_tab->name, "Most discussed");
        $this->assertEqual($post_tab->description, "Posts with the most comments");
        $post_tab_datasets = $post_tab->getDatasets();
        $post_tab_dataset = $post_tab_datasets[0];
        $this->assertEqual($post_tab_dataset->name, "gplus_posts");
        $this->assertEqual($post_tab_dataset->dao_name, 'PostDAO');
        $this->assertEqual($post_tab_dataset->dao_method_name, "getMostRepliedToPosts");

        $post_tab = $menus['posts-mostplusones'];
        $this->assertEqual($post_tab->name, "Most +1'ed");
        $this->assertEqual($post_tab->description, "Posts with most +1's");
        $post_tab_datasets = $post_tab->getDatasets();
        $post_tab_dataset = $post_tab_datasets[0];
        $this->assertEqual($post_tab_dataset->name, "gplus_posts");
        $this->assertEqual($post_tab_dataset->dao_name, 'PostDAO');
        $this->assertEqual($post_tab_dataset->dao_method_name, "getMostFavedPosts");

        $post_tab = $menus['posts-questions'];
        $this->assertEqual($post_tab->name, "Inquiries");
        $this->assertEqual($post_tab->description, "Inquiries, or posts with a question mark in them");
        $post_tab_datasets = $post_tab->getDatasets();
        $post_tab_dataset = $post_tab_datasets[0];
        $this->assertEqual($post_tab_dataset->name, "gplus_posts");
        $this->assertEqual($post_tab_dataset->dao_name, 'PostDAO');
        $this->assertEqual($post_tab_dataset->dao_method_name, "getAllQuestionPosts");

        $logger->close();
    }
}