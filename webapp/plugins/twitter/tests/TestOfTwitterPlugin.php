<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/tests/TestOfTwitterPlugin.php
 *
 * Copyright (c) 2009-2011 Gina Trapani, Guillaume Boudreau
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
 * @copyright 2009-2011 Gina Trapani, Guillaume Boudreau
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
require_once 'tests/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/web_tester.php';

require_once THINKUP_ROOT_PATH.'tests/classes/class.ThinkUpUnitTestCase.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterPlugin.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitterrealtime/model/class.TwitterRealtimePlugin.php';

class TestOfTwitterPlugin extends ThinkUpUnitTestCase {
    var $logger;
    var $webapp;
    var $crawler;

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

    public function testMenuItemRegistrationForDashboardAndPost() {
        $pd = DAOFactory::getDAO('PostDAO');
        $instance = new Instance();
        $instance->network_user_id = 1;

        $menus = $this->webapp->getDashboardMenu($instance);

        $this->assertEqual(sizeof($menus), 17, "Test number of Twitter Dashboard menu items");
        $first_post_menuitem = $menus["tweets-all"];
        $this->assertEqual($first_post_menuitem->name, "All Tweets", "Test name of first post menu item");
        $this->assertEqual($first_post_menuitem->description, "All tweets", "Test description of first post menu item");

        $first_post_menuitem_datasets = $first_post_menuitem->getDatasets();
        $first_post_menuitem_dataset = $first_post_menuitem_datasets[0];
        $this->assertEqual($first_post_menuitem_dataset->name, "all_tweets", "Test 1st menu item's 1st dataset name");
        $this->assertEqual($first_post_menuitem_dataset->dao_name, 'PostDAO');
        $this->assertEqual($first_post_menuitem_dataset->dao_method_name, "getAllPosts",
        "Test first post menu item's first dataset fetching method");

        // favorites menu
        $favs_menu = $menus["ftweets-all"];
        $this->assertEqual(sizeof($favs_menu), 1);
        $favs_menuitem_datasets = $favs_menu->getDatasets();
        $favs_menuitem_dataset = $favs_menuitem_datasets[0];
        $this->assertEqual($favs_menuitem_dataset->name, "all_tweets");

        // check links menu
        $links_menu = $menus["links-friends"];
        $links_menuitem_datasets = $links_menu->getDatasets();
        $links_menuitem_dataset = $links_menuitem_datasets[0];
        $this->assertEqual($links_menuitem_dataset->name, "links");

        $links_menuitem = $menus["links-favorites"];
        $links_menuitem_datasets = $links_menuitem->getDatasets();
        $links_menuitem_dataset = $links_menuitem_datasets[0];
        $this->assertEqual($links_menuitem_dataset->name, "links");

        $links_menuitem = $menus["links-photos"];
        $links_menuitem_datasets = $links_menuitem->getDatasets();
        $links_menuitem_dataset = $links_menuitem_datasets[0];
        $this->assertEqual($links_menuitem_dataset->name, "links");

        //Test post page menu items
        $post = new Post(array('id'=>1, 'author_user_id'=>10, 'author_username'=>'no one', 'author_fullname'=>"No One",
        'author_avatar'=>'yo.jpg', 'source'=>'TweetDeck', 'pub_date'=>'', 'adj_pub_date'=>'', 'in_reply_to_user_id'=>'',
        'in_reply_to_post_id'=>'', 'reply_count_cache'=>'', 'in_retweet_of_post_id'=>'', 'retweet_count_cache'=>'', 
        'retweet_count_api' => '', 'old_retweet_count_cache' => '', 'in_rt_of_user_id' =>'',
        'post_id'=>9021481076, 'is_protected'=>0, 'place_id' => 'ece7b97d252718cc', 'favlike_count_cache'=>0,
        'post_text'=>'I look cookies', 'network'=>'twitter', 'geo'=>'', 'place'=>'', 'location'=>'', 
        'is_geo_encoded'=>0, 'is_reply_by_friend'=>0, 'is_retweet_by_friend'=>0, 'reply_retweet_distance'=>0));

        $post_menus_array = $this->webapp->getPostDetailMenu($post);
        $this->assertIsA($post_menus_array, 'Array');
        $this->assertEqual(sizeof($post_menus_array), 2);
        $this->assertIsA($post_menus_array['fwds'], 'MenuItem');

        // these two should not be defined (since the twitter realtime plugin is not active)
        $this->assertFalse(isset($menus['home-timeline']));
        $this->assertFalse(isset($menus['favd-all']));
    }

