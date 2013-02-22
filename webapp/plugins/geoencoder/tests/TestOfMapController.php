<?php
/**
 *
 * ThinkUp/tests/TestOfMapController.php
 *
 * Copyright (c) 2009-2013 Gina Trapani, Ekansh Preet Singh
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
 * Test of Map Controller
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, Ekansh Preet Singh
 * @author Ekansh Preet Singh <ekanshpreet[at]gmail[dot]com>
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'plugins/geoencoder/controller/class.MapController.php';

class TestOfMapController extends ThinkUpUnitTestCase {

    public function testConstructor() {
        $controller = new MapController(true);
        $this->assertTrue(isset($controller), 'constructor test');
    }

    /**
     * Test controller when all data is correctly provided
     */
    public function testValidPostNotLoggedIn() {
        $builders = $this->buildData();
        $_GET["pid"] = '1001';
        $_GET["t"] = 'post';
        $_GET["n"] = 'twitter';
        $controller = new MapController(true);
        $results = $controller->go();

        $this->assertNoPattern("/This is a private retweet to 1001/", $results);
        $this->assertNoPattern("/This is a private reply to 1001/", $results);
    }

    public function testValidPostLoggedIn() {
        $builders = $this->buildData();
        $this->simulateLogin('me@example.com');
        $_GET["pid"] = '1001';
        $_GET["t"] = 'post';
        $_GET["n"] = 'twitter';
        $controller = new MapController(true);
        $results = $controller->go();

        $this->assertPattern('/This is a test post/', $results);
        $this->assertPattern("/This is a private retweet of 1001/", $results);
        $this->assertPattern("/This is a private reply to 1001/", $results);
    }

    /**
     * Test controller when post ID is invalid/non-existant
     */
    public function testNonNumericPostID(){
        $builder = $this->buildData();
        $this->simulateLogin('me@example.com');
        $_GET["pid"] = 'notapostID45';

        $controller = new MapController(true);
        $results = $controller->go();

        $this->assertPattern("/This post has not been geoencoded yet; cannot display map./", $results);
    }

    /**
     * Test controller when post ID is invalid/non-existant
     */
    public function testMissingPostID(){
        $builder = $this->buildData();
        $this->simulateLogin('me@example.com');

        $controller = new MapController(true);
        $results = $controller->go();

        $this->assertPattern("/This post has not been geoencoded yet; cannot display map./", $results);
    }

    /**
     * Test controller when network is invalid
     */
    public function testInvalidNetwork(){
        $builder = $this->buildData();
        $this->simulateLogin('me@example.com');
        $_GET['n'] = 'notavalidnetwork';
        $_GET["pid"] = '1001';

        $controller = new MapController(true);
        $results = $controller->go();

        $this->assertPattern("/This post has not been geoencoded yet; cannot display map./", $results);
    }

    /**
     * Test controller when type is invalid
     */
    public function testInvalidType(){
        $builder = $this->buildData();
        $this->simulateLogin('me@example.com');
        $_GET["pid"] = '1001';
        $_GET["t"] = 'notavalidtype';

        $controller = new MapController(true);
        $results = $controller->go();

        $this->assertPattern("/This post has not been geoencoded yet; cannot display map./", $results);
    }

    /**
     * Method to instantiate FixtureBuilder
     */
    private function buildData() {
        $post_data = array( 'post_id' => 1001, 'post_text' => 'This is a test post', 'author_user_id'=>'10',
        'location' => 'New Delhi, Delhi, India', 'geo' => '28.11,78.08', 'is_geo_encoded' => 1 );
        $post_builder = FixtureBuilder::build('posts', $post_data);

        $original_post_author_builder = FixtureBuilder::build('users', array('user_id'=>'10', 'username'=>'ev',
        'is_protected'=>'0', 'network'=>'twitter'));

        $public_reply_author_builder1 = FixtureBuilder::build('users', array('user_id'=>'11', 'username'=>'jack',
        'is_protected'=>'0', 'network'=>'twitter'));
        $reply_builder1 = FixtureBuilder::build('posts', array('post_id'=>'1002', 'author_user_id'=>'11',
        'author_username'=>'jack', 'post_text'=>'This is a public reply to 1001', 'network'=>'twitter',
        'in_reply_to_post_id'=>1001, 'is_geo_encoded'=>1, 'geo'=>'10,20'));

        $public_reply_author_builder2 = FixtureBuilder::build('users', array('user_id'=>'12', 'username'=>'jill',
        'is_protected'=>'0', 'network'=>'twitter'));
        $reply_builder2 = FixtureBuilder::build('posts', array('post_id'=>'1003', 'author_user_id'=>'12',
        'author_username'=>'jill', 'post_text'=>'This is a public reply to 1001', 'network'=>'twitter',
        'in_reply_to_post_id'=>1001, 'is_geo_encoded'=>1, 'geo'=>'10,20'));

        $private_reply_author_builder1 = FixtureBuilder::build('users', array('user_id'=>'13', 'username'=>'mary',
        'is_protected'=>'1', 'network'=>'twitter'));
        $reply_builder3 = FixtureBuilder::build('posts', array('post_id'=>'1004', 'author_user_id'=>'13',
        'author_username'=>'mary', 'post_text'=>'This is a private reply to 1001', 'network'=>'twitter',
        'in_reply_to_post_id'=>1001, 'is_geo_encoded'=>1, 'geo'=>'10,20'));

        $private_retweet_author_builder1 = FixtureBuilder::build('users', array('user_id'=>'14', 'username'=>'joan',
        'is_protected'=>'1', 'network'=>'twitter'));
        $retweet_builder1 = FixtureBuilder::build('posts', array('post_id'=>'1005', 'author_user_id'=>'14',
        'author_username'=>'joan', 'post_text'=>'This is a private retweet of 1001', 'network'=>'twitter',
        'in_retweet_of_post_id'=>1001, 'is_geo_encoded'=>1, 'geo'=>'10,20'));

        $private_retweet_author_builder2 = FixtureBuilder::build('users', array('user_id'=>'15', 'username'=>'peggy',
        'is_protected'=>'1', 'network'=>'twitter'));
        $retweet_builder2 = FixtureBuilder::build('posts', array('post_id'=>'1006', 'author_user_id'=>'15',
        'author_username'=>'peggy', 'post_text'=>'This is a private retweet of 1001', 'network'=>'twitter',
        'in_retweet_of_post_id'=>1001, 'is_geo_encoded'=>1, 'geo'=>'10,20'));

        $public_retweet_author_builder1 = FixtureBuilder::build('users', array('user_id'=>'16', 'username'=>'don',
        'is_protected'=>'0', 'network'=>'twitter'));
        $retweet_builder3 = FixtureBuilder::build('posts', array('post_id'=>'1007', 'author_user_id'=>'16',
        'author_username'=>'don', 'post_text'=>'This is a private retweet of 1001', 'network'=>'twitter',
        'in_retweet_of_post_id'=>1001, 'is_geo_encoded'=>1, 'geo'=>'10,20'));

        return array($post_builder, $original_post_author_builder, $public_reply_author_builder1, $reply_builder1,
        $public_reply_author_builder2, $reply_builder2, $private_reply_author_builder1, $reply_builder3,
        $private_retweet_author_builder1, $retweet_builder1, $private_retweet_author_builder2, $retweet_builder2,
        $public_retweet_author_builder1, $retweet_builder3);
    }
}
