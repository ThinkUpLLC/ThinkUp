<?php
/**
 *
 * ThinkUp/tests/WebTestOfTwitterDashboard.php
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
 * @copyright 2009-2010 Gina Trapani
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/web_tester.php';

class WebTestOfTwitterDashboard extends ThinkUpWebTestCase {

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

        $this->assertTitle("thinkupapp's Dashboard | ThinkUp");
        $this->assertText('Logged in as: me@example.com');
        $this->assertText('thinkupapp');
    }

    public function testAllTweets() {
        $this->get($this->url.'/index.php?v=tweets-all&u=ev&n=twitter');
        $this->assertTitle("ev on Twitter | ThinkUp");
        $this->assertText('This is post 39');
        $this->assertText('This is post 38');
        $this->assertText('This is post 37');
        $this->assertText('This is post 25');
    }

    public function testLinksFromFriends() {
        $this->get($this->url.'/index.php?v=links-friends&u=ev&n=twitter');
        $this->assertTitle("ev on Twitter | ThinkUp");
        //$this->showSource();
        $this->assertText('This is link post 25');
        $this->assertText('Link 25');
    }

    public function testPhotosByFriends() {
        $this->get($this->url.'/index.php?v=links-photos&u=ev&n=twitter');
        $this->assertTitle("ev on Twitter | ThinkUp");
        $this->assertText('This is image post 25');
    }
}
