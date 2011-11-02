<?php
/**
 *
 * ThinkUp/webapp/plugins/facebook/tests/TestOfFacebookPlugin.php
 *
 * Copyright (c) 2009-2011 Gina Trapani
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
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2011 Gina Trapani
 */
require_once 'tests/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/web_tester.php';

require_once THINKUP_ROOT_PATH.'webapp/plugins/facebook/model/class.FacebookPlugin.php';

class TestOfFacebookPlugin extends ThinkUpUnitTestCase {

    public function setUp() {
        parent::setUp();
        $webapp = Webapp::getInstance();
        $webapp->registerPlugin('facebook', 'FacebookPlugin');
        $webapp->setActivePlugin('facebook');
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $plugin = new FacebookPlugin();
        $this->assertIsA($plugin, 'FacebookPlugin');
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

        $this->assertEqual(sizeof($menus), 7);
        $post_tab = $menus['posts-all'];
        $this->assertEqual($post_tab->name, "All posts");
        $this->assertEqual($post_tab->description, "All your status updates");

        $post_tab_datasets = $post_tab->getDatasets();
        $this->assertEqual(count($post_tab_datasets), 1);
        $post_tab_dataset = $post_tab_datasets[0];
        $this->assertEqual($post_tab_dataset->name, "all_facebook_posts");
        $this->assertEqual($post_tab_dataset->dao_name, 'PostDAO');
        $this->assertEqual($post_tab_dataset->dao_method_name, "getAllPosts");

        $post_tab = $menus['posts-mostreplies'];
        $this->assertEqual($post_tab->name, "Most replied-to");
        $this->assertEqual($post_tab->description, "Posts with most replies");

        $post_tab_datasets = $post_tab->getDatasets();
        $this->assertEqual(count($post_tab_datasets), 1);
        $post_tab_dataset = $post_tab_datasets[0];
        $this->assertEqual($post_tab_dataset->name, "most_replied_to_posts");
        $this->assertEqual($post_tab_dataset->dao_name, 'PostDAO');
        $this->assertEqual($post_tab_dataset->dao_method_name, "getMostRepliedToPosts");

        $post_tab = $menus['posts-mostlikes'];
        $this->assertEqual($post_tab->name, "Most liked");
        $this->assertEqual($post_tab->description, "Posts with most likes");

        $post_tab_datasets = $post_tab->getDatasets();
        $this->assertEqual(count($post_tab_datasets), 1);
        $post_tab_dataset = $post_tab_datasets[0];
        $this->assertEqual($post_tab_dataset->name, "most_replied_to_posts");
        $this->assertEqual($post_tab_dataset->dao_name, 'PostDAO');
        $this->assertEqual($post_tab_dataset->dao_method_name, "getMostFavedPosts");

        $post_tab = $menus['posts-questions'];
        $this->assertEqual($post_tab->name, "Inquiries");
        $this->assertEqual($post_tab->description, "Inquiries, or posts with a question mark in them");

        $post_tab_datasets = $post_tab->getDatasets();
        $this->assertEqual(count($post_tab_datasets), 1);
        $post_tab_dataset = $post_tab_datasets[0];
        $this->assertEqual($post_tab_dataset->name, "all_facebook_posts");
        $this->assertEqual($post_tab_dataset->dao_name, 'PostDAO');
        $this->assertEqual($post_tab_dataset->dao_method_name, "getAllQuestionPosts");

        $post_tab_datasets = $post_tab->getDatasets();
        $this->assertEqual(count($post_tab_datasets), 1);

        $logger->close();
    }

    public function testDefaultDashboardModules() {
        $instance_builder_1 = FixtureBuilder::build('instances', array('network_username'=>'www.example.com',
        'network_user_id' => '10150093001972608', 'network'=>'facebook domain', 'is_activated'=>'1',
        'is_public'=>'1'));

        $webapp = Webapp::getInstance();
        $plugin_class_name = $webapp->getPluginObject($webapp->getActivePlugin());
        $plugin = new $plugin_class_name;

        $instance = new Instance();
        $instance->network = 'facebook';

        $modules = $plugin->customDefaultDashboardModules($instance);
        $this->assertTrue(is_array($modules));
        $this->assertEqual(sizeof($modules), 0);

        $instance->network = 'facebook domain';
        $modules = $plugin->customDefaultDashboardModules($instance);

        $this->assertTrue(is_array($modules));
        $this->assertEqual(sizeof($modules), 1);


        $datasets = $modules[0]->getDatasets();
        $this->assertTrue(is_array($datasets));
        $this->assertEqual(sizeof($datasets), 3);

        $this->assertEqual($modules[0]->view_template,
        Utils::getPluginViewDirectory('facebook').'facebook.domain.likes.tpl');

        $this->assertEqual($datasets[0]->name, 'domain_widget_likes_by_day');
        $this->assertEqual($datasets[1]->name, 'domain_widget_likes_by_week');
        $this->assertEqual($datasets[2]->name, 'domain_widget_likes_by_month');

        $this->assertEqual($datasets[0]->dao_name, 'DomainMetricsDAO');
        $this->assertEqual($datasets[0]->dao_method_name, 'getWidgetHistory');
    }

    public function testDeactivate() {
        //all facebook and facebook page accounts should be set to inactive on plugin deactivation
        $webapp = Webapp::getInstance();
        $logger = Logger::getInstance();
        $pd = DAOFactory::getDAO('PostDAO');
        $instance = new Instance();
        $instance->network_user_id = 1;

        $instance_builder_1 = FixtureBuilder::build('instances', array('network_username'=>'julie',
        'network'=>'facebook', 'crawler_last_run'=>'-1d', 'is_activated'=>'1', 'is_public'=>'1'));

        $instance_builder_2 = FixtureBuilder::build('instances', array('network_username'=>'julie',
        'network'=>'facebook page', 'crawler_last_run'=>'-1d', 'is_activated'=>'1', 'is_public'=>'1'));

        $instance_dao = DAOFactory::getDAO('InstanceDAO');
        $fb_active_instances = $instance_dao->getAllInstances("DESC", true, "facebook");
        $this->assertEqual(sizeof($fb_active_instances), 1);
        $fb_active_instances = $instance_dao->getAllInstances("DESC", true, "facebook page");
        $this->assertEqual(sizeof($fb_active_instances), 1);

        $fb_plugin = new FacebookPlugin();
        $fb_plugin->deactivate();

        $fb_active_instances = $instance_dao->getAllInstances("DESC", true, "facebook");
        $this->assertEqual(sizeof($fb_active_instances), 0);
        $fb_active_instances = $instance_dao->getAllInstances("DESC", true, "facebook page");
        $this->assertEqual(sizeof($fb_active_instances), 0);

        $logger->close();
    }

}
