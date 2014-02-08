<?php
/**
 *
 * ThinkUp/webapp/plugins/facebook/tests/TestOfFacebookPlugin.php
 *
 * Copyright (c) 2009-2013 Gina Trapani
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
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
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
        $webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
        $webapp_plugin_registrar->registerPlugin('facebook', 'FacebookPlugin');
        $webapp_plugin_registrar->setActivePlugin('facebook');
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


    public function testDeactivate() {
        //all facebook and facebook page accounts should be set to inactive on plugin deactivation
        $webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
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

    public function testCrawlWithSessionExpiredAuthError() {
        //build active instance owned by a owner
        $instance_with_autherror = array('id'=>5, 'network_username'=>'Liz Lemon',
        'network_user_id'=>'123456-session-expired', 'network_viewer_id'=>'123456-session-expired', 'last_post_id'=>'0',
        'total_posts_in_system'=>'0', 'total_replies_in_system'=>'0',
        'total_follows_in_system'=>'0', 'is_archive_loaded_replies'=>'0',
        'is_archive_loaded_follows'=>'0', 'crawler_last_run'=>'', 'earliest_reply_in_system'=>'',
        'avg_replies_per_day'=>'2', 'is_public'=>'0', 'is_active'=>'1', 'network'=>'facebook',
        'last_favorite_id' => '0', 'owner_favs_in_system' => '0', 'total_posts_by_owner'=>0,
        'posts_per_day'=>1, 'posts_per_week'=>1, 'percentage_replies'=>50, 'percentage_links'=>50,
        'earliest_post_in_system'=>'2009-01-01 13:48:05', 'favorites_profile' => '0'
        );

        $instance_builder_1 = FixtureBuilder::build('instances', $instance_with_autherror);

        $builders = array();
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User',
        'email'=>'admin@example.com', 'is_activated'=>1, 'is_admin'=>1) );
        $builders[] = FixtureBuilder::build('owners', array('id'=>2, 'full_name'=>'ThinkUp K. User',
        'email'=>'notadmin@example.com', 'is_activated'=>1) );
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>2, 'instance_id'=>5,
        'auth_error'=>'', 'oauth_access_token'=>'zL11BPY2fZPPyYY', 'oauth_access_token_secret'=>''));

        //assert invalid_oauth_email_sent_timestamp option is not set
        $option_dao = DAOFactory::getDAO('OptionDAO');
        $plugin_dao = DAOFactory::getDAO('PluginDAO');
        $plugin_id = $plugin_dao->getPluginId('facebook');
        $last_email_timestamp = $option_dao->getOptionByName(OptionDAO::PLUGIN_OPTIONS.'-'.$plugin_id,
        'invalid_oauth_email_sent_timestamp');
        $this->assertNull($last_email_timestamp);

        //log in as that owner
        $this->simulateLogin('admin@example.com');

        $_SERVER['HTTP_HOST'] = "mytestthinkup";

        //run the crawl
        $fb_plugin = new FacebookPlugin();
        $fb_plugin->crawl();

        //assert that APIOAuthException was caught and recorded in owner_instances table
        $owner_instance_dao = new OwnerInstanceMySQLDAO();
        $owner_instance = $owner_instance_dao->get(2, 5);
        $this->debug(Utils::varDumpToString($owner_instance));
        $this->assertEqual($owner_instance->auth_error, 'Error validating access token: Session has expired at unix '.
        'time SOME_TIME. The current unix time is SOME_TIME.');

        //assert that the email notification was sent to the user
        $expected_reg_email_pattern = '/to: notadmin@example.com
subject: Please re-authorize ThinkUp to access Liz Lemon on Facebook
message: Hi! Your ThinkUp installation is no longer connected to the Liz Lemon Facebook account./';

        $actual_reg_email = Mailer::getLastMail();
        $this->debug($actual_reg_email);
        $this->assertPattern($expected_reg_email_pattern, $actual_reg_email);

        //assert invalid_oauth_email_sent_timestamp option has been set
        $last_email_timestamp = $option_dao->getOptionByName(OptionDAO::PLUGIN_OPTIONS.'-'.$plugin_id,
        'invalid_oauth_email_sent_timestamp');
        $this->assertNotNull($last_email_timestamp);

        //Delete last mail file
        $test_email_file = FileDataManager::getDataPath(Mailer::EMAIL);
        unlink($test_email_file);
        $actual_reg_email = Mailer::getLastMail();
        //Assert it's been deleted
        $this->assertEqual($actual_reg_email, '');

        //Crawl again
        $fb_plugin->crawl();

        //Assert email has not been resent
        $actual_reg_email = Mailer::getLastMail();
        $this->debug($actual_reg_email);
        $this->debug($actual_reg_email);
        $this->assertEqual($actual_reg_email, '');
    }

    public function testCrawlWithApplicationRequestLimitReacheddAuthError() {
        //build active instance owned by a owner
        $instance_with_autherror = array('id'=>5, 'network_username'=>'Liz Lemon',
        'network_user_id'=>'123456-app-throttled', 'network_viewer_id'=>'123456-app-throttled', 'last_post_id'=>'0',
        'total_posts_in_system'=>'0', 'total_replies_in_system'=>'0',
        'total_follows_in_system'=>'0', 'is_archive_loaded_replies'=>'0',
        'is_archive_loaded_follows'=>'0', 'crawler_last_run'=>'', 'earliest_reply_in_system'=>'',
        'avg_replies_per_day'=>'2', 'is_public'=>'0', 'is_active'=>'1', 'network'=>'facebook',
        'last_favorite_id' => '0', 'owner_favs_in_system' => '0', 'total_posts_by_owner'=>0,
        'posts_per_day'=>1, 'posts_per_week'=>1, 'percentage_replies'=>50, 'percentage_links'=>50,
        'earliest_post_in_system'=>'2009-01-01 13:48:05', 'favorites_profile' => '0'
        );

        $instance_builder_1 = FixtureBuilder::build('instances', $instance_with_autherror);

        $builders = array();
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User',
        'email'=>'admin@example.com', 'is_activated'=>1, 'is_admin'=>1) );
        $builders[] = FixtureBuilder::build('owners', array('id'=>2, 'full_name'=>'ThinkUp K. User',
        'email'=>'notadmin@example.com', 'is_activated'=>1) );
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>2, 'instance_id'=>5,
        'auth_error'=>'', 'oauth_access_token'=>'zL11BPY2fZPPyYY', 'oauth_access_token_secret'=>''));

        //assert invalid_oauth_email_sent_timestamp option is not set
        $option_dao = DAOFactory::getDAO('OptionDAO');
        $plugin_dao = DAOFactory::getDAO('PluginDAO');
        $plugin_id = $plugin_dao->getPluginId('facebook');
        $last_email_timestamp = $option_dao->getOptionByName(OptionDAO::PLUGIN_OPTIONS.'-'.$plugin_id,
        'invalid_oauth_email_sent_timestamp');
        $this->assertNull($last_email_timestamp);

        //log in as that owner
        $this->simulateLogin('admin@example.com');

        $_SERVER['HTTP_HOST'] = "mytestthinkup";

        //run the crawl
        $fb_plugin = new FacebookPlugin();
        $fb_plugin->crawl();

        //assert that APIOAuthException was caught and recorded in owner_instances table
        $owner_instance_dao = new OwnerInstanceMySQLDAO();
        $owner_instance = $owner_instance_dao->get(2, 5);
        $this->debug(Utils::varDumpToString($owner_instance));
        //assert that the application request throttling error was not saved
        $this->assertEqual($owner_instance->auth_error, '');

        //assert that the reauth email notification was not sent to user
        $actual_reg_email = Mailer::getLastMail();
        $this->debug($actual_reg_email);
        $this->assertEqual($actual_reg_email, '');
    }

    public function testCrawlWithUnexpectedErrorPleaseTryAgain() {
        //build active instance owned by a owner
        $instance_with_autherror = array('id'=>5, 'network_username'=>'Liz Lemon',
        'network_user_id'=>'123456-app-throttled', 'network_viewer_id'=>'123456-unexpected-error-try-again',
        'last_post_id'=>'0', 'total_posts_in_system'=>'0', 'total_replies_in_system'=>'0',
        'total_follows_in_system'=>'0', 'is_archive_loaded_replies'=>'0',
        'is_archive_loaded_follows'=>'0', 'crawler_last_run'=>'', 'earliest_reply_in_system'=>'',
        'avg_replies_per_day'=>'2', 'is_public'=>'0', 'is_active'=>'1', 'network'=>'facebook',
        'last_favorite_id' => '0', 'owner_favs_in_system' => '0', 'total_posts_by_owner'=>0,
        'posts_per_day'=>1, 'posts_per_week'=>1, 'percentage_replies'=>50, 'percentage_links'=>50,
        'earliest_post_in_system'=>'2009-01-01 13:48:05', 'favorites_profile' => '0'
        );

        $instance_builder_1 = FixtureBuilder::build('instances', $instance_with_autherror);

        $builders = array();
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User',
        'email'=>'admin@example.com', 'is_activated'=>1, 'is_admin'=>1) );
        $builders[] = FixtureBuilder::build('owners', array('id'=>2, 'full_name'=>'ThinkUp K. User',
        'email'=>'notadmin@example.com', 'is_activated'=>1) );
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>2, 'instance_id'=>5,
        'auth_error'=>'', 'oauth_access_token'=>'zL11BPY2fZPPyYY', 'oauth_access_token_secret'=>''));

        //assert invalid_oauth_email_sent_timestamp option is not set
        $option_dao = DAOFactory::getDAO('OptionDAO');
        $plugin_dao = DAOFactory::getDAO('PluginDAO');
        $plugin_id = $plugin_dao->getPluginId('facebook');
        $last_email_timestamp = $option_dao->getOptionByName(OptionDAO::PLUGIN_OPTIONS.'-'.$plugin_id,
        'invalid_oauth_email_sent_timestamp');
        $this->assertNull($last_email_timestamp);

        //log in as that owner
        $this->simulateLogin('admin@example.com');

        $_SERVER['HTTP_HOST'] = "mytestthinkup";

        //run the crawl
        $fb_plugin = new FacebookPlugin();
        $fb_plugin->crawl();

        //assert that APIOAuthException was caught and recorded in owner_instances table
        $owner_instance_dao = new OwnerInstanceMySQLDAO();
        $owner_instance = $owner_instance_dao->get(2, 5);
        $this->debug(Utils::varDumpToString($owner_instance));
        //assert that the application request throttling error was not saved
        $this->assertEqual($owner_instance->auth_error, '');

        //assert that the reauth email notification was not sent to user
        $actual_reg_email = Mailer::getLastMail();
        $this->debug($actual_reg_email);
        $this->assertEqual($actual_reg_email, '');
    }
}
