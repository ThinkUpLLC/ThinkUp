<?php
/**
 *
 * ThinkUp/tests/TestOfSession.php
 *
 * Copyright (c) 2009-2013 Gina Trapani, Guillaume Boudreau
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkup.com).
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
 *
 * Test of Session
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, Guillaume Boudreau
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';


class TestOfSession extends ThinkUpUnitTestCase {
    public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
    }

    public function testConstructor() {
        $session = new Session();
        $this->assertTrue(isset($session));
    }

    public function testIsNotLoggedIn() {
        $this->assertFalse(Session::isLoggedIn());
        $is = Session::isLoggedIn();
        $this->assertIsA($is, 'boolean');
        $this->assertTrue($is === false);
    }

    public function testIsLoggedIn() {
        $this->simulateLogin('me@example.com');
        $this->assertTrue(Session::isLoggedIn());
        $this->assertEqual(Session::getLoggedInUser(), 'me@example.com');
    }

    public function testIsLoggedInCookie() {
        $email = 'me@example.com';
        $this->builders[] = FixtureBuilder::build('owners', array('email' => $email));

        $this->assertFalse(Session::isLoggedIn());

        $cookie_dao = DAOFactory::getDAO('CookieDAO');
        $cookie = $cookie_dao->generateForEmail($email);

        $_COOKIE[Session::COOKIE_NAME] = $cookie;
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
        $email = 'me@example.com';
        $val = array();
        $val["id"] = 10;
        $val["user_name"] = 'testuser';
        $val["full_name"] = 'Test User';
        $val['email'] = $email;
        $val['last_login'] = '1/1/2006';
        $val["is_admin"] = 0;
        $val["is_activated"] = 1;
        $val["failed_logins"] = 0;
        $val["account_status"] = '';
        $val["timezone"] = 'America/New_York';
        $val["joined"] = date('Y-m-d');
        $val["api_key"] = '';
        $val["api_key_private"] = '';
        $val["email_notification_frequency"] = 'daily';
        $val["membership_level"] = 0;

        $cookie_dao = DAOFactory::getDAO('CookieDAO');
        $deleted = $cookie_dao->deleteByEmail($email);
        $this->assertFalse($deleted);

        $owner = new Owner($val);

        $session = new Session();
        $session->completeLogin($owner);
        $config = Config::getInstance();
        $this->assertTrue(isset($_SESSION[$config->getValue('source_root_path')]['user']));
        $this->assertEqual($_SESSION[$config->getValue('source_root_path')]['user'], $email);
        $this->assertTrue(isset($_SESSION[$config->getValue('source_root_path')]['user_is_admin']));
        $this->assertFalse($_SESSION[$config->getValue('source_root_path')]['user_is_admin']);
        // we should have a CSRF token
        $this->assertNotNull($_SESSION[$config->getValue('source_root_path')]['csrf_token']);
        $deleted = $cookie_dao->deleteByEmail($email);
        $this->assertTrue($deleted);
    }

    public function testGetCSRFToken() {
        $val = array();
        $val["id"] = 10;
        $val["user_name"] = 'testuser';
        $val["full_name"] = 'Test User';
        $val['email'] = 'me@example.com';
        $val['last_login'] = '1/1/2006';
        $val["is_admin"] = 0;
        $val["is_activated"] = 1;
        $val["failed_logins"] = 0;
        $val["account_status"] = '';
        $val["timezone"] = 'America/New_York';
        $val["joined"] = date('Y-m-d');
        $val["api_key"] = '';
        $val["api_key_private"] = '';
        $val["email_notification_frequency"] = 'daily';
        $val["membership_level"] = 0;
        $owner = new Owner($val);
        $session = new Session();
        $this->assertNull($session->getCSRFToken());
        $session->completeLogin($owner);
        $this->assertNotNull($session->getCSRFToken());
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
        $val["failed_logins"] = 0;
        $val["account_status"] = '';
        $val["timezone"] = 'America/New_York';
        $val["joined"] = date('Y-m-d');
        $val["api_key"] = '';
        $val["api_key_private"] = '';
        $val["email_notification_frequency"] = 'daily';
        $val["membership_level"] = 0;

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
        $val["failed_logins"] = 0;
        $val["account_status"] = '';
        $val["timezone"] = 'America/New_York';
        $val["joined"] = date('Y-m-d');
        $val["api_key"] = '';
        $val["api_key_private"] = '';
        $val["email_notification_frequency"] = 'daily';
        $val["membership_level"] = 0;

        $owner = new Owner($val);
        $session->completeLogin($owner);
        $this->assertTrue(Session::isLoggedIn());
        $this->assertTrue(Session::isAdmin());
        $this->assertEqual(Session::getLoggedInUser(), 'me2@example.com');
    }

    public function testLogOut() {
        $email = 'me@example.com';
        $cookie_dao = DAOFactory::getDAO('CookieDAO');
        $cookie = $cookie_dao->generateForEmail($email);
        $_COOKIE[Session::COOKIE_NAME] = $cookie;
        $this->simulateLogin($email, true);
        $session = new Session();
        $this->assertTrue(Session::isLoggedIn());
        $this->assertTrue(Session::isAdmin());
        $this->assertEqual(Session::getLoggedInUser(), $email);

        $test_email = $cookie_dao->getEmailByCookie($cookie);
        $this->assertEqual($email, $test_email);

        $session->logOut();
        $this->assertFalse(Session::isLoggedIn());
        $this->assertFalse(Session::isAdmin());
        $this->assertNull(Session::getLoggedInUser());

        $test_email = $cookie_dao->getEmailByCookie($cookie);
        $this->assertNull($test_email);
    }
}
