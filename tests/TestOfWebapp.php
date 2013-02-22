<?php
/**
 *
 * ThinkUp/tests/TestOfWebapp.php
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
 * Test Webapp object
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

require_once THINKUP_WEBAPP_PATH.'plugins/hellothinkup/model/class.HelloThinkUpPlugin.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.TwitterPlugin.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitterrealtime/model/class.TwitterRealtimePlugin.php';

class TestOfWebapp extends ThinkUpUnitTestCase {

    /**
     * Test Webapp singleton instantiation
     */
    public function testWebappSingleton() {
        $webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
        //test default active plugin
        $this->assertEqual($webapp_plugin_registrar->getActivePlugin(), "twitter");
    }

    /**
     * Test activePlugin getter/setter
     */
    public function testWebappGetSetActivePlugin() {
        $webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
        $this->assertEqual($webapp_plugin_registrar->getActivePlugin(), "twitter");
        $webapp_plugin_registrar->setActivePlugin('facebook');
        $this->assertEqual($webapp_plugin_registrar->getActivePlugin(), "facebook");

        //make sure another instance reports back the same values
        $webapp_plugin_registrar_two = PluginRegistrarWebapp::getInstance();
        $this->assertEqual($webapp_plugin_registrar_two->getActivePlugin(), "facebook");
    }

    /**
     * Test registerPlugin when plugin object does not have the right methods available
     */
    public function testWebappRegisterPluginWithoutDashboardPluginInterfaceImplemented() {
        $webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
        $webapp_plugin_registrar->registerPlugin('hellothinkup', "HelloThinkUpPlugin");
        $webapp_plugin_registrar->setActivePlugin('hellothinkup');

        $menu = $webapp_plugin_registrar->getDashboardMenu(null);
        $this->assertIsA($menu, 'Array');
        $this->assertEqual(sizeof($menu), 0);
    }

    public function testGetDashboardMenu() {
        $webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
        $config = Config::getInstance();
        $webapp_plugin_registrar->registerPlugin('twitter', "TwitterPlugin");
        $webapp_plugin_registrar->setActivePlugin('twitter');

        $instance = new Instance();
        $instance->network_user_id = 930061;

        $menus_array = $webapp_plugin_registrar->getDashboardMenu($instance);
        $this->assertIsA($menus_array, 'Array');
        $this->assertIsA($menus_array['tweets-all'], 'MenuItem');

        // now define the twitter realtime plugin but don't set as active... count should be the same
        $builders = array();
        $builders[] = FixtureBuilder::build('plugins', array('name'=>'Twitter Realtime',
        'folder_name'=>'twitterrealtime',
        'is_active' =>0));

        $webapp_plugin_registrar->registerPlugin('twitterrealtime', "TwitterRealtimePlugin");
        $menus_array = $webapp_plugin_registrar->getDashboardMenu($instance);
        $this->assertIsA($menus_array, 'Array');
        // these two should only show up if the realtime plugin is active (which it is not in this case)
        $this->assertFalse(isset($menus_array['home-timeline']));
        $this->assertFalse(isset($menus_array['favd-all']));
    }

    public function testGetDashboardMenuWithRTPlugin() {
        // define an active twitter realtime plugin
        $builders = array();
        $builders[] = FixtureBuilder::build('plugins', array('name'=>'Twitter Realtime',
        'folder_name'=>'twitterrealtime',
        'is_active' =>1));

        $webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
        $config = Config::getInstance();
        $webapp_plugin_registrar->registerPlugin('twitter', "TwitterPlugin");
        $webapp_plugin_registrar->setActivePlugin('twitter');

        $instance = new Instance();
        $instance->network_user_id = 930061;

        $menus_array = $webapp_plugin_registrar->getDashboardMenu($instance);
        $this->assertIsA($menus_array, 'Array');
        // check that the two additional menus are defined
        $this->assertIsA($menus_array['home-timeline'], 'MenuItem');
        $this->assertIsA($menus_array['favd-all'], 'MenuItem');
    }

    public function testGetDashboardMenuItem() {
        $webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
        $config = Config::getInstance();
        $webapp_plugin_registrar->registerPlugin('twitter', "TwitterPlugin");
        $webapp_plugin_registrar->setActivePlugin('twitter');

        $instance = new Instance();
        $instance->network_user_id = 930061;

        $menu_item = $webapp_plugin_registrar->getDashboardMenuItem('tweets-all', $instance);
        $this->assertIsA($menu_item, 'MenuItem');
        $this->assertEqual($menu_item->view_template, Utils::getPluginViewDirectory('twitter').
        'twitter.inline.view.tpl', "Template ");
        $this->assertEqual($menu_item->name, 'Your tweets');
        $this->assertEqual($menu_item->description, 'All your tweets');
        $this->assertIsA($menu_item->datasets, 'array');
        $this->assertEqual(sizeOf($menu_item->datasets), 1);

        $menu_item = $webapp_plugin_registrar->getDashboardMenuItem('nonexistent', $instance);
        $this->assertEqual($menu_item, null);
    }

    public function testGetPostDetailMenu() {
        $webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
        $config = Config::getInstance();
        $webapp_plugin_registrar->registerPlugin('twitter', "TwitterPlugin");
        $webapp_plugin_registrar->setActivePlugin('twitter');

        $post = new Post(array('id'=>1, 'author_user_id'=>10, 'author_username'=>'no one', 'author_fullname'=>"No One",
        'author_avatar'=>'yo.jpg', 'source'=>'TweetDeck', 'pub_date'=>'', 'adj_pub_date'=>'', 'in_reply_to_user_id'=>'',
        'in_reply_to_post_id'=>'', 'reply_count_cache'=>'', 'in_retweet_of_post_id'=>'', 'retweet_count_cache'=>'',
        'retweet_count_api' =>'', 'old_retweet_count_cache' => '', 'in_rt_of_user_id' =>'',
        'post_id'=>9021481076, 'is_protected'=>0, 'place_id' => 'ece7b97d252718cc', 'favlike_count_cache'=>0,
        'post_text'=>'I look cookies', 'network'=>'twitter', 'geo'=>'', 'place'=>'', 'location'=>'',
        'is_geo_encoded'=>0, 'is_reply_by_friend'=>0, 'is_retweet_by_friend'=>0, 'reply_retweet_distance'=>0));

        $menus_array = $webapp_plugin_registrar->getPostDetailMenu($post);
        $this->assertIsA($menus_array, 'Array');
        $this->assertEqual(sizeof($menus_array), 1);
        $this->assertIsA($menus_array['fwds'], 'MenuItem');
    }

    public function testGetPostDetailMenuItem() {
        $webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
        $config = Config::getInstance();
        $webapp_plugin_registrar->registerPlugin('twitter', "TwitterPlugin");
        $webapp_plugin_registrar->setActivePlugin('twitter');

        $post = new Post(array('id'=>1, 'author_user_id'=>10, 'author_username'=>'no one', 'author_fullname'=>"No One",
        'author_avatar'=>'yo.jpg', 'source'=>'TweetDeck', 'pub_date'=>'', 'adj_pub_date'=>'', 'in_reply_to_user_id'=>'',
        'in_reply_to_post_id'=>'', 'reply_count_cache'=>'', 'in_retweet_of_post_id'=>'', 'retweet_count_cache'=>'',
        'retweet_count_api' =>'', 'old_retweet_count_cache' => '', 'in_rt_of_user_id' =>'',
        'post_id'=>9021481076, 'is_protected'=>0, 'place_id' => 'ece7b97d252718cc', 'favlike_count_cache'=>0,
        'post_text'=>'I look cookies', 'network'=>'twitter', 'geo'=>'', 'place'=>'', 'location'=>'',
        'is_geo_encoded'=>0, 'is_reply_by_friend'=>0, 'is_retweet_by_friend'=>0, 'reply_retweet_distance'=>0));

        $menu_item = $webapp_plugin_registrar->getPostDetailMenuItem('fwds', $post);
        $this->assertIsA($menu_item, 'MenuItem');
        $this->assertEqual($menu_item->view_template, Utils::getPluginViewDirectory('twitter').
        'twitter.post.retweets.tpl', "Template ");
        $this->assertEqual($menu_item->name, 'Retweets', "Name");
        $this->assertEqual($menu_item->description, 'Retweets of this tweet', "Description");
        $this->assertIsA($menu_item->datasets, 'array');
        $this->assertEqual(sizeOf($menu_item->datasets), 1);

        $menu_item = $webapp_plugin_registrar->getPostDetailMenuItem('nonexistent', $post);
        $this->assertEqual($menu_item, null);
    }
}
