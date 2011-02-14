<?php
/**
 *
 * ThinkUp/tests/TestOfCheckCrawlerController.php
 *
 * Copyright (c) 2009-2011 Gina Trapani
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
        $pass = $this->assertEqual('', $results);

        if (!$pass) {
            // debug message in case of a false negative test result
            $this->debug('testInstanceLessThan3Hours can fail if you do
                not have the correct permissions on your compiled_view
                folder. Folder needs to be writable.');
        }
    }

    public function testInstanceMoreThan3Hours() {
        $instance_builder = FixtureBuilder::build('instances', array('crawler_last_run'=>'-4h'));
        $controller = new CheckCrawlerController(true);
        $results = $controller->go();
        $pass = $this->assertEqual("Crawler hasn't run in 4 hours", $results);
        if (!$pass) {
            // debug message in case of a false negative test result
            $this->debug('testInstanceMoreThan3Hours can fail if you do
                not have the correct permissions on your compiled_view
                folder. Folder needs to be writable.');
        }
    }

    public function testInstanceDifferentThreshold() {
        $instance_builder = FixtureBuilder::build('instances', array('crawler_last_run'=>'-2h'));

        // 2nd argument is $argc, third argument is $argv
        $controller = new CheckCrawlerController(true, 1, array(1.0));
        
        $results = $controller->go();
        $pass = $this->assertEqual("Crawler hasn't run in 2 hours", $results);

        if (!$pass) {
            // debug message in case of a false negative test result
            $this->debug('testInstanceDifferentThreshold can fail if you do
                not have the correct permissions on your compiled_view
                folder. Folder needs to be writable.');
        }
    }
}
