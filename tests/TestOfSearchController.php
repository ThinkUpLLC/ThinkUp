<?php
/**
 *
 * ThinkUp/tests/TestOfSearchController.php
 *
 * Copyright (c) 2013 Gina Trapani
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
 * Test of SearchController
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfSearchController extends ThinkUpUnitTestCase {

    public function setUp() {
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testNotLoggedIn() {
        $controller = new SearchController(true);
        $this->assertTrue(isset($controller));

        $results = $controller->go();
        $this->assertPattern( '/session\/login.php\?redirect\=/', $controller->redirect_destination);
    }

    public function testConstructor() {
        $this->simulateLogin('admin@example.com', true, true);

        $controller = new SearchController(true);
        $this->assertTrue(isset($controller));

        $results = $controller->go();
        $this->debug($results);
        $this->assertPattern('/Uh-oh. Your search terms are missing. Please try again/', $results);
    }

    public function testSearchPosts() {
        $this->simulateLogin('admin@example.com', true, true);

        $_GET['c'] = "posts";
        $_GET['u'] = 'ev';
        $_GET['n'] = 'twitter';
        $_GET['q'] = "Apple";
        $controller = new SearchController(true);
        $this->assertTrue(isset($controller));

        $results = $controller->go();
        $this->assertPattern("/Sorry, ThinkUp couldn't find anything for your search\./", $results);
    }

    public function testSearchFollowers() {
        $this->simulateLogin('admin@example.com', true, true);

        $_GET['c'] = "followers";
        $_GET['u'] = 'ev';
        $_GET['n'] = 'twitter';
        $_GET['q'] = "name:Apple";
        $controller = new SearchController(true);
        $this->assertTrue(isset($controller));

        $results = $controller->go();
        $this->assertPattern("/Sorry, that search doesn't turn up any followers\./", $results);
    }

    protected function buildData() {
        $builders = array();

        //Add owner
        $hashed_pass = ThinkUpTestLoginHelper::hashPasswordUsingDeprecatedMethod("oldpassword");

        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User',
        'email'=>'me@example.com', 'is_activated'=>1, 'pwd'=>$hashed_pass,
        'pwd_salt'=> OwnerMySQLDAO::$default_salt, 'api_key' => 'c9089f3c9adaf0186f6ffb1ee8d6501c'));

        $builders[] = FixtureBuilder::build('owners', array('id'=>2, 'full_name'=>'ThinkUp J. Admin',
        'email'=>'admin@example.com', 'is_activated'=>1, 'is_admin'=>1));

        //Add instance_owner
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>1));
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>2, 'instance_id'=>1));

        //Insert test data into test table
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'13', 'user_name'=>'ev',
        'full_name'=>'Ev Williams'));

        //Make public
        //Insert test data into test table
        $builders[] = FixtureBuilder::build('instances', array('id'=>1, 'network_user_id'=>'13',
        'network_username'=>'ev', 'is_public'=>1, 'network'=>'twitter'));

        return $builders;
    }

    public function testSearchSearches() {
        //Before building data No posts
        $this->simulateLogin('admin@example.com', true, true);

        $_GET['c'] = "searches";
        $_GET['n'] = "twitter";
        $_GET['u'] = "ecucurella";
        $controller = new SearchController(true);
        $this->assertTrue(isset($controller));
        $results = $controller->go();
        $this->assertPattern("/ThinkUp couldn't find any matching results\./", $results);
        $this->assertNoPattern('/Whoops! That user doesn&#39;t exist. Please try again./',$results);
        $this->assertNoPattern('/Uh-oh. #totssomtv3 is not a saved search. Please try again./',$results);
        $this->assertNoPattern('/Whoops! You don&#39;t have access to that user. Please try again./',$results);
        $this->assertPattern('/Uh-oh. Your search terms are missing. Please try again./',$results);

        $_GET['q'] = "";
        $controller = new SearchController(true);
        $this->assertTrue(isset($controller));
        $results = $controller->go();
        $this->assertPattern("/ThinkUp couldn't find any matching results\./", $results);
        $this->assertNoPattern('/Whoops! That user doesn&#39;t exist. Please try again./',$results);
        $this->assertNoPattern('/Uh-oh. #totssomtv3 is not a saved search. Please try again./',$results);
        $this->assertNoPattern('/Whoops! You don&#39;t have access to that user. Please try again./',$results);
        $this->assertPattern('/Uh-oh. Your search term is missing. Please try again./',$results);

        $_GET['q'] = "totssomtv3";
        $controller = new SearchController(true);
        $this->assertTrue(isset($controller));
        $results = $controller->go();
        $this->assertPattern("/ThinkUp couldn't find any matching results\./", $results);
        $this->assertPattern('/Whoops! That user doesn&#39;t exist. Please try again./',$results);
        $this->assertNoPattern('/Uh-oh. #totssomtv3 is not a saved search. Please try again./',$results);
        $this->assertNoPattern('/Whoops! You don&#39;t have access to that user. Please try again./',$results);

        $_GET['u'] = "vetcastellnou";
        $_GET['q'] = "CCMA";
        $controller = new SearchController(true);
        $this->assertTrue(isset($controller));
        $results = $controller->go();
        $this->assertPattern("/ThinkUp couldn't find any matching results\./", $results);
        $this->assertPattern('/Whoops! That user doesn&#39;t exist. Please try again./',$results);
        $this->assertNoPattern('/Uh-oh. CCMA is not a saved search. Please try again./',$results);
        $this->assertNoPattern('/Whoops! You don&#39;t have access to that user. Please try again./',$results);

        //Buil data
        $builder = $this->buildSearchData1();

        //Hashtag NOT being searched
        $_GET['u'] = "ecucurella";
        $_GET['k'] = "#nohashtag";
        $_GET['q'] = "#nohashtag";
        $controller = new SearchController(true);
        $this->assertTrue(isset($controller));
        $results = $controller->go();
        $this->debug($results);
        $this->assertPattern("/ThinkUp couldn't find any matching results\./", $results);
        $this->assertNoPattern('/Whoops! That user doesn&#39;t exist. Please try again./',$results);
        $this->assertPattern('/Uh-oh. #nohashtag is not a saved search. Please try again./',$results);
        $this->assertNoPattern('/Whoops! You don&#39;t have access to that user. Please try again./',$results);

        //Keyword NOT being searched
        $_GET['u'] = 'vetcastellnou';
        $_GET['q'] = "nokeyword";
        $_GET['k'] = "nokeyword";
        $controller = new SearchController(true);
        $this->assertTrue(isset($controller));
        $results = $controller->go();
        $this->assertPattern("/ThinkUp couldn't find any matching results\./", $results);
        $this->assertNoPattern('/Whoops! That user doesn&#39;t exist. Please try again./',$results);
        $this->assertPattern('/Uh-oh. nokeyword is not a saved search. Please try again./',$results);
        $this->assertNoPattern('/Whoops! You don&#39;t have access to that user. Please try again./',$results);

        //Hashtag being searched
        $_GET['u'] = "ecucurella";
        $_GET['q'] = "#totssomtv3";
        $_GET['k'] = "#totssomtv3";
        $controller = new SearchController(true);
        $this->assertTrue(isset($controller));
        $results = $controller->go();
        $this->debug($results);
        $this->assertNoPattern("/ThinkUp couldn't find any matching results\./", $results);
        $this->assertNoPattern('/Whoops! That user doesn&#39;t exist. Please try again./',$results);
        $this->assertNoPattern('/Uh-oh. #totssomtv3 is not a saved search. Please try again./',$results);
        $this->assertPattern('/Dem treballadors de TV3 donarem sang #lasangdelatele #totssomtv3/',$results);
        $this->assertNoPattern('/El comit dempresa de TV3 acusa la direcci de la CCMA de populista/',$results);
        $this->assertNoPattern('/El Chelsea quiere a Mourinho YA #efectivament/',$results);
        $this->assertNoPattern('/Whoops! You don&#39;t have access to that user. Please try again./',$results);

        //Keyword being searched
        $_GET['u'] = 'vetcastellnou';
        $_GET['q'] = "CCMA";
        $_GET['k'] = "CCMA";
        $controller = new SearchController(true);
        $this->assertTrue(isset($controller));
        $results = $controller->go();
        $this->debug($results);
        $this->assertNoPattern("/ThinkUp couldn't find any matching results\./", $results);
        $this->assertNoPattern('/Whoops! That user doesn&#39;t exist. Please try again./',$results);
        $this->assertNoPattern('/Uh-oh. Keyword CCMA is not being searched. Please try again./',$results);
        $this->assertNoPattern('/Dem treballadors de TV3 donarem sang #lasangdelatele #totssomtv3/',$results);
        $this->assertPattern('/El comit dempresa de TV3 acusa la direcci de la CCMA de populista/',$results);
        $this->assertNoPattern('/El Chelsea quiere a Mourinho YA #efectivament/',$results);
        $this->assertNoPattern('/Whoops! You don&#39;t have access to that user. Please try again./',$results);

        //Another owner with no permission
        $this->simulateLogin('me@example.com', true, true);

        //Hashtag being searched
        $_GET['u'] = "ecucurella";
        $_GET['q'] = "totssomtv3";
        $controller = new SearchController(true);
        $this->assertTrue(isset($controller));
        $results = $controller->go();
        $this->assertPattern("/ThinkUp couldn't find any matching results\./", $results);
        $this->assertNoPattern('/Whoops! That user doesn&#39;t exist. Please try again./',$results);
        $this->assertNoPattern('/Uh-oh. Hashtag #totssomtv3 is not being searched. Please try again./',$results);
        $this->assertPattern('/Whoops! You don&#39;t have access to that user. Please try again./',$results);

        //Keyword being searched
        $_GET['u'] = 'vetcastellnou';
        $_GET['q'] = "CCMA";
        $controller = new SearchController(true);
        $this->assertTrue(isset($controller));
        $results = $controller->go();
        $this->assertPattern("/ThinkUp couldn't find any matching results\./", $results);
        $this->assertNoPattern('/Whoops! That user doesn&#39;t exist. Please try again./',$results);
        $this->assertNoPattern('/Uh-oh. Keyword CCMA is not being searched. Please try again./',$results);
        $this->assertPattern('/Whoops! You don&#39;t have access to that user. Please try again./',$results);
    }

    private function buildSearchData1() {
        $builders = array();

        $hashed_pass = ThinkUpTestLoginHelper::hashPasswordUsingDeprecatedMethod("oldpassword");

        $builders[] = FixtureBuilder::build('owners', array(
            'id'=>1,
            'full_name'=>'ThinkUp J. Admin',
            'email'=>'admin@example.com',
            'is_activated'=>1,
            'is_admin'=>1));

        $builders[] = FixtureBuilder::build('owners', array(
            'id'=>2,
            'full_name'=>'ThinkUp J. User',
            'email'=>'me@example.com',
            'is_activated'=>1,
            'pwd'=>$hashed_pass,
            'pwd_salt'=> OwnerMySQLDAO::$default_salt,
            'api_key' => 'c9089f3c9adaf0186f6ffb1ee8d6501c'));

        $builders[] = FixtureBuilder::build('owner_instances', array(
            'owner_id'=>1,
            'instance_id'=>1));

        $builders[] = FixtureBuilder::build('owner_instances', array(
            'owner_id'=>1,
            'instance_id'=>2));

        $builders[] = FixtureBuilder::build('users', array(
            'user_id'=>100,
            'user_name'=>'ecucurella',
            'full_name'=>'Eduard Cucurella'));

        $builders[] = FixtureBuilder::build('users', array(
            'user_id'=>101,
            'user_name'=>'vetcastellnou',
            'full_name'=>'Veterans Castellnou'));

        $builders[] = FixtureBuilder::build('instances', array(
            'id'=>1,
            'network_user_id'=>'100',
            'network_username'=>'ecucurella',
            'is_public'=>1,
            'is_active'=>1,
            'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('instances', array(
            'id'=>2,
            'network_user_id'=>'101',
            'network_username'=>'vetcastellnou',
            'is_public'=>1,
            'is_active'=>1,
            'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('hashtags',
        array('hashtag' => '#totssomtv3', 'network' => 'twitter', 'count_cache' => 0));
        $builders[] = FixtureBuilder::build('hashtags',
        array('hashtag' => 'CCMA', 'network' => 'twitter', 'count_cache' => 0));
        $builders[] = FixtureBuilder::build('hashtags',
        array('hashtag' => '#efectivament', 'network' => 'twitter', 'count_cache' => 0));
        $builders[] = FixtureBuilder::build('hashtags',
        array('hashtag' => 'tv3', 'network' => 'twitter', 'count_cache' => 0));
        $builders[] = FixtureBuilder::build('instances_hashtags',
        array('instance_id' => 1, 'hashtag_id' => 1, 'last_post_id' => '0', 'earliest_post_id' => 0));
        $builders[] = FixtureBuilder::build('instances_hashtags',
        array('instance_id' => 1, 'hashtag_id' => 2, 'last_post_id' => '0', 'earliest_post_id' => 0));
        $builders[] = FixtureBuilder::build('instances_hashtags',
        array('instance_id' => 1, 'hashtag_id' => 3, 'last_post_id' => '0', 'earliest_post_id' => 0));
        $builders[] = FixtureBuilder::build('instances_hashtags',
        array('instance_id' => 1, 'hashtag_id' => 4, 'last_post_id' => '0', 'earliest_post_id' => 0));
        $builders[] = FixtureBuilder::build('hashtags_posts',
        array('post_id' => 1, 'hashtag_id' => 1, 'network' => 'twitter'));
        $builders[] = FixtureBuilder::build('hashtags_posts',
        array('post_id' => 2, 'hashtag_id' => 2, 'network' => 'twitter'));
        $builders[] = FixtureBuilder::build('hashtags_posts',
        array('post_id' => 3, 'hashtag_id' => 3, 'network' => 'twitter'));
        $builders[] = FixtureBuilder::build('hashtags_posts',
        array('post_id' => 4, 'hashtag_id' => 4, 'network' => 'twitter'));

        $builders[] = FixtureBuilder::build('posts', array(
            'post_id' => '1',
            'author_user_id' => '100',
            'author_username' => 'ecucurella',
            'author_fullname' => 'Eduard Cucurella',
            'author_avatar' => 'http://aa.com',
            'author_follower_count' => 0,
            'post_text' => 'Dem treballadors de TV3 donarem sang #lasangdelatele #totssomtv3',
            'is_protected' => 0,
            'source' => '<a href=""></a>',
            'location' => 'BCN',
            'place' => '',
            'place_id' => '',
            'geo' => '',
            'pub_date' => '2013-02-28 11:02:34',
            'in_reply_to_user_id' => '',
            'in_reply_to_post_id' => '',
            'reply_count_cache' => 1,
            'is_reply_by_friend' => 0,
            'in_retweet_of_post_id' => '',
            'old_retweet_count_cache' => 0,
            'is_retweet_by_friend' => 0,
            'reply_retweet_distance' => 0,
            'network' => 'twitter',
            'is_geo_encoded' => 0,
            'in_rt_of_user_id' => '',
            'retweet_count_cache' => 0,
            'retweet_count_api' => 0,
            'favlike_count_cache' => 0));

        $builders[] = FixtureBuilder::build('posts', array(
            'post_id' => '2',
            'author_user_id' => '101',
            'author_username' => 'vetcastellnou',
            'author_fullname' => 'Veterans Castellnou',
            'author_avatar' => 'http://aa.com',
            'author_follower_count' => 0,
            'post_text' => 'El comit dempresa de TV3 acusa la direcci de la CCMA de populista',
            'is_protected' => 0,
            'source' => '<a href=""></a>',
            'location' => 'BCN',
            'place' => '',
            'place_id' => '',
            'geo' => '',
            'pub_date' => '2013-02-28 11:02:34',
            'in_reply_to_user_id' => '',
            'in_reply_to_post_id' => '',
            'reply_count_cache' => 1,
            'is_reply_by_friend' => 0,
            'in_retweet_of_post_id' => '',
            'old_retweet_count_cache' => 0,
            'is_retweet_by_friend' => 0,
            'reply_retweet_distance' => 0,
            'network' => 'twitter',
            'is_geo_encoded' => 0,
            'in_rt_of_user_id' => '',
            'retweet_count_cache' => 0,
            'retweet_count_api' => 0,
            'favlike_count_cache' => 0));
        $builders[] = FixtureBuilder::build('posts', array(
            'post_id' => '3',
            'author_user_id' => '100',
            'author_username' => 'ecucurella',
            'author_fullname' => 'Eduard Cucurella',
            'author_avatar' => 'http://aa.com',
            'author_follower_count' => 0,
            'post_text' => 'El Chelsea quiere a Mourinho YA #efectivament',
            'is_protected' => 0,
            'source' => '<a href=""></a>',
            'location' => 'BCN',
            'place' => '',
            'place_id' => '',
            'geo' => '',
            'pub_date' => '2013-02-28 11:02:34',
            'in_reply_to_user_id' => '',
            'in_reply_to_post_id' => '',
            'reply_count_cache' => 1,
            'is_reply_by_friend' => 0,
            'in_retweet_of_post_id' => '',
            'old_retweet_count_cache' => 0,
            'is_retweet_by_friend' => 0,
            'reply_retweet_distance' => 0,
            'network' => 'twitter',
            'is_geo_encoded' => 0,
            'in_rt_of_user_id' => '',
            'retweet_count_cache' => 0,
            'retweet_count_api' => 0,
            'favlike_count_cache' => 0));

        $builders[] = FixtureBuilder::build('posts', array(
            'post_id' => '4',
            'author_user_id' => '101',
            'author_username' => 'vetcastellnou',
            'author_fullname' => 'Veterans Castellnou',
            'author_avatar' => 'http://aa.com',
            'author_follower_count' => 0,
            'post_text' => 'El comit dempresa de TV3 acusa la direcci de la CCMA de populista',
            'is_protected' => 0,
            'source' => '<a href=""></a>',
            'location' => 'BCN',
            'place' => '',
            'place_id' => '',
            'geo' => '',
            'pub_date' => '2013-02-28 11:02:34',
            'in_reply_to_user_id' => '',
            'in_reply_to_post_id' => '',
            'reply_count_cache' => 1,
            'is_reply_by_friend' => 0,
            'in_retweet_of_post_id' => '',
            'old_retweet_count_cache' => 0,
            'is_retweet_by_friend' => 0,
            'reply_retweet_distance' => 0,
            'network' => 'twitter',
            'is_geo_encoded' => 0,
            'in_rt_of_user_id' => '',
            'retweet_count_cache' => 0,
            'retweet_count_api' => 0,
            'favlike_count_cache' => 0));
        return $builders;
    }
}