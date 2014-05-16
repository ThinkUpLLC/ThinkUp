<?php
/**
 *
 * ThinkUp/tests/TestOfAccountConfigurationController.php
 *
 * Copyright (c) 2009-2013 Gina Trapani, Terrance Shepherd
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
 *
 * Test of AccountConfigurationController
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, Terrance Shepherd
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
        $webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
        $webapp_plugin_registrar->registerPlugin('twitter', 'TwitterPlugin');
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
        'pwd_salt'=> OwnerMySQLDAO::$default_salt, 'api_key' => 'c9089f3c9adaf0186f6ffb1ee8d6501c',
        'email_notification_frequency' => 'daily', 'timezone'=>'UTC'));

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
        $this->debug(__METHOD__);
        $controller = new AccountConfigurationController(true);
        $this->assertTrue(isset($controller), 'constructor test');

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Configure Your Account');
    }

    public function testCSRFEnabled() {
        $this->debug(__METHOD__);
        $controller = new AccountConfigurationController(true);
        $this->assertTrue(isset($controller), 'constructor test');
        $this->assertTrue($controller->isEnableCSRFToken());
    }

    public function testDeleteExistingInstanceNoCSRFToken() {
        $this->debug(__METHOD__);
        $instance_dao = new InstanceMySQLDAO();
        $owner_instance_dao = new OwnerInstanceMySQLDAO();

        //Admin: should delete all owner instances and instance
        $this->simulateLogin('admin@example.com', true, true);
        $_POST['action'] = "Delete";
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
        $this->debug(__METHOD__);
        $instance_dao = new InstanceMySQLDAO();
        $owner_instance_dao = new OwnerInstanceMySQLDAO();

        //Admin: should delete all owner instances and instance
        $this->simulateLogin('admin@example.com', true, true);
        $_POST['action'] = "Delete";
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
        $this->debug(__METHOD__);
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
        $_POST['action'] = "Delete";
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
        $this->debug(__METHOD__);
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
        $_POST['action'] = "Delete";
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
        $this->debug(__METHOD__);
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
        $_POST['action'] = "Delete";
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
        $this->debug(__METHOD__);

        //Not admin, non existent instance (set error message)
        $instance_dao = new InstanceMySQLDAO();
        $owner_instance_dao = new OwnerInstanceMySQLDAO();

        //Should delete the owner instance but leave the instance alone
        $this->simulateLogin('me@example.com');
        $_POST['action'] = "Delete";
        $_POST["instance_id"] = 57;
        $controller = new AccountConfigurationController(true);

        //process controller
        $controller->go();

        //set error msg
        $v_mgr = $controller->getViewManager();
        $this->assertNull($v_mgr->getTemplateDataItem('success_msg'));
        $error_msgs = $v_mgr->getTemplateDataItem('error_msgs');
        $this->assertNotNull($error_msgs);
        $this->assertEqual($error_msgs['account'], 'Could not find that account.');
    }

    public function testControlNotLoggedIn() {
        $this->debug(__METHOD__);
        $controller = new AccountConfigurationController(true);
        $results = $controller->go();
        $this->assertPattern( '/session\/login.php\?redirect\=/', $controller->redirect_destination);
    }

    public function testAuthControlLoggedInNotAdmin() {
        $this->debug(__METHOD__);

        $this->simulateLogin('me@example.com');
        $controller = new AccountConfigurationController(true);
        $results = $controller->go();

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertIsA($v_mgr->getTemplateDataItem('installed_plugins'), 'array');
        $this->assertTrue(sizeof($v_mgr->getTemplateDataItem('installed_plugins')) >= 9);

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
        $this->debug(__METHOD__);

        $this->simulateLogin('admin@example.com', true);
        $controller = new AccountConfigurationController(true);
        $results = $controller->go();

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertIsA($v_mgr->getTemplateDataItem('installed_plugins'), 'array');
        $this->assertTrue(sizeof($v_mgr->getTemplateDataItem('installed_plugins')) >= 9);

        $owner = $v_mgr->getTemplateDataItem('owner');
        $this->assertIsA($owner, 'Owner');
        $this->assertTrue($owner->is_admin);
        $this->assertEqual($owner->full_name, 'ThinkUp J. Admin');
        $this->assertEqual($owner->email, 'admin@example.com');
        $this->assertIsA($v_mgr->getTemplateDataItem('owners'), 'array');
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('owners')), 2);
        $this->assertTrue($v_mgr->getTemplateDataItem('installed_plugins'));

        //not set: owners, body, success_msg, error_msg
        $this->assertTrue(!$v_mgr->getTemplateDataItem('body'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('success_msg'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('error_msg'));
    }

    public function testAuthControlLoggedInSpecificPluginExists() {
        $this->debug(__METHOD__);

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
        $this->assertTrue($v_mgr->getTemplateDataItem('installed_plugins'));

        //not set: owners, success_msg, error_msg
        $this->assertTrue(!$v_mgr->getTemplateDataItem('owners'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('success_msg'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('error_msg'));
    }

    public function testAuthControlLoggedInSpecificPluginDoesNotExist() {
        $this->debug(__METHOD__);

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
        $this->debug(__METHOD__);

        $this->simulateLogin('me@example.com', false, true);
        $_POST['changepass'] = 'Change';
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
        $this->debug(__METHOD__);

        $this->simulateLogin('me@example.com', false, true);
        $_POST['changepass'] = 'Change';
        $_POST['oldpass'] = 'oldpassword';
        $_POST['pass1'] = '123newpassword';
        $_POST['pass2'] = '123newpassword';
        $_GET['csrf_token'] = parent::CSRF_TOKEN;

        $controller = new AccountConfigurationController(true);
        $results = $controller->go();

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertIsA($v_mgr->getTemplateDataItem('installed_plugins'), 'array');
        $this->assertTrue(sizeof($v_mgr->getTemplateDataItem('installed_plugins')) >= 9);

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
        $this->debug(__METHOD__);

        $this->simulateLogin('me@example.com');
        $_POST['changepass'] = 'Change';
        $_POST['oldpass'] = 'oldddpassword';
        $_POST['pass1'] = 'newpassword';
        $_POST['pass2'] = 'newpassword';

        $controller = new AccountConfigurationController(true);
        $results = $controller->go();

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertIsA($v_mgr->getTemplateDataItem('installed_plugins'), 'array');
        $this->assertTrue(sizeof($v_mgr->getTemplateDataItem('installed_plugins')) >= 9);

        $owner = $v_mgr->getTemplateDataItem('owner');
        $this->assertIsA($owner, 'Owner');
        $this->assertTrue(!$owner->is_admin);
        $this->assertEqual($owner->full_name, 'ThinkUp J. User');
        $this->assertEqual($owner->email, 'me@example.com');
        $error_msgs = $v_mgr->getTemplateDataItem('error_msgs');
        $this->assertEqual($error_msgs['password'], 'Password is incorrect.');

        //not set: owners, body, success_msg, error_msg
        $this->assertTrue(!$v_mgr->getTemplateDataItem('owners'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('body'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('success_msg'));
    }

    public function testAuthControlLoggedInChangePasswordOldPwdEmpty() {
        $this->debug(__METHOD__);

        $this->simulateLogin('me@example.com');
        $_POST['changepass'] = 'Change';
        $_POST['oldpass'] = '';
        $_POST['pass1'] = 'newpassword';
        $_POST['pass2'] = 'newpassword';

        $controller = new AccountConfigurationController(true);
        $results = $controller->go();

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertIsA($v_mgr->getTemplateDataItem('installed_plugins'), 'array');
        $this->assertTrue(sizeof($v_mgr->getTemplateDataItem('installed_plugins')) >= 9);

        $owner = $v_mgr->getTemplateDataItem('owner');
        $this->assertIsA($owner, 'Owner');
        $this->assertTrue(!$owner->is_admin);
        $this->assertEqual($owner->full_name, 'ThinkUp J. User');
        $this->assertEqual($owner->email, 'me@example.com');
        $error_msgs = $v_mgr->getTemplateDataItem('error_msgs');
        $this->assertEqual($error_msgs['password'], 'Password is incorrect.');

        //not set: owners, body, success_msg, error_msg
        $this->assertTrue(!$v_mgr->getTemplateDataItem('owners'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('body'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('success_msg'));
    }

    public function testAuthControlLoggedInChangePasswordNewPwdsDontMatch() {
        $this->debug(__METHOD__);

        $this->simulateLogin('me@example.com');
        $_POST['changepass'] = 'Change';
        $_POST['oldpass'] = 'oldpassword';
        $_POST['pass1'] = 'newpassword1';
        $_POST['pass2'] = 'newpassword2';

        $controller = new AccountConfigurationController(true);
        $results = $controller->go();

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertIsA($v_mgr->getTemplateDataItem('installed_plugins'), 'array');
        $this->assertTrue(sizeof($v_mgr->getTemplateDataItem('installed_plugins')) >= 9);

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
        $this->debug(__METHOD__);

        $this->simulateLogin('me@example.com');
        $_POST['changepass'] = 'Change';
        $_POST['oldpass'] = 'oldpassword';
        $_POST['pass1'] = 'new1';
        $_POST['pass2'] = 'new1';

        $controller = new AccountConfigurationController(true);
        $results = $controller->go();

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertIsA($v_mgr->getTemplateDataItem('installed_plugins'), 'array');
        $this->assertTrue(sizeof($v_mgr->getTemplateDataItem('installed_plugins')) >= 9);

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
        $this->debug(__METHOD__);

        $this->simulateLogin('me@example.com');
        $_POST['changepass'] = 'Change';
        $_POST['oldpass'] = 'oldpassword';
        $_POST['pass1'] = 'newpasscode';
        $_POST['pass2'] = 'newpasscode';

        $controller = new AccountConfigurationController(true);
        $results = $controller->go();

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertIsA($v_mgr->getTemplateDataItem('installed_plugins'), 'array');
        $this->assertTrue(sizeof($v_mgr->getTemplateDataItem('installed_plugins')) >= 9);

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
        $this->debug(__METHOD__);

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
        $this->debug(__METHOD__);

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
        $this->debug(__METHOD__);

        $this->simulateLogin('me@example.com', false, true);
        $_POST['reset_api_key'] = 'Reset API Key';
        $_GET['csrf_token'] = parent::CSRF_TOKEN;

        $controller = new AccountConfigurationController(true);
        $results = $controller->go();

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertIsA($v_mgr->getTemplateDataItem('installed_plugins'), 'array');
        $this->assertTrue(sizeof($v_mgr->getTemplateDataItem('installed_plugins')) >= 9);

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
        $this->debug(__METHOD__);

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
        $this->debug(__METHOD__);

        $builder = $this->buildRSSData();
        $this->simulateLogin('me152@example.com', true, true);
        $controller = new AccountConfigurationController(true);
        $this->assertTrue(isset($controller));
        $result = $controller->control();
        $this->assertPattern('/crawler\/rss.php\?un=me152\%40example.com&as=c9089f3c9adaf0186f6ffb1ee8d6501c/',
        $result);
    }

    public function testLoadProperRSSUrlWithPlusSignInEmailAddress() {
        $this->debug(__METHOD__);

        $builder = $this->buildRSSData();
        $this->simulateLogin('me153+checkurlencoding@example.com', true, true);
        $controller = new AccountConfigurationController(true);
        $this->assertTrue(isset($controller));
        $result = $controller->control();
        $this->debug($result);
        $this->assertPattern(
        '/crawler\/rss.php\?un=me153\%2Bcheckurlencoding%40example.com&as=c9089f3c9adaf0186f6ffb1ee8d6501c/', $result);
    }

    public function testAuthControlLoggedInChangeNotificationFrequency() {
        $owner_dao = new OwnerMySQLDAO();
        $owner = $owner_dao->getByEmail('me@example.com');
        $this->assertEqual('daily', $owner->email_notification_frequency);

        $this->simulateLogin('me@example.com', false, true);
        $controller = new AccountConfigurationController(true);
        $output = $controller->go();
        $this->assertPattern('/"daily"[^>]*selected/', $output);
        $this->assertNoPattern('/"both"[^>]*selected/', $output);

        $this->simulateLogin('me@example.com', false, true);
        $_POST['updatepreferences'] = 'Update';
        $_POST['notificationfrequency'] = 'both';
        $controller = new AccountConfigurationController(true);
        $controller->go();
        $owner = $owner_dao->getByEmail('me@example.com');
        // No CSRF shouldn't update
        $this->assertNotEqual('both', $owner->email_notification_frequency);

        $this->simulateLogin('me@example.com', false, true);
        $_POST['updatepreferences'] = 'Update';
        $_POST['notificationfrequency'] = 'bananas';
        $_POST['csrf_token'] = parent::CSRF_TOKEN;

        $controller = new AccountConfigurationController(true);
        $output = $controller->go();
        $owner = $owner_dao->getByEmail('me@example.com');
        // bad value, shouldn't update
        $this->assertNotEqual('bananas', $owner->email_notification_frequency);
        $this->assertEqual('daily', $owner->email_notification_frequency);
        $this->assertNoPattern('/email notification frequency has been updated/', $output);

        $this->simulateLogin('me@example.com', false, true);
        $_POST['updatepreferences'] = 'Update';
        $_POST['notificationfrequency'] = 'both';
        $_POST['csrf_token'] = parent::CSRF_TOKEN;
        $controller = new AccountConfigurationController(true);
        $output = $controller->go();
        $owner = $owner_dao->getByEmail('me@example.com');
        $this->assertNotEqual('daily', $owner->email_notification_frequency);
        $this->assertEqual('both', $owner->email_notification_frequency);
        $this->assertNoPattern('/"daily"[^>]*selected/', $output);
        $this->assertPattern('/"both"[^>]*selected/', $output);
        $this->assertPattern('/email notification frequency has been updated/', $output);
    }

    public function testAuthControlLoggedInTimeZone() {
        $owner_dao = new OwnerMySQLDAO();
        $owner = $owner_dao->getByEmail('me@example.com');
        $this->assertEqual('UTC', $owner->timezone);

        $this->simulateLogin('me@example.com', false, true);
        $_POST['updatepreferences'] = 'Update';
        $_POST['timezone'] = 'America/New_York';
        $controller = new AccountConfigurationController(true);
        $controller->go();
        $owner = $owner_dao->getByEmail('me@example.com');
        // No CSRF shouldn't update
        $this->assertNotEqual('America/NewYork', $owner->timezone);

        $this->simulateLogin('me@example.com', false, true);
        $_POST['updatepreferences'] = 'Update';
        $_POST['timezone'] = 'bananas';
        $_POST['csrf_token'] = parent::CSRF_TOKEN;

        $controller = new AccountConfigurationController(true);
        $output = $controller->go();
        $owner = $owner_dao->getByEmail('me@example.com');
        // bad value, shouldn't update
        $this->assertNotEqual('bananas', $owner->timezone);
        $this->assertEqual('UTC', $owner->timezone);
        $this->assertNoPattern('/time zone has been saved/', $output);

        $this->simulateLogin('me@example.com', false, true);
        $_POST['updatepreferences'] = 'Update';
        $_POST['timezone'] = 'America/New_York';
        $_POST['csrf_token'] = parent::CSRF_TOKEN;
        $controller = new AccountConfigurationController(true);
        $output = $controller->go();
        $owner = $owner_dao->getByEmail('me@example.com');
        $this->assertNotEqual('UTC', $owner->timezone);
        $this->assertEqual('America/New_York', $owner->timezone);
        $this->assertPattern('/time zone has been saved/', $output);
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

    public function testAddAndDeleteHashtagSearch() {
        $this->debug(__METHOD__);

        //Hashtag does not exist
        $hashtag_dao = new HashtagMySQLDAO();
        $instance_hashtag_dao = new InstanceHashtagMySQLDAO();
        $hashtag = $hashtag_dao->getHashtag($_POST['new_hashtag_name'], 'twitter');
        $this->assertNull($hashtag);
        $instance_hashtag = $instance_hashtag_dao->getByInstance(1);
        $this->assertEqual(sizeof($instance_hashtag),0);

        //Add hashtag
        $this->simulateLogin('admin@example.com', true, true);
        $_POST['action'] = 'Save search';
        $_POST['new_hashtag_name'] = '#Messi';
        $_POST['instance_id'] = 1;
        $_POST['csrf_token'] = parent::CSRF_TOKEN;

        $controller = new AccountConfigurationController(true);
        $results = $controller->go();
        $this->debug($results);

        $hashtag = $hashtag_dao->getHashtag($_POST['new_hashtag_name'], 'twitter');
        $this->assertNotNull($hashtag);
        $this->assertEqual($hashtag->id,1);
        $this->assertEqual($hashtag->hashtag,$_POST['new_hashtag_name']);
        $this->assertEqual($hashtag->network,'twitter');
        $this->assertEqual($hashtag->count_cache,0);
        $instance_hashtag = $instance_hashtag_dao->getByInstance(1);
        $this->assertNotNull($instance_hashtag);
        $this->assertEqual(sizeof($instance_hashtag),1);
        $this->assertEqual($instance_hashtag[0]->instance_id,1);
        $this->assertEqual($instance_hashtag[0]->hashtag_id,1);
        $this->assertEqual($instance_hashtag[0]->last_post_id,0);
        $this->assertEqual($instance_hashtag[0]->earliest_post_id,0);

        $v_mgr = $controller->getViewManager();
        $success_msgs = $v_mgr->getTemplateDataItem('success_msgs');
        $this->assertNotNull($success_msgs);
        $this->assertEqual($success_msgs['account'], 'Saved search for #Messi.');
        $this->assertNull($v_mgr->getTemplateDataItem('error_msg'));

        //Search tweets
        //Saved search tweets do not exist
        $posts_dao = new PostMySQLDAO();
        $links_dao = new LinkMySQLDAO();
        $users_dao = new UserMySQLDAO();
        $hashtagpost_dao = new HashtagPostMySQLDAO();
        $hashtags_posts = $hashtagpost_dao->getHashtagsForPost(1, 'twitter');
        $this->assertEqual(sizeof($hashtags_posts),0);
        $posts = $posts_dao->getAllPostsByHashtagId(1, 'twitter', 20);
        $this->assertEqual(sizeof($posts),0);
        $posts = $posts_dao->getAllPostsByUsername('vetcastellnou','twitter');
        $this->assertEqual(sizeof($posts),0);
        $links = $links_dao->getLinksForPost(1);
        $this->assertEqual(sizeof($links),0);
        $this->assertFalse($users_dao->isUserInDB(100,'twitter'));
        $this->assertFalse($users_dao->isUserInDB(101,'twitter'));

        $builder = $this->buildSearchData();

        //Saved search tweets do exist
        $hashtags_posts = $hashtagpost_dao->getHashtagsForPost(1, 'twitter');
        $this->assertEqual(sizeof($hashtags_posts),1);
        $posts=$posts_dao->getAllPostsByHashtagId(1, 'twitter', 20);
        $this->assertEqual(sizeof($posts),2);
        $posts=$posts_dao->getAllPostsByUsername('vetcastellnou','twitter');
        $this->assertEqual(sizeof($posts),1);
        $links = $links_dao->getLinksForPost(1);
        $this->assertEqual(sizeof($links),2);
        $this->assertTrue($users_dao->isUserInDB(100,'twitter'));
        $this->assertTrue($users_dao->isUserInDB(101,'twitter'));

        //Delete hashtag
        $new_hashtag_name = $_POST['new_hashtag_name'];
        unset($_POST['new_hashtag_name']);
        $_POST['action'] = 'Delete';
        $_POST['instance_id'] = 1;
        $_POST['hashtag_id']=1;
        $_POST['csrf_token'] = parent::CSRF_TOKEN;

        $controller = new AccountConfigurationController(true);
        $controller->go();

        $hashtags_posts = $hashtagpost_dao->getHashtagsForPost(1, 'twitter');
        $this->assertEqual(sizeof($hashtags_posts),0);
        $posts = $posts_dao->getAllPostsByHashtagId(1, 'twitter', 20);
        $this->assertEqual(sizeof($posts),0);
        $posts = $posts_dao->getAllPostsByUsername('vetcastellnou','twitter');
        $this->assertEqual(sizeof($posts),1);
        $links = $links_dao->getLinksForPost(1);
        //Don't delete links
        $this->assertEqual(sizeof($links),2);
        //Don't delete users
        $this->assertTrue($users_dao->isUserInDB(100,'twitter'));
        $this->assertTrue($users_dao->isUserInDB(101,'twitter'));
        $hashtag = $hashtag_dao->getHashtag($new_hashtag_name);
        $this->assertNull($hashtag);
        $instance_hashtag = $instance_hashtag_dao->getByInstance(1);
        $this->assertEqual(sizeof($instance_hashtag),0);

        $v_mgr = $controller->getViewManager();
        $success_msgs = $v_mgr->getTemplateDataItem('success_msgs');
        $this->assertNotNull($success_msgs);
        $this->assertEqual($success_msgs['account'], 'Deleted saved search.');
        $this->assertNull($v_mgr->getTemplateDataItem('error_msg'));
    }

    private function buildSearchData() {
        $builders = array();

        $builders[] = FixtureBuilder::build('hashtags_posts',
        array('post_id' => 1, 'hashtag_id' => 1, 'network' => 'twitter'));
        $builders[] = FixtureBuilder::build('hashtags_posts',
        array('post_id' => 3, 'hashtag_id' => 1, 'network' => 'twitter'));

        $builders[] = FixtureBuilder::build('posts', array(
            'post_id' => '1',
            'author_user_id' => '100',
            'author_username' => 'ecucurella',
            'author_fullname' => 'Eduard Cucurella',
            'author_avatar' => 'http://aa.com',
            'author_follower_count' => 0,
            'post_text' => '#Messi is the best http://flic.kr/p/ http://flic.kr/a/',
            'is_protected' => 0,
            'source' => '<a href=""></a>',
            'location' => 'BCN',
            'place' => '',
            'place_id' => '',
            'geo' => '',
            'pub_date' => '2013-02-28 11:02:34',
            'in_reply_to_user_id' => '',
            'in_reply_to_post_id' => '',
            'reply_count_cache' => 1,
            'is_reply_by_friend' => 0,
            'in_retweet_of_post_id' => '',
            'old_retweet_count_cache' => 0,
            'is_retweet_by_friend' => 0,
            'reply_retweet_distance' => 0,
            'network' => 'twitter',
            'is_geo_encoded' => 0,
            'in_rt_of_user_id' => '',
            'retweet_count_cache' => 0,
            'retweet_count_api' => 0,
            'favlike_count_cache' => 0));

        $builders[] = FixtureBuilder::build('posts', array(
            'post_id' => '2',
            'author_user_id' => '101',
            'author_username' => 'vetcastellnou',
            'author_fullname' => 'Veterans Castellnou',
            'author_avatar' => 'http://aa.com',
            'author_follower_count' => 0,
            'post_text' => 'Post without any hashtag http://flic.kr/p/',
            'is_protected' => 0,
            'source' => '<a href=""></a>',
            'location' => 'BCN',
            'place' => '',
            'place_id' => '',
            'geo' => '',
            'pub_date' => '2013-02-28 11:02:34',
            'in_reply_to_user_id' => '',
            'in_reply_to_post_id' => '',
            'reply_count_cache' => 1,
            'is_reply_by_friend' => 0,
            'in_retweet_of_post_id' => '',
            'old_retweet_count_cache' => 0,
            'is_retweet_by_friend' => 0,
            'reply_retweet_distance' => 0,
            'network' => 'twitter',
            'is_geo_encoded' => 0,
            'in_rt_of_user_id' => '',
            'retweet_count_cache' => 0,
            'retweet_count_api' => 0,
            'favlike_count_cache' => 0));

        $builders[] = FixtureBuilder::build('posts', array(
            'post_id' => '3',
            'author_user_id' => '102',
            'author_username' => 'efectivament',
            'author_fullname' => 'efectivament',
            'author_avatar' => 'http://aa.com',
            'author_follower_count' => 0,
            'post_text' => 'Post with #Messi hashtag http://flic.kr/p/',
            'is_protected' => 0,
            'source' => '<a href=""></a>',
            'location' => 'BCN',
            'place' => '',
            'place_id' => '',
            'geo' => '',
            'pub_date' => '2013-02-28 11:02:34',
            'in_reply_to_user_id' => '',
            'in_reply_to_post_id' => '',
            'reply_count_cache' => 1,
            'is_reply_by_friend' => 0,
            'in_retweet_of_post_id' => '',
            'old_retweet_count_cache' => 0,
            'is_retweet_by_friend' => 0,
            'reply_retweet_distance' => 0,
            'network' => 'twitter',
            'is_geo_encoded' => 0,
            'in_rt_of_user_id' => '',
            'retweet_count_cache' => 0,
            'retweet_count_api' => 0,
            'favlike_count_cache' => 0));

        $builders[] = FixtureBuilder::build('posts', array(
            'post_id' => '4',
            'author_user_id' => '102',
            'author_username' => 'efectivament',
            'author_fullname' => 'efectivament',
            'author_avatar' => 'http://aa.com',
            'author_follower_count' => 0,
            'post_text' => 'Post without any hashtag 2',
            'is_protected' => 0,
            'source' => '<a href=""></a>',
            'location' => 'BCN',
            'place' => '',
            'place_id' => '',
            'geo' => '',
            'pub_date' => '2013-02-28 11:02:34',
            'in_reply_to_user_id' => '',
            'in_reply_to_post_id' => '',
            'reply_count_cache' => 1,
            'is_reply_by_friend' => 0,
            'in_retweet_of_post_id' => '',
            'old_retweet_count_cache' => 0,
            'is_retweet_by_friend' => 0,
            'reply_retweet_distance' => 0,
            'network' => 'twitter',
            'is_geo_encoded' => 0,
            'in_rt_of_user_id' => '',
            'retweet_count_cache' => 0,
            'retweet_count_api' => 0,
            'favlike_count_cache' => 0));

        $builders[] = FixtureBuilder::build('links', array(
            'url'=>'http://flic.kr/p/',
            'title'=>'Link ',
            'post_key'=>1,
            'expanded_url'=>'',
            'error'=>'',
            'image_src'=>'http://flic.kr/thumbnail.png'));

        $builders[] = FixtureBuilder::build('links', array(
            'url'=>'http://flic.kr/a/',
            'title'=>'Link ',
            'post_key'=>1,
            'expanded_url'=>'',
            'error'=>'',
            'image_src'=>'http://flic.kr/thumbnail.png'));

        $builders[] = FixtureBuilder::build('links', array(
            'url'=>'http://flic.kr/p/',
            'title'=>'Link ',
            'post_key'=>2,
            'expanded_url'=>'',
            'error'=>'',
            'image_src'=>'http://flic.kr/thumbnail.png'));

        $builders[] = FixtureBuilder::build('links', array(
            'url'=>'http://flic.kr/p/',
            'title'=>'Link ',
            'post_key'=>3,
            'expanded_url'=>'',
            'error'=>'',
            'image_src'=>'http://flic.kr/thumbnail.png'));

        $builders[] = FixtureBuilder::build('users', array(
            'user_id'=>100,
            'user_name'=>'ecucurella',
            'full_name'=>'Eduard Cucurella'));

        $builders[] = FixtureBuilder::build('users', array(
            'user_id'=>101,
            'user_name'=>'vetcastellnou',
            'full_name'=>'Veterans Castellnou'));

        $builders[] = FixtureBuilder::build('users', array(
            'user_id'=>102,
            'user_name'=>'efectivament',
            'full_name'=>'efectivament'));

        return $builders;
    }

    public function testDeleteExistingInstanceWithTweetSearchAsAdmin() {
        $this->debug(__METHOD__);

        $instance_dao = new InstanceMySQLDAO();
        $owner_instance_dao = new OwnerInstanceMySQLDAO();
        $instance_hashtag_dao = new InstanceHashtagMySQLDAO();

        //Admin: should delete also hashtag instance information
        $this->simulateLogin('admin@example.com', true, true);
        $_POST['action'] = "Delete";
        $_POST["instance_id"] = 1;
        $_POST['csrf_token'] = parent::CSRF_TOKEN;
        $controller = new AccountConfigurationController(true);

        //before
        $instance = $instance_dao->get(1);
        $this->assertNotNull($instance);
        $instances_hashtags = $instance_hashtag_dao->getByInstance($instance->id);
        $this->assertEqual(sizeof($instances_hashtags),0);

        $builder = $this->buildHashtagData(1);

        //hashtags posts should be deleted
        $hashtagpost_dao = new HashtagPostMySQLDAO();
        $hashtags = $hashtagpost_dao->getHashtagsForPost(1, 'twitter');
        $this->assertEqual(sizeof($hashtags),1);
        $hashtags = $hashtagpost_dao->getHashtagsForPost(2, 'twitter');
        $this->assertEqual(sizeof($hashtags),1);

        //instances hashtags should be deleted
        $instances_hashtags = $instance_hashtag_dao->getByInstance(1);
        $this->assertEqual(sizeof($instances_hashtags),2);

        //hashtag should be deleted
        $hashtag_dao = new HashtagMySQLDAO();
        $hashtag = $hashtag_dao->getHashtagByID(1);
        $this->assertEqual(sizeof($hashtag), 1);
        $hashtag = $hashtag_dao->getHashtagByID(2);
        $this->assertEqual(sizeof($hashtag), 1);

        //process controller
        $controller->go();

        //hashtags posts should be deleted
        //sleep(1000);
        $hashtags = $hashtagpost_dao->getHashtagsForPost(1, 'twitter');
        $this->assertEqual(sizeof($hashtags),0);
        $hashtags = $hashtagpost_dao->getHashtagsForPost(1, 'twitter');
        $this->assertEqual(sizeof($hashtags),0);
        //instances hashtags should be deleted
        $instances_hashtags = $instance_hashtag_dao->getByInstance(1);
        $this->assertEqual(sizeof($instances_hashtags),0);

        //hashtag should be deleted
        $hashtag = $hashtag_dao->getHashtagByID(1);
        $this->assertEqual(sizeof($hashtag),0);
        $hashtag = $hashtag_dao->getHashtagByID(2);
        $this->assertEqual(sizeof($hashtag),0);

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
        $this->assertEqual($success_msgs['account'], 'Account and its saved searches deleted.');
        $this->assertNull($v_mgr->getTemplateDataItem('error_msg'));
    }

    public function testDeleteExistingInstanceWithTweetSearchWithPrivilegesNoOtherOwners() {
        $this->debug(__METHOD__);

        $builders = array();
        $builders[] = FixtureBuilder::build('instances', array('id'=>2, 'network_user_id'=>12,
        'network_username'=>'tuinstance', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>2,
        'oauth_access_token'=>'xxx', 'oauth_access_token_secret'=>'yyy'));

        //Not admin with access privs, no other owners (delete owner instance AND instance)
        $instance_dao = new InstanceMySQLDAO();
        $owner_instance_dao = new OwnerInstanceMySQLDAO();
        $instance_hashtag_dao = new InstanceHashtagMySQLDAO();

        //before
        $instance = $instance_dao->get(2);
        $this->assertNotNull($instance);
        $instances_hashtags = $instance_hashtag_dao->getByInstance($instance->id);
        $this->assertEqual(sizeof($instances_hashtags),0);

        $builder = $this->buildHashtagData(2);

        //after builder
        $hashtagpost_dao = new HashtagPostMySQLDAO();
        $hashtags = $hashtagpost_dao->getHashtagsForPost(1, 'twitter');
        $this->assertEqual(sizeof($hashtags),1);
        $hashtags = $hashtagpost_dao->getHashtagsForPost(2, 'twitter');
        $this->assertEqual(sizeof($hashtags),1);

        //instances hashtags should be deleted
        $instances_hashtags = $instance_hashtag_dao->getByInstance(2);
        $this->assertEqual(sizeof($instances_hashtags),2);

        //hashtag should be deleted
        $hashtag_dao = new HashtagMySQLDAO();
        $hashtag = $hashtag_dao->getHashtagByID(1);
        $this->assertEqual(sizeof($hashtag),1);
        $hashtag = $hashtag_dao->getHashtagByID(2);
        $this->assertEqual(sizeof($hashtag),1);

        //Should delete the owner instance, and since there's no other owner, the instance itself
        $this->simulateLogin('me@example.com', false, true);
        $_POST['action'] = "Delete";
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
        //hashtags posts should be deleted
        $hashtags = $hashtagpost_dao->getHashtagsForPost(1, 'twitter');
        $this->assertEqual(sizeof($hashtags),0);
        $hashtags = $hashtagpost_dao->getHashtagsForPost(2, 'twitter');
        $this->assertEqual(sizeof($hashtags),0);

        //instances hashtags should be deleted
        $instances_hashtags = $instance_hashtag_dao->getByInstance(2);
        $this->assertEqual(sizeof($instances_hashtags),0);

        //hashtag should be deleted
        $hashtag = $hashtag_dao->getHashtagByID(1);
        $this->assertEqual(sizeof($hashtag),0);
        $hashtag = $hashtag_dao->getHashtagByID(2);
        $this->assertEqual(sizeof($hashtag),0);

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
        $this->assertEqual($success_msgs['account'], 'Account and its saved searches deleted.');
        $this->assertNull($v_mgr->getTemplateDataItem('error_msg'));
    }

    public function testDeleteExistingInstanceWithTweetSearchWithPrivilegesWithOtherOwners() {
        $this->debug(__METHOD__);

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
        $instance_hashtag_dao = new InstanceHashtagMySQLDAO();

        //before builder
        $instance = $instance_dao->get(2);
        $this->assertNotNull($instance);
        $instances_hashtags = $instance_hashtag_dao->getByInstance($instance->id);
        $this->assertEqual(sizeof($instances_hashtags),0);

        $builder = $this->buildHashtagData(2);

        //after builder
        $hashtag_dao = new HashtagMySQLDAO();
        $hashtagpost_dao = new HashtagPostMySQLDAO();
        $hashtags = $hashtagpost_dao->getHashtagsForPost(1, 'twitter');
        $this->assertEqual(sizeof($hashtags),1);
        $hashtags = $hashtagpost_dao->getHashtagsForPost(2, 'twitter');
        $this->assertEqual(sizeof($hashtags),1);

        //instances hashtags should be deleted
        $instances_hashtags = $instance_hashtag_dao->getByInstance(2);
        $this->assertEqual(sizeof($instances_hashtags),2);

        //hashtag should be deleted
        $hashtag = $hashtag_dao->getHashtagByID(1);
        $this->assertEqual(sizeof($hashtag),1);
        $hashtag = $hashtag_dao->getHashtagByID(2);
        $this->assertEqual(sizeof($hashtag),1);

        //Should delete the owner instance but leave the instance alone
        $this->simulateLogin('me@example.com', false, true);
        $_POST['action'] = "Delete";
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

        //hashtags posts should be deleted
        $hashtags = $hashtagpost_dao->getHashtagsForPost(1, 'twitter');
        $this->assertEqual(sizeof($hashtags),1);
        $hashtags = $hashtagpost_dao->getHashtagsForPost(2, 'twitter');
        $this->assertEqual(sizeof($hashtags),1);

        //instances hashtags should NOT be deleted
        $instances_hashtags = $instance_hashtag_dao->getByInstance(2);
        $this->assertEqual(sizeof($instances_hashtags),2);

        //hashtag should NOT be deleted
        $hashtag = $hashtag_dao->getHashtagByID(1);
        $this->assertEqual(sizeof($hashtag),1);
        $hashtag = $hashtag_dao->getHashtagByID(2);
        $this->assertEqual(sizeof($hashtag),1);
        $this->debug('Still running');

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

    public function testPluginShownWhenPSet() {
        $this->simulateLogin('admin@example.com', true, true);
        $_GET['p'] = "twitter";
        $controller = new AccountConfigurationController(true);
        $controller->go();
        $v_mgr = $controller->getViewManager();
        $this->assertTrue($v_mgr->getTemplateDataItem('force_plugin'));

        $_GET = array();
        $controller = new AccountConfigurationController(true);
        $controller->go();
        $v_mgr = $controller->getViewManager();
        $this->assertNull($v_mgr->getTemplateDataItem('force_plugin'));
    }

    private function buildHashtagData($instance) {
        $builders = array();

        $builders[] = FixtureBuilder::build('hashtags',
        array('hashtag' => 'first', 'network' => 'twitter', 'count_cache' => 0));
        $builders[] = FixtureBuilder::build('hashtags',
        array('hashtag' => 'second', 'network' => 'twitter', 'count_cache' => 0));
        $builders[] = FixtureBuilder::build('instances_hashtags',
        array('instance_id' => $instance, 'hashtag_id' => 1, 'last_post_id' => '0', 'earliest_post_id' => 0));
        $builders[] = FixtureBuilder::build('instances_hashtags',
        array('instance_id' => $instance, 'hashtag_id' => 2, 'last_post_id' => '0', 'earliest_post_id' => 0));
        $builders[] = FixtureBuilder::build('hashtags_posts',
        array('post_id' => 1, 'hashtag_id' => 1, 'network' => 'twitter'));
        $builders[] = FixtureBuilder::build('hashtags_posts',
        array('post_id' => 2, 'hashtag_id' => 2, 'network' => 'twitter'));

        $builders[] = FixtureBuilder::build('posts', array(
            'post_id' => '1',
            'author_user_id' => '100',
            'author_username' => 'ecucurella',
            'author_fullname' => 'Eduard Cucurella',
            'author_avatar' => 'http://aa.com',
            'author_follower_count' => 0,
            'post_text' => '#Messi is the best http://flic.kr/p/ http://flic.kr/a/',
            'is_protected' => 0,
            'source' => '<a href=""></a>',
            'location' => 'BCN',
            'place' => '',
            'place_id' => '',
            'geo' => '',
            'pub_date' => '2013-02-28 11:02:34',
            'in_reply_to_user_id' => '',
            'in_reply_to_post_id' => '',
            'reply_count_cache' => 1,
            'is_reply_by_friend' => 0,
            'in_retweet_of_post_id' => '',
            'old_retweet_count_cache' => 0,
            'is_retweet_by_friend' => 0,
            'reply_retweet_distance' => 0,
            'network' => 'twitter',
            'is_geo_encoded' => 0,
            'in_rt_of_user_id' => '',
            'retweet_count_cache' => 0,
            'retweet_count_api' => 0,
            'favlike_count_cache' => 0));

        $builders[] = FixtureBuilder::build('posts', array(
            'post_id' => '2',
            'author_user_id' => '101',
            'author_username' => 'vetcastellnou',
            'author_fullname' => 'Veterans Castellnou',
            'author_avatar' => 'http://aa.com',
            'author_follower_count' => 0,
            'post_text' => 'Post without any hashtag http://flic.kr/p/',
            'is_protected' => 0,
            'source' => '<a href=""></a>',
            'location' => 'BCN',
            'place' => '',
            'place_id' => '',
            'geo' => '',
            'pub_date' => '2013-02-28 11:02:34',
            'in_reply_to_user_id' => '',
            'in_reply_to_post_id' => '',
            'reply_count_cache' => 1,
            'is_reply_by_friend' => 0,
            'in_retweet_of_post_id' => '',
            'old_retweet_count_cache' => 0,
            'is_retweet_by_friend' => 0,
            'reply_retweet_distance' => 0,
            'network' => 'twitter',
            'is_geo_encoded' => 0,
            'in_rt_of_user_id' => '',
            'retweet_count_cache' => 0,
            'retweet_count_api' => 0,
            'favlike_count_cache' => 0));

        return $builders;
    }
}
