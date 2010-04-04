<?php 
require_once (dirname(__FILE__).'/simpletest/autorun.php');
require_once (dirname(__FILE__).'/simpletest/web_tester.php');

require_once (dirname(__FILE__).'/config.tests.inc.php');

ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);
require_once ("classes/class.ThinkTankTestCase.php");
require_once ("common/class.User.php");
require_once ("common/class.Follow.php");

class TestOfFollowDAO extends ThinkTankUnitTestCase {

    function TestOfFollowDAO() {
        $this->UnitTestCase('FollowDAO class test');
    }
    
    function setUp() {
        parent::setUp();
        
        //Insert test data into test table
        $q = "INSERT INTO tt_users (user_id, user_name, full_name, avatar) VALUES (12, 'jack', 'Jack Dorsey', 'avatar.jpg');";
        $this->db->exec($q);
        
        $q = "INSERT INTO tt_users (user_id, user_name, full_name, avatar, last_updated) VALUES (13, 'ev', 'Ev Williams', 'avatar.jpg', '1/1/2005');";
        $this->db->exec($q);
        
        $q = "INSERT INTO tt_users (user_id, user_name, full_name, avatar, is_protected) VALUES (16, 'private', 'Private Poster', 'avatar.jpg', 1);";
        $this->db->exec($q);
        
        $q = "INSERT INTO tt_user_errors (user_id, error_code, error_text, error_issued_to_user_id) VALUES (15, 404, 'User not found', 13);";
        $this->db->exec($q);
        
        $q = "INSERT INTO tt_follows (user_id, follower_id, last_seen) VALUES (13, 12, '1/1/2006');";
        $this->db->exec($q);
        
        $q = "INSERT INTO tt_follows (user_id, follower_id, last_seen) VALUES (13, 14, '1/1/2006');";
        $this->db->exec($q);
        
        $q = "INSERT INTO tt_follows (user_id, follower_id, last_seen) VALUES (13, 15, '1/1/2006');";
        $this->db->exec($q);
        
        $q = "INSERT INTO tt_follows (user_id, follower_id, last_seen) VALUES (13, 16, '1/1/2006');";
        $this->db->exec($q);
        
        $q = "INSERT INTO tt_follows (user_id, follower_id, last_seen) VALUES (16, 12, '1/1/2006');";
        $this->db->exec($q);
        
    }
    
    function tearDown() {
        parent::tearDown();
    }
    
    function testCreateNewFollowDAO() {
        $dao = new UserDAO($this->db, $this->logger);
        $this->assertTrue(isset($dao->logger), "Logger set");
        $this->assertTrue(isset($dao->db), "DB set");
        
    }
    
    function testFollowExists() {
        $dao = new FollowDAO($this->db, $this->logger);
        $this->assertTrue($dao->followExists(13, 12));
        $this->assertTrue(!$dao->followExists(12, 13));
    }
    
    function testUpdate() {
        $dao = new FollowDAO($this->db, $this->logger);
        $this->assertTrue(!$dao->update(12, 13));
        $this->assertTrue($dao->update(13, 12));
    }
    
    function testDeactivate() {
        $dao = new FollowDAO($this->db, $this->logger);
        $this->assertTrue(!$dao->deactivate(12, 13));
        $this->assertTrue($dao->deactivate(13, 12));
    }
    
    function testInsert() {
        $dao = new FollowDAO($this->db, $this->logger);
        $this->assertTrue($dao->insert(12, 13));
        $this->assertTrue($dao->followExists(12, 13));
    }
    
    function testGetUnloadedFollowerDetails() {
        $dao = new FollowDAO($this->db, $this->logger);
        $unloaded_followers = $dao->getUnloadedFollowerDetails(13);
        
        $this->assertTrue(count($unloaded_followers) == 1);
        $this->assertTrue($unloaded_followers[0]['follower_id'] == 14);
        
    }
    
    function testGetTotalFollowsWithErrors() {
        $dao = new FollowDAO($this->db, $this->logger);
        $total_follower_errors = $dao->getTotalFollowsWithErrors(13);
        
        $this->assertTrue($total_follower_errors == 1);
    }
    
    function testGetTotalFriendsWithErrors() {
        $dao = new FollowDAO($this->db, $this->logger);
        $total_friend_errors = $dao->getTotalFriendsWithErrors(13);
        
        $this->assertTrue($total_friend_errors == 0);
    }
    
    function testGetTotalFollowsWithFullDetails() {
        $dao = new FollowDAO($this->db, $this->logger);
        $total_follows_with_details = $dao->getTotalFollowsWithFullDetails(13);
        
        $this->assertTrue($total_follows_with_details == 2);
    }
    
    function testGetTotalFollowsProtected() {
        $dao = new FollowDAO($this->db, $this->logger);
        $total_follows_protected = $dao->getTotalFollowsProtected(13);
        
        $this->assertTrue($total_follows_protected == 1);
    }
    
    function testGetTotalFriends() {
        $dao = new FollowDAO($this->db, $this->logger);
        $total_friends = $dao->getTotalFriends(12);
        
        $this->assertTrue($total_friends == 2);
    }
    
    function testGetTotalFriendsProtected() {
        $dao = new FollowDAO($this->db, $this->logger);
        $total_friends_protected = $dao->getTotalFriendsProtected(12);
        
        $this->assertTrue($total_friends_protected == 1);
    }
    
    function testGetStalestFriend() {
        $dao = new FollowDAO($this->db, $this->logger);
        $stalest_friend = $dao->getStalestFriend(12);
        
        $this->assertTrue($stalest_friend != null);
        $this->assertTrue($stalest_friend->user_id == 13);
    }
    
    //TODO Complete FollowDAO tests for the rest of the methods
    
}
?>
