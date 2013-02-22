<?php
/**
 *
 * ThinkUp/tests/WebTestOfPostDetailPage.php
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
        $cfg = Config::getInstance();
        $this->get($this->url.'/post/index.php?t=10&n=twitter');
        $this->assertTitle("Post Details | " . $cfg->getValue('app_title_prefix') . "ThinkUp");
        $this->assertText('This is post 10');
        // allow search for non logged in users
        $this->assertPattern('/Search/');
        $this->assertField('Go', 'Go');
        $this->assertField('search', 'Search');

        $this->assertText('Retweets');
        $this->assertNoText('GeoEncoder');

        $this->click('Retweets');
        $this->assertTitle("Post Details | " . $cfg->getValue('app_title_prefix') . "ThinkUp");
        $this->assertText('This is post 10');
        $this->assertNoText('GeoEncoder');
    }

    public function testPostPageLoggedIn() {
        $cfg = Config::getInstance();
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword');

        $this->click("Log In");

        $this->get($this->url.'/post/index.php?t=10&n=twitter');
        $this->assertTitle("Post Details | " . $cfg->getValue('app_title_prefix') . "ThinkUp");
        $this->assertText('This is post 10');

        $this->assertField('Go', 'Go');
        $this->assertField('search', 'Search');
        $this->assertText('Retweets');
        $this->assertNoText('Response Map');

        $this->click('Retweets');
        $this->assertTitle("Post Details | " . $cfg->getValue('app_title_prefix') . "ThinkUp");
        $this->assertText('This is post 10');
        $this->assertNoText('Response Map');
    }

    public function testPostPageWithGeoencoderEnabled() {
        $cfg = Config::getInstance();

        //enable GeoEncoder plugin
        $builder = FixtureBuilder::build('plugins', array('name'=>'Geoencoder', 'folder_name'=>'geoencoder',
        'is_active'=>1));

        $this->get($this->url.'/post/index.php?t=10&n=twitter');
        $this->assertTitle("Post Details | " . $cfg->getValue('app_title_prefix') . "ThinkUp");
        $this->assertText('This is post 10');
        $this->assertPattern('/Search/'); // we now allow search for non logged in users...
        $this->assertText('Retweets');
        $this->assertText('Response Map');
        $this->assertText('Nearest Replies');

        $this->click('Nearest replies');
        $this->assertTitle("Post Details | " . $cfg->getValue('app_title_prefix') . "ThinkUp");
        $this->assertText('This is post 10');
    }

    public function testPostPageWithPostHTMLEntities() {
        //enable GeoEncoder plugin
        $builder = FixtureBuilder::build('plugins', array('name'=>'Geoencoder', 'folder_name'=>'geoencoder',
        'is_active'=>1));
        $config = Config::getInstance();
        $db_prefix = $config->getValue('table_prefix');
        $pdo = PostMySQLDAO::$PDO;
        $pdo->query("update " . $db_prefix . "posts set post_text = concat(post_text, ' &lt; &gt; &amp; < > &')");
        $this->get($this->url.'/post/index.php?t=10&n=twitter');
        $this->assertPattern("/This is post 10 &#60; &#62; &#38; &#60; &#62; &#38;/"); // we are all filtered entities
        $this->assertText('This is post 10 < > & < > &'); // we render properly
    }
}
