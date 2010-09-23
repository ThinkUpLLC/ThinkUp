<?php
/**
 *
 * ThinkUp/tests/TestOfCheckCrawlerController.php
 *
 * Copyright (c) 2009-2010 Gina Trapani
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
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

/**
 * Test of CheckCrawlerController
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfCheckCrawlerController extends ThinkUpUnitTestCase {

    public function __construct() {
        $this->UnitTestCase('CheckCrawlerController class test');
    }

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
        $instance_builder = FixtureBuilder::build('instances', array('crawler_last_run'=>'-1h'));
        $controller = new CheckCrawlerController(true);
        $results = $controller->go();
        $this->assertEqual('', $results);
    }

    public function testInstanceMoreThan3Hours() {
        $instance_builder = FixtureBuilder::build('instances', array('crawler_last_run'=>'-4h'));
        $controller = new CheckCrawlerController(true);
        $results = $controller->go();
        $this->assertEqual("Crawler hasn't run in 4 hours", $results, $results);
    }
}
