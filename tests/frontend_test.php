<?php 
require_once (dirname(__FILE__).'/simpletest/autorun.php');
require_once (dirname(__FILE__).'/simpletest/web_tester.php');
require_once (dirname(__FILE__).'/config.tests.inc.php');

ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once ("classes/class.ThinkTankTestCase.php");
require_once ("class.User.php");
require_once ("class.Follow.php");
require_once ("class.Session.php");


class TestOfThinkTankFrontEnd extends ThinkTankWebTestCase {

    function setUp() {
        parent::setUp();
        
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
        parent::tearDown();
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
