<?php
/**
 * ThinkUp/webapp/plugins/twitterrealtime/tests/TestOfStreamMessageQueueMySQL.php
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
 * Test of StreamMessageQueueMySQL
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
require_once THINKUP_WEBAPP_PATH.'plugins/twitterrealtime/model/class.StreamMessageQueue.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitterrealtime/model/class.StreamMessageQueueMySQL.php';

class TestOfStreamMessageQueueMySQL extends ThinkUpUnitTestCase {

    const TEST_TABLE = 'stream_data';

    public function setUp() {
        parent::setUp();
        $this->logger = Logger::getInstance();
        $this->config = Config::getInstance();
    }

    public function tearDown() {
        // truncate data....
        StreamDataMySQLDAO::$PDO->query("truncate table " . $this->table_prefix . 'stream_data');
        parent::tearDown();
    }

    public function testGetEnqueuedStatus() {
        // queue data set
        $queue = new StreamMessageQueueMySQL();
        $queue->enqueueStatus("this is a status");
        $stmt = StreamDataMySQLDAO::$PDO->query("select * from " . $this->table_prefix . 'stream_data');
        $data = $stmt->fetchAll();
        $this->assertEqual(count($data), 1);
        $this->assertEqual($data[0]['data'], "this is a status");
        $this->assertEqual($data[0]['network'], "twitter");

        $queue->enqueueStatus("this is a status too");
        $stmt = StreamDataMySQLDAO::$PDO->query("select * from " . $this->table_prefix . 'stream_data');
        $data = $stmt->fetchAll();
        $this->assertEqual(count($data), 2);
        $this->assertEqual($data[1]['data'], "this is a status too");
        $this->assertEqual($data[1]['network'], "twitter");
    }

    public function testEnqueueStatus() {
        // queue data set
        $queue = new StreamMessageQueueMySQL();
        $queue->enqueueStatus("this is a status");
        $stmt = StreamDataMySQLDAO::$PDO->query("select * from " . $this->table_prefix . 'stream_data');
        $data = $stmt->fetchAll();
        $this->assertEqual(count($data), 1);
        $this->assertEqual($data[0]['data'], "this is a status");
        $this->assertEqual($data[0]['network'], "twitter");

        $queue->enqueueStatus("this is a status too");
        $stmt = StreamDataMySQLDAO::$PDO->query("select * from " . $this->table_prefix . 'stream_data');
        $data = $stmt->fetchAll();
        $this->assertEqual(count($data), 2);
        $this->assertEqual($data[1]['data'], "this is a status too");
        $this->assertEqual($data[1]['network'], "twitter");
    }

    public function testProcessStatus() {
        // get data, empty
        $queue = new StreamMessageQueueMySQL();
        $this->assertNull($queue->processStreamData());
        $this->assertEqual($queue->last_id, 0);

        // get data, two items, should reset last id as well after fetching both
        $builder1 = FixtureBuilder::build(self::TEST_TABLE, array('data' => '{json:1}', 'network' => 'twitter'));
        $builder2 = FixtureBuilder::build(self::TEST_TABLE, array('data' => '{json:2}', 'network' => 'twitter'));
        $queue = new StreamMessageQueueMySQL();
        $queue->IDMAX = 1;

        $data = $queue->processStreamData();
        $this->assertNotNull($data);
        $this->assertEqual($queue->last_id, 1);
        $this->assertEqual($data, '{json:1}');

        $data = $queue->processStreamData();
        $this->assertNotNull($data);
        $this->assertEqual($queue->last_id, 2);
        $this->assertEqual($data, '{json:2}');

        $this->assertNull($queue->processStreamData());
        $this->assertEqual($queue->last_id, 0);
    }
}
