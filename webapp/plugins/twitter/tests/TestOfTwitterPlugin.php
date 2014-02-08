<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/tests/TestOfTwitterPlugin.php
 *
 * Copyright (c) 2009-2013 Gina Trapani, Guillaume Boudreau
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
 * Test of TwitterPlugin class
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, Guillaume Boudreau
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';

require_once THINKUP_ROOT_PATH.'tests/classes/class.ThinkUpUnitTestCase.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.TwitterCrawler.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.TwitterPlugin.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.TwitterInstanceMySQLDAO.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.TwitterInstance.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.TwitterAPIEndpoint.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitterrealtime/model/class.TwitterRealtimePlugin.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.TwitterOAuthThinkUp.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.TwitterAPIAccessorOAuth.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.CrawlerTwitterAPIAccessorOAuth.php';

class TestOfTwitterPlugin extends ThinkUpUnitTestCase {
    var $logger;

    public function setUp() {
        parent::setUp();
        $this->webapp = PluginRegistrarWebapp::getInstance();
        $this->crawler = PluginRegistrarCrawler::getInstance();
        $this->webapp->registerPlugin('twitter', 'TwitterPlugin');
        $this->crawler->registerCrawlerPlugin('TwitterPlugin');
        $this->webapp->setActivePlugin('twitter');
        $this->logger = Logger::getInstance();
    }

    public function tearDown() {
        parent::tearDown();
        $this->logger->close();
    }

    public function testConstructor() {
        $this->debug(__METHOD__);
        $plugin = new TwitterPlugin();
        $this->assertNotNull($plugin);
        $this->assertIsA($plugin, 'TwitterPlugin');
        $this->assertEqual(count($plugin->required_settings), 2);
        $this->assertFalse($plugin->isConfigured());
    }


    public function testRepliesOrdering() {
        $this->debug(__METHOD__);
        $this->assertEqual(TwitterPlugin::repliesOrdering('default'), 'is_reply_by_friend DESC, follower_count DESC');
        $this->assertEqual(TwitterPlugin::repliesOrdering('location'),
        'geo_status, reply_retweet_distance, is_reply_by_friend DESC, follower_count DESC');
        $this->assertEqual(TwitterPlugin::repliesOrdering(''), 'is_reply_by_friend DESC, follower_count DESC');
    }

    public function testDeactivate() {
        $this->debug(__METHOD__);
        //all facebook and facebook page accounts should be set to inactive on plugin deactivation
        $pd = DAOFactory::getDAO('PostDAO');
        $instance = new Instance();
        $instance->network_user_id = 1;

        $instance_builder_1 = FixtureBuilder::build('instances', array('network_username'=>'julie',
        'network'=>'twitter', 'crawler_last_run'=>'-1d', 'is_activated'=>'1', 'is_public'=>'1'));

        $instance_builder_2 = FixtureBuilder::build('instances', array('network_username'=>'john',
        'network'=>'twitter', 'crawler_last_run'=>'-1d', 'is_activated'=>'1', 'is_public'=>'1'));

        $instance_dao = DAOFactory::getDAO('InstanceDAO');
        $active_instances = $instance_dao->getAllInstances("DESC", true, "twitter");
        $this->assertEqual(sizeof($active_instances), 2);

        $twitter_plugin = new TwitterPlugin();
        $twitter_plugin->deactivate();

        $active_instances = $instance_dao->getAllInstances("DESC", true, "twitter");
        $this->assertEqual(sizeof($active_instances), 0);
    }

    public function testCrawlCompletion() {
        $this->debug(__METHOD__);
        $builders = array();

        //Add instances
        $instance_builder_1 = FixtureBuilder::build('instances', array('id'=>1, 'network_username'=>'julie',
        'network'=>'twitter', 'crawler_last_run'=>'-5d', 'is_activated'=>'1', 'is_public'=>'1'));
        $instance_builder_2 = FixtureBuilder::build('instances', array('id'=>2, 'network_username'=>'john',
        'network'=>'twitter', 'crawler_last_run'=>'-5d', 'is_activated'=>'1', 'is_public'=>'1'));
        $builders[] = FixtureBuilder::build('instances_twitter', array('id'=>1));
        $builders[] = FixtureBuilder::build('instances_twitter', array('id'=>2));
        //Add owner
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User',
        'email'=>'me@example.com', 'is_activated'=>1, 'is_admin'=>1));
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>1,
        'auth_error'=>''));
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>2,
        'auth_error'=>''));

        $this->simulateLogin('me@example.com', true, true);

        $test = new TwitterInstanceMySQLDAO();
        $twitter_plugin = new TwitterPlugin();
        $twitter_plugin->crawl();

        $instance_dao = new InstanceMySQLDAO();
        $updated_instance = $instance_dao->get(1);
        $this->debug(Utils::varDumpToString($updated_instance));
        // crawler_last_run should have been updated
        $this->assertNotEqual($instance_builder_1->columns['crawler_last_run'],$updated_instance->crawler_last_run );
    }
}
