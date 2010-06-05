<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.User.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.FollowMySQLDAO.php';

class TestOfFollowMySQLDAO extends ThinkTankUnitTestCase {
    protected $DAO;
    protected $logger;
    public function TestOfFollowMySQLDAO() {
        $this->UnitTestCase('FollowMySQLDAO class test');
    }

    public function setUp() {
        parent::setUp();
        $this->logger = Logger::getInstance();
        $this->DAO = new FollowMySQLDAO();

        //Insert test data into test table
        $q = "INSERT INTO tt_users (user_id, user_name, full_name, avatar) VALUES (12, 'jack', 'Jack Dorsey', 'avatar.jpg');";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tt_users (user_id, user_name, full_name, avatar, last_updated) VALUES (13, 'ev', 'Ev Williams', 'avatar.jpg', '1/1/2005');";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tt_users (user_id, user_name, full_name, avatar, is_protected) VALUES (16, 'private', 'Private Poster', 'avatar.jpg', 1);";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tt_user_errors (user_id, error_code, error_text, error_issued_to_user_id) VALUES (15, 404, 'User not found', 13);";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tt_follows (user_id, follower_id, last_seen) VALUES (13, 12, '2006-01-08 23:54:41');";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tt_follows (user_id, follower_id, last_seen) VALUES (13, 14, '2006-01-08 23:54:41');";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tt_follows (user_id, follower_id, last_seen) VALUES (13, 15, '2006-01-08 23:54:41');";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tt_follows (user_id, follower_id, last_seen) VALUES (13, 16, '2006-01-08 23:54:41');";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tt_follows (user_id, follower_id, last_seen) VALUES (16, 13, '2006-01-08 23:54:41');";
        PDODAO::$PDO->exec($q);


        $q = "INSERT INTO tt_follows (user_id, follower_id, last_seen) VALUES (16, 12, '2006-01-08 23:54:41');";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tt_follows (user_id, follower_id, active, last_seen) VALUES (14, 12, 0, '2006-01-08 23:54:41');";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tt_follows (user_id, follower_id, active, last_seen) VALUES (13, 17, 0, '2006-01-08 23:54:41');";
        PDODAO::$PDO->exec($q);
    }

    public function tearDown() {
        parent::tearDown();
        $this->logger->close();
        $this->DAO = null;
    }

    public function testFollowExists() {
        $this->assertTrue($this->DAO->followExists(13, 12));
        $this->assertFalse($this->DAO->followExists(12, 13));
    }

    public function testUpdate() {
        $this->assertEqual($this->DAO->update(12, 13), 0);
        $this->assertEqual($this->DAO->update(13, 12), 1);
    }

    public function testDeactivate() {
        $this->assertEqual($this->DAO->deactivate(12, 13), 0);
        $this->assertEqual($this->DAO->deactivate(13, 12), 1);
    }

    public function testInsert() {
        $this->assertEqual($this->DAO->insert(12, 14), 1);
        $this->assertTrue($this->DAO->followExists(12, 14));
    }

    public function testGetUnloadedFollowerDetails() {
        $unloaded_followers = $this->DAO->getUnloadedFollowerDetails(13);

        $this->assertIsA($unloaded_followers, "array");
        $this->assertEqual(count($unloaded_followers), 2);
        $this->assertEqual($unloaded_followers[0]['follower_id'], 17);
        $this->assertEqual($unloaded_followers[1]['follower_id'], 14);
    }

    public function testCountTotalFollowsWithErrors() {
        $total_follower_errors = $this->DAO->countTotalFollowsWithErrors(13);

        $this->assertIsA($total_follower_errors, "int");
        $this->assertEqual($total_follower_errors, 1);
    }

    public function testCountTotalFriendsWithErrors() {
        $total_friend_errors = $this->DAO->countTotalFriendsWithErrors(13);

        $this->assertIsA($total_friend_errors, "int");
        $this->assertEqual($total_friend_errors, 0);
    }

