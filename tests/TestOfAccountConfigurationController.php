<?php
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

if (isset($RUNNING_ALL_TESTS) && !$RUNNING_ALL_TESTS) {
    require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/twitteroauth/twitteroauth.php';
}
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterOAuthThinkUp.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterPlugin.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/controller/class.TwitterPluginConfigurationController.php';

/**
 * Test of AccountConfigurationController
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfAccountConfigurationController extends ThinkUpUnitTestCase {

    public function __construct() {
        $this->UnitTestCase('AccountConfigurationController class test');
    }

    public function setUp(){
        parent::setUp();
        $webapp = Webapp::getInstance();
        $webapp->registerPlugin('twitter', 'TwitterPlugin');

        //Add owner
        $session = new Session();
        $cryptpass = $session->pwdcrypt("oldpassword");
        $q = "INSERT INTO tu_owners SET id=1, full_name='ThinkUp J. User', email='me@example.com', is_activated=1,
        pwd='".$cryptpass."', activation_code='8888'";
        $this->db->exec($q);

        $q = "INSERT INTO tu_owners SET id=2, full_name='ThinkUp J. Admin', email='admin@example.com',
        is_activated=1, is_admin=1, pwd='XXX', activation_code='8888'";
        $this->db->exec($q);

        //Add instance_owner
        $q = "INSERT INTO tu_owner_instances (owner_id, instance_id, oauth_access_token, oauth_access_token_secret)
        VALUES (1, 1, 'xxx', 'yyy')";
        $this->db->exec($q);
        $q = "INSERT INTO tu_owner_instances (owner_id, instance_id, oauth_access_token, oauth_access_token_secret)
        VALUES (2, 1, 'xxx', 'yyy')";
        $this->db->exec($q);

        //Insert test data into test table
        $q = "INSERT INTO tu_users (user_id, user_name, full_name, avatar, last_updated) VALUES (13, 'ev',
        'Ev Williams', 'avatar.jpg', '1/1/2005');";
        $this->db->exec($q);

        //Make public
        $q = "INSERT INTO tu_instances (id, network_user_id, network_username, is_public, network)
        VALUES (1, 13, 'ev', 1, 'twitter');";
        $this->db->exec($q);
    }

    public function tearDown() {
        parent::tearDown();
    }
    public function testConstructor() {
        $controller = new AccountConfigurationController(true);
        $this->assertTrue(isset($controller), 'constructor test');

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Configure Your Account');
    }

    public function testControlNotLoggedIn() {
        $controller = new AccountConfigurationController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $config = Config::getInstance();
        $this->assertEqual('You must <a href="'.$config->getValue('site_root_path').
        'session/login.php">log in</a> to do this.', $v_mgr->getTemplateDataItem('errormsg'));
    }

    public function testAuthControlLoggedInNotAdmin() {
        $_SESSION['user'] = 'me@example.com';
        $controller = new AccountConfigurationController(true);
        $results = $controller->go();

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertIsA($v_mgr->getTemplateDataItem('installed_plugins'), 'array');
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('installed_plugins')), 6);

        $owner = $v_mgr->getTemplateDataItem('owner');
        $this->assertIsA($owner, 'Owner');
        $this->assertTrue(!$owner->is_admin);
        $this->assertEqual($owner->full_name, 'ThinkUp J. User');
        $this->assertEqual($owner->email, 'me@example.com');

        //not set: owners, body, successmsg, errormsg
        $this->assertTrue(!$v_mgr->getTemplateDataItem('owners'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('body'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('successmsg'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('errormsg'));
    }

    public function testAuthControlLoggedInAdmin() {
        $_SESSION['user'] = 'admin@example.com';
        $controller = new AccountConfigurationController(true);
        $results = $controller->go();

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertIsA($v_mgr->getTemplateDataItem('installed_plugins'), 'array');
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('installed_plugins')), 6);

        $owner = $v_mgr->getTemplateDataItem('owner');
        $this->assertIsA($owner, 'Owner');
        $this->assertTrue($owner->is_admin);
        $this->assertEqual($owner->full_name, 'ThinkUp J. Admin');
        $this->assertEqual($owner->email, 'admin@example.com');
        $this->assertIsA($v_mgr->getTemplateDataItem('owners'), 'array');
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('owners')), 2);

        //not set: owners, body, successmsg, errormsg
        $this->assertTrue(!$v_mgr->getTemplateDataItem('body'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('successmsg'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('errormsg'));
    }

    public function testAuthControlLoggedInSpecificPluginExists() {
        $_SESSION['user'] = 'me@example.com';
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

        //not set: owners, body, successmsg, errormsg
        $this->assertTrue(!$v_mgr->getTemplateDataItem('owners'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('successmsg'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('errormsg'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('installed_plugins'));
    }

    public function testAuthControlLoggedInSpecificPluginDoesNotExist() {
        $_SESSION['user'] = 'me@example.com';
        $_GET['p'] = 'idontexist';
        $controller = new AccountConfigurationController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $config = Config::getInstance();
        $this->assertEqual('No plugin object defined for: idontexist', $v_mgr->getTemplateDataItem('errormsg'));
        $owner = $v_mgr->getTemplateDataItem('owner');
        $this->assertIsA($owner, 'Owner');
        $this->assertTrue(!$owner->is_admin);
        $this->assertEqual($owner->full_name, 'ThinkUp J. User');
        $this->assertEqual($owner->email, 'me@example.com');

        //not set: owners, body, successmsg, errormsg
        $this->assertTrue(!$v_mgr->getTemplateDataItem('owners'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('successmsg'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('installed_plugins'));
    }

    public function testAuthControlLoggedInChangePasswordSuccess() {
        $_SESSION['user'] = 'me@example.com';
        $_POST['changepass'] = 'Change password';
        $_POST['oldpass'] = 'oldpassword';
        $_POST['pass1'] = 'newpassword';
        $_POST['pass2'] = 'newpassword';

        $controller = new AccountConfigurationController(true);
        $results = $controller->go();

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertIsA($v_mgr->getTemplateDataItem('installed_plugins'), 'array');
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('installed_plugins')), 6);

        $owner = $v_mgr->getTemplateDataItem('owner');
        $this->assertIsA($owner, 'Owner');
        $this->assertTrue(!$owner->is_admin);
        $this->assertEqual($owner->full_name, 'ThinkUp J. User');
        $this->assertEqual($owner->email, 'me@example.com');
        $this->assertEqual($v_mgr->getTemplateDataItem('successmsg'), 'Your password has been updated.');

        //not set: owners, body, successmsg, errormsg
        $this->assertTrue(!$v_mgr->getTemplateDataItem('owners'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('body'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('errormsg'));
    }

    public function testAuthControlLoggedInChangePasswordOldPwdDoesntMatch() {
        $_SESSION['user'] = 'me@example.com';
        $_POST['changepass'] = 'Change password';
        $_POST['oldpass'] = 'oldddpassword';
        $_POST['pass1'] = 'newpassword';
        $_POST['pass2'] = 'newpassword';

        $controller = new AccountConfigurationController(true);
        $results = $controller->go();

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertIsA($v_mgr->getTemplateDataItem('installed_plugins'), 'array');
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('installed_plugins')), 6);

        $owner = $v_mgr->getTemplateDataItem('owner');
        $this->assertIsA($owner, 'Owner');
        $this->assertTrue(!$owner->is_admin);
        $this->assertEqual($owner->full_name, 'ThinkUp J. User');
        $this->assertEqual($owner->email, 'me@example.com');
        $this->assertEqual($v_mgr->getTemplateDataItem('errormsg'), 'Old password does not match or empty.');

        //not set: owners, body, successmsg, errormsg
        $this->assertTrue(!$v_mgr->getTemplateDataItem('owners'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('body'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('successmsg'));
    }

    public function testAuthControlLoggedInChangePasswordOldPwdEmpty() {
        $_SESSION['user'] = 'me@example.com';
        $_POST['changepass'] = 'Change password';
        $_POST['oldpass'] = '';
        $_POST['pass1'] = 'newpassword';
        $_POST['pass2'] = 'newpassword';

        $controller = new AccountConfigurationController(true);
        $results = $controller->go();

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertIsA($v_mgr->getTemplateDataItem('installed_plugins'), 'array');
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('installed_plugins')), 6);

        $owner = $v_mgr->getTemplateDataItem('owner');
        $this->assertIsA($owner, 'Owner');
        $this->assertTrue(!$owner->is_admin);
        $this->assertEqual($owner->full_name, 'ThinkUp J. User');
        $this->assertEqual($owner->email, 'me@example.com');
        $this->assertEqual($v_mgr->getTemplateDataItem('errormsg'), 'Old password does not match or empty.');

        //not set: owners, body, successmsg, errormsg
        $this->assertTrue(!$v_mgr->getTemplateDataItem('owners'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('body'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('successmsg'));
    }

    public function testAuthControlLoggedInChangePasswordNewPwdsDontMatch() {
        $_SESSION['user'] = 'me@example.com';
        $_POST['changepass'] = 'Change password';
        $_POST['oldpass'] = 'oldpassword';
        $_POST['pass1'] = 'newpassword1';
        $_POST['pass2'] = 'newpassword2';

        $controller = new AccountConfigurationController(true);
        $results = $controller->go();

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertIsA($v_mgr->getTemplateDataItem('installed_plugins'), 'array');
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('installed_plugins')), 6);

        $owner = $v_mgr->getTemplateDataItem('owner');
        $this->assertIsA($owner, 'Owner');
        $this->assertTrue(!$owner->is_admin);
        $this->assertEqual($owner->full_name, 'ThinkUp J. User');
        $this->assertEqual($owner->email, 'me@example.com');
        $this->assertEqual($v_mgr->getTemplateDataItem('errormsg'),
        'New passwords did not match. Your password has not been changed.');

        //not set: owners, body, successmsg, errormsg
        $this->assertTrue(!$v_mgr->getTemplateDataItem('owners'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('body'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('successmsg'));
    }

    public function testAuthControlLoggedInChangePasswordNewPwdTooShort() {
        $_SESSION['user'] = 'me@example.com';
        $_POST['changepass'] = 'Change password';
        $_POST['oldpass'] = 'oldpassword';
        $_POST['pass1'] = 'new1';
        $_POST['pass2'] = 'new1';

        $controller = new AccountConfigurationController(true);
        $results = $controller->go();

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertIsA($v_mgr->getTemplateDataItem('installed_plugins'), 'array');
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('installed_plugins')), 6);

        $owner = $v_mgr->getTemplateDataItem('owner');
        $this->assertIsA($owner, 'Owner');
        $this->assertTrue(!$owner->is_admin);
        $this->assertEqual($owner->full_name, 'ThinkUp J. User');
        $this->assertEqual($owner->email, 'me@example.com');
        $this->assertEqual($v_mgr->getTemplateDataItem('errormsg'),
        'New password must be at least 5 characters. Your password has not been changed.');

        //not set: owners, body, successmsg, errormsg
        $this->assertTrue(!$v_mgr->getTemplateDataItem('owners'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('body'));
        $this->assertTrue(!$v_mgr->getTemplateDataItem('successmsg'));
    }

}

