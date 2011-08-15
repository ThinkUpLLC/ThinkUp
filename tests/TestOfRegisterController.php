<?php
/**
 *
 * ThinkUp/tests/TestOfRegisterController.php
 *
 * Copyright (c) 2009-2011 Terrance Shepherd, Gina Trapani
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
 * Test of RegisterController
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2011 Terrance, Shepherd, Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Terrance Shepherd
 *
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterOAuthThinkUp.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterPlugin.php';

class TestOfRegisterController extends ThinkUpUnitTestCase {

    public function setUp() {
        parent::setUp();
        $webapp = Webapp::getInstance();
        $webapp->registerPlugin('twitter', 'TwitterPlugin');
    }

    public function tearDown() {
        Config::destroyInstance();
        parent::tearDown();
    }

    public function testConstructor() {
        $controller = new RegisterController(true);
        $this->assertTrue(isset($controller));

        $results = $controller->go();
        $this->assertTrue(strpos( $results, "Register | ThinkUp") > 0);
    }

    public function testAlreadyLoggedIn() {
        $owner_dao = new OwnerMySQLDAO();
        $cryptpass = $owner_dao->pwdcrypt("secretpassword");
        $owner = array('id'=>1, 'email'=>'me@example.com', 'pwd'=>$cryptpass, 'is_activated'=>1);
        $builder1 = FixtureBuilder::build('owners', $owner);
        $instance = array('id'=>1, 'network_username'=>'thinkupapp');
        $builder2 = FixtureBuilder::build('instances', $instance);
        $owner_instance = array('owner_id'=>1, 'instance_id'=>1);
        $builder3 = FixtureBuilder::build('owner_instances', $owner_instance);
        $this->simulateLogin('me@example.com');

        $controller = new RegisterController(true);
        $results = $controller->go();
        $this->assertPattern("/thinkupapp's Dashboard | ThinkUp/", $results);
    }

    public function testAllMissingFields() {
        // make sure registration is on...
        $bvalues = array('namespace' => OptionDAO::APP_OPTIONS, 'option_name' => 'is_registration_open',
        'option_value' => 'true');
        $bdata = FixtureBuilder::build('options', $bvalues);

        $_POST['Submit'] = 'Register';
        $controller = new RegisterController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Register');
        $this->assertEqual($v_mgr->getTemplateDataItem('error_msg'), 'Please fill out all required fields.');
    }

    public function testSomeMissingFields() {
        // make sure registration is on...
        $bvalues = array('namespace' => OptionDAO::APP_OPTIONS, 'option_name' => 'is_registration_open',
        'option_value' => 'true');
        $bdata = FixtureBuilder::build('options', $bvalues);

        $_POST['Submit'] = 'Register';
        $_POST['full_name'] = "Angelina Jolie";
        $_POST['email'] = 'angie@example.com';
        $controller = new RegisterController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Register');
        $this->assertEqual($v_mgr->getTemplateDataItem('error_msg'), 'Please fill out all required fields.');
        $this->assertEqual($v_mgr->getTemplateDataItem('name'), 'Angelina Jolie');
        $this->assertEqual($v_mgr->getTemplateDataItem('mail'), 'angie@example.com');
    }

    public function testMismatchedPasswords() {
        // make sure registration is on...
        $bvalues = array('namespace' => OptionDAO::APP_OPTIONS, 'option_name' => 'is_registration_open',
        'option_value' => 'true');
        $bdata = FixtureBuilder::build('options', $bvalues);

        $_POST['Submit'] = 'Register';
        $_POST['full_name'] = "Angelina Jolie";
        $_POST['email'] = 'angie@example.com';
        $_POST['user_code'] = '123456';
        $_POST['pass1'] = 'mypass';
        $_POST['pass2'] = 'mmypass';
        $controller = new RegisterController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Register');
        $error_msgs = $v_mgr->getTemplateDataItem('error_msgs');
        $this->assertEqual($error_msgs['password'], 'Passwords do not match.');
        $this->assertEqual($v_mgr->getTemplateDataItem('name'), 'Angelina Jolie');
        $this->assertEqual($v_mgr->getTemplateDataItem('mail'), 'angie@example.com');
    }

    public function testSuccessfulRegistration() {
        $config = Config::getInstance();
        $site_root_path = $config->getValue('site_root_path');

        // make sure registration is on...
        $bvalues = array('namespace' => OptionDAO::APP_OPTIONS, 'option_name' => 'is_registration_open',
        'option_value' => 'true');
        $bdata = FixtureBuilder::build('options', $bvalues);

        $_SERVER['HTTP_HOST'] = "mytestthinkup";
        $_POST['Submit'] = 'Register';
        $_POST['full_name'] = "Angelina Jolie";
        $_POST['email'] = 'angie@example.com';
        $_POST['user_code'] = '123456';
        $_POST['pass1'] = 'mypass';
        $_POST['pass2'] = 'mypass';
        $controller = new RegisterController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Register');
        $this->debug($v_mgr->getTemplateDataItem('success_msg'));
        $this->debug($v_mgr->getTemplateDataItem('error_msg'));
        $this->debug(Utils::varDumpToString($v_mgr->getTemplateDataItem('error_msgs')));
        $this->assertEqual($v_mgr->getTemplateDataItem('success_msg'),
        'Success! Check your email for an activation link.');

        $expected_reg_email_pattern = '/to: angie@example.com
subject: Activate Your ThinkUp Account
message: Click on the link below to activate your new ThinkUp account:

http:\/\/mytestthinkup'.str_replace('/', '\/', $site_root_path).'session\/activate.php\?usr=angie%40example.com/';

        $actual_reg_email = Mailer::getLastMail();
        $this->debug($actual_reg_email);
        $this->assertPattern($expected_reg_email_pattern, $actual_reg_email);
    }

    public function testSuccessfulRegistrationWithSSL() {
        $config = Config::getInstance();
        $site_root_path = $config->getValue('site_root_path');

        // make sure registration is on...
        $bvalues = array('namespace' => OptionDAO::APP_OPTIONS, 'option_name' => 'is_registration_open',
        'option_value' => 'true');
        $bdata = FixtureBuilder::build('options', $bvalues);

        $_SERVER['HTTP_HOST'] = "mytestthinkup";
        $_SERVER['HTTPS'] = true;
        $_POST['Submit'] = 'Register';
        $_POST['full_name'] = "Angelina Jolie";
        $_POST['email'] = 'angie@example.com';
        $_POST['user_code'] = '123456';
        $_POST['pass1'] = 'mypass';
        $_POST['pass2'] = 'mypass';
        $controller = new RegisterController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Register');
        $this->assertEqual($v_mgr->getTemplateDataItem('success_msg'),
        'Success! Check your email for an activation link.');

        $expected_reg_email_pattern = '/to: angie@example.com
subject: Activate Your ThinkUp Account
message: Click on the link below to activate your new ThinkUp account:

https:\/\/mytestthinkup'.str_replace('/', '\/', $site_root_path).'session\/activate.php\?usr=angie%40example.com/';

        $actual_reg_email = Mailer::getLastMail();
        $this->debug($actual_reg_email);
        $this->assertPattern($expected_reg_email_pattern, $actual_reg_email);
    }

    public function testSpaceInHostName() {
        // make sure registration is on...
        $bvalues = array('namespace' => OptionDAO::APP_OPTIONS, 'option_name' => 'is_registration_open',
        'option_value' => 'true');
        $bdata = FixtureBuilder::build('options', $bvalues);

        $_SERVER['HTTP_HOST'] = "mytestthinkup/";
        $_POST['Submit'] = 'Register';
        $_POST['full_name'] = "Angelina Jolie";
        $_POST['email'] = 'angie@example.com';
        $_POST['user_code'] = '123456';
        $_POST['pass1'] = 'mypass';
        $_POST['pass2'] = 'mypass';
        $config = Config::getInstance();
        $config->setValue('site_root_path', 'test url with spaces/');
        $controller = new RegisterController(true);
        $results = $controller->go();

        $email = Mailer::getLastMail();
        $this->debug("Email contents: " . $email);
        $this->assertPattern('/test%20url%20with%20spaces/', $email, 'Spaces found in activation URL.');
    }

    public function testSlashesInHostName() {
        // make sure registration is on...
        $bvalues = array('namespace' => OptionDAO::APP_OPTIONS, 'option_name' => 'is_registration_open',
        'option_value' => 'true');
        $bdata = FixtureBuilder::build('options', $bvalues);

        $_SERVER['HTTP_HOST'] = "mytestthinkup/";
        $_POST['Submit'] = 'Register';
        $_POST['full_name'] = "Angelina Jolie";
        $_POST['email'] = 'angie@example.com';
        $_POST['user_code'] = '123456';
        $_POST['pass1'] = 'mypass';
        $_POST['pass2'] = 'mypass';
        $config = Config::getInstance();
        $config->setValue('site_root_path', 'test url with spaces/and/a few/slashes/too/');
        $controller = new RegisterController(true);
        $results = $controller->go();

        $email = Mailer::getLastMail();
        $this->debug("Email contents: " . $email);
        $this->assertPattern('/test%20url%20with%20spaces\/and\/a%20few\/slashes\/too/', $email,
        'Spaces properly escaped;slashes are not');
    }

    public function testInviteUser() {
        $config = Config::getInstance();
        $site_root_path = $config->getValue('site_root_path');

        $builders = array();
        // make sure registration is closed
        $builders[] = FixtureBuilder::build('options', array('namespace' => OptionDAO::APP_OPTIONS,
        'option_name' => 'is_registration_open', 'option_value' => 'false'));
        $builders[] = FixtureBuilder::build('invites', array( 'invite_code' => '0123456789', 'created_time' => '-3s'));

        $_SERVER['HTTP_HOST'] = "mythinkup" ;
        $_GET['code'] = '0123456789' ;
        $_POST['Submit'] = 'Register';
        $_POST['full_name'] = "Angelina Jolie";
        $_POST['email'] = 'angie@example.com';
        $_POST['user_code'] = '123456';
        $_POST['pass1'] = 'mypass';
        $_POST['pass2'] = 'mypass';
        $controller = new RegisterController(true);
        $results = $controller->go();

        $this->debug($results);
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Register');
        $this->assertEqual($v_mgr->getTemplateDataItem('success_msg'),
        'Success! Check your email for an activation link.');

        $expected_reg_email_pattern = '/to: angie@example.com
subject: Activate Your ThinkUp Account
message: Click on the link below to activate your new ThinkUp account:

http:\/\/mythinkup'.str_replace('/', '\/', $site_root_path).'session\/activate.php\?usr=angie%40example.com/';

        $actual_reg_email = Mailer::getLastMail();
        $this->debug($actual_reg_email);
        $this->assertPattern($expected_reg_email_pattern, $actual_reg_email);
    }

    public function testInviteExpiredCode() {
        // make sure registration is closed
        $bvalues = array('namespace' => OptionDAO::APP_OPTIONS, 'option_name' => 'is_registration_open',
        'option_value' => 'false');
        $bdata = FixtureBuilder::build('options', $bvalues);
        $bvalues1 = array( 'invite_code' => '0123456789', 'created_time' => '-8d');
        $bdata1 = FixtureBuilder::build('invites', $bvalues);

        $_SERVER['HTTP_HOST'] = "mythinkup/" ;
        $_GET['code'] = '0123456789' ;
        $_POST['full_name'] = "Angelina Jolie";
        $_POST['Submit'] = 'Register';
        $_POST['email'] = 'angie@example.com';
        $_POST['user_code'] = '123456';
        $_POST['pass1'] = 'mypass';
        $_POST['pass2'] = 'mypass';
        $controller = new RegisterController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Register');
        $this->assertEqual($v_mgr->getTemplateDataItem('error_msg'),
        '<p>Sorry, registration is closed on this ThinkUp installation.</p>'.
        '<p><a href="http://thinkupapp.com">Install ThinkUp on your own '.
        'server.</a></p>');
    }

    public function testInviteInvalidCode() {
        // make sure registration is closed
        $bvalues = array('namespace' => OptionDAO::APP_OPTIONS, 'option_name' => 'is_registration_open',
        'option_value' => 'false');
        $bdata = FixtureBuilder::build('options', $bvalues);
        $bvalues1 = array( 'invite_code' => '0123456789', 'created_time' => '-8d');
        $bdata1 = FixtureBuilder::build('invites', $bvalues);

        $_SERVER['HTTP_HOST'] = "mythinkup/" ;
        $_GET['code'] = '9876543210' ;
        $_POST['Submit'] = 'Register';
        $_POST['full_name'] = "Angelina Jolie";
        $_POST['email'] = 'angie@example.com';
        $_POST['user_code'] = '123456';
        $_POST['pass1'] = 'mypass';
        $_POST['pass2'] = 'mypass';
        $controller = new RegisterController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Register');
        $this->assertEqual($v_mgr->getTemplateDataItem('error_msg'),
        '<p>Sorry, registration is closed on this ThinkUp installation.</p>'.
        '<p><a href="http://thinkupapp.com">Install ThinkUp on your own '.
        'server.</a></p>');
    }
}