    public function testCountTotalFollowsWithFullDetails() {
        $total_follows_with_details = $this->DAO->countTotalFollowsWithFullDetails(13);

        $this->assertIsA($total_follows_with_details, "int");
        $this->assertEqual($total_follows_with_details, 2);
    }

    public function testCountTotalFollowsProtected() {
        $total_follows_protected = $this->DAO->countTotalFollowsProtected(13);

        $this->assertIsA($total_follows_protected, "int");
        $this->assertEqual($total_follows_protected, 1);
    }

    public function testCountTotalFriends() {
        $total_friends = $this->DAO->countTotalFriends(12);

        $this->assertIsA($total_friends, "int");
        $this->assertEqual($total_friends, 3);
    }

    public function testCountTotalFriendsProtected() {
        $total_friends_protected = $this->DAO->countTotalFriendsProtected(12);

        $this->assertIsA($total_friends_protected, "int");
        $this->assertEqual($total_friends_protected, 1);
    }

    public function testGetStalestFriend() {
        $stalest_friend = $this->DAO->getStalestFriend(12);

        $this->assertNotNull($stalest_friend);
        $this->assertEqual($stalest_friend->user_id, 13);
        $this->assertEqual($stalest_friend->username, 'ev');
    }

    public function testGetOldestFollow() {
        $q = "INSERT INTO tt_follows (user_id, follower_id, last_seen, active) VALUES (930061, 20, '2001-04-08 23:54:41', 1);";
        PDODAO::$PDO->exec($q);

        $oldest_follow = $this->DAO->getOldestFollow();

        $this->assertNotNull($oldest_follow);
        $this->assertEqual($oldest_follow["followee_id"], 930061);
        $this->assertEqual($oldest_follow["follower_id"], 20);
    }

    public function testGetMostFollowedFollowers(){
        $result = $this->DAO->getMostFollowedFollowers(13, 20);

        $this->assertEqual($result[0]["user_id"], 16);
        $this->assertEqual($result[1]["user_id"], 12);
    }

    public function testGetLeastLikelyFollowers(){
        //Method limited by hard coded limit, not able to test at this time.
    }

    public function testGetEarliestJoinerFollowers(){
        $result = $this->DAO->getEarliestJoinerFollowers(13);

        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 2);
        $this->assertEqual($result[0]['user_id'], 12);
        $this->assertEqual($result[1]['user_id'], 16);
    }

    public function testGetMostActiveFollowees(){
        $result = $this->DAO->getMostActiveFollowees(12);

        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 2);
        $this->assertEqual($result[0]['user_id'], 13);
        $this->assertEqual($result[1]['user_id'], 16);
    }

    public function testGetFormerFollowees(){
        $result = $this->DAO->getFormerFollowees(17);

        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 1);
        $this->assertEqual($result[0]['user_id'], 13);
    }

    public function testGetFormerFollowers(){
        $result = $this->DAO->getFormerFollowers(14);

        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 1);
        $this->assertEqual($result[0]['user_id'], 12);
    }

    public function testGetLeastActiveFollowees(){
        $result = $this->DAO->getLeastActiveFollowees(12);

        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 2);
        $this->assertEqual($result[0]['user_id'], 13);
        $this->assertEqual($result[1]['user_id'], 16);
    }

    public function testGetMostFollowedFollowees(){
        $result = $this->DAO->getMostFollowedFollowees(12);

        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 2);
        $this->assertEqual($result[0]['user_id'], 13);
        $this->assertEqual($result[1]['user_id'], 16);
    }

    public function testGetMutualFriends(){
        $result = $this->DAO->getMutualFriends(13, 12);

        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 1);
        $this->assertEqual($result[0]['user_id'], 16);
    }

    public function testGetFriendsNotFollowingBack(){
        $result = $this->DAO->getFriendsNotFollowingBack(12);

        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 2);
        $this->assertEqual($result[0]['user_id'], 13);
        $this->assertEqual($result[1]['user_id'], 16);
    }

}
?>