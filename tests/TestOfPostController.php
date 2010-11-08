<?php
/**
 *
 * ThinkUp/tests/TestOfPostController.php
 *
 * Copyright (c) 2009-2010 Gina Trapani
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
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

/**
 * Test of Post Controller
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfPostController extends ThinkUpUnitTestCase {

    /**
     * Constructor
     */
    public function __construct() {
        $this->UnitTestCase('PostController class test');
    }

    public function setUp(){
        parent::setUp();
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

    public function testControlExistingPublicPostID() {
        $post_builder = FixtureBuilder::build('posts', array('post_id'=>'1001', 'author_user_id'=>'10',
        'author_username'=>'ev', 'post_text'=>'This is a test post', 'retweet_count_cache'=>'5', 'network'=>'twitter',
        'is_protected'=>0));
        $user_builder = FixtureBuilder::build('users', array('user_id'=>'10', 'username'=>'ev', 'is_protected'=>'0',
        'network'=>'twitter'));
        $_GET["t"] = '1001';
        $controller = new PostController(true);
        $results = $controller->go();
        $this->assertPattern( "/This is a test post/", $results);
    }

    public function testControlWithNumericButNonExistentPostID(){
        $_GET["t"] = '11';
        $controller = new PostController(true);
        $results = $controller->go();
        $this->assertPattern("/Post not found/", $results);
    }

    public function testControlNonNumericPostID(){
        $_GET["t"] = 'notapostID45';
        $controller = new PostController(true);
        $results = $controller->go();
        $this->assertPattern("/Post not specified/", $results);
    }

    public function testControlExistingPrivatePostIDNotLoggedIn() {
        $post_builder = FixtureBuilder::build('posts', array('post_id'=>'1001', 'author_user_id'=>'10',
        'author_username'=>'ev', 'post_text'=>'This is a test post', 'retweet_count_cache'=>'5', 'network'=>'twitter'));
        $user_builder = FixtureBuilder::build('users', array('user_id'=>'10', 'username'=>'ev', 'is_protected'=>'1',
        'network'=>'twitter'));
        $_GET["t"] = '1001';
        $controller = new PostController(true);
        $results = $controller->go();
        $this->assertPattern( "/Insufficient privileges/", $results);
    }

    public function testControlExistingPrivatePostIDLoggedIn() {
        $this->simulateLogin('me@example.com');
        $post_builder = FixtureBuilder::build('posts', array('post_id'=>'1001', 'author_user_id'=>'10',
        'author_username'=>'ev', 'post_text'=>'This is a test post', 'retweet_count_cache'=>'5', 'network'=>'twitter'));
        $user_builder = FixtureBuilder::build('users', array('user_id'=>'10', 'username'=>'ev', 'is_protected'=>'1',
        'network'=>'twitter'));
        $_GET["t"] = '1001';
        $controller = new PostController(true);
        $results = $controller->go();
        $this->assertPattern( "/This is a test post/", $results);
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
        $this->assertNoPattern("/This is a private retweet of 1001/", $results);
    }

    public function testPublicPostWithMixedAccessRepliesLoggedIn() {
        $this->simulateLogin('me@example.com');
        $builders = $this->buildPublicPostWithMixedAccessResponses();
        $_GET["t"] = '1001';
        $controller = new PostController(true);
        $results = $controller->go();
        $this->assertPattern( "/This is a test post/", $results);
        $this->assertPattern( "/This is a public reply to 1001/", $results);
        $this->assertPattern("/This is a private reply to 1001/", $results);
        $this->assertPattern("/This is a private retweet of 1001/", $results);
    }

    private function buildPublicPostWithMixedAccessResponses() {
        $post_builder = FixtureBuilder::build('posts', array('post_id'=>'1001', 'author_user_id'=>'10',
        'author_username'=>'ev', 'post_text'=>'This is a test post', 'retweet_count_cache'=>'5', 'network'=>'twitter',
        'is_protected'=>'0'));
        $original_post_author_builder = FixtureBuilder::build('users', array('user_id'=>'10', 'username'=>'ev',
        'is_protected'=>'0', 'network'=>'twitter'));

        $public_reply_author_builder1 = FixtureBuilder::build('users', array('user_id'=>'11', 'username'=>'jack',
        'is_protected'=>'0', 'network'=>'twitter'));
        $reply_builder1 = FixtureBuilder::build('posts', array('post_id'=>'1002', 'author_user_id'=>'11',
        'author_username'=>'jack', 'post_text'=>'This is a public reply to 1001', 'network'=>'twitter', 
        'in_reply_to_post_id'=>1001, 'is_protected'=>'0'));

        $public_reply_author_builder2 = FixtureBuilder::build('users', array('user_id'=>'12', 'username'=>'jill',
        'is_protected'=>'0', 'network'=>'twitter'));
        $reply_builder2 = FixtureBuilder::build('posts', array('post_id'=>'1003', 'author_user_id'=>'12',
        'author_username'=>'jill', 'post_text'=>'This is a public reply to 1001', 'network'=>'twitter', 
        'in_reply_to_post_id'=>1001, 'is_protected'=>'0'));

        $private_reply_author_builder1 = FixtureBuilder::build('users', array('user_id'=>'13', 'username'=>'mary',
        'is_protected'=>'1', 'network'=>'twitter'));
        $reply_builder3 = FixtureBuilder::build('posts', array('post_id'=>'1004', 'author_user_id'=>'13',
        'author_username'=>'mary', 'post_text'=>'This is a private reply to 1001', 'network'=>'twitter', 
        'in_reply_to_post_id'=>1001, 'is_protected'=>'1'));

        $private_retweet_author_builder1 = FixtureBuilder::build('users', array('user_id'=>'14', 'username'=>'joan',
        'is_protected'=>'1', 'network'=>'twitter'));
        $retweet_builder1 = FixtureBuilder::build('posts', array('post_id'=>'1005', 'author_user_id'=>'14',
        'author_username'=>'joan', 'post_text'=>'This is a private retweet of 1001', 'network'=>'twitter', 
        'in_retweet_of_post_id'=>1001, 'is_protected'=>'1'));

        $private_retweet_author_builder2 = FixtureBuilder::build('users', array('user_id'=>'15', 'username'=>'peggy',
        'is_protected'=>'1', 'network'=>'twitter'));
        $retweet_builder2 = FixtureBuilder::build('posts', array('post_id'=>'1006', 'author_user_id'=>'15',
        'author_username'=>'peggy', 'post_text'=>'This is a private retweet of 1001', 'network'=>'twitter', 
        'in_retweet_of_post_id'=>1001, 'is_protected'=>'1'));

        $public_retweet_author_builder1 = FixtureBuilder::build('users', array('user_id'=>'16', 'username'=>'don',
        'is_protected'=>'0', 'network'=>'twitter'));
        $retweet_builder3 = FixtureBuilder::build('posts', array('post_id'=>'1007', 'author_user_id'=>'16',
        'author_username'=>'don', 'post_text'=>'This is a public retweet of 1001', 'network'=>'twitter', 
        'in_retweet_of_post_id'=>1001, 'is_protected'=>'0'));

        return array($post_builder, $original_post_author_builder, $public_reply_author_builder1, $reply_builder1,
        $public_reply_author_builder2, $reply_builder2, $private_reply_author_builder1, $reply_builder3,
        $private_retweet_author_builder1, $retweet_builder1, $private_retweet_author_builder2, $retweet_builder2,
        $public_retweet_author_builder1, $retweet_builder3);
    }
}