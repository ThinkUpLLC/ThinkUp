<?php
/**
 *
 * ThinkUp/tests/TestOfLoginController.php
 *
 * Copyright (c) 2009-2011 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
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
 * Test of LoginController
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2011 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */

require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterOAuthThinkUp.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterPlugin.php';

class TestOfLoginController extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
        $this->DAO = new OwnerMySQLDAO();
        $this->builders = self::buildData();
        $webapp = Webapp::getInstance();
        $webapp->registerPlugin('twitter', 'TwitterPlugin');
    }
    
    protected function buildData() {
        $builders = array();
        
        $cryptpass = $this->DAO->pwdCrypt("secretpassword");
        $salt = $this->DAO->generateSalt('salt@example.com');
        $password = $this->DAO->generateUniqueSaltedPassword('secretpassword', $salt);

        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'email'=>'me@example.com', 'pwd'=>$cryptpass, 
        'salt'=>'ab194d42da0dff4a5c01ad33cb4f650a7069178b', 'is_activated'=>1, 'is_admin'=>1));
        
        $builders[] = FixtureBuilder::build('instances', array('id'=>1));
       
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>1));
        
        $builders[] = FixtureBuilder::build('owners', array('id'=>6, 'email'=>'salt@example.com', 'pwd'=>$password, 
        'pwd_salt'=>$salt, 'is_activated'=>1, 'is_admin'=>1));
        
        return $builders;
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
    }

    public function testNoSubmission() {
        $controller = new LoginController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Log in');
        $this->assertPattern("/Log In/", $results);
    }

    public function testNoEmail() {
        $_POST['Submit'] = 'Log In';
        $_POST['email'] = '';
        $_POST['pwd'] = 'somepassword';
        $controller = new LoginController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Log in');
        $this->assertEqual($v_mgr->getTemplateDataItem('error_msg'), 'Email must not be empty');
        $this->assertPattern("/Log In/", $results);
    }

    public function testNoPassword() {
        $_POST['Submit'] = 'Log In';
        $_POST['email'] = 'me@example.com';
        $_POST['pwd'] = '';
        $controller = new LoginController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Log in');
        $this->assertEqual($v_mgr->getTemplateDataItem('error_msg'), 'Password must not be empty');
        $this->assertPattern("/Log In/", $results);
    }

    public function testUserNotFound() {
        $_POST['Submit'] = 'Log In';
        $_POST['email'] = 'me1@example.com';
        $_POST['pwd'] = 'ddd';
        $controller = new LoginController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Log in');
        $this->assertEqual($v_mgr->getTemplateDataItem('error_msg'), 'Incorrect email');
        $this->assertPattern("/Log In/", $results);
    }

    public function testIncorrectPassword() {
        $_POST['Submit'] = 'Log In';
        $_POST['email'] = 'me@example.com';
        $_POST['pwd'] = 'notherightpassword';
        $controller = new LoginController(true);
        $results = $controller->go();
        
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Log in');
        $this->assertEqual($v_mgr->getTemplateDataItem('error_msg'), 'Incorrect password');
        $this->assertPattern("/Log In/", $results);
    }

    public function testCleanXSS() {
        $_POST['Submit'] = 'Log In';
        $_POST['email'] = "me@example.com <script>alert('wa');</script>";
        $_POST['pwd'] = 'notherightpassword';
        $controller = new LoginController(true);
        $results = $controller->go();
        $this->assertPattern("/me@example.com &#60;script&#62;alert\(&#39;wa&#39;\);&#60;\/script&#62;/", $results);
    }

    public function testDeactivatedUser() {
        $cryptpass = $this->DAO->pwdcrypt("blah");

        $owner = array('id'=>2, 'email'=>'me2@example.com', 'pwd'=>$cryptpass, 'is_activated'=>0);
        $builder = FixtureBuilder::build('owners', $owner);

        $_POST['Submit'] = 'Log In';
        $_POST['email'] = 'me2@example.com';
        $_POST['pwd'] = 'blah';
        $controller = new LoginController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Log in');
        $this->assertPattern("/Inactive account/", $v_mgr->getTemplateDataItem('error_msg'));
    }

    public function testCorrectUserPasswordAndUniqueSalt() {
        $_POST['Submit'] = 'Log In';
        $_POST['email'] = 'salt@example.com';
        $_POST['pwd'] = 'secretpassword';

        $controller = new LoginController(true);
        $results = $controller->go();
        
        $this->assertPattern("/Logged in as admin: salt@example.com/", $results);
    }
    
    public function testCorrectUserPasswordAndNoUniqueSalt() {
        $_POST['Submit'] = 'Log In';
        $_POST['email'] = 'me@example.com';
        $_POST['pwd'] = 'secretpassword';
        
        $controller = new LoginController(true);
        $results = $controller->go();
        
        $this->assertPattern("/Logged in as admin: me@example.com/", $results);
    }

    public function testAlreadyLoggedIn() {
        $this->simulateLogin('me@example.com', true);

        $controller = new LoginController(true);
        $results = $controller->go();

        $this->assertPattern('/Logged in as admin: me@example.com/', $results);
    }

    public function testFailedLoginIncrements() {
        $cryptpass = $this->DAO->pwdcrypt("blah");

        $owner = array('id'=>2, 'email'=>'me2@example.com', 'pwd'=>$cryptpass, 'is_activated'=>1);
        $builder = FixtureBuilder::build('owners', $owner);

        //try 5 failed logins then a successful one and assert failed login count gets reset
        $i = 1;
        while ($i <= 5) {
            $_POST['Submit'] = 'Log In';
            $_POST['email'] = 'me2@example.com';
            $_POST['pwd'] = 'incorrectpassword';
            $controller = new LoginController(true);
            $results = $controller->go();

            $v_mgr = $controller->getViewManager();
            $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Log in');
            $this->assertPattern("/Incorrect password/", $v_mgr->getTemplateDataItem('error_msg'));
            $owner = $this->DAO->getByEmail('me2@example.com');
            $this->assertEqual($owner->failed_logins, $i);
            $i = $i + 1;
        }

        $_POST['Submit'] = 'Log In';
        $_POST['email'] = 'me2@example.com';
        $_POST['pwd'] = 'blah';
        $controller = new LoginController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Log in');
        $this->assertNoPattern("/Incorrect password/", $v_mgr->getTemplateDataItem('error_msg'));
        $owner = $this->DAO->getByEmail('me2@example.com');
        $this->assertEqual($owner->failed_logins, 0);
    }

    public function testFailedLoginLockout() {
        $cryptpass =$this->DAO->pwdcrypt("blah");

        $owner = array('id'=>2, 'email'=>'me2@example.com', 'pwd'=>$cryptpass, 'is_activated'=>1);
        $builder = FixtureBuilder::build('owners', $owner);

        //force login lockout by providing the wrong password more than 10 times
        $i = 1;
        while ($i <= 15) {
            $_POST['Submit'] = 'Log In';
            $_POST['email'] = 'me2@example.com';
            $_POST['pwd'] = 'blah1';
            $controller = new LoginController(true);
            $results = $controller->go();

            $v_mgr = $controller->getViewManager();
            $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Log in');
            if ($i <= 11) {
                $this->assertPattern("/Incorrect password/", $v_mgr->getTemplateDataItem('error_msg'));
                $owner = $this->DAO->getByEmail('me2@example.com');
                $this->assertEqual($owner->failed_logins, $i);
            } else {
                $this->assertEqual("Inactive account. Account deactivated due to too many failed logins. ".
                '<a href="forgot.php">Reset your password.</a>', $v_mgr->getTemplateDataItem('error_msg'));
                $owner = $this->DAO->getByEmail('me2@example.com');
                $this->assertEqual($owner->account_status, "Account deactivated due to too many failed logins");
            }
            $i = $i + 1;
        }
    }
}

