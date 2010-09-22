<?php
/**
 *
 * ThinkUp/tests/TestOfFollowMySQLDAO.php
 *
 * Copyright (c) 2009-2010 Gina Trapani, Christoffer Viken, Dwi Widiastuti
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
/**
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Christoffer Viken <christoffer[at]viken[dot]me>
 * @author Dwi Widiastuti <admin[at]diazuwi[dot]web[dot]id>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani, Christoffer Viken, Dwi Widiastuti
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

        //Insert test data into test table
        $q = "INSERT INTO tu_users (user_id, user_name, full_name, avatar, follower_count, friend_count)
        VALUES (1234567890, 'jack', 'Jack Dorsey', 'avatar.jpg', 150210, 124);";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tu_users (user_id, user_name, full_name, avatar, last_updated)
        VALUES (1324567890, 'ev', 'Ev Williams', 'avatar.jpg', '1/1/2005');";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tu_users (user_id, user_name, full_name, avatar, is_protected, follower_count, friend_count)
        VALUES (1623457890, 'private', 'Private Poster', 'avatar.jpg', 1, 35342, 1345);";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tu_users (user_id, user_name, full_name, avatar, is_protected, follower_count, friend_count,
        network) VALUES (1723457890, 'facebookuser1', 'Facebook User 1', 'avatar.jpg', 1, 35342, 1345, 'facebook');";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tu_users (user_id, user_name, full_name, avatar, is_protected, follower_count, friend_count,
        network) VALUES (1823457890, 'facebookuser2', 'Facebook User 2', 'avatar.jpg', 1, 35342, 1345, 'facebook');";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tu_user_errors (user_id, error_code, error_text, error_issued_to_user_id, network)
        VALUES (15, 404, 'User not found', 1324567890, 'twitter');";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tu_follows (user_id, follower_id, last_seen, network)
        VALUES (1324567890, 1234567890, '2006-01-08 23:54:41', 'twitter');";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tu_follows (user_id, follower_id, last_seen, network)
        VALUES (1324567890, 14, '2006-01-08 23:54:41', 'twitter');";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tu_follows (user_id, follower_id, last_seen, network)
        VALUES (1324567890, 15, '2006-01-08 23:54:41', 'twitter');";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tu_follows (user_id, follower_id, last_seen, network)
        VALUES (1324567890, 1623457890, '2006-01-08 23:54:41', 'twitter');";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tu_follows (user_id, follower_id, last_seen, network)
        VALUES (1623457890, 1324567890, '2006-01-08 23:54:41', 'twitter');";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tu_follows (user_id, follower_id, last_seen, network)
        VALUES (1623457890, 1234567890, '2006-01-08 23:54:41', 'twitter');";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tu_follows (user_id, follower_id, active, last_seen, network)
        VALUES (14, 1234567890, 0, '2006-01-08 23:54:41', 'twitter');";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tu_follows (user_id, follower_id, active, last_seen, network)
        VALUES (1324567890, 17, 0, '2006-01-08 23:54:41', 'twitter');";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tu_follows (user_id, follower_id, active, last_seen, network)
        VALUES (1723457890, 1823457890, 1, '2006-01-08 23:54:41', 'facebook');";
        PDODAO::$PDO->exec($q);
    }

    public function tearDown() {
        parent::tearDown();
        $this->logger->close();
        $this->DAO = null;
    }

    public function testFollowExists() {
        $this->assertTrue($this->DAO->followExists(1324567890, 1234567890, 'twitter'));
        $this->assertTrue($this->DAO->followExists(1723457890, 1823457890, 'facebook'));
        $this->assertFalse($this->DAO->followExists(1234567890, 1324567890, 'twitter'));
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
        $q = "INSERT INTO tu_follows (user_id, follower_id, last_seen, active, network)
        VALUES (930061, 20, '2001-04-08 23:54:41', 1, 'twitter');";
        PDODAO::$PDO->exec($q);

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
        $this->assertEqual($result[0]['user_id'], 1623457890);
        $this->assertEqual($result[1]['user_id'], 1324567890);
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
        $this->assertEqual($result[0]['user_id'], 1324567890);
        $this->assertEqual($result[1]['user_id'], 1623457890);
    }
}