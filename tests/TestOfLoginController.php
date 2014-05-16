<?php
/**
 *
 * ThinkUp/tests/TestOfLoginController.php
 *
 * Copyright (c) 2009-2013 Gina Trapani
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
 * Test of LoginController
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */

require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfLoginController extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
        $this->DAO = new OwnerMySQLDAO();
        $this->builders = self::buildData();
    }

    protected function buildData() {
        $builders = array();

        $hashed_pass = ThinkUpTestLoginHelper::hashPasswordUsingDeprecatedMethod("secretpassword");

        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'email'=>'me@example.com', 'pwd'=>$hashed_pass,
        'pwd_salt'=>OwnerMySQLDAO::$default_salt, 'is_activated'=>1, 'is_admin'=>1));

        $builders[] = FixtureBuilder::build('instances', array('id'=>1));

        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>1));

        $test_salt = 'test_salt';
        $password = ThinkUpTestLoginHelper::hashPasswordUsingCurrentMethod('secretpassword', $test_salt);

        $builders[] = FixtureBuilder::build('owners', array('id'=>6, 'email'=>'salt@example.com', 'pwd'=>$password,
        'pwd_salt'=>$test_salt, 'is_activated'=>1, 'is_admin'=>1));

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
        $this->assertEqual($v_mgr->getTemplateDataItem('error_msg'), 'Hmm, that email seems wrong?');
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
        $this->assertEqual($v_mgr->getTemplateDataItem('error_msg'), 'Hmm, that password seems wrong?');
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
        $hashed_pass = ThinkUpTestLoginHelper::hashPasswordUsingDeprecatedMethod("blah");

        $owner = array('id'=>2, 'email'=>'me2@example.com', 'pwd'=>$hashed_pass, 'is_activated'=>0);
        $builder = FixtureBuilder::build('owners', $owner);

        $_POST['Submit'] = 'Log In';
        $_POST['email'] = 'me2@example.com';
        $_POST['pwd'] = 'blah';
        $controller = new LoginController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Log in');
        $error_msg = 'Inactive account. ';
        $error_msg .= '<a href="http://thinkup.com/docs/install/install.html#activate-your-account">';
        $error_msg .= 'You must activate your account.</a>';
        $this->assertEqual($error_msg, $v_mgr->getTemplateDataItem('error_msg'));
    }

    public function testCorrectUserPasswordAndUniqueSalt() {
        $_POST['Submit'] = 'Log In';
        $_POST['email'] = 'salt@example.com';
        $_POST['pwd'] = 'secretpassword';

        $controller = new LoginController(true);
        $results = $controller->go();

        $this->assertPattern("/salt@example.com/", $results);
    }

    public function testCorrectUserPasswordAndNoUniqueSalt() {
        $_POST['Submit'] = 'Log In';
        $_POST['email'] = 'me@example.com';
        $_POST['pwd'] = 'secretpassword';

        $controller = new LoginController(true);
        $results = $controller->go();
        $this->debug($results);

        $this->assertPattern("/me@example.com/", $results);
    }

    public function testAlreadyLoggedIn() {
        $this->simulateLogin('me@example.com', true);

        $controller = new LoginController(true);
        $results = $controller->go();

        $this->assertPattern('/me@example.com/', $results);
    }

    public function testFailedLoginIncrements() {
        $hashed_pass = ThinkUpTestLoginHelper::hashPasswordUsingDeprecatedMethod("blah");

        $owner = array('id'=>2, 'email'=>'me2@example.com', 'pwd'=>$hashed_pass, 'is_activated'=>1,
        'pwd_salt'=>OwnerMySQLDAO::$default_salt);
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
            $this->assertPattern("/Hmm, that password seems wrong?/", $v_mgr->getTemplateDataItem('error_msg'));
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
        $hashed_pass =ThinkUpTestLoginHelper::hashPasswordUsingDeprecatedMethod("blah");

        $owner = array('id'=>2, 'email'=>'me2@example.com', 'pwd'=>$hashed_pass, 'is_activated'=>1);
        $builder = FixtureBuilder::build('owners', $owner);

        //force login lockout by providing the wrong password more than 10 times
        $i = 1;
        while ($i <= 11) {
            $_POST['Submit'] = 'Log In';
            $_POST['email'] = 'me2@example.com';
            $_POST['pwd'] = 'blah1';
            $controller = new LoginController(true);
            $results = $controller->go();

            $v_mgr = $controller->getViewManager();
            $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Log in');

            $owner = $this->DAO->getByEmail('me2@example.com');

            if ($i < 10) {
                $this->assertPattern("/Hmm, that password seems wrong?/", $v_mgr->getTemplateDataItem('error_msg'));
                $this->assertEqual($owner->failed_logins, $i);
            } else {
                $this->assertEqual("Inactive account. Account deactivated due to too many failed logins. ".
                '<a href="forgot.php">Reset your password.</a>', $v_mgr->getTemplateDataItem('error_msg'));
                $this->assertEqual($owner->account_status, "Account deactivated due to too many failed logins");
            }
            $i = $i + 1;
        }
    }

    public function testOfControllerWithRegistrationOpen() {
        // make sure registration is on...
        $bvalues = array('namespace' => OptionDAO::APP_OPTIONS, 'option_name' => 'is_registration_open',
        'option_value' => 'true');
        $bdata = FixtureBuilder::build('options', $bvalues);

        $controller = new LoginController(true);
        $result = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('is_registration_open'), true);
        $this->assertPattern('/Register/', $result);
    }

    public function testOfControllerWithRegistrationClosed() {
        // make sure registration is closed
        $bvalues = array('namespace' => OptionDAO::APP_OPTIONS, 'option_name' => 'is_registration_open',
        'option_value' => 'false');
        $bdata = FixtureBuilder::build('options', $bvalues);

        $controller = new LoginController(true);
        $result = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('is_registration_open'), false);
        $this->assertNoPattern('/Register/', $result);
    }

    public function testOfThinkUpLLCRedirect() {
        $config = Config::getInstance();
        $config->setValue('thinkupllc_endpoint', 'http://example.com/user/');

        $controller = new LoginController(true);
        $result = $controller->go();

        $this->assertEqual($controller->redirect_destination, 'http://example.com/user/');
    }

    public function testLoginFormWithOutRedirect() {
        $_GET['redirect'] = 'http://example.com/redirect/';
        $controller = new LoginController(true);
        $results = $controller->go();
        $this->debug($results);
        $this->assertPattern( '/http\:\/\/example.com\/redirect/', $results);
    }

    public function testLoginFormWithRedirect() {
        $_GET['redirect'] = 'http://example.com/redirect/';
        $controller = new LoginController(true);
        $results = $controller->go();
        $this->debug($results);
        $this->assertPattern( '/http\:\/\/example.com\/redirect/', $results);
    }

    public function testInvalidLoginWithCustomRedirect() {
        $_POST['Submit'] = 'Log In';
        $_POST['email'] = 'dontexist@example.com';
        $_POST['pwd'] = 'secretpassword';
        $_POST['redirect'] = 'http://example.com/redirect/';
        $controller = new LoginController(true);
        $results = $controller->go();
        $this->debug($results);
        $this->assertPattern( '/http\:\/\/example.com\/redirect/', $results);
    }

    public function testValidLoginWithCustomRedirect() {
        $_POST['Submit'] = 'Log In';
        $_POST['email'] = 'me@example.com';
        $_POST['pwd'] = 'secretpassword';
        $_POST['redirect'] = 'http://example.com/redirect/';
        $controller = new LoginController(true);
        $results = $controller->go();
        $this->debug($controller->redirect_destination);
        $this->assertPattern( '/example\.com\/redirect/', $controller->redirect_destination);
    }
}
