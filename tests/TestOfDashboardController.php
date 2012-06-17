<?php
/**
 *
 * ThinkUp/tests/TestOfDashboardController.php
 *
 * Copyright (c) 2009-2012 Gina Trapani, Mark Wilkie
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
 * @copyright 2009-2012 Gina Trapani, Mark Wilkie
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.TwitterOAuthThinkUp.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.TwitterPlugin.php';

class TestOfDashboardController extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
        $webapp = Webapp::getInstance();
        $webapp->registerPlugin('twitter', 'TwitterPlugin');
        $webapp->registerPlugin('facebook', 'FacebookPlugin');
        $webapp->registerPlugin('google+', 'GooglePlusPlugin');
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
        $this->assertEqual($v_mgr->getTemplateDataItem('info_msg'),
        'There are no public accounts set up in this ThinkUp installation.');

        $this->assertNoPattern("/Adjust Your Settings/", $results);
    }

    public function testNoInstancesLoggedIn() {
        //Add owner
        $owner_builder = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User',
        'email'=>'me@example.com', 'is_activated'=>1) );

        $this->simulateLogin('me@example.com');
        $controller = new DashboardController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertPattern("/Welcome to ThinkUp. Let\'s get started./", $v_mgr->getTemplateDataItem('info_msg'));
        $this->assertPattern("/Adjust Your Settings/", $results);
    }

    public function testNotLoggedInNoUserOrViewSpecifiedDefaultServiceUserSet() {
        $builders = $this->buildData();
        //Add another public instance
        $instance_builder = FixtureBuilder::build('instances', array('id'=>4, 'network_user_id'=>14,
        'network_username'=>'jack', 'is_public'=>1, 'crawler_last_run'=>'-2d'));
        $instance_owner_builder = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>4));
        //Set the default service user to jack, who is not last updated
        $app_option_builder = FixtureBuilder::build('options', array('namespace'=>'application_options',
        'option_name'=>'default_instance', 'option_value'=>'4'));

        $controller = new DashboardController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), "jack's Dashboard");
        $instance = $v_mgr->getTemplateDataItem('instance');
        $this->assertEqual($instance->network_username, 'jack');

        $this->assertEqual($controller->getCacheKeyString(), '.htdashboard.tpl-', $controller->getCacheKeyString());
        $this->assertFalse($v_mgr->getTemplateDataItem('is_searchable'));
    }

    public function testNotLoggedInNoUserOrViewSpecifiedNoDefaultServiceUserSet() {
        $builders = $this->buildData();
        //Add another public instance
        $instance_builder = FixtureBuilder::build('instances', array('id'=>4, 'network_user_id'=>14,
        'network_username'=>'jack', 'is_public'=>1, 'crawler_last_run'=>'-2d'));
        $instance_owner_builder = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>4));

        $controller = new DashboardController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), "ev's Dashboard");
        $instance = $v_mgr->getTemplateDataItem('instance');
        $this->assertEqual($instance->network_username, 'ev');

        $this->assertEqual($controller->getCacheKeyString(), '.htdashboard.tpl-', $controller->getCacheKeyString());
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

        $this->assertEqual($controller->getCacheKeyString(), '.htdashboard.tpl-me@example.com-ev-twitter', 'Cache key');
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
        $this->assertEqual($v_mgr->getTemplateDataItem('header'), 'Your tweets');
        $this->assertEqual($v_mgr->getTemplateDataItem('description'), 'All your tweets');
        $this->assertIsA($v_mgr->getTemplateDataItem('all_tweets'), 'array', 'Array of tweets');
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('all_tweets')), 15, '15 posts in listing');

        $this->assertFalse($v_mgr->getTemplateDataItem('is_searchable'));
    }

    public function testNonExistentUsername() {
        $builders = $this->buildData();
        $_GET['u'] = 'idontexist';
        $_GET['n'] = 'twitter';
        $_GET['v'] = 'tweets-all';
        $controller = new DashboardController(true);
        $results = $controller->go();

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $error_msg = $v_mgr->getTemplateDataItem('error_msg');
        $this->assertNotNull($error_msg);
        $this->assertEqual($error_msg, 'idontexist on Twitter is not in ThinkUp.');

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
        $this->debug($results);

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('header'), 'Your tweets');
        $this->assertEqual($v_mgr->getTemplateDataItem('description'), 'All your tweets');
        $this->assertIsA($v_mgr->getTemplateDataItem('all_tweets'), 'array', 'Array of tweets');
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('all_tweets')), 15, '15 posts in listing');

        $config = Config::getInstance();
        $this->assertEqual($controller->getCacheKeyString(), '.htdashboard.tpl-me@example.com-ev-twitter-tweets-all');
        $this->assertTrue($v_mgr->getTemplateDataItem('is_searchable'));
        $this->assertPattern('/Export/', $results);
    }

    public function testLoggedInPostsFacebook() {
        $builders = $this->buildData();
        $this->simulateLogin('me@example.com');

        //required params
        $_GET['u'] ="Joe O\'Malley";
        $_GET['n'] = 'facebook';
        $_GET['v'] = 'posts-all';
        $controller = new DashboardController(true);
        $results = $controller->go();
        $this->debug($results);

        $config = Config::getInstance();
        $this->assertPattern('/Export/', $results);
    }

    public function testLoggedInPostsGooglePlus() {
        $builders = $this->buildData();
        $this->simulateLogin('me@example.com');
        //required params
        $_GET['u'] ='Kim';
        $_GET['n'] = 'google+';
        $_GET['v'] = 'posts-all';
        $controller = new DashboardController(true);
        $results = $controller->go();

        $config = Config::getInstance();
        $this->assertPattern('/Export/', $results);
    }

    public function testLoggedInPostsWithUsernameApostrophe() {
        $builders = $this->buildData();
        $this->simulateLogin('me@example.com');
        //required params
        $_GET['u'] ="Joe O\'Malley";
        $_GET['n'] = 'facebook';
        $_GET['v'] = 'posts-all';
        $controller = new DashboardController(true);
        $results = $controller->go();

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('header'), 'All posts');
        $this->assertEqual($v_mgr->getTemplateDataItem('description'), 'All your status updates');

        $config = Config::getInstance();
        $this->assertEqual($controller->getCacheKeyString(),
        ".htdashboard.tpl-me@example.com-Joe O\'Malley-facebook-posts-all");
        $this->assertTrue($v_mgr->getTemplateDataItem('is_searchable'));
        $this->assertPattern('/Export/', $results);
    }

    public function testNotLoggedInNotPublicInstance() {
        $builders = $this->buildData();
        //required params
        $_GET['u'] = "Joe O\'Malley";
        $_GET['n'] = 'facebook';
        $_GET['v'] = 'posts-all';
        $controller = new DashboardController(true);
        $results = $controller->go();

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $error_msg = $v_mgr->getTemplateDataItem('error_msg');
        $this->assertNotNull($error_msg);
        $this->assertEqual($error_msg, 'Insufficient privileges');
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
        $this->assertEqual($v_mgr->getTemplateDataItem('description'), 'People you follow who tweet the most',
        'Description');
        $this->assertIsA($v_mgr->getTemplateDataItem('people'), 'array', 'Array of users');
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('people')), 2, '2 users in listing');

        $config = Config::getInstance();
        $this->assertEqual($controller->getCacheKeyString(),
        '.htdashboard.tpl-me@example.com-ev-twitter-friends-mostactive');
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

    public function testCleanXSS() {
        $with_xss = true;
        $builders = $this->buildData($with_xss);
        $this->simulateLogin('me@example.com');
        //required params
        $_GET['u'] = 'ev';
        $_GET['n'] = 'twitter';
        $_GET['v'] = 'tweets-all';
        $controller = new DashboardController(true);
        $results = $controller->go();
        $this->assertNoPattern("/This is post <script>alert\('wa'\);<\/script>\d+/", $results);
        $this->assertPattern("/This is post &#60;script&#62;alert\(&#39;wa&#39;\);&#60;\/script&#62;\d+/", $results);
    }

    private function buildData($with_xss = false) {
        //Add owner
        $owner_builder = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User',
        'email'=>'me@example.com', 'is_activated'=>1) );

        //Add instance_owner
        $instance_owner_builder_1 = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>1));
        $instance_owner_builder_2 = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>2));
        $instance_owner_builder_3 = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>3));

        //Insert test data into test table
        $user_builders = array();

        $user_builders[] = FixtureBuilder::build('users', array('user_id'=>'13', 'user_name'=>'ev',
        'full_name'=>'Ev Williams', 'network'=>'twitter', 'last_updated'=>'-1d'));

        $user_builders[] = FixtureBuilder::build('users', array('user_id'=>'12', 'user_name'=>'jack',
        'network'=>'twitter', 'last_updated'=>'-5d'));

        $user_builders[] = FixtureBuilder::build('users', array('user_id'=>'14', 'user_name'=>"Joe O'Malley",
        'last_updated'=>'-5d', 'network'=>'facebook'));

        $user_builders[] = FixtureBuilder::build('users', array('user_id'=>'16', 'user_name'=>"Kim",
        'last_updated'=>'-5d', 'network'=>'google+'));

        //Make public
        $instance_builder_1 = FixtureBuilder::build('instances', array('id'=>1, 'network_user_id'=>'13',
        'network_username'=>'ev', 'is_public'=>1, 'crawler_last_run'=>'-1d', 'network'=>'twitter'));

        $instance_builder_3 = FixtureBuilder::build('instances', array('id'=>3, 'network_user_id'=>'14',
        'network_username'=>"Joe O'Malley", 'is_public'=>0, 'crawler_last_run'=>'-1d', 'network'=>'facebook'));

        $instance_builder_2 = FixtureBuilder::build('instances', array('id'=>2, 'network_user_id'=>'16',
        'network_username'=>"Kim", 'is_public'=>0, 'crawler_last_run'=>'-1d', 'network'=>'google+'));

        $post_builders = array();
        //Add a bunch of posts
        $counter = 0;
        $post_data = 'This is post ';
        if ($with_xss) { $post_data .= "<script>alert('wa');</script>"; }
        while ($counter < 40) {
            $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
            $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>$counter, 'author_user_id'=>13,
            'author_username'=>'ev', 'author_fullname'=>'Ev Williams', 'post_text'=>$post_data.$counter,
            'pub_date'=>'2006-01-01 00:'.$pseudo_minute.':00'));
            $counter++;
        }

        $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>41, 'author_user_id'=>'13',
        'author_username'=>'ev', 'author_fullname'=>'Ev Williams',
        'post_text'=>'This post is in reply to jacks post 50', 'in_reply_to_post_id'=>'50', 'network'=>'twitter',
        'in_reply_to_user_id'=>'12'));

        $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>42, 'author_user_id'=>'12',
        'author_username'=>'jack', 'author_fullname'=>'Jack Dorsey', 'post_text'=>'Ev replies to this post',
        'network'=>'twitter'));

        $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>43, 'author_user_id'=>'14',
        'author_username'=>"Joe O'Malley", 'author_fullname'=>"Joe O\'Malley", 'post_text'=>"Joe's post",
        'network'=>'facebook'));

        $counter = 0;
        $post_data = 'This is post ';
        while ($counter < 40) {
            $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
            $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>($counter+45), 'author_user_id'=>'14',
            'author_username'=>"Joe O'Malley", 'author_fullname'=>"Joe O'Malley", 'post_text'=>$post_data.($counter+45),
            'pub_date'=>'2006-01-01 00:'.$pseudo_minute.':00', 'network'=>'facebook'));
            $counter++;
        }

        return array($owner_builder, $instance_owner_builder_1, $instance_owner_builder_2, $instance_owner_builder_3,
        $user_builders, $instance_builder_1, $instance_builder_2, $instance_builder_3, $post_builders);
    }
}
