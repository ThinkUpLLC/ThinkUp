<?php
/**
 *
 * ThinkUp/tests/WebTestOfDashboard.php
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
 *
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2011 Gina Trapani
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/web_tester.php';

class WebTestOfDashboard extends ThinkUpWebTestCase {
    public function setUp() {
        parent::setUp();
        $this->builders = self::buildData();
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
    }

    public function testDashboardWithPosts() {
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword');

        $this->click("Log In");
        //        $this->showSource();

        $this->assertTitle("thinkupapp's Dashboard | ThinkUp");
        $this->assertText('Logged in as: me@example.com');
        $this->assertText('thinkupapp');
    }

    /**
     * Assert menu nav links don't send you to the login screen after logging out.
     */
    public function testDashboardMenuNavLinksOnLogout() {
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword');

        $this->click("Log In");
        $this->click("Log Out");

        //assert you're logged out
        $this->assertNoText('Logged in as: me@example.com');

        //click on a nav link
        $this->click("All Tweets");

        //make sure it takes you to posts view
        $this->assertText('All tweets');
        $this->assertTitle("thinkupapp on Twitter | ThinkUp");

        //not the login screen
        $this->assertNoText("Password");
    }

    public function testUserPage() {
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword');

        $this->click("Log In");
        $this->assertTitle("thinkupapp's Dashboard | ThinkUp");

        $this->get($this->url.'/user/index.php?i=thinkupapp&u=ev&n=twitter');
        $this->assertTitle('User Details: ev | ThinkUp');
        $this->assertText('Logged in as: me@example.com');
        $this->assertText('ev');

        $this->get($this->url.'/user/index.php?i=thinkupapp&u=usernotinsystem');
        $this->assertText('User and network not specified.');
    }

    public function testConfiguration() {
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword');

        $this->click("Log In");
        $this->assertTitle("thinkupapp's Dashboard | ThinkUp");

        $this->click("Settings");
        $this->assertTitle('Configure Your Account | ThinkUp');
        $this->assertText('configure');
        $this->assertText('Expand URLs');

        $this->click("Twitter");
        $this->assertText('Configure the Twitter Plugin');
    }

    public function testExport() {
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword');

        $this->click("Log In");
        $this->assertTitle("thinkupapp's Dashboard | ThinkUp");
        //        $this->showSource();
        $this->assertText('CSV');

        $this->click("CSV");
        $this->assertText('This is test post');
    }
}