    // this version checks the menus with the twitter realtime plugin active
    public function testMenuItemRegistrationForDashboardAndPostRealtimeActive() {

        // define an active twitter realtime plugin
        $builders = array();
        $builders[] = FixtureBuilder::build('plugins', array('name'=>'Twitter Realtime',
        'folder_name'=>'twitterrealtime',
        'is_active' =>1));
        $this->webapp->registerPlugin('twitterrealtime', 'TwitterRealtimePlugin');

        $pd = DAOFactory::getDAO('PostDAO');
        $instance = new Instance();
        $instance->network_user_id = 1;

        $menus = $this->webapp->getDashboardMenu($instance);

        $this->assertEqual(sizeof($menus), 19, "Test number of Twitter Dashboard menu items");
        $first_post_menuitem = $menus["tweets-all"];
        $this->assertEqual($first_post_menuitem->name, "All Tweets", "Test name of first post menu item");
        $this->assertEqual($first_post_menuitem->description, "All tweets", "Test description of first post menu item");

        $first_post_menuitem_datasets = $first_post_menuitem->getDatasets();
        $first_post_menuitem_dataset = $first_post_menuitem_datasets[0];
        $this->assertEqual($first_post_menuitem_dataset->name, "all_tweets", "Test 1st menu item's 1st dataset name");
        $this->assertEqual($first_post_menuitem_dataset->dao_name, 'PostDAO');
        $this->assertEqual($first_post_menuitem_dataset->dao_method_name, "getAllPosts",
        "Test first post menu item's first dataset fetching method");

        // favorites menu
        $favs_menu = $menus["ftweets-all"];
        $this->assertEqual(sizeof($favs_menu), 1);
        $favs_menuitem_datasets = $favs_menu->getDatasets();
        $favs_menuitem_dataset = $favs_menuitem_datasets[0];
        $this->assertEqual($favs_menuitem_dataset->name, "all_tweets");

        $links_menuitem = $menus["favd-all"];
        $links_menuitem_datasets = $links_menuitem->getDatasets();
        $links_menuitem_dataset = $links_menuitem_datasets[0];
        $this->assertEqual($links_menuitem_dataset->name, "all_favd");

        // check links menu
        $links_menu = $menus["links-friends"];
        $links_menuitem_datasets = $links_menu->getDatasets();
        $links_menuitem_dataset = $links_menuitem_datasets[0];
        $this->assertEqual($links_menuitem_dataset->name, "links");

        $links_menuitem = $menus["links-favorites"];
        $links_menuitem_datasets = $links_menuitem->getDatasets();
        $links_menuitem_dataset = $links_menuitem_datasets[0];
        $this->assertEqual($links_menuitem_dataset->name, "links");

        $links_menuitem = $menus["links-photos"];
        $links_menuitem_datasets = $links_menuitem->getDatasets();
        $links_menuitem_dataset = $links_menuitem_datasets[0];
        $this->assertEqual($links_menuitem_dataset->name, "links");

        $links_menuitem = $menus["home-timeline"];
        $links_menuitem_datasets = $links_menuitem->getDatasets();
        $links_menuitem_dataset = $links_menuitem_datasets[0];
        $this->assertEqual($links_menuitem_dataset->name, "home_timeline");

        //Test post page menu items
        $post = new Post(array('id'=>1, 'author_user_id'=>10, 'author_username'=>'no one', 'author_fullname'=>"No One",
        'author_avatar'=>'yo.jpg', 'source'=>'TweetDeck', 'pub_date'=>'', 'adj_pub_date'=>'', 'in_reply_to_user_id'=>'',
        'in_reply_to_post_id'=>'', 'reply_count_cache'=>'', 'in_retweet_of_post_id'=>'', 'retweet_count_cache'=>'', 
        'retweet_count_api' => '', 'old_retweet_count_cache' => '', 'in_rt_of_user_id' =>'',
        'post_id'=>9021481076, 'is_protected'=>0, 'place_id' => 'ece7b97d252718cc', 'favlike_count_cache'=>0,
        'post_text'=>'I look cookies', 'network'=>'twitter', 'geo'=>'', 'place'=>'', 'location'=>'', 
        'is_geo_encoded'=>0, 'is_reply_by_friend'=>0, 'is_retweet_by_friend'=>0, 'reply_retweet_distance'=>0));

        $post_menus_array = $this->webapp->getPostDetailMenu($post);
        $this->assertIsA($post_menus_array, 'Array');
        $this->assertEqual(sizeof($post_menus_array), 2);
        $this->assertIsA($post_menus_array['fwds'], 'MenuItem');
    }

    public function testRepliesOrdering() {
        $this->assertEqual(TwitterPlugin::repliesOrdering('default'), 'is_reply_by_friend DESC, follower_count DESC');
        $this->assertEqual(TwitterPlugin::repliesOrdering('location'),
        'geo_status, reply_retweet_distance, is_reply_by_friend DESC, follower_count DESC');
        $this->assertEqual(TwitterPlugin::repliesOrdering(''), 'is_reply_by_friend DESC, follower_count DESC');
    }

    public function testDeactivate() {
        //all facebook and facebook page accounts should be set to inactive on plugin deactivation
        $webapp = Webapp::getInstance();
        $logger = Logger::getInstance();
        $pd = DAOFactory::getDAO('PostDAO');
        $instance = new Instance();
        $instance->network_user_id = 1;

        $instance_builder_1 = FixtureBuilder::build('instances', array('network_username'=>'julie',
        'network'=>'twitter', 'crawler_last_run'=>'-1d', 'is_activated'=>'1', 'is_public'=>'1'));

        $instance_builder_2 = FixtureBuilder::build('instances', array('network_username'=>'john',
        'network'=>'twitter', 'crawler_last_run'=>'-1d', 'is_activated'=>'1', 'is_public'=>'1'));

        $instance_dao = DAOFactory::getDAO('InstanceDAO');
        $active_instances = $instance_dao->getAllInstances("DESC", true, "twitter");
        $this->assertEqual(sizeof($active_instances), 2);

        $tw_plugin = new TwitterPlugin();
        $tw_plugin->deactivate();

        $active_instances = $instance_dao->getAllInstances("DESC", true, "twitter");
        $this->assertEqual(sizeof($active_instances), 0);
    }
}
