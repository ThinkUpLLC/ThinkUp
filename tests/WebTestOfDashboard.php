<?php
/**
 *
 * ThinkUp/tests/WebTestOfDashboard.php
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
        //$this->showSource();
        $this->get($this->url.'/dashboard.php');
        $this->assertTitle("thinkupapp's Dashboard | ". Config::getInstance()->getValue('app_title_prefix') ."ThinkUp");
        $this->assertText('Logged in as admin: me@example.com');
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
        $this->assertNoText('me@example.com');

        $this->get($this->url.'/dashboard.php');

        //click on a nav link
        $this->click("Tweets");

        //make sure it takes you to posts view
        $this->assertText('No posts to display');
        $this->assertTitle("thinkupapp on Twitter | ". Config::getInstance()->getValue('app_title_prefix') ."ThinkUp");

        //not the login screen
        $this->assertNoText("Password");
    }

    public function testUserPage() {
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword');

        $this->click("Log In");
        $this->get($this->url.'/dashboard.php');

        $this->assertTitle("thinkupapp's Dashboard | ". Config::getInstance()->getValue('app_title_prefix'). "ThinkUp");

        $this->get($this->url.'/user/index.php?i=thinkupapp&u=ev&n=twitter');
        $this->assertTitle('User Details: ev | ' . Config::getInstance()->getValue('app_title_prefix') . 'ThinkUp');
        $this->assertText('Logged in as admin: me@example.com');
        $this->assertText('ev');

        $this->get($this->url.'/user/index.php?i=thinkupapp&u=usernotinsystem');
        $this->assertText('User and network not specified.');
    }

    public function testConfiguration() {
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword');

        $this->click("Log In");
        $this->get($this->url.'/dashboard.php');

        $this->assertTitle("thinkupapp's Dashboard | ". Config::getInstance()->getValue('app_title_prefix'). "ThinkUp");

        $this->click("Settings");
        $this->assertTitle('Configure Your Account | '. Config::getInstance()->getValue('app_title_prefix'). 'ThinkUp');
        $this->assertText('Expand URLs');

        $this->click("Twitter");
        $this->assertText('Settings');
        $this->assertText('To set up the Twitter plugin');
    }

    public function testExport() {
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword');

        $this->click("Log In");
        $this->get($this->url.'/dashboard.php');

        $this->assertTitle("thinkupapp's Dashboard | ". Config::getInstance()->getValue('app_title_prefix'). "ThinkUp");

        $this->get($this->url.'/dashboard.php?v=tweets-all&u=thinkupapp&n=twitter');
        //        $this->showSource();
        $this->assertText('Export');

        $this->click("Export");
        $this->assertText('This is test post');
    }
}