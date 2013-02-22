<?php
/**
 * ThinkUp/webapp/plugins/twitterrealtime/tests/TestStreamMessageQueueFactory.php
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
require_once THINKUP_WEBAPP_PATH.'plugins/twitterrealtime/model/class.StreamMessageQueue.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitterrealtime/model/class.StreamMessageQueueFactory.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitterrealtime/model/class.StreamMessageQueueMySQL.php';

class TestOfStreamMessageQueueFactory extends ThinkUpUnitTestCase {

    public function setUp() {
        parent::setUp();
        $this->logger = Logger::getInstance();
        $this->config = Config::getInstance();
        $this->table_prefix = $this->config->getValue('table_prefix');
    }

    public function tearDown() {
        parent::tearDown();
        StreamMessageQueueFactory::$queue = null;
    }

    public function testGetMySQLQueue() {
        $queue = StreamMessageQueueFactory::getQueue();
        $this->assertIsA($queue, 'StreamMessageQueueMySQL');
    }

    public function testGetRedisQueue() {

        // if we are php 5.3 or greater run test, else skip...
        $version = explode('.', PHP_VERSION);
        if (!($version[0] >= 5 && $version[1] >= 3)) {
            //error_log("PHP version less than 5.3, Skipping Redis Tests...");
            return;
        } else {
            require_once THINKUP_WEBAPP_PATH.'plugins/twitterrealtime/model/class.StreamMessageQueueRedis.php';
        }
        $builder_plugin = FixtureBuilder::build('plugins', array('folder_name' => 'twitterrealtime', 'is_active' => 1) );
        $plugin_id = $builder_plugin->columns['last_insert_id'];
        $namespace = OptionDAO::PLUGIN_OPTIONS . '-' .$plugin_id;
        $plgin_data = FixtureBuilder::build('options',
        array('namespace' => $namespace, 'option_name' => 'use_redis', 'option_value' => 'true') );
        $queue = StreamMessageQueueFactory::getQueue();
        $this->assertIsA($queue, 'StreamMessageQueueRedis');
    }
}