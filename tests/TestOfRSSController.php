<?php
/**
 *
 * ThinkUp/tests/TestOfRSSController.php
 *
 * Copyright (c) 2009-2012 Guillaume Boudreau
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
 * Test of RSSController
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2012 Guillaume Boudreau
 * @author Guillaume Boudreau <gboudreau@pommepause.com>
 *
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

class TestOfRSSController extends ThinkUpUnitTestCase {

    public function setUp() {
        parent::setUp();
        $_SERVER['HTTP_HOST'] = 'http://localhost';
    }

    public function testConstructor() {
        $controller = new RSSController(true);
        $this->assertTrue(isset($controller));
    }

    public function testGoNoLoggerLogSet() {
        $builders = $this->buildData();
        $controller = new RSSController(true);
        $_GET['un'] = 'me@example.com';
        $_GET['as'] = 'c9089f3c9adaf0186f6ffb1ee8d6501c';
        $logger = Logger::getInstance();
        $logger->close();
        $config = Config::getInstance();
        $config->setValue('log_location', false);
        $results = $controller->go();
        $this->assertPattern("/ThinkUp crawl started/", $results);
        $this->assertPattern("/<rss version=\"2.0\"/", $results);
    }

    public function testGetAdditionalItems() {
        $builders = $this->buildData();
        // Test that an item is added in the RSS feed when the crawler log is not writable
        $controller = new RSSController(true);
        $config = Config::getInstance();
        $config->setValue('log_location', '/something/that/doesnt/exits');
        $_GET['un'] = 'me@example.com';
        $_GET['as'] = 'c9089f3c9adaf0186f6ffb1ee8d6501c';
        $_SERVER['HTTP_HOST'] = 'http://localhost';
        $results = $controller->go();
        $this->assertPattern("/Error: crawler log is not writable/", $results);
    }

    private function buildData() {
        $owner_builder = FixtureBuilder::build('owners', array(
            'id' => 1, 
            'email' => 'me@example.com', 
            'pwd' => 'XXX', 
            'is_activated' => 1,
            'api_key' => 'c9089f3c9adaf0186f6ffb1ee8d6501c'
        ));

        $instance_builder = FixtureBuilder::build('instances', array(
            'id' => 1,
            'network_username' => 'jack',
            'network' => 'twitter'
            ));

            $owner_instance_builder = FixtureBuilder::build('owner_instances', array(
            'owner_id' => 1, 
            'instance_id' => 1
            ));

            return array($owner_builder, $instance_builder, $owner_instance_builder);
    }
}
