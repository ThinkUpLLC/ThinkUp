<?php 
require_once dirname(__FILE__).'/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.User.php';

class TestOfUserDAO extends ThinkTankUnitTestCase {
    var $logger;
    
    function TestOfUserDAO() {
        $this->UnitTestCase('UserDAO class test');
    }
    
    function setUp() {
        parent::setUp();
        
        //Insert test data into test table
        $q = "INSERT INTO tt_users (user_id, user_name, full_name, avatar) VALUES (12, 'jack', 'Jack Dorsey', 'avatar.jpg');";
        $this->db->exec($q);
        $this->logger = Logger::getInstance();
    }
    
    function tearDown() {
        parent::tearDown();
        $this->logger->close();
    }
    
    function testCreateNewUserDAO() {
        $dao = new UserDAO($this->db, $this->logger);
        $this->assertTrue(isset($dao->logger), "Logger set");
        $this->assertTrue(isset($dao->db), "DB set");
        
    }
    
    function testIsUserInDB() {
        $udao = new UserDAO($this->db, $this->logger);
        $this->assertTrue($udao->isUserInDB(12));
        $this->assertTrue(!$udao->isUserInDB(13));
        
    }
    
    function testIsUserInDBByName() {
        $udao = new UserDAO($this->db, $this->logger);
        $this->assertTrue($udao->isUserInDBByName('jack'));
        $this->assertTrue(!$udao->isUserInDBByName('gina'));
        
    }
    
    function testUpdateUser() {
        $udao = new UserDAO($this->db, $this->logger);
        
        $uarr = array('id'=>2, 'user_id'=>13, 'user_name'=>'ginatrapani', 'full_name'=>'Gina Trapani', 'avatar'=>'avatar.jpg', 'location'=>'NYC', 'description'=>'Blogger', 'url'=>'http://ginatrapani.org', 'is_protected'=>0, 'follower_count'=>5000, 'post_count'=>1000, 'joined'=>'3/6/2007');
        $u = new User($uarr, 'Test');
        $this->assertTrue($udao->updateUser($u));
    }
    
    function testUpdateUsers() {
        $udao = new UserDAO($this->db, $this->logger);
        
        $user_array1 = array('id'=>2, 'user_id'=>13, 'user_name'=>'ginatrapani', 'full_name'=>'Gina Trapani', 'avatar'=>'avatar.jpg', 'location'=>'NYC', 'description'=>'Blogger', 'url'=>'http://ginatrapani.org', 'is_protected'=>0, 'follower_count'=>5000, 'post_count'=>1000, 'joined'=>'3/6/2007');
        $user1 = new User($user_array1, 'Test');
        $user_array2 = array('id'=>3, 'user_id'=>14, 'user_name'=>'anildash', 'full_name'=>'Anil Dash', 'avatar'=>'avatar.jpg', 'location'=>'NYC', 'description'=>'Blogger', 'url'=>'http://ginatrapani.org', 'is_protected'=>0, 'follower_count'=>5000, 'post_count'=>1000, 'joined'=>'3/6/2007');
        $user2 = new User($user_array2, 'Test');
        
        $users_to_update = array($user1, $user2);
        
        $this->assertTrue($udao->updateUsers($users_to_update) == 2);
    }
    
    function testGetDetailsUserExists() {
        $udao = new UserDAO($this->db, $this->logger);
        
        $user = $udao->getDetails(12);
        $this->assertTrue($user->id == 1);
        $this->assertTrue($user->user_id == 12);
        $this->assertTrue($user->username == 'jack');
    }
    
    function testGetDetailsUserDoesNotExist() {
        $udao = new UserDAO($this->db, $this->logger);
        
        $user = $udao->getDetails(13);
        
        $this->assertTrue(!isset($user));
    }
    
    function testGetUserByNameUserExists() {
        $udao = new UserDAO($this->db, $this->logger);
        
        $user = $udao->getUserByName('jack');
        $this->assertTrue($user->id == 1);
        $this->assertTrue($user->user_id == 12);
        $this->assertTrue($user->username == 'jack');
    }
    
    function testGetUserByNameUserDoesNotExist() {
        $udao = new UserDAO($this->db, $this->logger);
        
        $user = $udao->getUserByName('gina');
        $this->assertTrue(!isset($user));
    }
    
}
?>
