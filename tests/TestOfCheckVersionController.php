<?php
/**
 *
 * ThinkUp/tests/TestOfCheckVersionController.php
 *
 * Copyright (c) 2011-2013 Gina Trapani
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
 * @copyright 2011-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfCheckVersionController extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function tearDown(){
        parent::tearDown();
    }

    public function testConstructor() {
        $controller = new CheckVersionController(true);
        $this->assertTrue(isset($controller));
    }

    public function testNotLoggedIn() {
        $controller = new CheckVersionController(true);
        $results = $controller->go();
        $this->assertPattern( '/session\/login.php\?redirect\=/', $controller->redirect_destination);
    }

    public function testLoggedin() {
        $this->simulateLogin('me@example.com');
        $controller = new CheckVersionController(true);

        $results = $controller->go();
        $this->assertNoPattern('/You must <a href="\/session\/login.php">log in<\/a> to do this/', $results);
        $this->assertPattern('/var ROOT = \'thinkup_version\'/', $results);
    }

    public function testOptedOut() {
        include THINKUP_WEBAPP_PATH.'install/version.php';
        $bvalues = array('namespace' => OptionDAO::APP_OPTIONS, 'option_name' => 'is_opted_out_usage_stats',
        'option_value' => 'true');
        $bdata = FixtureBuilder::build('options', $bvalues);

        $this->simulateLogin('me@example.com');
        $controller = new CheckVersionController(true);

        $results = $controller->go();
        $this->assertNoPattern('/You must <a href="\/session\/login.php">log in<\/a> to do this/', $results);
        $this->assertPattern('/var ROOT = \'thinkup_version\'/', $results);
        $this->assertPattern('/var CONTENT_URL = \'http:\/\/thinkup.com\/version.php\?usage=n\&v='.$THINKUP_VERSION.
        '/', $results);
    }

    public function testNotOptedOut() {
        include THINKUP_WEBAPP_PATH.'install/version.php';
        $this->simulateLogin('me@example.com');
        $controller = new CheckVersionController(true);

        $results = $controller->go();
        $this->assertPattern('/var CONTENT_URL = \'http:\/\/thinkup.com\/version.php\?v='.$THINKUP_VERSION.
        '/', $results);
        $this->assertNoPattern('/var CONTENT_URL = \'http:\/\/thinkup.com\/version.php?v='.$THINKUP_VERSION.
        '\&usage=n/', $results);
    }

    public function testInBetaNotOptedOut() {
        include THINKUP_WEBAPP_PATH.'install/version.php';
        $bvalues = array('namespace' => OptionDAO::APP_OPTIONS, 'option_name' => 'is_subscribed_to_beta',
        'option_value' => 'true');
        $bdata = FixtureBuilder::build('options', $bvalues);

        $this->simulateLogin('me@example.com');
        $controller = new CheckVersionController(true);

        $results = $controller->go();
        $this->assertPattern('/var CONTENT_URL = \'http:\/\/thinkup.com\/version.php\?channel=beta\&v='.
        $THINKUP_VERSION.'/', $results);
        $this->assertNoPattern('/var CONTENT_URL = \'http:\/\/thinkup.com\/version.php?v='.$THINKUP_VERSION.
        '\&usage=n/', $results);
    }

    public function testInBetaOptedOut() {
        include THINKUP_WEBAPP_PATH.'install/version.php';
        $bvalues = array('namespace' => OptionDAO::APP_OPTIONS, 'option_name' => 'is_subscribed_to_beta',
        'option_value' => 'true');
        $bdata = FixtureBuilder::build('options', $bvalues);
        $bvalues1 = array('namespace' => OptionDAO::APP_OPTIONS, 'option_name' => 'is_opted_out_usage_stats',
        'option_value' => 'true');
        $bdata2= FixtureBuilder::build('options', $bvalues);

        $this->simulateLogin('me@example.com');
        $controller = new CheckVersionController(true);

        $results = $controller->go();
        $this->assertPattern('/var CONTENT_URL = \'http:\/\/thinkup.com\/version.php\?channel=beta\&v='.
        $THINKUP_VERSION.'/', $results);
        $this->assertNoPattern('/var CONTENT_URL = \'http:\/\/thinkup.com\/version.php?v='.$THINKUP_VERSION.
        '\&usage=n/', $results);
    }
}