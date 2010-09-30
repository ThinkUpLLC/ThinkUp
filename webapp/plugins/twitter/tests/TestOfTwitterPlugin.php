<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/tests/TestOfTwitterPlugin.php
 *
 * Copyright (c) 2009-2010 Gina Trapani, Guillaume Boudreau
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
 * Test of TwitterPlugin class
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani, Guillaume Boudreau
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
if ( !isset($RUNNING_ALL_TESTS) || !$RUNNING_ALL_TESTS ) {
    require_once '../../../../tests/init.tests.php';
}

require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/web_tester.php';

require_once THINKUP_ROOT_PATH.'tests/classes/class.ThinkUpUnitTestCase.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterPlugin.php';

class TestOfTwitterPlugin extends ThinkUpUnitTestCase {
    var $logger;
    var $webapp;
    var $crawler;

    public function __construct() {
        $this->UnitTestCase('TwitterPlugin class test');
    }

    public function setUp() {
        parent::setUp();
        $this->webapp = Webapp::getInstance();
        $this->crawler = Crawler::getInstance();
        $this->webapp->registerPlugin('twitter', 'TwitterPlugin');
        $this->crawler->registerCrawlerPlugin('TwitterPlugin');
        $this->webapp->setActivePlugin('twitter');
        $this->logger = Logger::getInstance();
    }

    public function tearDown() {
        parent::tearDown();
        $this->logger->close();
    }

    public function testWebappTabRegistration() {
        $pd = DAOFactory::getDAO('PostDAO');
        $instance = new Instance();
        $instance->network_user_id = 1;

        $post_tabs = $this->webapp->getChildTabsUnderPosts($instance);

        $this->assertEqual(sizeof($post_tabs), 4, "Test number of post tabs");
        $first_post_tab = $post_tabs[0];
        $this->assertEqual($first_post_tab->short_name, "tweets-all", "Test short name of first post tab");
        $this->assertEqual($first_post_tab->name, "All Tweets", "Test name of first post tab");
        $this->assertEqual($first_post_tab->description, "All tweets", "Test description of first post tab");

        $first_post_tab_datasets = $first_post_tab->getDatasets();
        $first_post_tab_dataset = $first_post_tab_datasets[0];
        $this->assertEqual($first_post_tab_dataset->name, "all_tweets", "Test first post tab's first dataset name");
        $this->assertEqual($first_post_tab_dataset->dao_name, 'PostDAO');
        $this->assertEqual($first_post_tab_dataset->dao_method_name, "getAllPosts",
        "Test first post tab's first dataset fetching method");
    }

    public function testGetChildTabsUnderLinks() {
        $pd = DAOFactory::getDAO('PostDAO');
        $instance = new Instance();
        $instance->network_user_id = 1;

        $links_tabs = $this->webapp->getChildTabsUnderLinks($instance);
        $this->assertEqual(sizeof($links_tabs), 2);

        $links_tab = $links_tabs[0];
        $this->assertEqual($links_tab->short_name, "links-friends");
        $links_tab_datasets = $links_tab->getDatasets();
        $links_tab_dataset = $links_tab_datasets[0];
        $this->assertEqual($links_tab_dataset->name, "links");

        $links_tab = $links_tabs[1];
        $this->assertEqual($links_tab->short_name, "links-photos");
        $links_tab_datasets = $links_tab->getDatasets();
        $links_tab_dataset = $links_tab_datasets[0];
        $this->assertEqual($links_tab_dataset->name, "links");
    }

    public function testRepliesOrdering() {
        $this->assertEqual(TwitterPlugin::repliesOrdering('default'), 'is_reply_by_friend DESC, follower_count DESC');
        $this->assertEqual(TwitterPlugin::repliesOrdering('location'),
        'geo_status, reply_retweet_distance, is_reply_by_friend DESC, follower_count DESC');
        $this->assertEqual(TwitterPlugin::repliesOrdering(''), 'is_reply_by_friend DESC, follower_count DESC');
    }
}
