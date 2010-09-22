<?php
/**
 *
 * ThinkUp/tests/TestOfSession.php
 *
 * Copyright (c) 2009-2010 Gina Trapani, Dwi Widiastuti, Guillaume Boudreau
 *
 * LICENSE:
 *
 * This file is part of ThinkUp.
 * 
 * ThinkUp is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * ThinkUp is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with ThinkUp.  If not, see <http://www.gnu.org/licenses/>.
 *
*/
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

/**
 * Test of Session
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani, Dwi Widiastuti, Guillaume Boudreau
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
        $this->assertFalse(Session::isLoggedIn());
    }

    public function testIsLoggedIn() {
        $this->simulateLogin('me@example.com');
        $this->assertTrue(Session::isLoggedIn());
        $this->assertEqual(Session::getLoggedInUser(), 'me@example.com');
    }

    public function testIsNotAdmin() {
        $this->assertFalse(Session::isAdmin());

        $this->simulateLogin('me@example.com');
        $this->assertFalse(Session::isAdmin());
    }

    public function testIsAdmin() {
        $this->simulateLogin('me@example.com', true);
        $this->assertTrue(Session::isAdmin());
        $this->assertEqual(Session::getLoggedInUser(), 'me@example.com');
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
        $config = Config::getInstance();
        $this->assertTrue(isset($_SESSION[$config->getValue('source_root_path')]['user']));
        $this->assertEqual($_SESSION[$config->getValue('source_root_path')]['user'], 'me@example.com');
        $this->assertTrue(isset($_SESSION[$config->getValue('source_root_path')]['user_is_admin']));
        $this->assertFalse($_SESSION[$config->getValue('source_root_path')]['user_is_admin']);
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
        $this->assertTrue(Session::isLoggedIn());
        $this->assertFalse(Session::isAdmin());

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
        $this->assertTrue(Session::isLoggedIn());
        $this->assertTrue(Session::isAdmin());
        $this->assertEqual(Session::getLoggedInUser(), 'me2@example.com');
    }

    public function testLogOut() {
        $this->simulateLogin('me@example.com', true);
        $session = new Session();
        $this->assertTrue(Session::isLoggedIn());
        $this->assertTrue(Session::isAdmin());
        $this->assertEqual(Session::getLoggedInUser(), 'me@example.com');

        $session->logOut();
        $this->assertFalse(Session::isLoggedIn());
        $this->assertFalse(Session::isAdmin());
        $this->assertNull(Session::getLoggedInUser());
    }

    public function testIsAPICallAuthorized() {
        $builders = $this->buildData();
        $this->assertTrue(Session::isAPICallAuthorized('me@example.com', '1829cc1b13f920a05fb201e8d2a9e4dc58b669b1'));
        $this->assertFalse(Session::isAPICallAuthorized('me@example.com', '1829cc1b13f920a05fb201e8d2a9e4dc58b669b2'));
        $this->assertFalse(Session::isAPICallAuthorized('me@example.com', null));
        $this->assertFalse(Session::isAPICallAuthorized(null, '1829cc1b13f920a05fb201e8d2a9e4dc58b669b1'));
        $this->assertFalse(Session::isAPICallAuthorized(null, null));
    }

    public function testGetAPISecretFromPassword() {
        $this->assertEqual(Session::getAPISecretFromPassword('XXX'),
        '1829cc1b13f920a05fb201e8d2a9e4dc58b669b1');
        $this->assertEqual(Session::getAPISecretFromPassword(
        'abcdefghijklmnopqrstuvwxyz1234567890,.Ž;ˆ^=-/\'ƒ":é‚¬+_)(*&?%$#@\\'), 
        '450f86da4df70ba8957cb230c01c0f6c1347e19c');
    }

    private function buildData() {
        $owner_builder = FixtureBuilder::build('owners', array(
            'id' => 1, 
            'email' => 'me@example.com', 
            'pwd' => 'XXX', 
            'is_activated' => 1
        ));
         
        return array($owner_builder);
    }
}