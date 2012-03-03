<?php
/**
 *
 * ThinkUp/tests/TestOfAccountConfigurationController.php
 *
 * Copyright (c) 2009-2012 Gina Trapani, Terrance Shepherd
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
 *
 * Test of AccountConfigurationController
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2012 Gina Trapani, Terrance Shepherd
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Terrance Shepherd
 *
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

if (isset($RUNNING_ALL_TESTS) && !$RUNNING_ALL_TESTS) {
    require_once THINKUP_WEBAPP_PATH.'plugins/twitter/extlib/twitteroauth/twitteroauth.php';
}
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.TwitterOAuthThinkUp.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.TwitterPlugin.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/controller/class.TwitterPluginConfigurationController.php';

class TestOfAccountConfigurationController extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
        $webapp = Webapp::getInstance();
        $webapp->registerPlugin('twitter', 'TwitterPlugin');
        $this->builders = self::buildData();
        $_SERVER['HTTP_HOST'] = "mytesthost";
        $_SERVER['SERVER_NAME'] = 'dev.thinkup.com';
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
    }

    protected function buildData() {
        $builders = array();

        //Add owner
        $hashed_pass = ThinkUpTestLoginHelper::hashPasswordUsingDeprecatedMethod("oldpassword");

        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User',
        'email'=>'me@example.com', 'is_activated'=>1, 'pwd'=>$hashed_pass,
        'pwd_salt'=> OwnerMySQLDAO::$default_salt, 'api_key' => 'c9089f3c9adaf0186f6ffb1ee8d6501c'));

        $builders[] = FixtureBuilder::build('owners', array('id'=>2, 'full_name'=>'ThinkUp J. Admin',
        'email'=>'admin@example.com', 'is_activated'=>1, 'is_admin'=>1));

        //Add instance_owner
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>1));
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>2, 'instance_id'=>1));

        //Insert test data into test table
        $builders[] = FixtureBuilder::build('users', array('user_id'=>13, 'user_name'=>'ev',
        'full_name'=>'Ev Williams'));

        //Make public
        //Insert test data into test table
        $builders[] = FixtureBuilder::build('instances', array('id'=>1, 'network_user_id'=>13,
        'network_username'=>'ev', 'is_public'=>1, 'network'=>'twitter'));

        return $builders;
    }

    public function testConstructor() {
        $controller = new AccountConfigurationController(true);
        $this->assertTrue(isset($controller), 'constructor test');

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Configure Your Account');
    }

    public function testCSRFEnabled() {
        $controller = new AccountConfigurationController(true);
        $this->assertTrue(isset($controller), 'constructor test');
        $this->assertTrue($controller->isEnableCSRFToken());
    }

    public function testDeleteExistingInstanceNoCSRFToken() {
        $instance_dao = new InstanceMySQLDAO();
        $owner_instance_dao = new OwnerInstanceMySQLDAO();

        //Admin: should delete all owner instances and instance
        $this->simulateLogin('admin@example.com', true, true);
        $_POST['action'] = "delete";
        $_POST["instance_id"] = 1;
        $controller = new AccountConfigurationController(true);

        //before
        $instance = $instance_dao->get(1);
        $this->assertNotNull($instance);

        $owner_instances = $owner_instance_dao->getByInstance(1);
        $this->assertNotNull($owner_instances);
        $this->assertIsA($owner_instances, 'Array');
        $this->assertEqual(sizeof($owner_instances), 2);
        try {
            $results = $controller->control();
            $this->fail("should throw InvalidCSRFTokenException");
        } catch(InvalidCSRFTokenException $e) {
            $this->assertIsA($e, 'InvalidCSRFTokenException');
        }
    }

    public function testDeleteExistingInstanceAsAdmin() {
        $instance_dao = new InstanceMySQLDAO();
        $owner_instance_dao = new OwnerInstanceMySQLDAO();

        //Admin: should delete all owner instances and instance
        $this->simulateLogin('admin@example.com', true, true);
        $_POST['action'] = "delete";
        $_POST["instance_id"] = 1;
        $_POST['csrf_token'] = parent::CSRF_TOKEN;
        $controller = new AccountConfigurationController(true);

        //before
        $instance = $instance_dao->get(1);
        $this->assertNotNull($instance);

        $owner_instances = $owner_instance_dao->getByInstance(1);
        $this->assertNotNull($owner_instances);
        $this->assertIsA($owner_instances, 'Array');
        $this->assertEqual(sizeof($owner_instances), 2);

        //process controller
        $controller->go();

        //instance should be deleted
        $instance = $instance_dao->get(1);
        $this->assertNull($instance);

        //all owner_instances should be deleted
        $owner_instances = $owner_instance_dao->getByInstance(1);
        $this->assertIsA($owner_instances, 'Array');
        $this->assertEqual(sizeof($owner_instances), 0);

        $v_mgr = $controller->getViewManager();
        $success_msgs = $v_mgr->getTemplateDataItem('success_msgs');
        $this->assertNotNull($success_msgs);
        $this->assertEqual($success_msgs['account'], 'Account deleted.');
        $this->assertNull($v_mgr->getTemplateDataItem('error_msg'));
    }

    public function testDeleteExistingInstanceWithPrivilegesNoOtherOwners() {
        $builders = array();
        $builders[] = FixtureBuilder::build('instances', array('id'=>2, 'network_user_id'=>12,
        'network_username'=>'tuinstance', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>2,
        'oauth_access_token'=>'xxx', 'oauth_access_token_secret'=>'yyy'));

        //Not admin with access privs, no other owners (delete owner instance AND instance)
        $instance_dao = new InstanceMySQLDAO();
        $owner_instance_dao = new OwnerInstanceMySQLDAO();

        //Should delete the owner instance, and since there's no other owner, the instance itself
        $this->simulateLogin('me@example.com', false, true);
        $_POST['action'] = "delete";
        $_POST["instance_id"] = 2;
        $_POST['csrf_token'] = parent::CSRF_TOKEN;
        $controller = new AccountConfigurationController(true);

        //before
        $instance = $instance_dao->get(2);
        $this->assertNotNull($instance);

        $owner_instances = $owner_instance_dao->getByInstance(2);
        $this->assertNotNull($owner_instances);
        $this->assertIsA($owner_instances, 'Array');
        $this->assertEqual(sizeof($owner_instances), 1);

        //process controller
        $controller->go();

        //instance should be deleted
        $instance = $instance_dao->get(2);
        $this->assertNull($instance);

        //all owner_instances should be deleted
        $owner_instances = $owner_instance_dao->getByInstance(2);
        $this->assertIsA($owner_instances, 'Array');
        $this->assertEqual(sizeof($owner_instances), 0);

        $v_mgr = $controller->getViewManager();
        $success_msgs = $v_mgr->getTemplateDataItem('success_msgs');
        $this->assertNotNull($success_msgs);
        $this->assertEqual($success_msgs['account'], 'Account deleted.');
        $this->assertNull($v_mgr->getTemplateDataItem('error_msg'));
    }

    public function testDeleteExistingInstanceWithPrivilegesWithOtherOwners() {
        //Not admin with access privs, with other owners (delete owner instance and NOT instance)
        $builders = array();
        $builders[] = FixtureBuilder::build('instances', array('id'=>2, 'network_user_id'=>12,
        'network_username'=>'tuinstance', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>2,
        'oauth_access_token'=>'xxx', 'oauth_access_token_secret'=>'yyy'));

        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>2, 'instance_id'=>2,
        'oauth_access_token'=>'xxx', 'oauth_access_token_secret'=>'yyy'));

        $instance_dao = new InstanceMySQLDAO();
        $owner_instance_dao = new OwnerInstanceMySQLDAO();

        //Should delete the owner instance but leave the instance alone
        $this->simulateLogin('me@example.com', false, true);
        $_POST['action'] = "delete";
        $_POST["instance_id"] = 2;
        $_POST['csrf_token'] = parent::CSRF_TOKEN;
        $controller = new AccountConfigurationController(true);

        //before
        $instance = $instance_dao->get(2);
        $this->assertNotNull($instance);

        $owner_instances = $owner_instance_dao->getByInstance(2);
        $this->assertNotNull($owner_instances);
        $this->assertIsA($owner_instances, 'Array');
        $this->assertEqual(sizeof($owner_instances), 2);

        //process controller
        $controller->go();

        //instance should NOT be deleted
        $instance = $instance_dao->get(2);
        $this->assertNotNull($instance);

        //just one owner_instance should be deleted
        $owner_instances = $owner_instance_dao->getByInstance(2);
        $this->assertIsA($owner_instances, 'Array');
        $this->assertEqual(sizeof($owner_instances), 1);

        $v_mgr = $controller->getViewManager();
        $success_msgs = $v_mgr->getTemplateDataItem('success_msgs');
        $this->assertNotNull($success_msgs);
        $this->assertEqual($success_msgs['account'], 'Account deleted.');
        $this->assertNull($v_mgr->getTemplateDataItem('error_msg'));
    }

    public function testDeleteExistingInstanceNoPrivileges() {
        //Not admin without access privs (set error messages)
        $builders = array();
        $builders[] = FixtureBuilder::build('instances', array('id'=>2, 'network_user_id'=>12,
        'network_username'=>'tuinstance', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>2, 'instance_id'=>2,
        'oauth_access_token'=>'xxx', 'oauth_access_token_secret'=>'yyy'));

        $instance_dao = new InstanceMySQLDAO();
        $owner_instance_dao = new OwnerInstanceMySQLDAO();

        //Should delete the owner instance but leave the instance alone
        $this->simulateLogin('me@example.com', false, true);
        $_POST['action'] = "delete";
        $_POST["instance_id"] = 2;
        $_POST['csrf_token'] = parent::CSRF_TOKEN;
        $controller = new AccountConfigurationController(true);

        //before
        $instance = $instance_dao->get(2);
        $this->assertNotNull($instance);

        $owner_instances = $owner_instance_dao->getByInstance(2);
        $this->assertNotNull($owner_instances);
        $this->assertIsA($owner_instances, 'Array');
        $this->assertEqual(sizeof($owner_instances), 1);

        //process controller
        $controller->go();

        //instance should NOT be deleted
        $instance = $instance_dao->get(2);
        $this->assertNotNull($instance);

        //owner instance should NOT be deleted
        $owner_instances = $owner_instance_dao->getByInstance(2);
        $this->assertIsA($owner_instances, 'Array');
        $this->assertEqual(sizeof($owner_instances), 1);

        $v_mgr = $controller->getViewManager();
        $this->assertNull($v_mgr->getTemplateDataItem('success_msgs'));
        $error_msgs = $v_mgr->getTemplateDataItem('error_msgs');
        $this->assertNotNull($error_msgs);
        $this->assertEqual($error_msgs['account'], 'Insufficient privileges.');
    }

    public function testDeleteNonExistentInstance() {
        //Not admin, non existent instance (set error message)

        $instance_dao = new InstanceMySQLDAO();
        $owner_instance_dao = new OwnerInstanceMySQLDAO();

        //Should delete the owner instance but leave the instance alone
        $this->simulateLogin('me@example.com');
        $_POST['action'] = "delete";
        $_POST["instance_id"] = 57;
        $controller = new AccountConfigurationController(true);

        //process controller
        $controller->go();

        //set error msg
        $v_mgr = $controller->getViewManager();
        $this->assertNull($v_mgr->getTemplateDataItem('success_msg'));
        $error_msgs = $v_mgr->getTemplateDataItem('error_msgs');
        $this->assertNotNull($error_msgs);
        $this->assertEqual($error_msgs['account'], 'Instance doesn\'t exist.');
    }

    public function testControlNotLoggedIn() {
        $controller = new AccountConfigurationController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $config = Config::getInstance();
        $this->assertEqual('You must <a href="'.$config->getValue('site_root_path').
        'session/login.php">log in</a> to do this.', $v_mgr->getTemplateDataItem('error_msg'));
    }

    public function testAuthControlLoggedInNotAdmin() {
        $this->simulateLogin('me@example.com');
        $controller = new AccountConfigurationController(true);
        $results = $controller->go();

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertIsA($v_mgr->getTemplateDataItem('installed_plugins'), 'array');
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('installed_plugins')), 8);

        $owner = $v_mgr->getTemplateDataItem('owner');
        $this->assertIsA($owner, 'Owner');
        $this->assertTrue(!$owner->is_admin);
        $this->assertEqual($owner->full_name, 'ThinkUp J. User');
        $this->assertEqual($owner->email, 'me@example.com');

        //not set: owners, body, success_msg, error_msg
        $this->assertTrue(!$v_mgr->getTemplateDataItem('owners'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('body'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('success_msg'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('error_msg'));
    }

    public function testAuthControlLoggedInAdmin() {
        $this->simulateLogin('admin@example.com', true);
        $controller = new AccountConfigurationController(true);
        $results = $controller->go();

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertIsA($v_mgr->getTemplateDataItem('installed_plugins'), 'array');
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('installed_plugins')), 8);

        $owner = $v_mgr->getTemplateDataItem('owner');
        $this->assertIsA($owner, 'Owner');
        $this->assertTrue($owner->is_admin);
        $this->assertEqual($owner->full_name, 'ThinkUp J. Admin');
        $this->assertEqual($owner->email, 'admin@example.com');
        $this->assertIsA($v_mgr->getTemplateDataItem('owners'), 'array');
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('owners')), 2);

        //not set: owners, body, success_msg, error_msg
        $this->assertTrue(!$v_mgr->getTemplateDataItem('body'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('success_msg'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('error_msg'));
    }

    public function testAuthControlLoggedInSpecificPluginExists() {
        $this->simulateLogin('me@example.com');
        $_GET['p'] = 'twitter';
        $controller = new AccountConfigurationController(true);
        $results = $controller->go();

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();

        $owner = $v_mgr->getTemplateDataItem('owner');
        $this->assertIsA($owner, 'Owner');
        $this->assertTrue(!$owner->is_admin);
        $this->assertEqual($owner->full_name, 'ThinkUp J. User');
        $this->assertEqual($owner->email, 'me@example.com');
        $this->assertTrue($v_mgr->getTemplateDataItem('body'));

        //not set: owners, body, success_msg, error_msg
        $this->assertTrue(!$v_mgr->getTemplateDataItem('owners'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('success_msg'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('error_msg'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('installed_plugins'));
    }

    public function testAuthControlLoggedInSpecificPluginDoesNotExist() {
        $this->simulateLogin('me@example.com');
        $_GET['p'] = 'idontexist';
        $controller = new AccountConfigurationController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $config = Config::getInstance();
        $this->assertEqual('No plugin object defined for idontexist', $v_mgr->getTemplateDataItem('error_msg'));
        $owner = $v_mgr->getTemplateDataItem('owner');
        $this->assertIsA($owner, 'Owner');
        $this->assertTrue(!$owner->is_admin);
        $this->assertEqual($owner->full_name, 'ThinkUp J. User');
        $this->assertEqual($owner->email, 'me@example.com');

        //not set: owners, body, success_msg, error_msg
        $this->assertTrue(!$v_mgr->getTemplateDataItem('owners'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('success_msg'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('installed_plugins'));
    }

    public function testAuthControlLoggedInChangePasswordNoCSRFToken() {
        $this->simulateLogin('me@example.com', false, true);
        $_POST['changepass'] = 'Change password';
        $_POST['oldpass'] = 'oldpassword';
        $_POST['pass1'] = 'newpassword1';
        $_POST['pass2'] = 'newpassword1';

        $controller = new AccountConfigurationController(true);
        try {
            $results = $controller->control();
            $this->debug($results);
            $this->fail("should throw InvalidCSRFTokenException");
        } catch(InvalidCSRFTokenException $e) {
            $this->assertIsA($e, 'InvalidCSRFTokenException');
        }
    }

    public function testAuthControlLoggedInChangePasswordSuccess() {
        $this->simulateLogin('me@example.com', false, true);
        $_POST['changepass'] = 'Change password';
        $_POST['oldpass'] = 'oldpassword';
        $_POST['pass1'] = '123newpassword';
        $_POST['pass2'] = '123newpassword';
        $_GET['csrf_token'] = parent::CSRF_TOKEN;

        $controller = new AccountConfigurationController(true);
        $results = $controller->go();

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertIsA($v_mgr->getTemplateDataItem('installed_plugins'), 'array');
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('installed_plugins')), 8);

        $owner = $v_mgr->getTemplateDataItem('owner');
        $this->assertIsA($owner, 'Owner');
        $this->assertTrue(!$owner->is_admin);
        $this->assertEqual($owner->full_name, 'ThinkUp J. User');
        $this->assertEqual($owner->email, 'me@example.com');
        $success_msgs = $v_mgr->getTemplateDataItem('success_msgs');
        $this->assertEqual($success_msgs['password'], 'Your password has been updated.');

        //not set: owners, body, success_msg, error_msg
        $this->assertTrue(!$v_mgr->getTemplateDataItem('owners'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('body'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('error_msg'));

        // Check a new unique salt was generated
        $sql = "select pwd_salt from " . $this->table_prefix . "owners where email = 'me@example.com'";
        $stmt = OwnerMySQLDAO::$PDO->query($sql);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertNotEqual($data['pwd_salt'], OwnerMySQLDAO::$default_salt);
    }

    public function testAuthControlLoggedInChangePasswordOldPwdDoesntMatch() {
        $this->simulateLogin('me@example.com');
        $_POST['changepass'] = 'Change password';
        $_POST['oldpass'] = 'oldddpassword';
        $_POST['pass1'] = 'newpassword';
        $_POST['pass2'] = 'newpassword';

        $controller = new AccountConfigurationController(true);
        $results = $controller->go();

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertIsA($v_mgr->getTemplateDataItem('installed_plugins'), 'array');
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('installed_plugins')), 8);

        $owner = $v_mgr->getTemplateDataItem('owner');
        $this->assertIsA($owner, 'Owner');
        $this->assertTrue(!$owner->is_admin);
        $this->assertEqual($owner->full_name, 'ThinkUp J. User');
        $this->assertEqual($owner->email, 'me@example.com');
        $error_msgs = $v_mgr->getTemplateDataItem('error_msgs');
        $this->assertEqual($error_msgs['password'], 'Old password does not match or empty.');

        //not set: owners, body, success_msg, error_msg
        $this->assertTrue(!$v_mgr->getTemplateDataItem('owners'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('body'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('success_msg'));
    }

    public function testAuthControlLoggedInChangePasswordOldPwdEmpty() {
        $this->simulateLogin('me@example.com');
        $_POST['changepass'] = 'Change password';
        $_POST['oldpass'] = '';
        $_POST['pass1'] = 'newpassword';
        $_POST['pass2'] = 'newpassword';

        $controller = new AccountConfigurationController(true);
        $results = $controller->go();

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertIsA($v_mgr->getTemplateDataItem('installed_plugins'), 'array');
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('installed_plugins')), 8);

        $owner = $v_mgr->getTemplateDataItem('owner');
        $this->assertIsA($owner, 'Owner');
        $this->assertTrue(!$owner->is_admin);
        $this->assertEqual($owner->full_name, 'ThinkUp J. User');
        $this->assertEqual($owner->email, 'me@example.com');
        $error_msgs = $v_mgr->getTemplateDataItem('error_msgs');
        $this->assertEqual($error_msgs['password'], 'Old password does not match or empty.');

        //not set: owners, body, success_msg, error_msg
        $this->assertTrue(!$v_mgr->getTemplateDataItem('owners'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('body'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('success_msg'));
    }

    public function testAuthControlLoggedInChangePasswordNewPwdsDontMatch() {
        $this->simulateLogin('me@example.com');
        $_POST['changepass'] = 'Change password';
        $_POST['oldpass'] = 'oldpassword';
        $_POST['pass1'] = 'newpassword1';
        $_POST['pass2'] = 'newpassword2';

        $controller = new AccountConfigurationController(true);
        $results = $controller->go();

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertIsA($v_mgr->getTemplateDataItem('installed_plugins'), 'array');
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('installed_plugins')), 8);

        $owner = $v_mgr->getTemplateDataItem('owner');
        $this->assertIsA($owner, 'Owner');
        $this->assertTrue(!$owner->is_admin);
        $this->assertEqual($owner->full_name, 'ThinkUp J. User');
        $this->assertEqual($owner->email, 'me@example.com');
        $error_msgs = $v_mgr->getTemplateDataItem('error_msgs');
        $this->assertEqual($error_msgs['password'],
        'New passwords did not match. Your password has not been changed.');

        //not set: owners, body, success_msg, error_msg
        $this->assertTrue(!$v_mgr->getTemplateDataItem('owners'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('body'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('success_msg'));
    }

    public function testAuthControlLoggedInChangePasswordNewPwdTooShort() {
        $this->simulateLogin('me@example.com');
        $_POST['changepass'] = 'Change password';
        $_POST['oldpass'] = 'oldpassword';
        $_POST['pass1'] = 'new1';
        $_POST['pass2'] = 'new1';

        $controller = new AccountConfigurationController(true);
        $results = $controller->go();

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertIsA($v_mgr->getTemplateDataItem('installed_plugins'), 'array');
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('installed_plugins')), 8);

        $owner = $v_mgr->getTemplateDataItem('owner');
        $this->assertIsA($owner, 'Owner');
        $this->assertTrue(!$owner->is_admin);
        $this->assertEqual($owner->full_name, 'ThinkUp J. User');
        $this->assertEqual($owner->email, 'me@example.com');
        $error_msgs = $v_mgr->getTemplateDataItem('error_msgs');
        $this->assertEqual($error_msgs['password'],
        'Your new password must be at least 8 characters and contain both numbers and letters. '.
        'Your password has not been changed.');

        //not set: owners, body, success_msg, error_msg
        $this->assertTrue(!$v_mgr->getTemplateDataItem('owners'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('body'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('success_msg'));
    }

    public function testAuthControlLoggedInChangePasswordNewPwdNotAlphanumeric() {
        $this->simulateLogin('me@example.com');
        $_POST['changepass'] = 'Change password';
        $_POST['oldpass'] = 'oldpassword';
        $_POST['pass1'] = 'newpasscode';
        $_POST['pass2'] = 'newpasscode';

        $controller = new AccountConfigurationController(true);
        $results = $controller->go();

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertIsA($v_mgr->getTemplateDataItem('installed_plugins'), 'array');
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('installed_plugins')), 8);

        $owner = $v_mgr->getTemplateDataItem('owner');
        $this->assertIsA($owner, 'Owner');
        $this->assertTrue(!$owner->is_admin);
        $this->assertEqual($owner->full_name, 'ThinkUp J. User');
        $this->assertEqual($owner->email, 'me@example.com');
        $error_msgs = $v_mgr->getTemplateDataItem('error_msgs');
        $this->assertEqual($error_msgs['password'],
        'Your new password must be at least 8 characters and contain both numbers and letters. '.
        'Your password has not been changed.');

        //not set: owners, body, success_msg, error_msg
        $this->assertTrue(!$v_mgr->getTemplateDataItem('owners'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('body'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('success_msg'));
    }

    public function testAuthControlInviteUserNoCSRFToken() {
        $this->simulateLogin('me@example.com', false, true);
        $_SERVER['HTTP_HOST'] = "mytestthinkup";
        $_SERVER['HTTPS'] = null;
        $_POST['invite'] = 'Create Invitation' ;

        $controller = new AccountConfigurationController(true);
        try {
            $results = $controller->control();
            $this->fail("should throw InvalidCSRFTokenException");
        } catch(InvalidCSRFTokenException $e) {
            $this->assertIsA($e, 'InvalidCSRFTokenException');
        }
    }

    public function testAuthControlInviteUser() {
        $cfg = Config::getInstance();
        $cfg->setValue('site_root_path', '/');
        $this->simulateLogin('me@example.com', false, true);

        $_SERVER['HTTP_HOST'] = "mytestthinkup";
        $_SERVER['SERVER_NAME'] = 'mytestthinkup';
        $_SERVER['HTTPS'] = null;
        $_POST['invite'] = 'Create Invitation' ;
        $_POST['csrf_token'] = parent::CSRF_TOKEN;

        $controller = new AccountConfigurationController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();

        $msgs_array = $v_mgr->getTemplateDataItem('success_msgs');
        $this->assertPattern('/Invitation created!/', $msgs_array['invite']);
        $this->assertPattern('/http:\/\/mytestthinkup\/session\/register.php\?code=/', $msgs_array['invite']);

        //test HTTPS
        $_SERVER['HTTPS'] = 1;
        $_SERVER['HTTP_HOST'] = "myotherwtestthinkup";
        $_SERVER['SERVER_NAME'] = 'myotherwtestthinkup';
        $_POST['invite'] = 'Create Invitation' ;

        $controller = new AccountConfigurationController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();

        $msgs_array = $v_mgr->getTemplateDataItem('success_msgs');
        $this->assertPattern('/Invitation created!/', $msgs_array['invite']);
        $this->assertPattern('/https:\/\/myotherwtestthinkup\/session\/register.php\?code=/',
        $msgs_array['invite']);
    }

    public function testResetAPIKey() {
        $this->simulateLogin('me@example.com', false, true);
        $_POST['reset_api_key'] = 'Reset API Key';
        $_GET['csrf_token'] = parent::CSRF_TOKEN;

        $controller = new AccountConfigurationController(true);
        $results = $controller->go();

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertIsA($v_mgr->getTemplateDataItem('installed_plugins'), 'array');
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('installed_plugins')), 8);

        $owner = $v_mgr->getTemplateDataItem('owner');
        $this->assertIsA($owner, 'Owner');
        $this->assertTrue(!$owner->is_admin);
        $this->assertEqual($owner->full_name, 'ThinkUp J. User');
        $this->assertEqual($owner->email, 'me@example.com');
        $success_msgs = $v_mgr->getTemplateDataItem('success_msgs');
        $this->assertEqual($success_msgs['api_key'],
        'Your API Key has been reset! Please update your ThinkUp RSS feed subscription.');

        // Has API Key actually changed
        $this->assertNotEqual('c9089f3c9adaf0186f6ffb1ee8d6501c', $owner->api_key);

        //not set: owners, body, success_msg, error_msg
        $this->assertTrue(!$v_mgr->getTemplateDataItem('owners'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('body'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('error_msg'));
    }

    public function testResetAPIKeyBadCSRFToken() {
        $this->simulateLogin('me@example.com', false, true);
        $_POST['reset_api_key'] = 'Reset API Key';
        $_GET['csrf_token'] = parent::CSRF_TOKEN . 'lalla';

        $controller = new AccountConfigurationController(true);
        $results = $controller->go();
        try {
            $results = $controller->control();
            $this->fail("should throw InvalidCSRFTokenException");
        } catch(InvalidCSRFTokenException $e) {
            $this->assertIsA($e, 'InvalidCSRFTokenException');
        }
    }

    public function testLoadProperRSSUrl() {
        $builder = $this->buildRSSData();
        $this->simulateLogin('me152@example.com', true, true);
        $controller = new AccountConfigurationController(true);
        $this->assertTrue(isset($controller));
        $result = $controller->control();
        $this->assertPattern('/crawler\/rss.php\?un=me152\%40example.com&as=c9089f3c9adaf0186f6ffb1ee8d6501c/',
        $result);
    }

    public function testLoadProperRSSUrlWithPlusSignInEmailAddress() {
        $builder = $this->buildRSSData();
        $this->simulateLogin('me153+checkurlencoding@example.com', true, true);
        $controller = new AccountConfigurationController(true);
        $this->assertTrue(isset($controller));
        $result = $controller->control();
        $this->debug($result);
        $this->assertPattern(
        '/crawler\/rss.php\?un=me153\%2Bcheckurlencoding%40example.com&as=c9089f3c9adaf0186f6ffb1ee8d6501c/', $result);
    }

    private function buildRSSData() {
        $builders[] = FixtureBuilder::build('owners', array(
            'id' => 152,
            'email' => 'me152@example.com',
            'pwd' => 'XXX',
            'is_activated' => 1,
            'api_key' => 'c9089f3c9adaf0186f6ffb1ee8d6501c'
            ));
            $builders[] = FixtureBuilder::build('owners', array(
            'id' => 153,
            'email' => 'me153+checkurlencoding@example.com',
            'pwd' => 'XXX',
            'is_activated' => 1,
            'api_key' => 'c9089f3c9adaf0186f6ffb1ee8d6501c'
            ));
            return $builders;
    }
}
