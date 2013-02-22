<?php
/**
 *
 * ThinkUp/tests/WebTestOfTwitterDashboard.php
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

class WebTestOfTwitterDashboard extends ThinkUpWebTestCase {

    public function setUp() {
        parent::setUp();
        $this->builders = self::buildData();

        //set up some private data
        //private image post
        $this->builders[] = FixtureBuilder::build('posts', array('id'=>150, 'post_id'=>150, 'author_user_id'=>18,
        'author_username'=>'shutterbug', 'author_fullname'=>'Shutter Bug', 'author_avatar'=>'avatar.jpg',
        'source'=>'web', 'pub_date'=>'-1d', 'reply_count_cache'=>0, 'retweet_count_cache'=>0,
        'post_text'=>'This is private image post 1', 'network'=>'twitter', 'is_protected'=>1));

        $this->builders[] = FixtureBuilder::build('links', array('url'=>'http://example.com/1private',
        'expanded_url'=>'http://example.com/1private.jpg', 'title'=>'', 'clicks'=>0, 'post_key'=>150,
        'image_src'=>'image.png'));

        //private link post
        $this->builders[] = FixtureBuilder::build('posts', array('id'=>151, 'post_id'=>151, 'author_user_id'=>18,
        'author_username'=>'shutterbug', 'author_fullname'=>'Shutter Bug', 'author_avatar'=>'avatar.jpg',
        'source'=>'web', 'pub_date'=>'-1d', 'reply_count_cache'=>0, 'retweet_count_cache'=>0,
        'post_text'=>'This is private link post 1', 'network'=>'twitter', 'is_protected'=>1));

        $this->builders[] = FixtureBuilder::build('links', array('url'=>'http://example.com/1private',
        'expanded_url'=>'http://example.com/private1', 'title'=>'', 'clicks'=>0, 'post_key'=>151,
        'image_src'=>''));
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
        $this->get($this->url.'/dashboard.php');
        $this->assertTitle("thinkupapp's Dashboard | " . Config::getInstance()->getValue('app_title_prefix') . "ThinkUp");
        $this->assertText('Logged in as admin: me@example.com');
        $this->assertText('thinkupapp');
    }

    public function testAllTweets() {
        $this->get($this->url.'/dashboard.php?v=tweets-all&u=ev&n=twitter');
        $this->assertTitle("ev on Twitter | " . Config::getInstance()->getValue('app_title_prefix') . "ThinkUp");
        $this->assertText('This is post 39');
        $this->assertText('This is post 38');
        $this->assertText('This is post 37');
        $this->assertText('This is post 25');
    }

    public function testLinksFromFriends() {
        $this->get($this->url.'/dashboard.php?v=links-friends&u=ev&n=twitter');
        $this->assertTitle("ev on Twitter | " . Config::getInstance()->getValue('app_title_prefix') . "ThinkUp");
        //$this->showSource();
        $this->assertText('This is link post 25');
        $this->assertText('Link 25');
        //not logged in, shouldn't display private link post
        $this->assertNoText('This is private link post 1');
    }

    public function testPrivateLinksFromFriends() {
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword');

        $this->click("Log In");

        $this->get($this->url.'/dashboard.php?v=links-friends&u=ev&n=twitter');
        $this->assertTitle("ev on Twitter | " . Config::getInstance()->getValue('app_title_prefix') . "ThinkUp");
        //$this->showSource();
        $this->assertText('This is link post 39');
        $this->assertText('Link 39');
        //logged in, should display private link post
        $this->assertText('This is private link post 1');
    }

    public function testPhotosByFriends() {
        $this->get($this->url.'/dashboard.php?v=links-photos&u=ev&n=twitter');
        $this->assertTitle("ev on Twitter | " . Config::getInstance()->getValue('app_title_prefix') . "ThinkUp");
        $this->assertText('This is image post 25');
        //not logged in, shouldn't display private image post
        $this->assertNoText('This is private image post 1');
    }

    public function testPrivatePhotosByFriends() {
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword');

        $this->click("Log In");

        $this->get($this->url.'/dashboard.php?v=links-photos&u=ev&n=twitter');
        $this->assertTitle("ev on Twitter | " . Config::getInstance()->getValue('app_title_prefix') . "ThinkUp");
        $this->assertText('This is image post 39');
        //logged in, should display private image post
        $this->assertText('This is private image post 1');
    }

    public function testConversations() {
        //not logged in
        $this->get($this->url.'/dashboard.php?v=tweets-convo&u=ev&n=twitter');
        $this->assertText('No tweets to display.');

        //logged in
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword');

        $this->click("Log In");
        $this->get($this->url.'/dashboard.php?v=tweets-convo&u=ev&n=twitter');
        $this->assertText('No tweets to display.');
    }

    //@TODO Add tests for Favorites and Links from Favorites
}
