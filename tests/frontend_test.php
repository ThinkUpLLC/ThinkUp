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

        $q = "INSERT INTO tt_instances (network_user_id, network_username, is_public) VALUES (13, 'ev', 1);";
        $this->db->exec($q);

        $counter = 0;
        while($counter < 40){
            $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT); 
            $q = "INSERT INTO tt_posts (post_id, author_user_id, author_username, author_fullname, author_avatar, post_text, source, pub_date, mention_count_cache, retweet_count_cache) VALUES (".($counter + 1000).", 13, 'ev', 'Ev Williams', 'avatar.jpg', 'This is post $counter', 'web', '2006-01-01 00:$pseudo_minute:00', ".rand(0, 4).", 5);";
            $this->db->exec($q);
            $counter++;
        }
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
    function testNextAndPreviousLinks() {
        global $TEST_SERVER_DOMAIN;

        $this->get($TEST_SERVER_DOMAIN.'/public.php');
        $this->assertTitle('ThinkTank Public Timeline');
        
        $this->assertText('This is post 39');
        $this->assertText('This is post 25');
        $this->assertText('Page 1 of 3');
        
        $this->assertLinkById("next_page");
        $this->assertNoLinkById("prev_page");
        
        $this->clickLinkById("next_page");

        $this->assertText('Page 2 of 3');
        $this->assertText('This is post 24');
        $this->assertText('This is post 10');
        $this->assertLinkById("next_page");
        $this->assertLinkById("prev_page");
       
        $this->clickLinkById("next_page");

        $this->assertNoLinkById("next_page");
        $this->assertLinkById("prev_page");
        $this->assertText('This is post 9');
        $this->assertText('This is post 0');
        $this->assertText('Page 3 of 3');
    }
    
    //TODO Write account page and status page tests
}
?>
