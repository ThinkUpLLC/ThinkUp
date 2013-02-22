<?php
/**
 *
 * ThinkUp/tests/TestOfPostController.php
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
 * Test of Post Controller
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */

require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.TwitterOAuthThinkUp.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.TwitterPlugin.php';

require_once THINKUP_WEBAPP_PATH.'_lib/dao/class.OwnerInstanceMySQLDAO.php';

class TestOfPostController extends ThinkUpUnitTestCase {
    public function setUp(){
        parent::setUp();
        $webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
        $webapp_plugin_registrar->registerPlugin('twitter', 'TwitterPlugin');
        $this->config = Config::getInstance();
    }

    public function tearDown(){
        parent::tearDown();
        //clear doesOwnerHaveAccessToPost query cache
        OwnerInstanceMySQLDAO::$post_access_query_cache = array();
    }

    public function testConstructor() {
        $controller = new PostController(true);
        $this->assertTrue(isset($controller), 'constructor test');
    }

    public function testControlNoPostID() {
        $controller = new PostController(true);
        $results = $controller->go();
        $this->assertPattern( "/Post not specified/", $results);
    }

    public function testControlExistingPostIDByPublicInstance() {
        $instance_builder = FixtureBuilder::build('instances', array('network_user_id'=>'10', 'network_username'=>'ev',
        'is_public'=>1, 'network'=>'twitter'));
        $post_builder = FixtureBuilder::build('posts', array('post_id'=>'1001', 'author_user_id'=>'10',
        'author_username'=>'ev', 'post_text'=>'This is a test post', 'retweet_count_cache'=>'5', 'network'=>'twitter',
        'is_protected'=>0));
        $user_builder = FixtureBuilder::build('users', array('user_id'=>'10', 'username'=>'ev', 'is_protected'=>0,
        'network'=>'twitter'));
        $_GET["t"] = '1001';
        $controller = new PostController(true);
        $results = $controller->go();
        //sleep(1000);
        $this->assertPattern( "/This is a test post/", $results);
    }

