<?php
/**
 *
 * ThinkUp/tests/TestOfFollowMySQLDAO.php
 *
 * Copyright (c) 2009-2013 Gina Trapani, Christoffer Viken
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
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Christoffer Viken <christoffer[at]viken[dot]me>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, Christoffer Viken
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfFollowMySQLDAO extends ThinkUpUnitTestCase {
    protected $DAO;
    protected $logger;

    public function setUp() {
        parent::setUp();
        $this->logger = Logger::getInstance();
        $this->DAO = new FollowMySQLDAO();
        $this->builders = self::buildData();
    }

    protected function buildData() {

        $builders = array();
        //Insert test data into test table

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'1234567890', 'user_name'=>'jack',
        'joined' => '2008-01-01',
        'full_name'=>'Jack Dorsey', 'avatar'=>'avatar.jpg', 'follower_count'=>150210, 'friend_count'=>124,
        'is_verified'=>1, 'is_protected'=>0, 'network'=>'twitter', 'description'=>'Square founder, Twitter creator'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'1324567890', 'user_name'=>'ev',
        'joined' => '2009-01-01',
        'full_name'=>'Ev Williams', 'avatar'=>'avatar.jpg', 'last_updated'=>'2005-01-01 13:58:25',
        'follower_count'=>36000, 'is_protected'=>0, 'network'=>'twitter',
        'description'=>'Former Googler, Twitter creator'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'1623457890', 'user_name'=>'private',
        'joined' => '2010-01-01',
        'full_name'=>'Private Poster', 'avatar'=>'avatar.jpg', 'is_protected'=>1, 'follower_count'=>35342,
        'is_verified'=>0, 'friend_count'=>1345));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'1723457890', 'user_name'=>'facebookuser1',
        'joined' => '2011-01-01',
        'full_name'=>'Facebook User 1', 'avatar'=>'avatar.jpg', 'is_protected'=>1, 'follower_count'=>35342,
        'friend_count'=>1345, 'network'=>'facebook'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'1823457890', 'user_name'=>'facebookuser2',
        'joined' => '2012-01-01',
        'full_name'=>'Facebook User 2', 'avatar'=>'avatar.jpg', 'is_protected'=>1, 'follower_count'=>35342,
        'friend_count'=>1345, 'network'=>'facebook'));

        $builders[] = FixtureBuilder::build('user_errors', array('user_id'=>'15', 'error_code'=>404,
        'error_text'=>'User not found', 'error_issued_to_user_id'=>'1324567890', 'network'=>'twitter'));

        //ev is followed by jack
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'1324567890', 'follower_id'=>'1234567890',
        'last_seen'=>'2006-01-08 23:54:41', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'1324567890', 'follower_id'=>'14',
        'last_seen'=>'-1d', 'first_seen'=>'-1d', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'1324567890', 'follower_id'=>'15',
        'last_seen'=>'-1d', 'first_seen'=>'-8d', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'1324567890', 'follower_id'=>'1623457890',
        'last_seen'=>'-2d', 'first_seen'=>'-2d', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'1623457890', 'follower_id'=>'1324567890',
        'last_seen'=>'2006-01-08 23:54:41', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'1623457890', 'follower_id'=>'1234567890',
        'last_seen'=>'2006-01-08 23:54:41', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'14', 'follower_id'=>'1234567890',
        'active'=>0, 'last_seen'=>'2006-01-08 23:54:41', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'1324567890', 'follower_id'=>'17', 'active'=>0,
        'last_seen'=>'2006-01-08 23:54:41', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'1723457890', 'follower_id'=>'1823457890',
        'active'=>1, 'last_seen'=>'2006-01-08 23:54:41', 'network'=>'facebook'));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'1324567890', 'follower_id'=>'1',
        'last_seen'=>'2006-01-08 23:54:41', 'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'1234567890', 'follower_id'=>'1',
        'last_seen'=>'2006-01-08 23:54:41', 'network'=>'twitter'));

        return $builders;
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
        $this->logger->close();
        $this->DAO = null;
    }

    public function testFollowExists() {
        $this->assertTrue($this->DAO->followExists(1324567890, 1234567890, 'twitter'));
        $this->assertTrue($this->DAO->followExists(1723457890, 1823457890, 'facebook'));
        $this->assertFalse($this->DAO->followExists(1234567890, 1324567890, 'twitter'));

        //inactive follow
        $this->assertFalse($this->DAO->followExists(14, 1234567890, 'twitter', true));
        $this->assertTrue($this->DAO->followExists(14, 1234567890, 'twitter'));

        //active follow
        $this->assertTrue($this->DAO->followExists(1723457890, 1823457890, 'facebook', true));
        $this->assertTrue($this->DAO->followExists(1723457890, 1823457890, 'facebook'));
    }

    public function testUpdate() {
        $this->assertEqual($this->DAO->update('1234567890', '1324567890', 'twitter'), 0);
        $this->assertEqual($this->DAO->update('1324567890', '1234567890', 'twitter'), 1);
        $this->assertEqual($this->DAO->update('1723457890', '1823457890', 'facebook'), 1);

        //Test active bit
        $q = "SELECT * FROM " . $this->table_prefix . "follows WHERE ";
        $q .= "user_id = :user_id AND follower_id = :follower_id AND network = :network ";

        $stmt = FollowMySQLDAO::$PDO->prepare($q);
        $stmt->execute(array(':user_id' => '1723457890', 'follower_id'=>'1823457890', ':network' => 'facebook'));
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEqual($data['active'], 1, 'Should be active by default, active bit not specified');

        $this->assertEqual($this->DAO->update('1723457890', '1823457890', 'facebook', false), 1);
        $stmt = FollowMySQLDAO::$PDO->prepare($q);
        $stmt->execute(array(':user_id' => '1723457890', 'follower_id'=>'1823457890', ':network' => 'facebook'));
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEqual($data['active'], 0, 'Should be inactive');

        $this->assertEqual($this->DAO->update('1723457890', '1823457890', 'facebook', true), 1);
        $stmt = FollowMySQLDAO::$PDO->prepare($q);
        $stmt->execute(array(':user_id' => '1723457890', 'follower_id'=>'1823457890', ':network' => 'facebook'));
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEqual($data['active'], 1, 'Should be active');
    }

    public function testDeactivate() {
        $this->assertEqual($this->DAO->deactivate(1234567890, 1324567890, 'twitter'), 0);
        $this->assertEqual($this->DAO->deactivate(1324567890, 1234567890, 'twitter'), 1);
        $this->assertEqual($this->DAO->deactivate(1723457890, 1823457890, 'facebook'), 1);
    }

    public function testInsert() {
        $this->assertEqual($this->DAO->insert(1234567890, 14, 'twitter'), 1);
        $this->assertTrue($this->DAO->followExists(1234567890, 14, 'twitter'));
    }

    public function testGetUnloadedFollowerDetails() {
        $unloaded_followers = $this->DAO->getUnloadedFollowerDetails(1324567890, 'twitter');

        $this->assertIsA($unloaded_followers, "array");
        $this->assertEqual(count($unloaded_followers), 3);
        $this->assertEqual($unloaded_followers[0]['follower_id'], 17);
        $this->assertEqual($unloaded_followers[1]['follower_id'], 14);
    }

    public function testCountTotalFollowsWithErrors() {
        $total_follower_errors = $this->DAO->countTotalFollowsWithErrors(1324567890, 'twitter');

        $this->assertIsA($total_follower_errors, "int");
        $this->assertEqual($total_follower_errors, 1);
    }

    public function testCountTotalFriendsWithErrors() {
        $total_friend_errors = $this->DAO->countTotalFriendsWithErrors(1324567890, 'twitter');

        $this->assertIsA($total_friend_errors, "int");
        $this->assertEqual($total_friend_errors, 0);
    }

    public function testCountTotalFollowsWithFullDetails() {
        $total_follows_with_details = $this->DAO->countTotalFollowsWithFullDetails(1324567890, 'twitter');

        $this->assertIsA($total_follows_with_details, "int");
        $this->assertEqual($total_follows_with_details, 2);
    }

    public function testCountTotalFollowsProtected() {
        $total_follows_protected = $this->DAO->countTotalFollowsProtected(1324567890, 'twitter');

        $this->assertIsA($total_follows_protected, "int");
        $this->assertEqual($total_follows_protected, 1);
    }

    public function testCountTotalFriends() {
        $total_friends = $this->DAO->countTotalFriends(1234567890, 'twitter');

        $this->assertIsA($total_friends, "int");
        $this->assertEqual($total_friends, 3);
    }

    public function testCountTotalFriendsProtected() {
        $total_friends_protected = $this->DAO->countTotalFriendsProtected(1234567890, 'twitter');

        $this->assertIsA($total_friends_protected, "int");
        $this->assertEqual($total_friends_protected, 1);
    }

    public function testGetStalestFriends() {
        $stalest_friends = $this->DAO->getStalestFriends('1234567890', 'twitter');

        $this->assertNotNull($stalest_friends);
        $this->assertEqual(count($stalest_friends), 2);
        $this->assertEqual($stalest_friends[0]->user_id, '1324567890');
        $this->assertEqual($stalest_friends[0]->username, 'ev');
    }

    public function testGetOldestFollow() {
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>930061, 'follower_id'=>20,
        'last_seen'=>'2001-04-08 23:54:41', 'active'=>1, 'network'=>'twitter'));

        $oldest_follow = $this->DAO->getOldestFollow('twitter');

        $this->assertNotNull($oldest_follow);
        $this->assertEqual($oldest_follow["followee_id"], 930061);
        $this->assertEqual($oldest_follow["follower_id"], 20);
    }

    public function testGetMostFollowedFollowers(){
        $result = $this->DAO->getMostFollowedFollowers(1324567890, 'twitter', 20);

        $this->assertEqual($result[0]["user_id"], 1234567890);
        $this->assertEqual($result[1]["user_id"], 1623457890);

        //test paging
        $result = $this->DAO->getMostFollowedFollowers(1324567890, 'twitter', 1, $page = 1);
        $this->assertEqual($result[0]["user_id"], 1234567890);

        $result = $this->DAO->getMostFollowedFollowers(1324567890, 'twitter', 1, $page = 2);
        $this->assertEqual($result[0]["user_id"], 1623457890);
    }

    public function testGetLeastLikelyFollowers(){
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'12345678911110', 'user_name'=>'jack',
        'full_name'=>'Jack Dorsey', 'avatar'=>'avatar.jpg', 'follower_count'=>150210, 'friend_count'=>124,
        'is_verified'=>1, 'is_protected'=>0, 'network'=>'twitter', 'description'=>'Square founder, Twitter creator'));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'1324567890', 'follower_id'=>'12345678911110',
        'last_seen'=>'-1d', 'network'=>'twitter'));

        $result = $this->DAO->getLeastLikelyFollowers(1324567890, 'twitter', 15);

        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 3);
        $this->assertEqual($result[0]["user_id"], 1234567890);
        $this->assertEqual($result[1]["user_id"], 12345678911110);
        $this->assertEqual($result[2]["user_id"], 1623457890);

        //test paging
        $result = $this->DAO->getLeastLikelyFollowers(1324567890, 'twitter', 1, $page = 1);
        $this->assertEqual($result[0]["user_id"], 1234567890);

        $result = $this->DAO->getLeastLikelyFollowers(1324567890, 'twitter', 1, $page = 2);
        $this->assertEqual(count($result), 1);
        $this->assertEqual($result[0]["user_id"], 12345678911110);

        $result = $this->DAO->getLeastLikelyFollowersThisWeek(1324567890, 'twitter', 15);
        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 1);
        $this->assertEqual($result[0]["user_id"], 1623457890);

        $result = $this->DAO->getLeastLikelyFollowersByDay(1324567890, 'twitter', 2);
        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 1);
        //assert the verified follower doesn't get returned
        $this->assertEqual($result[0]->user_id, 1623457890);
    }

    public function testGetVerifiedFollowersByDay() {
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'789456123', 'user_name'=>'twitteruser',
        'full_name'=>'Twitter User', 'avatar'=>'avatar.jpg', 'last_updated'=>'2005-01-01 13:58:25',
        'follower_count'=>36000, 'is_protected'=>0, 'network'=>'twitter',
        'description'=>'A test Twitter User'));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'789456123', 'follower_id'=>'1234567890',
        'last_seen'=>'-1d', 'first_seen'=>'-1d', 'network'=>'twitter'));

        $result = $this->DAO->getVerifiedFollowersByDay(789456123, 'twitter', 1);
        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 1);
        $this->assertEqual($result[0]->user_id, 1234567890);
    }

    public function testGetFollowersFromLocationByDay() {
        // User
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000768', 'user_name'=>'twitteruser',
        'full_name'=>'Twitter User', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'twitter', 'description'=>'A test Twitter User'));

        // Followers
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000769', 'user_name'=>'twitterfollower1',
        'full_name'=>'Twitter Follower One', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'twitter', 'description'=>'A test Twitter Folower', 'location'=>'San Francisco, CA'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000770', 'user_name'=>'twitterfollower2',
        'full_name'=>'Twitter Follower Two', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'twitter', 'description'=>'A test Twitter Folower', 'location'=>'San Francisco, CA'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000771', 'user_name'=>'twitterfollower3',
        'full_name'=>'Twitter Follower Three', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'twitter', 'description'=>'A test Twitter Folower', 'location'=>'San Diego, CA'));

        // Follows
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'9654000768', 'follower_id'=>'9654000769',
        'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'9654000768', 'follower_id'=>'9654000770',
        'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'9654000768', 'follower_id'=>'9654000771',
        'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter'));

        $result = $this->DAO->getFollowersFromLocationByDay(9654000768, 'twitter', 'San Francisco, CA', 0);
        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 2);
        $this->assertEqual($result[0]->user_id, 9654000769);
        $this->assertEqual($result[1]->user_id, 9654000770);
    }

    public function testGetEarliestJoinerFollowers(){
        $result = $this->DAO->getEarliestJoinerFollowers(1324567890, 'twitter');

        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 2);
        $this->assertEqual($result[0]['user_id'], 1234567890);
        $this->assertEqual($result[1]['user_id'], 1623457890);

        //test paging
        $result = $this->DAO->getEarliestJoinerFollowers(1324567890, 'twitter', 1, $page = 1);
        $this->assertEqual($result[0]['user_id'], 1234567890);

        $result = $this->DAO->getEarliestJoinerFollowers(1324567890, 'twitter', 1, $page = 2);
        $this->assertEqual($result[0]['user_id'], 1623457890);
    }

    public function testGetMostActiveFollowees(){
        $result = $this->DAO->getMostActiveFollowees(1234567890, 'twitter');

        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 2);
        $this->assertEqual($result[0]['user_id'], 1324567890);
        $this->assertEqual($result[1]['user_id'], 1623457890);

        //test paging
        $result = $this->DAO->getMostActiveFollowees(1234567890, 'twitter', 1, $page = 1);
        $this->assertEqual($result[0]['user_id'], 1324567890);

        $result = $this->DAO->getMostActiveFollowees(1234567890, 'twitter', 1, $page = 2);
        $this->assertEqual($result[0]['user_id'], 1623457890);
    }

    public function testGetFormerFollowees(){
        $result = $this->DAO->getFormerFollowees(17, 'twitter');

        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 1);
        $this->assertEqual($result[0]['user_id'], 1324567890);
    }

    public function testGetFormerFollowers(){
        $result = $this->DAO->getFormerFollowers(14, 'twitter');

        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 1);
        $this->assertEqual($result[0]['user_id'], 1234567890);
    }

    public function testGetLeastActiveFollowees(){
        $result = $this->DAO->getLeastActiveFollowees(1234567890, 'twitter');

        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 2);
        $this->assertEqual($result[0]['user_id'], 1324567890);
        $this->assertEqual($result[1]['user_id'], 1623457890);
    }

    public function testGetMostFollowedFollowees(){
        $result = $this->DAO->getMostFollowedFollowees(1234567890, 'twitter');
        $this->debug(Utils::varDumpToString($result));
        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 2);
        $this->assertEqual($result[1]['user_id'], 1623457890);
        $this->assertEqual($result[0]['user_id'], 1324567890);
    }

    public function testGetMutualFriends(){
        $result = $this->DAO->getMutualFriends(1324567890, 1234567890, 'twitter');

        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 1);
        $this->assertEqual($result[0]['user_id'], 1623457890);
    }

    public function testGetFriendsNotFollowingBack(){
        $result = $this->DAO->getFriendsNotFollowingBack(1234567890, 'twitter');

        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 2);
        $this->assertEqual($result[1]['user_id'], 1324567890);
        $this->assertEqual($result[0]['user_id'], 1623457890);
    }

    public function testGetFolloweesRepliedToThisWeekLastYear() {
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'7612345', 'user_name'=>'twitteruser',
        'full_name'=>'Twitter User', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'twitter', 'description'=>'A test Twitter User'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'7612346', 'user_name'=>'twitterfoll1',
        'full_name'=>'Twitter Follower One', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'twitter', 'description'=>'A test Twitter Follower'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'7612347', 'user_name'=>'twitterfoll2',
        'full_name'=>'Twitter Follower Two', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'twitter', 'description'=>'A test Twitter Follower'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'7612348', 'user_name'=>'twitterfoll3',
        'full_name'=>'Twitter Follower Three', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'twitter', 'description'=>'A test Twitter Follower'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'7612349', 'user_name'=>'twitterfoll4',
        'full_name'=>'Twitter Follower Four', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'twitter', 'description'=>'A test Twitter Follower'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'7612350', 'user_name'=>'twitterfoll5',
        'full_name'=>'Twitter Follower Five', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'twitter', 'description'=>'A test Twitter Follower'));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'7612346', 'follower_id'=>'7612345',
        'last_seen'=>'-1d', 'first_seen'=>'-1d', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'7612347', 'follower_id'=>'7612345',
        'last_seen'=>'-1d', 'first_seen'=>'-1d', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'7612348', 'follower_id'=>'7612345',
        'last_seen'=>'-1d', 'first_seen'=>'-1d', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'7612349', 'follower_id'=>'7612345',
        'last_seen'=>'-1d', 'first_seen'=>'-1d', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'7612350', 'follower_id'=>'7612345',
        'last_seen'=>'-1d', 'first_seen'=>'-1d', 'network'=>'twitter'));

        $time_ago_1 = date('Y-m-d H:i:s', strtotime('-370 days'));
        $time_ago_2 = date('Y-m-d H:i:s', strtotime('-369 days'));
        $time_ago_3 = date('Y-m-d H:i:s', strtotime('-367 days'));
        $time_ago_4 = date('Y-m-d H:i:s', strtotime('-130 days'));
        $time_ago_5 = date('Y-m-d H:i:s', strtotime('-20 days'));

        $builders[] = FixtureBuilder::build('posts', array('id'=>138, 'post_id'=>138, 'author_user_id'=>7612345,
        'author_username'=>'twitteruser', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>'This is a reply to a twitter post', 'source'=>'web',
        'pub_date'=>$time_ago_1, 'in_reply_to_user_id'=>7612346, 'reply_count_cache'=>0, 'is_protected'=>0));

        $builders[] = FixtureBuilder::build('posts', array('id'=>139, 'post_id'=>139, 'author_user_id'=>7612345,
        'author_username'=>'twitteruser', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>'This is a reply to a twitter post', 'source'=>'web',
        'pub_date'=>$time_ago_2, 'in_reply_to_user_id'=>7612347, 'reply_count_cache'=>0, 'is_protected'=>0));

        $builders[] = FixtureBuilder::build('posts', array('id'=>140, 'post_id'=>140, 'author_user_id'=>7612345,
        'author_username'=>'twitteruser', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>'This is a reply to a twitter post', 'source'=>'web',
        'pub_date'=>$time_ago_3, 'in_reply_to_user_id'=>7612348, 'reply_count_cache'=>0, 'is_protected'=>0));

        $builders[] = FixtureBuilder::build('posts', array('id'=>141, 'post_id'=>141, 'author_user_id'=>7612345,
        'author_username'=>'twitteruser', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>'This is a reply to a twitter post', 'source'=>'web',
        'pub_date'=>$time_ago_4, 'in_reply_to_user_id'=>7612349, 'reply_count_cache'=>0, 'is_protected'=>0));

        $builders[] = FixtureBuilder::build('posts', array('id'=>142, 'post_id'=>142, 'author_user_id'=>7612345,
        'author_username'=>'twitteruser', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>'This is a reply to a twitter post', 'source'=>'web',
        'pub_date'=>$time_ago_5, 'in_reply_to_user_id'=>7612350, 'reply_count_cache'=>0, 'is_protected'=>0));

        $result = $this->DAO->getFolloweesRepliedToThisWeekLastYear(7612345, 'twitter');

        $this->debug(Utils::varDumpToString($result));
        $this->assertIsA($result, "array");
        $this->assertIsA($result[0], "User");
        $this->assertEqual(count($result), 3);
        $this->assertEqual($result[0]->full_name, "Twitter Follower One");
        $this->assertEqual($result[1]->full_name, "Twitter Follower Two");
        $this->assertEqual($result[2]->full_name, "Twitter Follower Three");
    }

    public function testSearchFollowers(){
        $result = $this->DAO->searchFollowers($keywords=array("Square", "name:jack"),
        $network="twitter", $user_id="1324567890");

        $this->debug(Utils::varDumpToString($result));
        $this->assertIsA($result, "array");
        $this->assertIsA($result[0], "User");
        $this->assertEqual(count($result), 1);
        $this->assertEqual($result[0]->full_name, "Jack Dorsey");
    }

    public function testGetFriendsJoinedInTimeFrame() {
        $result = $this->DAO->getFriendsJoinedInTimeFrame(1234567890, 'twitter', '2008-02-01', '2010-02-01');
        $this->assertEqual(1, count($result));
        $this->assertEqual("ev", $result[0]->username);

        $result = $this->DAO->getFriendsJoinedInTimeFrame(1, 'twitter', '2008-02-01', '2011-02-01');
        $this->assertEqual(1, count($result));
        $this->assertEqual("ev", $result[0]->username);

        $result = $this->DAO->getFriendsJoinedInTimeFrame(1, 'twitter', '2006-02-01', '2011-02-01');
        $this->assertEqual(2, count($result));
        $this->assertEqual("jack", $result[0]->username);
        $this->assertEqual("ev", $result[1]->username);
    }

    public function testCountTotalFriendsJoinedAfterDate() {
        $result = $this->DAO->countTotalFriendsJoinedAfterDate(1234567890, 'twitter', '2009-02-01');
        $this->assertEqual(1, $result);
        $result = $this->DAO->countTotalFriendsJoinedAfterDate(1234567890, 'twitter', '2008-02-01');
        $this->assertEqual(2, $result);
        $result = $this->DAO->countTotalFriendsJoinedAfterDate(1234567890, 'twitter', '2018-02-01');
        $this->assertEqual(0, $result);
        $result = $this->DAO->countTotalFriendsJoinedAfterDate(1234567890, 'facebook', '1812-02-01');
        $this->assertEqual(0, $result);
    }

    public function testGetVerifiedFollowers() {
        $result = $this->DAO->getVerifiedFollowers(1234567890, 'twitter');
        $this->assertEqual(count($result), 0);

        $result = $this->DAO->getVerifiedFollowers(1324567890, 'twitter');
        $this->assertEqual(count($result), 1);
        $this->assertEqual($result[0]->username, 'jack');

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'999', 'user_name'=>'v2',
        'full_name'=>'Jack Dorsey', 'avatar'=>'avatar.jpg', 'follower_count'=>999999, 'friend_count'=>12,
        'is_verified'=>1, 'is_protected'=>0, 'network'=>'twitter', 'description'=>'Test'));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'1324567890', 'follower_id'=>'999',
        'last_seen'=>'-1d', 'network'=>'twitter'));

        $result = $this->DAO->getVerifiedFollowers(1324567890, 'twitter');
        $this->assertEqual(count($result), 2);
        $this->assertEqual($result[0]->username, 'v2');
        $this->assertEqual($result[1]->username, 'jack');

        $result = $this->DAO->getVerifiedFollowers(1324567890, 'twitter', 1);
        $this->assertEqual(count($result), 1);
        $this->assertEqual($result[0]->username, 'v2');

    }

    public function testGetVerifiedFollowerCount() {
        $result = $this->DAO->getVerifiedFollowerCount(1234567890, 'twitter');
        $this->assertEqual($result, 0);

        $result = $this->DAO->getVerifiedFollowerCount(1324567890, 'twitter');
        $this->assertEqual($result, 1);

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'999', 'user_name'=>'v2',
        'full_name'=>'Jack Dorsey', 'avatar'=>'avatar.jpg', 'follower_count'=>999999, 'friend_count'=>12,
        'is_verified'=>1, 'is_protected'=>0, 'network'=>'twitter', 'description'=>'Test'));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'1324567890', 'follower_id'=>'999',
        'last_seen'=>'-1d', 'network'=>'twitter'));

        $result = $this->DAO->getVerifiedFollowerCount(1324567890, 'twitter');
        $this->assertEqual($result, 2);
    }
}
