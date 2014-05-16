<?php
/**
 *
 * ThinkUp/tests/TestOfRegisterController.php
 *
 * Copyright (c) 2009-2013 Terrance Shepherd, Gina Trapani
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
 * Test of RegisterController
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Terrance, Shepherd, Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Terrance Shepherd
 *
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.TwitterOAuthThinkUp.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.TwitterPlugin.php';

class TestOfRegisterController extends ThinkUpUnitTestCase {

    public function setUp() {
        parent::setUp();
        $webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
        $webapp_plugin_registrar->registerPlugin('twitter', 'TwitterPlugin');
    }

    public function tearDown() {
        Config::destroyInstance();
        parent::tearDown();
    }

    public function testConstructor() {
        $controller = new RegisterController(true);
        $this->assertTrue(isset($controller));

        $results = $controller->go();
        $this->assertTrue(strpos( $results, "Register | " . Config::getInstance()->getValue('app_title_prefix') .
        "ThinkUp") > 0);
    }

    public function testAlreadyLoggedIn() {
        $owner_dao = new OwnerMySQLDAO();
        $hashed_pass = ThinkUpTestLoginHelper::hashPasswordUsingDeprecatedMethod("secretpassword");
        $owner = array('id'=>1, 'email'=>'me@example.com', 'pwd'=>$hashed_pass, 'is_activated'=>1,
        'pwd_salt'=>OwnerMySQLDAO::$default_salt);
        $builder1 = FixtureBuilder::build('owners', $owner);
        $instance = array('id'=>1, 'network_username'=>'thinkupapp');
        $builder2 = FixtureBuilder::build('instances', $instance);
        $owner_instance = array('owner_id'=>1, 'instance_id'=>1);
        $builder3 = FixtureBuilder::build('owner_instances', $owner_instance);
        $this->simulateLogin('me@example.com');

        $controller = new RegisterController(true);
        $results = $controller->go();
        $this->debug($results);
        $this->assertPattern("/".(Config::getInstance()->getValue('app_title_prefix') . "ThinkUp/"), $results);
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

    public function testPasswordPolicyTooShort() {
        // make sure registration is on...
        $bvalues = array('namespace' => OptionDAO::APP_OPTIONS, 'option_name' => 'is_registration_open',
        'option_value' => 'true');
        $bdata = FixtureBuilder::build('options', $bvalues);

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
        $error_msgs = $v_mgr->getTemplateDataItem('error_msgs');
        $this->assertEqual($error_msgs['password'], 'Password must be at least 8 characters and contain both numbers '.
        'and letters.');
        $this->assertEqual($v_mgr->getTemplateDataItem('name'), 'Angelina Jolie');
        $this->assertEqual($v_mgr->getTemplateDataItem('mail'), 'angie@example.com');
    }

    public function testPasswordPolicyNotMixed() {
        // make sure registration is on...
        $bvalues = array('namespace' => OptionDAO::APP_OPTIONS, 'option_name' => 'is_registration_open',
        'option_value' => 'true');
        $bdata = FixtureBuilder::build('options', $bvalues);

        $_POST['Submit'] = 'Register';
        $_POST['full_name'] = "Angelina Jolie";
        $_POST['email'] = 'angie@example.com';
        $_POST['user_code'] = '123456';
        $_POST['pass1'] = 'mypassnomix';
        $_POST['pass2'] = 'mypassnomix';
        $controller = new RegisterController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Register');
        $error_msgs = $v_mgr->getTemplateDataItem('error_msgs');
        $this->assertEqual($error_msgs['password'], 'Password must be at least 8 characters and contain both numbers '.
        'and letters.');
        $this->assertEqual($v_mgr->getTemplateDataItem('name'), 'Angelina Jolie');
        $this->assertEqual($v_mgr->getTemplateDataItem('mail'), 'angie@example.com');
    }

    public function testSuccessfulRegistration() {
        $config = Config::getInstance();
        $site_root_path = $config->getValue('site_root_path');
        $config->setValue('app_title_prefix', "Bo's ");

        // make sure registration is on...
        $bvalues = array('namespace' => OptionDAO::APP_OPTIONS, 'option_name' => 'is_registration_open',
        'option_value' => 'true');
        $bdata = FixtureBuilder::build('options', $bvalues);

        $_SERVER['HTTP_HOST'] = "mytestthinkup";
        $_POST['Submit'] = 'Register';
        $_POST['full_name'] = "Angelina Jolie";
        $_POST['email'] = 'angie@example.com';
        $_POST['user_code'] = '123456';
        $_POST['pass1'] = 'mypass123';
        $_POST['pass2'] = 'mypass123';
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
subject: Activate Your Account on Bo\'s ThinkUp
message: Click on the link below to activate your new account on Bo&#39;s ThinkUp:

http:\/\/mytestthinkup'.str_replace('/', '\/', $site_root_path).'session\/activate.php\?usr=angie%40example.com/';

        $actual_reg_email = Mailer::getLastMail();
        $this->debug($actual_reg_email);
        $this->assertPattern($expected_reg_email_pattern, $actual_reg_email);
    }

    public function testSuccessfulRegistrationNoAppTitlePrefix() {
        $config = Config::getInstance();
        $site_root_path = $config->getValue('site_root_path');
        $config->setValue('app_title_prefix', "");

        // make sure registration is on...
        $bvalues = array('namespace' => OptionDAO::APP_OPTIONS, 'option_name' => 'is_registration_open',
        'option_value' => 'true');
        $bdata = FixtureBuilder::build('options', $bvalues);

        $_SERVER['HTTP_HOST'] = "mytestthinkup";
        $_POST['Submit'] = 'Register';
        $_POST['full_name'] = "Angelina Jolie";
        $_POST['email'] = 'angie@example.com';
        $_POST['user_code'] = '123456';
        $_POST['pass1'] = 'mypass123';
        $_POST['pass2'] = 'mypass123';
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
subject: Activate Your Account on ThinkUp
message: Click on the link below to activate your new account on ThinkUp:

http:\/\/mytestthinkup'.str_replace('/', '\/', $site_root_path).'session\/activate.php\?usr=angie%40example.com/';

        $actual_reg_email = Mailer::getLastMail();
        $this->debug($actual_reg_email);
        $this->assertPattern($expected_reg_email_pattern, $actual_reg_email);
    }

    public function testSuccessfulRegistrationWithSSL() {
        $config = Config::getInstance();
        $site_root_path = $config->getValue('site_root_path');
        $config->setValue('app_title_prefix', "Bo's ");

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
        $_POST['pass1'] = 'mypass123';
        $_POST['pass2'] = 'mypass123';
        $controller = new RegisterController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Register');
        $this->assertEqual($v_mgr->getTemplateDataItem('success_msg'),
        'Success! Check your email for an activation link.');

        $expected_reg_email_pattern = '/to: angie@example.com
subject: Activate Your Account on Bo\'s ThinkUp
message: Click on the link below to activate your new account on Bo&#39;s ThinkUp:

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
        $_POST['pass1'] = 'm1y2p3ass';
        $_POST['pass2'] = 'm1y2p3ass';
        $config = Config::getInstance();
        $config->setValue('site_root_path', 'test url with spaces/');
        $controller = new RegisterController(true);
        $results = $controller->go();

        $email = Mailer::getLastMail();
        $this->debug("Email contents: " . $email);
        $this->assertPattern('/test\+url\+with\+spaces/', $email, 'Spaces found in activation URL.');
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
        $_POST['pass1'] = '123mypass';
        $_POST['pass2'] = '123mypass';
        $config = Config::getInstance();
        $config->setValue('site_root_path', 'test url with spaces/and/a few/slashes/too/');
        $controller = new RegisterController(true);
        $results = $controller->go();

        $email = Mailer::getLastMail();
        $this->debug("Email contents: " . $email);
        $this->assertPattern('/test\+url\+with\+spaces\/and\/a\+few\/slashes\/too/', $email,
        'Spaces properly escaped;slashes are not');
    }

    public function testValidInviteGreeting() {
        $config = Config::getInstance();
        $site_root_path = $config->getValue('site_root_path');
        $config->setValue('app_title_prefix', "Bo's ");

        $builders = array();
        // make sure registration is closed
        $builders[] = FixtureBuilder::build('options', array('namespace' => OptionDAO::APP_OPTIONS,
        'option_name' => 'is_registration_open', 'option_value' => 'false'));
        $builders[] = FixtureBuilder::build('invites', array( 'invite_code' => '012345678', 'created_time' => '-3s'));

        $_SERVER['HTTP_HOST'] = "mythinkup" ;
        $_GET['code'] = '012345678' ;
        $controller = new RegisterController(true);
        $results = $controller->go();

        $this->debug($results);
        $this->assertPattern('/Welcome, VIP! You&#39;ve been invited to register on Bo&#39;s ThinkUp./',
        $results);
    }

    public function testValidInviteGreetingNoAppTitlePrefix() {
        $config = Config::getInstance();
        $site_root_path = $config->getValue('site_root_path');
        $config->setValue('app_title_prefix', "");

        $builders = array();
        // make sure registration is closed
        $builders[] = FixtureBuilder::build('options', array('namespace' => OptionDAO::APP_OPTIONS,
        'option_name' => 'is_registration_open', 'option_value' => 'false'));
        $builders[] = FixtureBuilder::build('invites', array( 'invite_code' => '012345678', 'created_time' => '-3s'));

        $_SERVER['HTTP_HOST'] = "mythinkup" ;
        $_GET['code'] = '012345678' ;
        $controller = new RegisterController(true);
        $results = $controller->go();

        $this->debug($results);
        $this->assertPattern('/Welcome, VIP! You&#39;ve been invited to register on ThinkUp./',
        $results);
    }

    public function testInviteUser() {
        $config = Config::getInstance();
        $site_root_path = $config->getValue('site_root_path');
        $config->setValue('app_title_prefix', "Bo's ");

        $builders = array();
        // make sure registration is closed
        $builders[] = FixtureBuilder::build('options', array('namespace' => OptionDAO::APP_OPTIONS,
        'option_name' => 'is_registration_open', 'option_value' => 'false'));
        $builders[] = FixtureBuilder::build('invites', array( 'invite_code' => '012345678', 'created_time' => '-3s'));

        $_SERVER['HTTP_HOST'] = "mythinkup" ;
        $_GET['code'] = '012345678' ;
        $_POST['Submit'] = 'Register';
        $_POST['full_name'] = "Angelina Jolie";
        $_POST['email'] = 'angie@example.com';
        $_POST['user_code'] = '123456';
        $_POST['pass1'] = 'my123pass';
        $_POST['pass2'] = 'my123pass';
        $controller = new RegisterController(true);
        $results = $controller->go();

        $this->debug($results);
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Register');
        $this->assertEqual($v_mgr->getTemplateDataItem('success_msg'),
        'Success! Check your email for an activation link.');

        $expected_reg_email_pattern = '/to: angie@example.com
subject: Activate Your Account on Bo\'s ThinkUp
message: Click on the link below to activate your new account on Bo&#39;s ThinkUp:

http:\/\/mythinkup'.str_replace('/', '\/', $site_root_path).'session\/activate.php\?usr=angie%40example.com/';

        $actual_reg_email = Mailer::getLastMail();
        $this->debug($actual_reg_email);
        $this->assertPattern($expected_reg_email_pattern, $actual_reg_email);
    }

    public function testInviteUserNoAppTitlePrefix() {
        $config = Config::getInstance();
        $site_root_path = $config->getValue('site_root_path');
        $config->setValue('app_title_prefix', "");

        $builders = array();
        // make sure registration is closed
        $builders[] = FixtureBuilder::build('options', array('namespace' => OptionDAO::APP_OPTIONS,
        'option_name' => 'is_registration_open', 'option_value' => 'false'));
        $builders[] = FixtureBuilder::build('invites', array( 'invite_code' => '012345678', 'created_time' => '-3s'));

        $_SERVER['HTTP_HOST'] = "mythinkup" ;
        $_GET['code'] = '012345678' ;
        $_POST['Submit'] = 'Register';
        $_POST['full_name'] = "Angelina Jolie";
        $_POST['email'] = 'angie@example.com';
        $_POST['user_code'] = '123456';
        $_POST['pass1'] = 'my123pass';
        $_POST['pass2'] = 'my123pass';
        $controller = new RegisterController(true);
        $results = $controller->go();

        $this->debug($results);
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Register');
        $this->assertEqual($v_mgr->getTemplateDataItem('success_msg'),
        'Success! Check your email for an activation link.');

        $expected_reg_email_pattern = '/to: angie@example.com
subject: Activate Your Account on ThinkUp
message: Click on the link below to activate your new account on ThinkUp:

http:\/\/mythinkup'.str_replace('/', '\/', $site_root_path).'session\/activate.php\?usr=angie%40example.com/';

        $actual_reg_email = Mailer::getLastMail();
        $this->debug($actual_reg_email);
        $this->assertPattern($expected_reg_email_pattern, $actual_reg_email);
    }

    public function testInviteExpiredCode() {
        $config = Config::getInstance();
        $config->setValue('app_title_prefix', "Bo's ");

        // make sure registration is closed
        $bvalues = array('namespace' => OptionDAO::APP_OPTIONS, 'option_name' => 'is_registration_open',
        'option_value' => 'false');
        $bdata = FixtureBuilder::build('options', $bvalues);
        $bvalues1 = array( 'invite_code' => '012345678', 'created_time' => '-8d');
        $bdata1 = FixtureBuilder::build('invites', $bvalues1);

        $_SERVER['HTTP_HOST'] = "mythinkup/" ;
        $_GET['code'] = '012345678' ;
        $_POST['full_name'] = "Angelina Jolie";
        $_POST['Submit'] = 'Register';
        $_POST['email'] = 'angie@example.com';
        $_POST['user_code'] = '123456';
        $_POST['pass1'] = 'my12pass3';
        $_POST['pass2'] = 'my12pass3';
        $controller = new RegisterController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Register');
        $this->assertEqual($v_mgr->getTemplateDataItem('error_msg'),
        'Sorry, registration is closed on Bo\'s ThinkUp. '.
        'Try <a href="https://thinkup.com">ThinkUp.com</a>.');
    }

    public function testInviteExpiredCodeNoAppTitlePrefix() {
        $config = Config::getInstance();
        $config->setValue('app_title_prefix', "");

        // make sure registration is closed
        $bvalues = array('namespace' => OptionDAO::APP_OPTIONS, 'option_name' => 'is_registration_open',
        'option_value' => 'false');
        $bdata = FixtureBuilder::build('options', $bvalues);
        $bvalues1 = array( 'invite_code' => '012345678', 'created_time' => '-8d');
        $bdata1 = FixtureBuilder::build('invites', $bvalues1);

        $_SERVER['HTTP_HOST'] = "mythinkup/" ;
        $_GET['code'] = '012345678' ;
        $_POST['full_name'] = "Angelina Jolie";
        $_POST['Submit'] = 'Register';
        $_POST['email'] = 'angie@example.com';
        $_POST['user_code'] = '123456';
        $_POST['pass1'] = 'my12pass3';
        $_POST['pass2'] = 'my12pass3';
        $controller = new RegisterController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Register');
        $this->assertEqual($v_mgr->getTemplateDataItem('error_msg'),
        'Sorry, registration is closed on ThinkUp. '.
        'Try <a href="https://thinkup.com">ThinkUp.com</a>.');
    }

    public function testInviteInvalidCode() {
        $config = Config::getInstance();
        $config->setValue('app_title_prefix', "Bo's ");

        // make sure registration is closed
        $bvalues = array('namespace' => OptionDAO::APP_OPTIONS, 'option_name' => 'is_registration_open',
        'option_value' => 'false');
        $bdata = FixtureBuilder::build('options', $bvalues);
        $bvalues1 = array( 'invite_code' => '012345678', 'created_time' => '-8d');
        $bdata1 = FixtureBuilder::build('invites', $bvalues1);

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
        'Sorry, registration is closed on Bo\'s ThinkUp. '.
        'Try <a href="https://thinkup.com">ThinkUp.com</a>.');
    }

    public function testInviteInvalidCodeNoAppTitlePrefix() {
        $config = Config::getInstance();
        $config->setValue('app_title_prefix', "");

        // make sure registration is closed
        $bvalues = array('namespace' => OptionDAO::APP_OPTIONS, 'option_name' => 'is_registration_open',
        'option_value' => 'false');
        $bdata = FixtureBuilder::build('options', $bvalues);
        $bvalues1 = array( 'invite_code' => '012345678', 'created_time' => '-8d');
        $bdata1 = FixtureBuilder::build('invites', $bvalues1);

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
        'Sorry, registration is closed on ThinkUp. '.
        'Try <a href="https://thinkup.com">ThinkUp.com</a>.');
    }

    public function testOfThinkUpLLCRedirect() {
        $config = Config::getInstance();
        $config->setValue('thinkupllc_endpoint', 'http://example.com/user/');

        $controller = new RegisterController(true);
        $result = $controller->go();

        $this->assertEqual($controller->redirect_destination, 'http://example.com/user/');
    }
}