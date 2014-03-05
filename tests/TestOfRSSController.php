<?php
/**
 *
 * ThinkUp/tests/TestOfRSSController.php
 *
 * Copyright (c) 2009-2013 Guillaume Boudreau
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
 * Test of RSSController
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Guillaume Boudreau
 * @author Guillaume Boudreau <gboudreau@pommepause.com>
 *
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

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
        $this->debug($results);
        $this->assertPattern("/ThinkUp crawl started/", $results);
        $this->assertPattern("/No insights exist/", $results);
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
        $this->assertPattern("/No insights exist/", $results);
    }

    public function testPerOwnerRefreshRate() {
        // $THINKUP_CFG['rss_crawler_refresh_rate'] should apply per owner
        $builders = $this->buildData();
        $controller = new RSSController(true);
        $_GET['un'] = 'me@example.com';
        $_GET['as'] = 'c9089f3c9adaf0186f6ffb1ee8d6501c';
        $results = $controller->go();
        $this->assertPattern("/ThinkUp crawl started/", $results);
        $this->assertPattern("/No insights exist/", $results);

        $instanceDAO = new InstanceMySQLDAO();
        $instanceDAO->updateLastRun(1);

        // the crawler should start for the second owner despite a recent last
        // run time for instance 1 owned by owner 1
        $controller = new RSSController(true);
        $_GET['un'] = 'me@example.net';
        $_GET['as'] = 'a34e120dc6807e0dffc0d2b973b9d55b';
        $results = $controller->go();
        $this->assertPattern("/ThinkUp crawl started/", $results);
        $this->assertPattern("/No insights exist/", $results);
    }

    public function testInsightsInFeed() {
        $builders = $this->buildData();
        $builders_insights = $this->buildDataInsights();
        $controller = new RSSController(true);
        $_GET['un'] = 'me@example.com';
        $_GET['as'] = 'c9089f3c9adaf0186f6ffb1ee8d6501c';
        $results = $controller->go();
        $this->debug($results);
        $this->assertNoPattern("/No insights exist/", $results);
        $this->assertPattern("/Hello/", $results);
        $this->assertPattern("/This is a test of a hello world insight/", $results);
    }

    private function buildDataInsights() {
        $builders = array();

        $time_now = date("Y-m-d H:i:s");
        $builders[] = FixtureBuilder::build('insights', array(
            'headline' => 'Hello:',
            'text' => 'This is a test of a hello world insight',
            'instance_id' => 1,
            'time_generated' => $time_now,
            'related_data'=>null
        ));

        return $builders;
    }

    private function buildData() {
        $builders = array();

        $owner1_builder = FixtureBuilder::build('owners', array(
            'id' => 1,
            'email' => 'me@example.com',
            'pwd' => 'XXX',
            'is_activated' => 1,
            'api_key' => 'c9089f3c9adaf0186f6ffb1ee8d6501c' ));
        array_push($builders, $owner1_builder);

        $owner2_builder = FixtureBuilder::build('owners', array(
            'id' => 2,
            'email' => 'me@example.net',
            'pwd' => 'YYY',
            'is_activated' => 1,
            'api_key' => 'a34e120dc6807e0dffc0d2b973b9d55b' ));
        array_push($builders, $owner2_builder);

        $instance1_builder = FixtureBuilder::build('instances', array(
            'id' => 1,
            'network_username' => 'jack',
            'crawler_last_run' => '-2h',
            'network' => 'twitter' ));
        array_push($builders, $instance1_builder);

        $instance2_builder = FixtureBuilder::build('instances', array(
            'id' => 2,
            'network_username' => 'fred',
            'crawler_last_run' => '-2h',
            'network' => 'twitter' ));
        array_push($builders, $instance2_builder);

        $owner_instance1_builder = FixtureBuilder::build('owner_instances', array(
            'owner_id' => 1,
            'instance_id' => 1 ));
        array_push($builders, $owner_instance1_builder);

        $owner_instance2_builder = FixtureBuilder::build('owner_instances', array(
            'owner_id' => 2,
            'instance_id' => 2 ));
        array_push($builders, $owner_instance2_builder);

        return $builders;
    }
}
