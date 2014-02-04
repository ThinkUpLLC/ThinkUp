<?php
/**
 *
 * ThinkUp/tests/WebTestOfCrawlerRun.php
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
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';

class WebTestOfCrawlerRun extends ThinkUpWebTestCase {

    public function setUp() {
        parent::setUp();
        $this->builders = self::buildData();
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
    }

    public function testLoggedIn() {
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword');

        $this->click("Log In");

        $this->assertTitle(Config::getInstance()->getValue('app_title_prefix') . "ThinkUp");
        $this->assertText('me@example.com');

        //For the sake of time, set all instances to inactive so the crawler itself doesn't actually run
        $q = "UPDATE #prefix#instances SET is_active=0;";
        $this->testdb_helper->runSQL($q);

        $this->clickLinkById('refresh-data');
        //$this->showHeaders();
        $this->assertHeader('Content-Type', 'text/html; charset=UTF-8; charset=UTF-8');
        //@TODO Assert that this Hint text appears in its new Javascripty format
        //$this->assertText('Hint:');
    }

    public function testNotLoggedIn() {
        $this->get($this->url.'/crawler/run.php');
        //should redirect to Log in page
        $this->assertTitle('Log in | ThinkUp');
    }
}