    public function testControlExistingPostIDByPublicInstanceWithLink() {
        $instance_builder = FixtureBuilder::build('instances', array('network_user_id'=>'10', 'network_username'=>'ev',
        'is_public'=>1, 'network'=>'twitter'));
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1001', 'author_user_id'=>'10',
        'author_username'=>'ev', 'post_text'=>'This is a test post', 'retweet_count_cache'=>'5', 'network'=>'twitter',
        'is_protected'=>0));
        $user_builder = FixtureBuilder::build('users', array('user_id'=>'10', 'username'=>'ev', 'is_protected'=>'0',
        'network'=>'twitter'));
        $link_builder = FixtureBuilder::build('links', array('post_key'=>'1', 'description'=>'My test description',
        'expanded_url'=>'http://example.com/i/am/expanded/yo/index.html'));
        $_GET["t"] = '1001';
        $controller = new PostController(true);
        $results = $controller->go();
        $this->assertPattern( "/This is a test post/", $results);
        $this->assertPattern( "/My test description/", $results);
        $this->assertPattern( "/http:\/\/example.com\/i\/am\/expanded\/yo\/index.html/", $results);
    }

    public function testControlWithNumericButNonExistentPostID(){
        $_GET["t"] = '11';
        $controller = new PostController(true);
        $results = $controller->go();
        $this->assertPattern("/Post not found/", $results);
    }

    public function testControlNonNumericPostID(){
        $_GET["t"] = 'postsnowhavelettersandnumbersinthem1324324';
        $controller = new PostController(true);
        $results = $controller->go();
        $this->assertNoPattern("/Post not specified/", $results);
    }

    public function testControlExistingPrivatePostIDNotLoggedIn() {
        $post_builder = FixtureBuilder::build('posts', array('post_id'=>'1001', 'author_user_id'=>'10',
        'author_username'=>'ev', 'post_text'=>'This is a test post', 'retweet_count_cache'=>'5',
        'network'=>'twitter'));
        $user_builder = FixtureBuilder::build('users', array('user_id'=>'10', 'username'=>'ev', 'is_protected'=>'1',
        'network'=>'twitter'));
        $_GET["t"] = '1001';
        $controller = new PostController(true);
        $results = $controller->go();
        $this->assertPattern( "/Insufficient privileges/", $results);
    }

    public function testControlExistingPrivatePostIDLoggedInFollowed() {
        $owner_builder = FixtureBuilder::build('owners', array('email'=>'me@example.com', 'is_admin'=>0));

        $i_data = array('id' => 1, 'network_username' => 'mojojojo', 'network_user_id' =>'20', 'network'=>'twitter');
        $instances_builder = FixtureBuilder::build('instances',  $i_data);

        $oi_data = array('owner_id' => 1, 'instance_id' => 1);
        $oinstances_builder = FixtureBuilder::build('owner_instances',  $oi_data);

        $follows_builder = FixtureBuilder::build('follows', array('user_id'=>'10', 'follower_id'=>'20',
        'network'=>'twitter'));

        $post_builder = FixtureBuilder::build('posts', array('post_id'=>'1001', 'author_user_id'=>'10',
        'author_username'=>'ev', 'post_text'=>'This is a test private post', 'retweet_count_cache'=>'5',
        'network'=>'twitter'));
        $user_builder = FixtureBuilder::build('users', array('user_id'=>'10', 'username'=>'ev', 'is_protected'=>'1',
        'network'=>'twitter'));

        $this->simulateLogin('me@example.com');

        $_GET["t"] = '1001';
        $controller = new PostController(true);
        $results = $controller->go();

        $this->assertPattern( "/This is a test private post/", $results);
        $this->assertTrue($controller->getViewManager()->getTemplateDataItem('disable_embed_code'));
    }

    public function testControlExistingPrivatePostIDLoggedInNotFollowed() {
        $owner_builder = FixtureBuilder::build('owners', array('email'=>'me@example.com', 'is_admin'=>0));

        $i_data = array('id' => 1, 'network_username' => 'mojojojo', 'network_user_id' =>'20', 'network'=>'twitter');
        $instances_builder = FixtureBuilder::build('instances',  $i_data);

        $oi_data = array('owner_id' => 1, 'instance_id' => 1);
        $oinstances_builder = FixtureBuilder::build('owner_instances',  $oi_data);

        $post_builder = FixtureBuilder::build('posts', array('post_id'=>'1001', 'author_user_id'=>'10',
        'author_username'=>'ev', 'post_text'=>'This is a test private post', 'retweet_count_cache'=>'5',
        'network'=>'twitter'));
        $user_builder = FixtureBuilder::build('users', array('user_id'=>'10', 'username'=>'ev', 'is_protected'=>'1',
        'network'=>'twitter'));

        $this->simulateLogin('me@example.com');

        $_GET["t"] = '1001';
        $controller = new PostController(true);
        $results = $controller->go();

        $this->assertNoPattern( "/This is a test private post/", $results);
        $this->assertTrue($controller->getViewManager()->getTemplateDataItem('disable_embed_code'));
    }

    public function testControlExistingPrivatePostIDLoggedInNotFollowedAdmin() {
        $owner_builder = FixtureBuilder::build('owners', array('email'=>'me@example.com', 'is_admin'=>1));

        $i_data = array('id' => 1, 'network_username' => 'mojojojo', 'network_user_id' =>'20', 'network'=>'twitter');
        $instances_builder = FixtureBuilder::build('instances',  $i_data);

        $oi_data = array('owner_id' => 1, 'instance_id' => 1);
        $oinstances_builder = FixtureBuilder::build('owner_instances',  $oi_data);

        $post_builder = FixtureBuilder::build('posts', array('post_id'=>'1001', 'author_user_id'=>'10',
        'author_username'=>'ev', 'post_text'=>'This is a test private post', 'retweet_count_cache'=>'5',
        'network'=>'twitter'));
        $user_builder = FixtureBuilder::build('users', array('user_id'=>'10', 'username'=>'ev', 'is_protected'=>'1',
        'network'=>'twitter'));

        $this->simulateLogin('me@example.com');

        $_GET["t"] = '1001';
        $controller = new PostController(true);
        $results = $controller->go();

        $this->assertPattern( "/This is a test private post/", $results);
        $this->assertTrue($controller->getViewManager()->getTemplateDataItem('disable_embed_code'));
    }

    public function testPublicPostWithMixedAccessRepliesNotLoggedIn() {
        $builders = $this->buildPublicPostWithMixedAccessResponses();
        $_GET["t"] = '1001';
        $controller = new PostController(true);
        $results = $controller->go();
        $this->assertPattern( "/This is a test post/", $results);
        $this->assertPattern( "/This is a public reply to 1001/", $results);
        $this->assertPattern( "/Not showing 1 private reply./", $results);
        $this->assertNoPattern("/This is a private reply to 1001/", $results);
        $this->assertFalse($controller->getViewManager()->getTemplateDataItem('disable_embed_code'));
    }

    public function testPublicPostWithEmbedDisabled() {
        $builders = $this->buildPublicPostWithMixedAccessResponses();
        $builders[]  = FixtureBuilder::build('options', array('namespace'=>'application_options',
        'option_name'=>'is_embed_disabled', 'option_value'=>'true'));

        $_GET["t"] = '1001';
        $controller = new PostController(true);
        $results = $controller->go();
        $this->assertTrue($controller->getViewManager()->getTemplateDataItem('disable_embed_code'));
    }

    public function testPublicPostWithMixedAccessRepliesLoggedInNotFollowed() {
        $owner_builder = FixtureBuilder::build('owners', array('email'=>'me@example.com', 'is_admin'=>0));

        $this->simulateLogin('me@example.com');
        $builders = $this->buildPublicPostWithMixedAccessResponses();
        $_GET["t"] = '1001';
        $controller = new PostController(true);
        $results = $controller->go();
        $this->assertPattern( "/This is a test post/", $results);
        $this->assertPattern( "/This is a public reply to 1001/", $results);
        $this->assertNoPattern("/This is a private reply to 1001/", $results);
        $this->assertFalse($controller->getViewManager()->getTemplateDataItem('disable_embed_code'));
    }

    public function testPublicPostWithMixedAccessRepliesLoggedInFollowed() {
        $owner_builder = FixtureBuilder::build('owners', array('email'=>'me@example.com', 'is_admin'=>0));

        $i_data = array('id' => 1, 'network_username' => 'mojojojo', 'network_user_id' =>'20', 'network'=>'twitter');
        $instances_builder = FixtureBuilder::build('instances',  $i_data);

        $oi_data = array('owner_id' => 1, 'instance_id' => 1);
        $oinstances_builder = FixtureBuilder::build('owner_instances',  $oi_data);

        $follows_builder = FixtureBuilder::build('follows', array('user_id'=>'13', 'follower_id'=>'20',
        'network'=>'twitter'));

        $this->simulateLogin('me@example.com');
        $builders = $this->buildPublicPostWithMixedAccessResponses();
        $_GET["t"] = '1001';
        $controller = new PostController(true);
        $results = $controller->go();
        //echo $results;
        $this->assertPattern( "/This is a test post/", $results);
        $this->assertPattern( "/This is a public reply to 1001/", $results);
        $this->assertPattern("/This is a private reply to 1001/", $results);
        $this->assertFalse($controller->getViewManager()->getTemplateDataItem('disable_embed_code'));
    }

    public function testPublicPostWithMixedAccessRepliesLoggedInNotFollowedAdmin() {
        $owner_builder = FixtureBuilder::build('owners', array('email'=>'me@example.com', 'is_admin'=>1));

        $this->simulateLogin('me@example.com');
        $builders = $this->buildPublicPostWithMixedAccessResponses();
        $_GET["t"] = '1001';
        $controller = new PostController(true);
        $results = $controller->go();
        //echo $results;
        $this->assertPattern( "/This is a test post/", $results);
        $this->assertPattern( "/This is a public reply to 1001/", $results);
        $this->assertPattern("/This is a private reply to 1001/", $results);
        $this->assertFalse($controller->getViewManager()->getTemplateDataItem('disable_embed_code'));
    }

    public function testNotLoggedInPostWithViewsSpecified() {
        $builders = $this->buildPublicPostWithMixedAccessResponses();
        $_GET["t"] = '1001';
        //default menu item
        $_GET["v"] = 'default';
        $controller = new PostController(true);
        $results = $controller->go();
        $this->assertPattern( "/This is a test post/", $results);
        $this->assertPattern( "/This is a public reply to 1001/", $results);
        $this->assertNoPattern("/This is a private reply to 1001/", $results);

        //retweets menu item
        $_GET["v"] = 'fwds';
        $controller = new PostController(true);
        $results = $controller->go();
        $this->assertPattern( "/This is a test post/", $results);
        $this->assertPattern( "/This is a public retweet of 1001/", $results);
        //not logged in, shouldn't see private RTs
        $this->assertNoPattern("/This is a private retweet of 1001/", $results);

        //non-existent menu item
        $_GET["v"] = 'idontexist';
        $controller = new PostController(true);
        $results = $controller->go();
        $this->assertPattern( "/This is a test post/", $results);
    }

    public function testLoggedInPostWithViewsSpecified() {
        $builders = $this->buildPublicPostWithMixedAccessResponses();
        $owner_builder = FixtureBuilder::build('owners', array('email'=>'me@example.com', 'is_admin'=>0));
        $i_data = array('id'=>2, 'network_username' => 'mojojojo', 'network_user_id' =>'20', 'network'=>'twitter');
        $instances_builder = FixtureBuilder::build('instances',  $i_data);

        $oi_data = array('owner_id' => 1, 'instance_id' => 2);
        $oinstances_builder = FixtureBuilder::build('owner_instances',  $oi_data);

        $follows_builder = FixtureBuilder::build('follows', array('user_id'=>'13', 'follower_id'=>'20',
        'network'=>'twitter'));

        $_GET["t"] = '1001';
        $_GET['n'] = 'twitter';
        //Log in and see private replies and retweets
        $this->simulateLogin('me@example.com');
        //default menu item
        $_GET["v"] = 'default';
        $controller = new PostController(true);
        $results = $controller->go();
        $this->assertPattern( "/This is a test post/", $results);
        $this->assertPattern( "/This is a public reply to 1001/", $results);
        $this->assertPattern("/This is a private reply to 1001/", $results);

        //retweets menu item
        $this->simulateLogin('me@example.com');
        $_GET["v"] = 'fwds';
        $controller = new PostController(true);

        $results = $controller->go();
        $this->assertPattern( "/This is a test post/", $results);
        //shouldn't see replies, just retweets
        $this->assertNoPattern( "/This is a public reply to 1001/", $results);
        $this->assertNoPattern("/This is a private reply to 1001/", $results);
        $this->assertPattern( "/This is a public retweet of 1001/", $results);
        //logged in, should see private responses
        $this->assertPattern("/This is a private retweet of 1001/", $results);

        //non-existent menu item
        $_GET["v"] = 'idontexist';
        $controller = new PostController(true);
        $results = $controller->go();
        $this->assertPattern( "/This is a test post/", $results);
    }

    public function testCleanXSS() {
        $owner_builder = FixtureBuilder::build('owners', array('email'=>'me@example.com', 'is_admin'=>0));

        $with_xss = true;
        $builders = $this->buildPublicPostWithMixedAccessResponses($with_xss);
        $_GET["t"] = '1001';
        $_GET['n'] = 'twitter';
        //Log in and see private replies and retweets
        $this->simulateLogin('me@example.com');
        //default menu item
        $_GET["v"] = 'default';
        $controller = new PostController(true);
        $results = $controller->go();
        $this->assertPattern("/This is a test post&#60;script&#62;alert\(&#39;wa&#39;\);&#60;\/script&#62;/", $results);
    }

    public function testControlWithNonExistentPluginActivated() {
        $data[] = FixtureBuilder::build('instances', array('network_user_id'=>'10', 'network_username'=>'ev',
        'is_public'=>1, 'network'=>'twitter'));
        $data[] = FixtureBuilder::build('posts', array('post_id'=>'1001', 'author_user_id'=>'10',
        'author_username'=>'ev', 'post_text'=>'This is a test post', 'retweet_count_cache'=>'5', 'network'=>'twitter',
        'is_protected'=>0));
        $data[] = FixtureBuilder::build('users', array('user_id'=>'10', 'username'=>'ev', 'is_protected'=>'0',
        'network'=>'twitter'));
        $data[] = FixtureBuilder::build('plugins', array('name'=>"Nonexistent", 'folder_name'=>'idontexist',
        'is_active'=>1));
        $_GET["t"] = '1001';
        $controller = new PostController(true);
        $results = $controller->go();
        $this->assertPattern( "/This is a test post/", $results);
        $this->assertNoPattern("/No plugin object defined for/", $results);

        //assert plugin has been deactivated
        $sql = "SELECT * FROM " . $this->table_prefix . "plugins WHERE folder_name='idontexist';";
        $stmt = PluginMySQLDAO::$PDO->query($sql);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEqual(0, $data['is_active']);
    }

    private function buildPublicPostWithMixedAccessResponses($with_xss = false) {
        $post_text = 'This is a test post';
        if ($with_xss) {
            $post_text .= "<script>alert('wa');</script>";
        }

        $instance_builder = FixtureBuilder::build('instances', array('network_user_id'=>'10', 'network_username'=>'ev',
        'is_public'=>1, 'network'=>'twitter'));

        $post_builder = FixtureBuilder::build('posts', array('post_id'=>'1001', 'author_user_id'=>'10',
        'author_username'=>'ev', 'post_text'=>$post_text, 'retweet_count_cache'=>'5', 'network'=>'twitter',
        'is_protected'=>'0'));
        $original_post_author_builder = FixtureBuilder::build('users', array('user_id'=>'10', 'username'=>'ev',
        'is_protected'=>'0', 'network'=>'twitter'));

        $public_reply_author_builder1 = FixtureBuilder::build('users', array('user_id'=>'11', 'username'=>'jack',
        'is_protected'=>'0', 'network'=>'twitter'));
        $reply_builder1 = FixtureBuilder::build('posts', array('post_id'=>'1002', 'author_user_id'=>'11',
        'author_username'=>'jack', 'post_text'=>'This is a public reply to 1001', 'network'=>'twitter',
        'in_reply_to_post_id'=>'1001', 'is_protected'=>'0'));

        $public_reply_author_builder2 = FixtureBuilder::build('users', array('user_id'=>'12', 'username'=>'jill',
        'is_protected'=>'0', 'network'=>'twitter'));
        $reply_builder2 = FixtureBuilder::build('posts', array('post_id'=>'1003', 'author_user_id'=>'12',
        'author_username'=>'jill', 'post_text'=>'This is a public reply to 1001', 'network'=>'twitter',
        'in_reply_to_post_id'=>'1001', 'is_protected'=>'0'));

        $private_reply_author_builder1 = FixtureBuilder::build('users', array('user_id'=>'13', 'username'=>'mary',
        'is_protected'=>'1', 'network'=>'twitter'));
        $reply_builder3 = FixtureBuilder::build('posts', array('post_id'=>'1004', 'author_user_id'=>'13',
        'author_username'=>'mary', 'post_text'=>'This is a private reply to 1001', 'network'=>'twitter',
        'in_reply_to_post_id'=>'1001', 'is_protected'=>'1'));

        $private_retweet_author_builder1 = FixtureBuilder::build('users', array('user_id'=>'14', 'username'=>'joan',
        'is_protected'=>'1', 'network'=>'twitter'));
        $retweet_builder1 = FixtureBuilder::build('posts', array('post_id'=>'1005', 'author_user_id'=>'14',
        'author_username'=>'joan', 'post_text'=>'This is a private retweet of 1001', 'network'=>'twitter',
        'in_retweet_of_post_id'=>'1001', 'is_protected'=>'1'));

        $private_retweet_author_builder2 = FixtureBuilder::build('users', array('user_id'=>'15', 'username'=>'peggy',
        'is_protected'=>'1', 'network'=>'twitter'));
        $retweet_builder2 = FixtureBuilder::build('posts', array('post_id'=>'1006', 'author_user_id'=>'15',
        'author_username'=>'peggy', 'post_text'=>'This is a private retweet of 1001', 'network'=>'twitter',
        'in_retweet_of_post_id'=>'1001', 'is_protected'=>'1'));

        $public_retweet_author_builder1 = FixtureBuilder::build('users', array('user_id'=>'16', 'username'=>'don',
        'is_protected'=>'0', 'network'=>'twitter'));
        $retweet_builder3 = FixtureBuilder::build('posts', array('post_id'=>'1007', 'author_user_id'=>'16',
        'author_username'=>'don', 'post_text'=>'This is a public retweet of 1001', 'network'=>'twitter',
        'in_retweet_of_post_id'=>'1001', 'is_protected'=>'0'));

        return array($post_builder, $original_post_author_builder, $public_reply_author_builder1, $reply_builder1,
        $public_reply_author_builder2, $reply_builder2, $private_reply_author_builder1, $reply_builder3,
        $private_retweet_author_builder1, $retweet_builder1, $private_retweet_author_builder2, $retweet_builder2,
        $public_retweet_author_builder1, $retweet_builder3, $instance_builder);
    }
}