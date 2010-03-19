<?php 
require_once (dirname(__FILE__).'/simpletest/autorun.php');


require_once (dirname(__FILE__).'/config.tests.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once ("class.MySQLDAO.php");
require_once ("class.User.php");
require_once ("class.Database.php");
require_once ("class.Logger.php");
require_once ("class.LoggerSlowSQL.php");
require_once ("config.inc.php");


class TestOfUserDAO extends UnitTestCase {
    var $logger;
    var $db;
    var $conn;
    
    function TestOfUserDAO() {
        $this->UnitTestCase('UserDAO class test');
    }
    
    function setUp() {
        global $THINKTANK_CFG;
        
        //Override default CFG values
        $THINKTANK_CFG['db_name'] = "thinktank_tests";
        
        $this->logger = new Logger($THINKTANK_CFG['log_location']);
        $this->db = new Database($THINKTANK_CFG);
        $this->conn = $this->db->getConnection();

        
        //Create all the tables based on the build script
        $create_db_script = file_get_contents($THINKTANK_CFG['source_root_path']."sql/build-db_mysql.sql");
        $create_db_script = str_replace("ALTER DATABASE thinktank", "ALTER DATABASE thinktank_tests", $create_db_script);
        $create_statements = split(";", $create_db_script);
        foreach ($create_statements as $q) {
            if (trim($q) != '') {
                $this->db->exec($q.";");
            }
        }
        
        //Insert test data into test table
        $q = "INSERT INTO tt_users (user_id, user_name, full_name, avatar) VALUES (12, 'jack', 'Jack Dorsey', 'avatar.jpg');";
        $this->db->exec($q);
        
    }
    
    function tearDown() {
        $this->logger->close();
        
        //Delete test data
        $q = "DROP TABLE `tt_follows`, `tt_instances`, `tt_links`, `tt_owners`, `tt_owner_instances`, `tt_users`, `tt_user_errors`, `tt_plugins`, `tt_plugin_options`, `tt_posts`, `tt_post_errors`, `tt_replies`;";
        $this->db->exec($q);
        
        //Clean up
        $this->db->closeConnection($this->conn);
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
        $this->assertTrue($user->user_name == 'jack');
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
        $this->assertTrue($user->user_name == 'jack');
    }
    
    function testGetUserByNameUserDoesNotExist() {
        $udao = new UserDAO($this->db, $this->logger);
        
        $user = $udao->getUserByName('gina');
        $this->assertTrue(!isset($user));
    }
    
}
?>
