<?php
/**
 * ThinkUp/webapp/plugins/twitterrealtime/tests/TestOfConsumerStreamProcess.php
 *
 * Copyright (c) 2009-2013 Gina Trapani, Amy Unruh, Mark Wilkie
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
 * Test of TestOfConsumerUserStream
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, Amy Unruh, Mark Wilkie
 * @author Amy Unruh
 */
require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';

require_once THINKUP_ROOT_PATH.'tests/classes/class.ThinkUpUnitTestCase.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitterrealtime/tests/TestOfConsumerUserStream.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitterrealtime/model/class.ConsumerUserStream.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitterrealtime/model/class.ConsumerStreamProcess.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitterrealtime/model/class.TwitterJSONStreamParser.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitterrealtime/model/class.StreamMessageQueue.php';

class TestOfConsumerStreamProcess extends ThinkUpUnitTestCase {

    const TEST_TABLE = 'stream_data';

    public function setUp() {
        parent::setUp();
        $this->config = Config::getInstance();
        $this->test_dir = THINKUP_WEBAPP_PATH.'plugins/twitterrealtime/tests/testdata/';
        $this->post_dao = DAOFactory::getDAO('PostDAO');
        $this->favs_dao = DAOFactory::getDAO('FavoritePostDAO');
        $this->user_dao = DAOFactory::getDAO('UserDAO');
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testProcessStreamDataMySQL() {
        $this->setUpTwitterData();
        $stream_process = new ConsumerStreamProcess();
        $stream_process->STIME = 0;

        // no data in the queue
        $queue = new StreamMessageQueueMySQL();
        $stream_process->process($queue);

        $retweet_test_data = file_get_contents($this->test_dir . "retweet1.json");
        $builder1 = FixtureBuilder::build(self::TEST_TABLE, array('data' => $retweet_test_data, 'network'=>'twitter'));

        $stream_process->process($queue);

        // now test that both users have been added
        $user = $this->user_dao->getDetails(19202541, 'twitter');
        $this->assertEqual($user->user_id, 19202541);
        $user = $this->user_dao->getDetails(17567533, 'twitter');
        $this->assertEqual($user->user_id, 17567533);
        // check post RT count
        $post = $this->post_dao->getPost('36479682404687872', 'twitter');
        $this->assertEqual($post->retweet_count_cache, 1);
    }

    public function testProcessStreamDataMockRedis() {
        //dont run redis test for php less than 5.3
        $version = explode('.', PHP_VERSION);
        if (!($version[0] >= 5 && $version[1] >= 3)) {
            //error_log("PHP version less than 5.3, Skipping Redis Tests...");
            return;
        } else {
            require_once THINKUP_WEBAPP_PATH.'plugins/twitterrealtime/model/class.StreamMessageQueueRedis.php';
        }
        $this->setUpTwitterData();
        $this->setUpData('true');

        $stream_process = new ConsumerStreamProcess();
        $stream_process->STIME = 0;

        // no data in the queue
        $queue = new StreamMessageQueueRedis();
        $queue->redis = new MockRedis();

        $stream_process->process($queue);

        $retweet_test_data = file_get_contents($this->test_dir . "retweet1.json");
        array_push(MockRedis::$queue, $retweet_test_data);
        $stream_process->process($queue);

        // now test that both users have been added
        $user = $this->user_dao->getDetails('19202541', 'twitter');
        $this->assertEqual($user->user_id, '19202541');
        $user = $this->user_dao->getDetails('17567533', 'twitter');
        $this->assertEqual($user->user_id, '17567533');
        // check post RT count
        $post = $this->post_dao->getPost('36479682404687872', 'twitter');
        $this->assertEqual($post->retweet_count_cache, 1);
    }

    public function testProcessStreamDataRedis() {
        $version = explode('.', PHP_VERSION);
        if (!($version[0] >= 5 && $version[1] >= 3)) {
            //error_log("PHP version less than 5.3, Skipping Redis Tests...");
            return;
        }
        if ((getenv('WITH_REDIS')!==false)) {
            if ($this->DEBUG) { print "NOTE: Running redis test againt a local redis server\n"; }
            $this->setUpTwitterData();
            $this->setUpData('true');

            $stream_process = new ConsumerStreamProcess();
            $stream_process->STIME = 0;

            // no data in the queue
            $queue = new StreamMessageQueueRedis();

            // items in the queue
            $retweet_test_data = file_get_contents($this->test_dir . "retweet1.json");
            $queue->enqueueStatus($retweet_test_data);
            $stream_process->process($queue);

            // now test that both users have been added
            $user = $this->user_dao->getDetails(19202541, 'twitter');
            $this->assertEqual($user->user_id, 19202541);
            $user = $this->user_dao->getDetails(17567533, 'twitter');
            $this->assertEqual($user->user_id, 17567533);
            // check post RT count
            $post = $this->post_dao->getPost(36479682404687872, 'twitter');
            $this->assertEqual($post->retweet_count_cache, 1);
        }
    }

    /**
     * set up stream data
     */
    private function setUpData($use_redis = 0) {
        $builder_owner = FixtureBuilder::build('owners', array('email' => 'me@example.com', 'user_activated' => 1) );
        $builder_plugin = FixtureBuilder::build('plugins', array('folder_name' => 'twitterrealtime', 'is_active' => 1));
        $plugin_id = $builder_plugin->columns['last_insert_id'];
        $namespace = OptionDAO::PLUGIN_OPTIONS . '-' .$plugin_id;
        $builder_plugin_options =
        FixtureBuilder::build('options',
        array('namespace' => $namespace, 'option_name' => 'use_redis', 'option_value' => $use_redis) );
        $this->simulateLogin('me@example.com');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        return array($builder_owner, $builder_plugin, $builder_plugin_options);
    }

    /**
     * set up twitter data
     */
    private function setUpTwitterData() {
        $plugin_id = 1;
        $namespace = OptionDAO::PLUGIN_OPTIONS . '-' .$plugin_id;
        $builder_plugin_option1 =
        FixtureBuilder::build('options',
        array('namespace' => $namespace, 'option_name' => 'oauth_consumer_key', 'option_value' => 'token'));
        $builder_plugin_option2 =
        FixtureBuilder::build('options',
        array('namespace' => $namespace, 'option_name' => 'oauth_consumer_secret', 'option_value' => 'secret'));
        return array($builder_plugin_option1, $builder_plugin_option2);
    }
}