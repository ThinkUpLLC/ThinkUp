<?php
/**
 * ThinkUp/webapp/plugins/twitterrealtime/tests/TestOfStreamMessageQueueRedis.php
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
 *
 */
require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';

require_once THINKUP_ROOT_PATH.'tests/classes/class.ThinkUpUnitTestCase.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitterrealtime/model/class.ConsumerUserStream.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitterrealtime/tests/TestOfConsumerUserStream.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitterrealtime/model/class.StreamMessageQueue.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitterrealtime/model/class.StreamMessageQueueRedis.php';

class TestOfStreamMessageQueueRedis extends ThinkUpUnitTestCase {

    public function setUp() {
        parent::setUp();
        $this->logger = Logger::getInstance();
        $this->config = Config::getInstance();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testEnqueueStatus() {
        //dont run redis test for php less than 5.3
        $version = explode('.', PHP_VERSION);
        if (!($version[0] >= 5 && $version[1] >= 3)) {
            //error_log("PHP version less than 5.3, Skipping Redis Tests...");
            return;
        } else {
            require_once THINKUP_WEBAPP_PATH.'plugins/twitterrealtime/model/class.StreamMessageQueueRedis.php';
        }
        // queue data set
        MockRedis::$queue = null;
        $queue = new StreamMessageQueueRedis();
        $queue->redis = new MockRedis();
        $queue->enqueueStatus("this is a mock status");
        $this->assertEqual(count(MockRedis::$queue), 1);
        $this->assertEqual(MockRedis::$queue[0], "this is a mock status");
        $queue->enqueueStatus("this is a mock status too");
        $this->assertEqual(count(MockRedis::$queue), 2);
        $this->assertEqual(MockRedis::$queue[1], "this is a mock status too");

    }

    public function testProcessStatus() {
        //dont run redis test for php less than 5.3
        $version = explode('.', PHP_VERSION);
        if (!($version[0] >= 5 && $version[1] >= 3)) {
            //error_log("PHP version less than 5.3, Skipping Redis Tests...");
            return;
        }

        MockRedis::$queue = null;
        $queue = new StreamMessageQueueRedis();
        $queue->redis = new MockRedis();

        // no data in the queue
        $this->assertNull($queue->processStreamData());

        // two items on the queue
        MockRedis::$queue = array('{json:1}', '{json:2}');
        $data = $queue->processStreamData();
        $this->assertNotNull($data);
        $this->assertEqual(count(MockRedis::$queue), 1, 'should be one item in the queue');
        $this->assertEqual($data, '{json:1}');

        $data = $queue->processStreamData();
        $this->assertNotNull($data);
        $this->assertEqual(count(MockRedis::$queue), 0, 'should be no items in the queue');
        $this->assertEqual($data, '{json:2}');

    }

    /**
     * NOTE: to run these tests, use the ENV/CLI flag WITH_REDIS=1, ie:
     *
     *     WITH_REDIS=1 php tests/this_test.php
     */
    public function testWithRedis() {
        if ((getenv('WITH_REDIS')!==false)) {
            if ($this->DEBUG) { print "NOTE: Running redis test againt a local redis server\n"; }
            $queue = new StreamMessageQueueRedis();
            $queue->enqueueStatus("this is a mock status 1");
            $queue->enqueueStatus("this is a mock status 2");
            $data1 = $queue->processStreamData();
            $this->assertEqual($data1, "this is a mock status 1");
            $data2 = $queue->processStreamData();
            $this->assertEqual($data2, "this is a mock status 2");
            $this->assertNull($queue->processStreamData());
        } else {
            if ($this->DEBUG) { print "NOTE: Skipping local redis server tests...\n"; }
        }
    }
}
