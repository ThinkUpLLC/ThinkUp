<?php
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

/**
 * Test of Session
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfSession extends ThinkUpUnitTestCase {
    var $builder1;
    var $builder2;
    var $builder3;

    public function __construct() {
        $this->UnitTestCase('Session class test');
    }

    public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $session = new Session();
        $this->assertTrue(isset($session));
    }

    public function testIsNotLoggedIn() {
        $session = new Session();
        $this->assertFalse($session->isLoggedIn());
    }

    public function testIsLoggedIn() {
        $_SESSION['user'] = 'me@example.com';
        $session = new Session();
        $this->assertTrue($session->isLoggedIn());
    }

    public function testIsNotAdmin() {
        $session = new Session();
        $this->assertFalse($session->isAdmin());

        $_SESSION['user'] = 'me@example.com';
        $this->assertFalse($session->isAdmin());
    }

    public function testIsAdmin() {
        $_SESSION['user'] = 'me@example.com';
        $_SESSION['user_is_admin'] = true;
        $session = new Session();
        $this->assertTrue($session->isAdmin());
    }

    public function testCompleteLogin() {
        $val = array();
        $val["id"] = 10;
        $val["user_name"] = 'testuser';
        $val["full_name"] = 'Test User';
        $val['email'] = 'me@example.com';
        $val['last_login'] = '1/1/2006';
        $val["is_admin"] = 0;
        $val["is_activated"] = 1;

        $owner = new Owner($val);

        $session = new Session();
        $session->completeLogin($owner);
        $this->assertTrue(isset($_SESSION['user']));
        $this->assertEqual($_SESSION['user'], 'me@example.com');
        $this->assertTrue(isset($_SESSION['user_is_admin']));
        $this->assertFalse($_SESSION['user_is_admin']);
        //        $cryptpass = $session->pwdcrypt("secretpassword");
        //
        //        $owner = array('id'=>1, 'email'=>'me@example.com', 'pwd'=>$cryptpass, 'is_activated'=>1);
        //        $this->builder1 = FixtureBuilder::build('owners', $owner);
    }

    public function testCompleteLoginAndIsLoggedInIsAdmin() {
        $val = array();
        $val["id"] = 10;
        $val["user_name"] = 'testuser';
        $val["full_name"] = 'Test User';
        $val['email'] = 'me@example.com';
        $val['last_login'] = '1/1/2006';
        $val["is_admin"] = 0;
        $val["is_activated"] = 1;

        $owner = new Owner($val);

        $session = new Session();
        $session->completeLogin($owner);
        $this->assertTrue($session->isLoggedIn());
        $this->assertFalse($session->isAdmin());

        $val = array();
        $val["id"] = 11;
        $val["user_name"] = 'testuser';
        $val["full_name"] = 'Test User2';
        $val['email'] = 'me2@example.com';
        $val['last_login'] = '1/1/2006';
        $val["is_admin"] = 1;
        $val["is_activated"] = 1;

        $owner = new Owner($val);
        $session->completeLogin($owner);
        $this->assertTrue($session->isLoggedIn());
        $this->assertTrue($session->isAdmin());
    }

    public function testLogOut() {
        $_SESSION['user'] = 'me@example.com';
        $_SESSION['user_is_admin'] = true;
        $session = new Session();
        $this->assertTrue($session->isLoggedIn());
        $this->assertTrue($session->isAdmin());

        $session->logOut();
        $this->assertFalse($session->isLoggedIn());
        $this->assertFalse($session->isAdmin());
    }
}