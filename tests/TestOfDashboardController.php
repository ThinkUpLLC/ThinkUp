<?php
/**
 *
 * ThinkUp/tests/TestOfDashboardController.php
 *
 * Copyright (c) 2009-2011 Gina Trapani, Mark Wilkie
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
 * Test of DashboardController
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2011 Gina Trapani, Mark Wilkie
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterOAuthThinkUp.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterPlugin.php';

class TestOfDashboardController extends ThinkUpUnitTestCase {

    public function __construct() {
        $this->UnitTestCase('DashboardController class test');
    }

    public function setUp(){
        parent::setUp();
        $webapp = Webapp::getInstance();
        $webapp->registerPlugin('twitter', 'TwitterPlugin');
    }

    public function testConstructor() {
        $controller = new DashboardController(true);
        $this->assertTrue(isset($controller), 'constructor test');
        $this->assertIsA($controller, 'DashboardController');
    }

    public function testNoInstanceNotLoggedIn() {
        $controller = new DashboardController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('infomsg'),
        'There are no public accounts set up in this ThinkUp installation.<br /><br />'.
        'To make a current account public, log in and click on "Configuration." Click on one of the plugins '.
        'that contain accounts (like Twitter or Facebook) and click "Set Public" next to the account that '.
        ' should appear to users who are not logged in.');
    }

    public function testNoInstancesLoggedIn() {
        //Add owner
        $owner_builder = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User',
        'email'=>'me@example.com', 'is_activated'=>1) );

        $this->simulateLogin('me@example.com');
        $controller = new DashboardController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertPattern("/You have no accounts configured./", $v_mgr->getTemplateDataItem('infomsg'));
        $this->assertPattern("/Set up an account now/", $v_mgr->getTemplateDataItem('infomsg'));
    }

    public function testNotLoggedInNoUserOrViewSpecified() {
        $builders = $this->buildData();
        $controller = new DashboardController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $config = Config::getInstance();
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), "ev's Dashboard");
        $instance = $v_mgr->getTemplateDataItem('instance');
        $this->assertEqual($instance->network_username, 'ev');

        $config = Config::getInstance();
        $this->assertEqual($controller->getCacheKeyString(), 'dashboard.tpl-', $controller->getCacheKeyString());
        $this->assertFalse($v_mgr->getTemplateDataItem('is_searchable'));
    }

    public function testLoggedInUserSpecifiedNoViewSpecified() {
        $builders = $this->buildData();
        //must be logged in
        $this->simulateLogin('me@example.com');
        //required params
        $_GET['u'] = 'ev';
        $_GET['n'] = 'twitter';
        //default view
        $controller = new DashboardController(true);
        $results = $controller->go();

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $instance = $v_mgr->getTemplateDataItem('instance');
        $this->assertEqual($instance->network_username, 'ev');

        $config = Config::getInstance();
        $this->assertEqual($controller->getCacheKeyString(), 'dashboard.tpl-me@example.com-ev-twitter',
        'Cache key');
    }

    public function testNotLoggedInPosts() {
        $builders = $this->buildData();
        $_GET['u'] = 'ev';
        $_GET['n'] = 'twitter';
        $_GET['v'] = 'tweets-all';
        $controller = new DashboardController(true);
        $results = $controller->go();

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('header'), 'All Tweets', 'Header');
        $this->assertEqual($v_mgr->getTemplateDataItem('description'), 'All tweets', 'Description');
        $this->assertIsA($v_mgr->getTemplateDataItem('all_tweets'), 'array', 'Array of tweets');
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('all_tweets')), 15, '15 posts in listing');

        $this->assertFalse($v_mgr->getTemplateDataItem('is_searchable'));
    }

    public function testLoggedInPosts() {
        $builders = $this->buildData();
        $this->simulateLogin('me@example.com');
        //required params
        $_GET['u'] = 'ev';
        $_GET['n'] = 'twitter';
        $_GET['v'] = 'tweets-all';
        $controller = new DashboardController(true);
        $results = $controller->go();

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('header'), 'All Tweets', 'Header');
        $this->assertEqual($v_mgr->getTemplateDataItem('description'), 'All tweets', 'Description');
        $this->assertIsA($v_mgr->getTemplateDataItem('all_tweets'), 'array', 'Array of tweets');
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('all_tweets')), 15, '15 posts in listing');

        $config = Config::getInstance();
        $this->assertEqual($controller->getCacheKeyString(), 'dashboard.tpl-me@example.com-ev-twitter-tweets-all',
        'Cache key');
        $this->assertTrue($v_mgr->getTemplateDataItem('is_searchable'));
    }

    public function testLoggedInConversations() {
        $builders = $this->buildData();
        //must be logged in
        $this->simulateLogin('me@example.com');
        //required params
        $_GET['u'] = 'ev';
        $_GET['n'] = 'twitter';
        $_GET['v'] = 'tweets-convo';
        $controller = new DashboardController(true);
        $results = $controller->go();

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('header'), 'Conversations', 'Header');
        $this->assertIsA($v_mgr->getTemplateDataItem('author_replies'), 'array', 'Array of tweets');
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('author_replies')), 1, '1 posts in listing');

        $config = Config::getInstance();
        $this->assertEqual($controller->getCacheKeyString(), 'dashboard.tpl-me@example.com-ev-twitter-tweets-convo',
        'Cache key');
    }

    public function testLoggedInPeople() {
        $builders = $this->buildData();
        //first, add some people
        $user1_builder = FixtureBuilder::build('users', array(
                'user_name'=>'ginatrapani',
                'user_id'=>'930061',
                'network'=>'twitter')
        );
        $user2_builder = FixtureBuilder::build('users', array(
                'user_name'=>'anildash',
                'user_id'=>'123456',
                'network'=>'twitter')
        );

        $follower_builders = array();
        $follower_builders[] = FixtureBuilder::build('follows', array('user_id'=>'930061', 'follower_id'=>'13'));
        $follower_builders[] = FixtureBuilder::build('follows', array('user_id'=>'123456', 'follower_id'=>'13'));

        //must be logged in
        $this->simulateLogin('me@example.com');
        //required params
        $_GET['u'] = 'ev';
        $_GET['n'] = 'twitter';
        $_GET['v'] = 'friends-mostactive';
        $controller = new DashboardController(true);
        $results = $controller->go();

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('header'), 'Chatterboxes', 'Header');
        $this->assertEqual($v_mgr->getTemplateDataItem('description'), '', 'Description');
        $this->assertIsA($v_mgr->getTemplateDataItem('people'), 'array', 'Array of users');
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('people')), 2, '2 users in listing');

        $config = Config::getInstance();
        $this->assertEqual($controller->getCacheKeyString(),
        'dashboard.tpl-me@example.com-ev-twitter-friends-mostactive', 'Cache key');
    }

    public function testNonexistentPluginIsActive() {
        $builders = $this->buildData();
        //add a plugin which is activatd, but doesn't exist on the file system
        $plugin_builder = FixtureBuilder::build('plugins', array(
                'name'=>'Flickr Thumbnails',
                'folder_name'=>'flickrthumbnails',
                'is_active'=>1)
        );
        $controller = new DashboardController(true);
        $results = $controller->go();
        //make sure there's no fatal error because the plugin files don't exist
    }

    private function buildData() {
        //Add owner
        $owner_builder = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User',
        'email'=>'me@example.com', 'is_activated'=>1) );

        //Add instance_owner
        $instance_owner_builder = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>1));

        //Insert test data into test table
        $user_builder_1 = FixtureBuilder::build('users', array('user_id'=>13, 'user_name'=>'ev',
        'full_name'=>'Ev Williams', 'last_updated'=>'-1d'));

        $user_builder_2 = FixtureBuilder::build('users', array('user_id'=>12, 'user_name'=>'jack',
        'last_updated'=>'-5d'));

        //Make public
        $instance_builder = FixtureBuilder::build('instances', array('id'=>1, 'network_user_id'=>13,
        'network_username'=>'ev', 'is_public'=>1));

        $post_builders = array();
        //Add a bunch of posts
        $counter = 0;
        while ($counter < 40) {
            $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
            $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>$counter, 'author_user_id'=>13,
            'author_username'=>'ev', 'author_fullname'=>'Ev Williams', 'post_text'=>'This is post '.$counter,
            'pub_date'=>'2006-01-01 00:'.$pseudo_minute.':00'));
            $counter++;
        }

        $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>41, 'author_user_id'=>13,
        'author_username'=>'ev', 'author_fullname'=>'Ev Williams', 
        'post_text'=>'This post is in reply to jacks post 50', 'in_reply_to_post_id'=>50, 'network'=>'twitter',
        'in_reply_to_user_id'=>12));

        $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>50, 'author_user_id'=>12,
        'author_username'=>'jack', 'author_fullname'=>'Jack Dorsey', 'post_text'=>'Ev replies to this post',
        'network'=>'twitter'));

        return array($owner_builder, $instance_owner_builder, $user_builder_1, $user_builder_2, $instance_builder,
        $post_builders);
    }
}