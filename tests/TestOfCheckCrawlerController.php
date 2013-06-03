<?php
/**
 *
 * ThinkUp/tests/TestOfCheckCrawlerController.php
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
 * Test of CheckCrawlerController
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfCheckCrawlerController extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function tearDown(){
        parent::tearDown();
    }

    public function testConstructor() {
        $controller = new CheckCrawlerController(true);
        $this->assertTrue(isset($controller));
    }

    public function testNoInstances() {
        $controller = new CheckCrawlerController(true);
        $results = $controller->go();
        $this->assertEqual('', $results);
    }

    public function testInstanceLessThan3Hours() {
        $instance_builder = FixtureBuilder::build('instances', array('crawler_last_run'=>'-1h', 'is_active'=>1));
        $controller = new CheckCrawlerController(true);
        $results = $controller->go();
        $this->assertEqual('', $results);
    }

    public function testActiveInstancesMoreThan3Hours() {
        $cfg = Config::getInstance();
        $cfg->setValue('site_root_path', '/my/path/to/thinkup/');
        $_SERVER['SERVER_NAME'] = 'mytestservername';

        $instance_builders[] = FixtureBuilder::build('instances', array('crawler_last_run'=>'-4h', 'is_active'=>1));
        $instance_builders[] = FixtureBuilder::build('instances', array('crawler_last_run'=>'-3h', 'is_active'=>1));
        $instance_builders[] = FixtureBuilder::build('instances', array('crawler_last_run'=>'-2h', 'is_active'=>1));
        $controller = new CheckCrawlerController(true);
        $results = $controller->go();
        $this->assertEqual("http://mytestservername/my/path/to/thinkup/: Crawler hasn't run in 4 hours", $results);
    }

    public function testInactiveInstancesMoreThan3Hours() {
        $instance_builders[] = FixtureBuilder::build('instances', array('crawler_last_run'=>'-4h', 'is_active'=>0));
        $instance_builders[] = FixtureBuilder::build('instances', array('crawler_last_run'=>'-3h', 'is_active'=>0));
        $instance_builders[] = FixtureBuilder::build('instances', array('crawler_last_run'=>'-2h', 'is_active'=>0));
        $controller = new CheckCrawlerController(true);
        $results = $controller->go();
        $this->assertEqual('', $results);
    }

    public function testInstanceDifferentThreshold() {
        $cfg = Config::getInstance();
        $cfg->setValue('site_root_path', '/my/path/to/thinkup/');
        $_SERVER['SERVER_NAME'] = 'mytestservername';

        $instance_builders[] = FixtureBuilder::build('instances', array('crawler_last_run'=>'-2h', 'is_active'=>1));

        // 2nd argument is $argc, third argument is $argv
        $controller = new CheckCrawlerController(true, 1, array('scriptname', 1.0));

        $results = $controller->go();
        $this->assertEqual("http://mytestservername/my/path/to/thinkup/: Crawler hasn't run in 2 hours", $results);

        $instance_builders[] = FixtureBuilder::build('instances', array('crawler_last_run'=>'-3h', 'is_active'=>1));
        $instance_builders[] = FixtureBuilder::build('instances', array('crawler_last_run'=>'-4h', 'is_active'=>1));

        // 2nd argument is $argc, third argument is $argv
        $controller = new CheckCrawlerController(true, 1, array(1.0));

        $results = $controller->go();
        $this->assertEqual("http://mytestservername/my/path/to/thinkup/: Crawler hasn't run in 4 hours", $results);
    }
}
