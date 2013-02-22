<?php
/**
 * ThinkUp/webapp/plugins/twitterrealtime/tests/TestOfConsumerUserStream.php
 *
 * Copyright (c) 2011-2013 Amy Unruh, Mark Wilkie
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
 * Test of ConsumerUserStream
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 Amy Unruh, Mark Wilkie
 * @author Amy Unruh
 *
 */
require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';

require_once THINKUP_ROOT_PATH.'tests/classes/class.ThinkUpUnitTestCase.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitterrealtime/tests/classes/mock.Redis.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitterrealtime/model/class.ConsumerUserStream.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitterrealtime/model/class.StreamMessageQueue.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitterrealtime/model/class.StreamMessageQueueMySQL.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitterrealtime/model/class.StreamMessageQueueFactory.php';

class TestOfConsumerUserStream extends ThinkUpUnitTestCase {

    public function setUp() {
        parent::setUp();
        StreamMessageQueueFactory::$queue = null;
        $this->logger = Logger::getInstance();
        $this->config = Config::getInstance();
    }

    public function tearDown() {
        // $this->builders = null;
        parent::tearDown();
        StreamMessageQueueFactory::$queue = null;
    }

    public function testGetInstance() {
        $stream_data = $this->setUpData(true);
        $twitter_data = $this->setUpTwitterData();
        $consumer_user_stream = ConsumerUserStream::getInstance('token', 'secret');
        $this->assertNotNull($consumer_user_stream);
    }

    public function testEnqueueStatusMockRedis() {
        //dont run redis test for php less than 5.3
        $version = explode('.', PHP_VERSION);
        if (!($version[0] >= 5 && $version[1] >= 3)) {
            //error_log("PHP version less than 5.3, Skipping Redis Tests...");
            return;
        } else {
            require_once THINKUP_WEBAPP_PATH.'plugins/twitterrealtime/model/class.StreamMessageQueueRedis.php';
        }
        $stream_data = $this->setUpData(true);
        $twitter_data = $this->setUpTwitterData();
        $queue = new StreamMessageQueueRedis();
        $queue->redis = new MockRedis();
        StreamMessageQueueFactory::$queue = $queue;

        $consumer_user_stream = new ConsumerUserStream('username', 'password');

        $consumer_user_stream->setKey('mark@example.com', 1);
        $procs_data = FixtureBuilder::build('stream_procs', array('process_id'=>getmypid(),
        'email'=>'mark@example.com', 'instance_id' => 1));

        $consumer_user_stream->enqueueStatus("string1");
        $consumer_user_stream->enqueueStatus("string2");
        $this->assertIdentical(array('string1', 'string2'), MockRedis::$queue);

        StreamMessageQueueFactory::$queue = null;
        MockRedis::$queue = null;

        // stream proc data set
        $sql = "select process_id, email, instance_id, unix_timestamp(last_report) as last_report from " .
        $this->table_prefix . "stream_procs";
        $stmt = PluginOptionMySQLDAO::$PDO->query($sql);
        $data = $stmt->fetchAll();
        $process_id = getmypid();
        $this->assertIdentical($data[0]['process_id'], $process_id . '');
        $recent_time = time() - 50;
        $this->assertTrue($data[0]['last_report'] > $recent_time);
    }

    public function testEnqueueStatusRedis() {
        //dont run redis test for php less than 5.3
        $version = explode('.', PHP_VERSION);
        if (!($version[0] >= 5 && $version[1] >= 3)) {
            //error_log("PHP version less than 5.3, Skipping Redis Tests...");
            return;
        }
        if ((getenv('WITH_REDIS')!==false)) {
            if ($this->DEBUG) { print "NOTE: Running redis test againt a local redis server\n"; }
            $stream_data = $this->setUpData('true');
            $twitter_data = $this->setUpTwitterData();

            $consumer_user_stream = new ConsumerUserStream('username', 'password');
            $consumer_user_stream->setKey('mark@example.com', 1);
            $procs_data = FixtureBuilder::build('stream_procs', array('process_id' => getmypid(),
            'email' => 'mark@example.com', 'instance_id' => 1));

            $consumer_user_stream->enqueueStatus("string1");
            $consumer_user_stream->enqueueStatus("string2");

            $queue = new StreamMessageQueueRedis();

            $this->assertEqual($queue->processStreamData(), 'string1');
            $this->assertEqual($queue->processStreamData(), 'string2');
            $this->assertNull($queue->processStreamData());

            // stream proc data set
            $sql = "select process_id, email, instance_id, unix_timestamp(last_report) as last_report from " .
            $this->table_prefix . "stream_procs";
            $stmt = PluginOptionMySQLDAO::$PDO->query($sql);
            $data = $stmt->fetchAll();
            $process_id = getmypid();
            $this->assertIdentical($data[0]['process_id'], $process_id . '');
            $recent_time = time() - 50;
            $this->assertTrue($data[0]['last_report'] > $recent_time);
        }
    }

    public function testEnqueueStatusMySQL() {
        $stream_data = $this->setUpData();
        $twitter_data = $this->setUpTwitterData();
        $consumer_user_stream = new ConsumerUserStream('username', 'password');
        $consumer_user_stream->setKey('mark@example.com', 1);
        $procs_data = FixtureBuilder::build('stream_procs', array('process_id' => getmypid(),
        'email' => 'mark@example.com', 'instance_id' => 1));
        $consumer_user_stream->enqueueStatus("string1");
        $consumer_user_stream->enqueueStatus("string2");
        $sql = "select * from " . $this->table_prefix . "stream_data";
        $stmt = PluginOptionMySQLDAO::$PDO->query($sql);
        $data = $stmt->fetchAll();
        $this->assertIdentical($data[0][0],'1');
        $this->assertIdentical($data[0][1],'string1');
        $this->assertIdentical($data[0][2],'twitter');
        $this->assertIdentical($data[1][0],'2');
        $this->assertIdentical($data[1][1],'string2');
        $this->assertIdentical($data[1][2],'twitter');

        $sql = "select process_id, email, instance_id, unix_timestamp(last_report) as last_report from " .
        $this->table_prefix . "stream_procs";
        $stmt = PluginOptionMySQLDAO::$PDO->query($sql);
        $data = $stmt->fetchAll();
        $process_id = getmypid();
        $this->assertIdentical($data[0]['process_id'], $process_id . '');
        $recent_time = time() - 50;
        $this->assertTrue($data[0]['last_report'] > $recent_time);
    }

    /**
     * set up stream data
     */
    private function setUpData($use_redis = 0) {
        $builder_owner = FixtureBuilder::build('owners', array('email' => 'me@example.com', 'user_activated' => 1) );
        $builder_plugin = FixtureBuilder::build('plugins', array('folder_name' => 'twitterrealtime',
        'is_active' => 1) );
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
