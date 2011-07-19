<?php
/**
 *
 * ThinkUp/tests/WebTestOfPostDetailPage.php
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

class WebTestOfPostDetailPage extends ThinkUpWebTestCase {

    public function setUp() {
        parent::setUp();
        $this->builders = self::buildData();
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
    }

    public function testPostPageNotLoggedIn() {
        $this->get($this->url.'/post/index.php?t=10&n=twitter');
        $this->assertTitle("Post Details | ThinkUp");
        $this->assertText('This is post 10');
        //must be logged in to search
        //$this->assertNoField('Search', 'Must be logged in to search');
        $this->assertNoPattern('/Search/');
        $this->assertText('Retweets');
        $this->assertNoText('GeoEncoder');

        $this->click('Retweets');
        $this->assertTitle("Post Details | ThinkUp");
        $this->assertText('This is post 10');
        $this->assertNoText('GeoEncoder');
    }

    public function testPostPageLoggedIn() {
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword');

        $this->click("Log In");

        $this->get($this->url.'/post/index.php?t=10&n=twitter');
        $this->assertTitle("Post Details | ThinkUp");
        $this->assertText('This is post 10');
        //must be logged in to search
        $this->assertField('Search', 'Search');
        $this->assertText('Retweets');
        $this->assertNoText('GeoEncoder');

        $this->click('Retweets');
        $this->assertTitle("Post Details | ThinkUp");
        $this->assertText('This is post 10');
        $this->assertNoText('GeoEncoder');
    }

    public function testPostPageWithGeoencoderEnabled() {
        //enable GeoEncoder plugin
        $builder = FixtureBuilder::build('plugins', array('name'=>'Geoencoder', 'folder_name'=>'geoencoder',
        'is_active'=>1));

        $this->get($this->url.'/post/index.php?t=10&n=twitter');
        $this->assertTitle("Post Details | ThinkUp");
        $this->assertText('This is post 10');
        //must be logged in to search
        $this->assertNoPattern('/Search/');
        $this->assertText('Retweets');
        $this->assertText('GeoEncoder');
        $this->assertText('Response Map');
        $this->assertText('Nearest Responses');

        $this->click('Nearest responses');
        $this->assertTitle("Post Details | ThinkUp");
        $this->assertText('This is post 10');
    }
}
