<?php
/**
 *
 * ThinkUp/tests/TestOfUserMySQLDAO.php
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
 * Test of UserDAO
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */

require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfUserMySQLDAO extends ThinkUpUnitTestCase {

    public function setUp() {
        parent::setUp();
        $this->builders = self::buildData();
    }

    protected function buildData() {
        //Insert test data into test table
        $builders[] = FixtureBuilder::build('users', array('user_id'=>12, 'user_name'=>'jack',
        'full_name'=>'Jack Dorsey', 'avatar'=>'avatar.jpg', 'gender'=>'', 'location'=>'San Francisco',
        'is_verified'=>1, 'network'=>'twitter', 'id' => 1));

        //Insert test data into test table
        $builders[] = FixtureBuilder::build('users', array('user_id'=>13, 'user_name'=>'zuck',
        'full_name'=>'Mark Zuckerberg', 'avatar'=>'avatar.jpg', 'gender'=>'Male', 'location'=>'San Francisco',
        'network'=>'facebook', 'id' => 2));

        $this->logger = Logger::getInstance();
        return $builders;
    }

    /**
     * Tear down
     */
    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
        $this->logger->close();
    }

    /**
     * Test DAO constructor
     */
    public function testCreateNewUserDAO() {
        $dao = DAOFactory::getDAO('UserDAO');
        $this->assertTrue(isset($dao));
    }

    /**
     * Test isUserInDB
     */
    public function testIsUserInDB() {
        $user_dao = DAOFactory::getDAO('UserDAO');
        $this->assertTrue($user_dao->isUserInDB(12, 'twitter'));
        $this->assertFalse($user_dao->isUserInDB(13, 'twitter'));
        $this->assertTrue($user_dao->isUserInDB(13, 'facebook'));
    }

    /**
     * Test isUserInDBByName
     */
    public function testIsUserInDBByName() {
        $user_dao = DAOFactory::getDAO('UserDAO');
        $this->assertTrue($user_dao->isUserInDBByName('jack', 'twitter'));
        $this->assertFalse($user_dao->isUserInDBByName('gina', 'twitter'));
        $this->assertTrue($user_dao->isUserInDBByName('zuck', 'facebook'));
        $this->assertFalse($user_dao->isUserInDBByName('zuck', 'twitter'));
    }

    /**
     * Test getDetails when the user exists
     */
    public function testGetDetailsUserExists() {
        $user_dao = DAOFactory::getDAO('UserDAO');
        $user = $user_dao->getDetails(12, 'twitter');
        $this->assertEqual($user->id, 1);
        $this->assertEqual($user->user_id, 12);
        $this->assertEqual($user->username, 'jack');
        $this->assertEqual($user->gender, '');
        $this->assertEqual($user->network, 'twitter');
        $user = $user_dao->getDetails(13, 'facebook');
        $this->assertEqual($user->id, 2);
        $this->assertEqual($user->user_id, 13);
        $this->assertEqual($user->username, 'zuck');
        $this->assertEqual($user->gender, 'Male');
        $this->assertEqual($user->network, 'facebook');

        $user = $user_dao->getDetails(13, 'twitter');
        $this->assertTrue(!isset($user));
    }
    /**
     * Test getDetails when user does not exist
     */
    public function testGetDetailsUserDoesNotExist() {
        $user_dao = DAOFactory::getDAO('UserDAO');
        $user = $user_dao->getDetails(13, 'twitter');
        $this->assertTrue(!isset($user));
    }

    /*
     * Test getDetailsByUserKey when the user exists
     */
    public function testGetDetailsByUserKeyUserExists() {
        $user_dao = DAOFactory::getDAO('UserDAO');
        $user = $user_dao->getDetailsByUserKey(1);
        $this->assertEqual($user->id, 1);
        $this->assertEqual($user->user_id, 12);
        $this->assertEqual($user->username, 'jack');
        $this->assertEqual($user->gender, '');
        $this->assertEqual($user->network, 'twitter');
        $user = $user_dao->getDetailsByUserKey(2);
        $this->assertEqual($user->id, 2);
        $this->assertEqual($user->user_id, 13);
        $this->assertEqual($user->username, 'zuck');
        $this->assertEqual($user->gender, 'Male');
        $this->assertEqual($user->network, 'facebook');
    }

    /**
     * Test getDetailsByUserKey when user does not exist
     */
    public function testGetDetailsByUserKeyDoesNotExist() {
        $user_dao = DAOFactory::getDAO('UserDAO');
        $user = $user_dao->getDetailsByUserKey(12312412421);
        $this->assertNull($user);
    }


    /**
     * Test update individual user
     */
    public function testUpdateUser() {
        $user_dao = DAOFactory::getDAO('UserDAO');
        $user_versions_dao = DAOFactory::getDAO('UserVersionsDAO');

        $user_array = array('id'=>3, 'user_id'=>'13', 'user_name'=>'ginatrapani', 'full_name'=>'Gina Trapani',
            'avatar'=>'avatar.jpg', 'gender'=>'', 'location'=>'NYC', 'description'=>'Blogger',
            'url'=>'http://ginatrapani.org', 'is_verified'=>1, 'is_protected'=>0, 'follower_count'=>5000,
            'post_count'=>1000, 'joined'=>'2007-03-06 13:48:05', 'network'=>'twitter', 'last_post_id'=>'abc102');
        $user = new User($user_array, 'Test Insert');
        $this->assertEqual($user_dao->updateUser($user), 1, "1 user inserted");
        $user_from_db = $user_dao->getDetails('13', 'twitter');
        $this->assertEqual($user_from_db->user_id, '13');
        $this->assertEqual($user_from_db->username, 'ginatrapani');
        $this->assertEqual($user_from_db->avatar, 'avatar.jpg');
        $this->assertEqual($user_from_db->gender, '');
        $this->assertEqual($user_from_db->location, 'NYC');
        $this->assertTrue($user_from_db->is_verified);
        $changes = $user_versions_dao->getRecentVersions(3, 999);
        //Bio version got inserted
        $this->assertEqual(1, count($changes));

        $user_array = array('user_id'=>'13', 'user_name'=>'ginatrapanichanged', 'full_name'=>'Gina Trapani ',
            'avatar'=>'avatara.jpg', 'gender'=>'', 'location'=>'San Diego', 'description'=>'Blogger',
            'url'=>'http://ginatrapani.org', 'is_verified'=>0, 'is_protected'=>0, 'follower_count'=>5000,
            'post_count'=>1000, 'joined'=>'2007-03-06 13:48:05', 'network'=>'twitter');
        $user1 = new User($user_array, 'Test Update');
        $this->assertEqual($user_dao->updateUser($user1), 1, "1 row updated");
        $user_from_db = $user_dao->getDetails(13, 'twitter');
        $this->assertEqual($user_from_db->user_id, 13);
        $this->assertEqual($user_from_db->username, 'ginatrapanichanged');
        $this->assertEqual($user_from_db->avatar, 'avatara.jpg');
        $this->assertEqual($user_from_db->gender, '');
        $this->assertEqual($user_from_db->location, 'San Diego');
        $this->assertFalse($user_from_db->is_verified);
        $changes = $user_versions_dao->getRecentVersions(3, 999);
        //Bio didn't change
        $this->assertEqual(1, count($changes));

        //Test description version capture
        $user_array = array('user_id'=>'13', 'user_name'=>'ginatrapanichanged', 'full_name'=>'Gina Trapani ',
            'avatar'=>'avatara.jpg', 'gender'=>'', 'location'=>'San Diego', 'description'=>'Writer http://example.com',
            'url'=>'http://ginatrapani.org', 'is_verified'=>0, 'is_protected'=>0, 'follower_count'=>5000,
            'post_count'=>1000, 'joined'=>'2007-03-06 13:48:05', 'network'=>'twitter');
        $user1 = new User($user_array, 'Test Update');
        $this->assertEqual($user_dao->updateUser($user1), 1, "1 row updated");
        $changes = $user_versions_dao->getRecentVersions(3, 999);
        //Bio changed
        $this->assertEqual(2, count($changes));

        //Test description version non-capture (URLs only)
        $user_array = array('user_id'=>'13', 'user_name'=>'ginatrapanichanged', 'full_name'=>'Gina Trapani ',
            'avatar'=>'avatara.jpg', 'gender'=>'', 'location'=>'San Diego', 'description'=>'Writer http://t.co',
            'url'=>'http://ginatrapani.org', 'is_verified'=>0, 'is_protected'=>0, 'follower_count'=>5000,
            'post_count'=>1000, 'joined'=>'2007-03-06 13:48:05', 'network'=>'twitter');
        $user1 = new User($user_array, 'Test Update');
        $this->assertEqual($user_dao->updateUser($user1), 1, "1 row updated");
        $changes = $user_versions_dao->getRecentVersions(3, 999);
        //Only URL in bio changed, doesn't count as a change so this doesn't go up
        $this->assertEqual(2, count($changes));

        //Test no username set
        $user_array = array('user_id'=>13, 'user_name'=>null, 'full_name'=>'Gina Trapani ', 'avatar'=>'avatara.jpg',
            'gender'=>'', 'location'=>'San Diego', 'description'=>'Blogger', 'url'=>'http://ginatrapani.org',
            'is_verified'=>1, 'is_protected'=>0, 'follower_count'=>5000, 'post_count'=>1000,
            'joined'=>'2007-03-06 13:48:05', 'network'=>'twitter');
        $user1 = new User($user_array, 'Test Update');
        $this->assertEqual($user_dao->updateUser($user1), 0);
    }

    /**
     * Test updateUsers for multiple user update
     */
    public function testUpdateUsers() {
        $user_dao = DAOFactory::getDAO('UserDAO');

        $user_array1 = array('id'=>2, 'user_id'=>'13', 'user_name'=>'ginatrapani', 'full_name'=>'Gina Trapani',
        'avatar'=>'avatar.jpg', 'gender'=>'', 'location'=>'NYC', 'description'=>'Blogger',
        'url'=>'http://ginatrapani.org', 'is_verified'=>1, 'is_protected'=>0, 'follower_count'=>5000,
        'post_count'=>1000, 'joined'=>'2007-03-06 13:48:05', 'network'=>'twitter', 'last_post_id'=>'abc123');
        $user1 = new User($user_array1, 'Test');
        $user_array2 = array('id'=>3, 'user_id'=>'14', 'user_name'=>'anildash', 'full_name'=>'Anil Dash',
        'avatar'=>'avatar.jpg', 'gender'=>'', 'location'=>'NYC', 'description'=>'Blogger',
        'url'=>'http://ginatrapani.org', 'is_verified'=>1, 'is_protected'=>0, 'follower_count'=>5000,
        'post_count'=>1000, 'joined'=>'2007-03-06 13:48:05', 'network'=>'twitter', 'last_post_id'=>'abc456');
        $user2 = new User($user_array2, 'Test');

        $users_to_update = array($user1, $user2);

        $this->assertTrue($user_dao->updateUsers($users_to_update) == 2);
    }

    /**
     * Test getUserByName when user exists
     */
    public function testGetUserByNameUserExists() {
        $user_dao = DAOFactory::getDAO('UserDAO');

        $user = $user_dao->getUserByName('jack', 'twitter');
        $this->assertEqual($user->id, 1);
        $this->assertEqual($user->user_id, 12);
        $this->assertEqual($user->username, 'jack');
        $this->assertEqual($user->full_name, 'Jack Dorsey');
        $this->assertEqual($user->gender, '');
        $this->assertEqual($user->location, 'San Francisco');
    }

    /**
     * Test getUserByName when user does not exist
     */
    public function testGetUserByNameUserDoesNotExist() {
        $user_dao = DAOFactory::getDAO('UserDAO');

        $user = $user_dao->getUserByName('gina', 'twitter');
        $this->assertNull($user);
    }

    public function testDeleteUsersByHashtagId() {
        $user_dao = DAOFactory::getDAO('UserDAO');

        $user = $user_dao->getUserByName('ecucurella', 'twitter');
        $this->assertNull($user);
        $user = $user_dao->getUserByName('vetcastellnou', 'twitter');
        $this->assertNull($user);
        $user = $user_dao->getUserByName('efectivament', 'twitter');
        $this->assertNull($user);

        $builder = $this->buildSearchData();

        $user = $user_dao->getUserByName('ecucurella', 'twitter');
        $this->assertNotNull($user);
        $this->assertEqual($user->full_name, 'Eduard Cucurella');
        $user = $user_dao->getUserByName('vetcastellnou', 'twitter');
        $this->assertNotNull($user);
        $this->assertEqual($user->full_name, 'Veterans Castellnou');
        $user = $user_dao->getUserByName('efectivament', 'twitter');
        $this->assertNotNull($user);
        $this->assertEqual($user->full_name, 'efectivament');

        $result = $user_dao->deleteUsersByHashtagId(1);
        $this->assertEqual($result,2);

        $user = $user_dao->getUserByName('ecucurella', 'twitter');
        $this->assertNull($user);
        $user = $user_dao->getUserByName('vetcastellnou', 'twitter');
        $this->assertNotNull($user);
        $this->assertEqual($user->full_name, 'Veterans Castellnou');
        $user = $user_dao->getUserByName('efectivament', 'twitter');
        $this->assertNull($user);
    }

    public function testUserUpdateWithVersions() {
        $builders = array();
        $builders[] = FixtureBuilder::build('users', $data = array('id'=>9, 'user_id'=>'99', 'user_name'=>'changey',
            'description' => 'I am static.', 'joined'=>'2007-03-06 13:48:05', 'network'=>'twitter'));
        $user = new User($data);

        $user_dao = DAOFactory::getDAO('UserDAO');
        $user_versions_dao = DAOFactory::getDAO('UserVersionsDAO');

        $changes = $user_versions_dao->getRecentVersions(9, 9999);
        $this->assertEqual(0, count($changes));
        $user_dao->updateUser($user);
        $changes = $user_versions_dao->getRecentVersions(9, 9999);
        $this->assertEqual(0, count($changes));

        $user->description = 'I am dynamic!';
        $user_dao->updateUser($user);
        $changes = $user_versions_dao->getRecentVersions(9, 9999);
        $this->assertEqual(1, count($changes));

        $user_dao->updateUser($user);
        $changes = $user_versions_dao->getRecentVersions(9, 9999);
        $this->assertEqual(1, count($changes));

        $user->description = 'I am un-dynamic!';
        $user_dao->updateUser($user);
        $changes = $user_versions_dao->getRecentVersions(9, 9999);
        $this->assertEqual(2, count($changes));

        $user->url = 'http://newurl.com/';
        $user_dao->updateUser($user);
        $changes = $user_versions_dao->getRecentVersions(9, 9999);
        $this->assertEqual(2, count($changes));

        $user->user_name = 'dynamichuman';
        $user_dao->updateUser($user);
        $changes = $user_versions_dao->getRecentVersions(9, 9999);
        $this->assertEqual(2, count($changes));
    }

    private function buildSearchData() {
        $builders = array();

        $builders[] = FixtureBuilder::build('hashtags_posts',
        array('post_id' => 1, 'hashtag_id' => 1, 'network' => 'twitter'));
        $builders[] = FixtureBuilder::build('hashtags_posts',
        array('post_id' => 3, 'hashtag_id' => 1, 'network' => 'twitter'));

        $builders[] = FixtureBuilder::build('posts', array(
            'post_id' => '1',
            'author_user_id' => '100',
            'author_username' => 'ecucurella',
            'author_fullname' => 'Eduard Cucurella',
        	'author_gender' => '',
            'author_avatar' => 'http://aa.com',
            'author_follower_count' => 0,
            'post_text' => '#Messi is the best http://flic.kr/p/ http://flic.kr/a/',
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
        	'author_gender' => '',
            'author_avatar' => 'http://aa.com',
            'author_follower_count' => 0,
            'post_text' => 'Post without any hashtag http://flic.kr/p/',
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
            'author_user_id' => '102',
            'author_username' => 'efectivament',
            'author_fullname' => 'efectivament',
        	'author_gender' => '',
            'author_avatar' => 'http://aa.com',
            'author_follower_count' => 0,
            'post_text' => 'Post with #Messi hashtag http://flic.kr/p/',
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
            'author_user_id' => '102',
            'author_username' => 'efectivament',
            'author_fullname' => 'efectivament',
        	'author_gender' => '',
            'author_avatar' => 'http://aa.com',
            'author_follower_count' => 0,
            'post_text' => 'Post without any hashtag 2',
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

        $builders[] = FixtureBuilder::build('links', array(
            'url'=>'http://flic.kr/p/',
            'title'=>'Link ',
            'post_key'=>1,
            'expanded_url'=>'',
            'error'=>'',
            'image_src'=>'http://flic.kr/thumbnail.png'));

        $builders[] = FixtureBuilder::build('links', array(
            'url'=>'http://flic.kr/a/',
            'title'=>'Link ',
            'post_key'=>1,
            'expanded_url'=>'',
            'error'=>'',
            'image_src'=>'http://flic.kr/thumbnail.png'));

        $builders[] = FixtureBuilder::build('links', array(
            'url'=>'http://flic.kr/p/',
            'title'=>'Link ',
            'post_key'=>2,
            'expanded_url'=>'',
            'error'=>'',
            'image_src'=>'http://flic.kr/thumbnail.png'));

        $builders[] = FixtureBuilder::build('links', array(
            'url'=>'http://flic.kr/p/',
            'title'=>'Link ',
            'post_key'=>3,
            'expanded_url'=>'',
            'error'=>'',
            'image_src'=>'http://flic.kr/thumbnail.png'));

        $builders[] = FixtureBuilder::build('users', array(
            'user_id'=>100,
            'user_name'=>'ecucurella',
            'full_name'=>'Eduard Cucurella'));

        $builders[] = FixtureBuilder::build('users', array(
            'user_id'=>101,
            'user_name'=>'vetcastellnou',
            'full_name'=>'Veterans Castellnou'));

        $builders[] = FixtureBuilder::build('users', array(
            'user_id'=>102,
            'user_name'=>'efectivament',
            'full_name'=>'efectivament'));

        return $builders;
    }
}
