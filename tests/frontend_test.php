<?php 
require_once (dirname(__FILE__).'/simpletest/autorun.php');
require_once (dirname(__FILE__).'/simpletest/web_tester.php');
require_once (dirname(__FILE__).'/config.tests.inc.php');

ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once ("class.MySQLDAO.php");
require_once ("class.User.php");
require_once ("class.Database.php");
require_once ("class.Logger.php");
require_once ("class.LoggerSlowSQL.php");
require_once ("class.Follow.php");
require_once ("class.Session.php");
require_once ("config.inc.php");


class TestOfThinkTankFrontEnd extends WebTestCase {
    var $logger;
    var $db;
    var $conn;
    
    function setUp() {
        global $THINKTANK_CFG;
        
        $this->logger = new Logger($THINKTANK_CFG['log_location']);
        
        //Override default CFG value for the database to use tests DB
        $THINKTANK_CFG['db_name'] = "thinktank_tests";
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
        
        //Add owner
        $session = new Session();
        $cryptpass = $session->pwdcrypt("secretpassword");
        $q = "INSERT INTO tt_owners (id, user_email, user_pwd, user_activated) VALUES (1, 'me@example.com', '".$cryptpass."', 1)";
        $this->db->exec($q);
        
        //Add instance
        $q = "INSERT INTO tt_instances (id, network_user_id, network_username, is_public) VALUES (1, 1234, 'thinktankapp', 1)";
        $this->db->exec($q);
        
        //Add instance_owner
        $q = "INSERT INTO tt_owner_instances (owner_id, instance_id) VALUES (1, 1)";
        $this->db->exec($q);
        
        //Insert test data into test table
        $q = "INSERT INTO tt_users (user_id, user_name, full_name, avatar) VALUES (12, 'jack', 'Jack Dorsey', 'avatar.jpg');";
        $this->db->exec($q);
        
        $q = "INSERT INTO tt_users (user_id, user_name, full_name, avatar, last_updated) VALUES (13, 'ev', 'Ev Williams', 'avatar.jpg', '1/1/2005');";
        $this->db->exec($q);
        
        $q = "INSERT INTO tt_users (user_id, user_name, full_name, avatar, is_protected) VALUES (16, 'private', 'Private Poster', 'avatar.jpg', 1);";
        $this->db->exec($q);
        
        $q = "INSERT INTO tt_users (user_id, user_name, full_name, avatar, is_protected, follower_count) VALUES (17, 'thinktankapp', 'ThinkTankers', 'avatar.jpg', 0, 10);";
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
        $this->logger->close();
        
        //Delete test data
        $q = "DROP TABLE `tt_follows`, `tt_instances`, `tt_links`, `tt_owners`, `tt_owner_instances`, `tt_users`, `tt_user_errors`, `tt_plugins`, `tt_plugin_options`, `tt_posts`, `tt_post_errors`, `tt_replies`;";
        $this->db->exec($q);
        
        //Clean up
        $this->db->closeConnection($this->conn);
    }
    
    function testPublicTimeline() {
        global $TEST_SERVER_DOMAIN;
        
        $this->get($TEST_SERVER_DOMAIN.'/public.php');
        $this->assertTitle('ThinkTank Public Timeline');
        $this->assertText('Log In');
        $this->click('Log In');
        $this->assertTitle('ThinkTank Sign In');
    }

    
    function testSignInAndPrivateDashboard() {
        global $TEST_SERVER_DOMAIN;
        
        $this->get($TEST_SERVER_DOMAIN.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword');
        
        $this->click("Login");
        $this->assertTitle('ThinkTank');
        $this->assertText('Logged in as: me@example.com');
        
    }
    
    function testUserPage() {
        global $TEST_SERVER_DOMAIN;
        
        $this->get($TEST_SERVER_DOMAIN.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword');
        
        $this->click("Login");
        $this->assertTitle('ThinkTank');
        
        $this->get($TEST_SERVER_DOMAIN.'/user/index.php?i=thinktankapp&u=ev');
        $this->assertTitle('ThinkTank');
        $this->assertText('Logged in as: me@example.com');
        $this->assertText('ev');
        
        $this->get($TEST_SERVER_DOMAIN.'/user/index.php?i=thinktankapp&u=usernotinsystem');
        $this->assertText('This user is not in the system.');
        
    }
    
    //TODO Write account page and status page tests
}
?>
