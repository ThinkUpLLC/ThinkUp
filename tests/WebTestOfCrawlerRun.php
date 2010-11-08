<?php
/**
 *
 * ThinkUp/tests/WebTestOfCrawlerRun.php
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
 *
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/web_tester.php';

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

        $this->assertTitle("thinkupapp's Dashboard | ThinkUp");
        $this->assertText('Logged in as: me@example.com');
        $this->assertText('thinkupapp');

        $this->assertText('Update now');

        //For the sake of time, set all instances to inactive so the crawler itself doesn't actually run
        $q = "UPDATE tu_instances SET is_active=0;";
        $this->db->exec($q);

        $this->click("Update now");
        //$this->showHeaders();
        $this->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    public function testNotLoggedIn() {
        $this->get($this->url.'/crawler/run.php');
        $this->assertText('You must log in to do this.');
    }

}
