<?php
/**
 *
 * ThinkUp/webapp/plugins/facebook/tests/TestOfFacebookPlugin.php
 *
 * Copyright (c) 2009-2012 Gina Trapani
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
 * @copyright 2009-2012 Gina Trapani
 */
require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';

require_once THINKUP_WEBAPP_PATH.'plugins/facebook/model/class.FacebookPlugin.php';
require_once THINKUP_WEBAPP_PATH.'plugins/facebook/model/class.FacebookCrawler.php';
require_once THINKUP_WEBAPP_PATH.'plugins/facebook/tests/classes/mock.FacebookGraphAPIAccessor.php';
require_once THINKUP_WEBAPP_PATH.'plugins/facebook/tests/classes/mock.facebook.php';

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

        $this->assertEqual(sizeof($menus), 8);
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

    public function testCrawlWithAuthError() {
        //build active instance owned by a owner
        $instance_with_autherror = array('id'=>5, 'network_username'=>'Liz Lemon', 'network_user_id'=>'123456',
        'network_viewer_id'=>'123456', 'last_post_id'=>'0', 'last_page_fetched_replies'=>0,
        'last_page_fetched_tweets'=>'0', 'total_posts_in_system'=>'0', 'total_replies_in_system'=>'0',
        'total_follows_in_system'=>'0', 'is_archive_loaded_replies'=>'0',
        'is_archive_loaded_follows'=>'0', 'crawler_last_run'=>'', 'earliest_reply_in_system'=>'',
        'avg_replies_per_day'=>'2', 'is_public'=>'0', 'is_active'=>'1', 'network'=>'facebook',
        'last_favorite_id' => '0', 'last_unfav_page_checked' => '0', 'last_page_fetched_favorites' => '0',
        'owner_favs_in_system' => '0', 'total_posts_by_owner'=>0,
        'posts_per_day'=>1, 'posts_per_week'=>1, 'percentage_replies'=>50, 'percentage_links'=>50,
        'earliest_post_in_system'=>'01-01-2009', 'favorites_profile' => '0'
        );

        $instance_builder_1 = FixtureBuilder::build('instances', $instance_with_autherror);

        $builders = array();
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User',
        'email'=>'me@example.com', 'is_activated'=>1) );
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>5,
        'auth_error'=>''));

        //log in as that owner
        $this->simulateLogin('me@example.com');

        $_SERVER['HTTP_HOST'] = "mytestthinkup";

        //run the crawl
        $fb_plugin = new FacebookPlugin();
        $fb_plugin->crawl();

        //assert that APIOAuthException was caught and recorded in owner_instances table
        $owner_instance_dao = new OwnerInstanceMySQLDAO();
        $owner_instance = $owner_instance_dao->get(1, 5);
        $this->assertEqual($owner_instance->auth_error, 'Error validating access token: Session has expired at unix '.
        'time SOME_TIME. The current unix time is SOME_TIME.');

        //assert that the email notification was sent to the user
        $expected_reg_email_pattern = '/to: me@example.com
subject: Please re-authorize ThinkUp to access Liz Lemon on Facebook
message: Hi! Your ThinkUp installation is no longer connected to the Liz Lemon Facebook account./';

        $actual_reg_email = Mailer::getLastMail();
        $this->debug($actual_reg_email);
        $this->assertPattern($expected_reg_email_pattern, $actual_reg_email);
    }
}
