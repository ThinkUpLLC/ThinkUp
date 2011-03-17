<?php
/**
 *
 * ThinkUp/tests/TestOfFollowMySQLDAO.php
 *
 * Copyright (c) 2009-2011 Gina Trapani, Christoffer Viken
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
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Christoffer Viken <christoffer[at]viken[dot]me>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2011 Gina Trapani, Christoffer Viken
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

class TestOfFollowMySQLDAO extends ThinkUpUnitTestCase {
    protected $DAO;
    protected $logger;
    public function __construct() {
        $this->UnitTestCase('FollowMySQLDAO class test');
    }

    public function setUp() {
        parent::setUp();
        $this->logger = Logger::getInstance();
        $this->DAO = new FollowMySQLDAO();
        $this->builders = self::buildData();
    }

    protected function buildData() {

        $builders = array();
        //Insert test data into test table

        $builders[] = FixtureBuilder::build('users', array('user_id'=>1234567890, 'user_name'=>'jack',
        'full_name'=>'Jack Dorsey', 'avatar'=>'avatar.jpg', 'follower_count'=>150210, 'friend_count'=>124));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>1324567890, 'user_name'=>'ev',
        'full_name'=>'Ev Williams', 'avatar'=>'avatar.jpg', 'last_updated'=>'1/1/2005'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>1623457890, 'user_name'=>'private',
        'full_name'=>'Private Poster', 'avatar'=>'avatar.jpg', 'is_protected'=>1, 'follower_count'=>35342, 
        'friend_count'=>1345));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>1723457890, 'user_name'=>'facebookuser1',
        'full_name'=>'Facebook User 1', 'avatar'=>'avatar.jpg', 'is_protected'=>1, 'follower_count'=>35342, 
        'friend_count'=>1345, 'network'=>'facebook'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>1823457890, 'user_name'=>'facebookuser2',
        'full_name'=>'Facebook User 2', 'avatar'=>'avatar.jpg', 'is_protected'=>1, 'follower_count'=>35342, 
        'friend_count'=>1345, 'network'=>'facebook'));

        $builders[] = FixtureBuilder::build('user_errors', array('user_id'=>15, 'error_code'=>404,
        'error_text'=>'User not found', 'error_issued_to_user_id'=>1324567890, 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>1324567890, 'follower_id'=>1234567890,
        'last_seen'=>'2006-01-08 23:54:41', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>1324567890, 'follower_id'=>14,
        'last_seen'=>'2006-01-08 23:54:41', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>1324567890, 'follower_id'=>15,
        'last_seen'=>'2006-01-08 23:54:41', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>1324567890, 'follower_id'=>1623457890,
        'last_seen'=>'2006-01-08 23:54:41', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>1623457890, 'follower_id'=>1324567890,
        'last_seen'=>'2006-01-08 23:54:41', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>1623457890, 'follower_id'=>1234567890,
        'last_seen'=>'2006-01-08 23:54:41', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>14, 'follower_id'=>1234567890,
        'active'=>0, 'last_seen'=>'2006-01-08 23:54:41', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>1324567890, 'follower_id'=>17, 'active'=>0,
        'last_seen'=>'2006-01-08 23:54:41', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>1723457890, 'follower_id'=>1823457890,
        'active'=>1, 'last_seen'=>'2006-01-08 23:54:41', 'network'=>'facebook'));

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
        $this->assertEqual($this->DAO->update(1234567890, 1324567890, 'twitter'), 0);
        $this->assertEqual($this->DAO->update(1324567890, 1234567890, 'twitter'), 1);
        $this->assertEqual($this->DAO->update(1723457890, 1823457890, 'facebook'), 1);
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
        $this->assertEqual(count($unloaded_followers), 2);
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

    public function testGetStalestFriend() {
        $stalest_friend = $this->DAO->getStalestFriend(1234567890, 'twitter');

        $this->assertNotNull($stalest_friend);
        $this->assertEqual($stalest_friend->user_id, 1324567890);
        $this->assertEqual($stalest_friend->username, 'ev');
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
    }

    public function testGetLeastLikelyFollowers(){
        $result = $this->DAO->getLeastLikelyFollowers(1324567890, 'twitter', 15);

        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 2);
        $this->assertEqual($result[0]["user_id"], 1234567890);
        $this->assertEqual($result[1]["user_id"], 1623457890);
    }

    public function testGetEarliestJoinerFollowers(){
        $result = $this->DAO->getEarliestJoinerFollowers(1324567890, 'twitter');

        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 2);
        $this->assertEqual($result[0]['user_id'], 1234567890);
        $this->assertEqual($result[1]['user_id'], 1623457890);
    }

    public function testGetMostActiveFollowees(){
        $result = $this->DAO->getMostActiveFollowees(1234567890, 'twitter');

        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 2);
        $this->assertEqual($result[0]['user_id'], 1324567890);
        $this->assertEqual($result[1]['user_id'], 1623457890);
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
}