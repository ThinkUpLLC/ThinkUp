<?php
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterOAuthThinkUp.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterPlugin.php';

/**
 * Test of LoginController
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfLoginController extends ThinkUpUnitTestCase {
    var $builder1;
    var $builder2;
    var $builder3;

    public function __construct() {
        $this->UnitTestCase('LoginController class test');
    }

    public function setUp(){
        parent::setUp();
        $webapp = Webapp::getInstance();
        $webapp->registerPlugin('twitter', 'TwitterPlugin');

        $session = new Session();
        $cryptpass = $session->pwdcrypt("secretpassword");

        $owner = array('id'=>1, 'email'=>'me@example.com', 'pwd'=>$cryptpass, 'is_activated'=>1);
        $this->builder1 = FixtureBuilder::build('owners', $owner);

        $instance = array('id'=>1);
        $this->builder2 = FixtureBuilder::build('instances', $instance);

        $owner_instance = array('owner_id'=>1, 'instance_id'=>1);
        $this->builder3 = FixtureBuilder::build('owner_instances', $owner_instance);

    }

    public function tearDown() {
        $this->builder1 = null;
        $this->builder2 = null;
        $this->builder3 = null;
        parent::tearDown();
    }

    public function testNoSubmission() {
        $controller = new LoginController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Log in');
        $this->assertTrue(strpos( $results, "Log In") > 0 );
    }

    public function testNoEmail() {
        $_POST['Submit'] = 'Log In';
        $_POST['email'] = '';
        $_POST['pwd'] = 'somepassword';
        $controller = new LoginController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Log in');
        $this->assertEqual($v_mgr->getTemplateDataItem('errormsg'), 'Email must not be empty');
        $this->assertTrue(strpos( $results, "Log In") > 0 );
    }

    public function testNoPassword() {
        $_POST['Submit'] = 'Log In';
        $_POST['email'] = 'me@example.com';
        $_POST['pwd'] = '';
        $controller = new LoginController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Log in');
        $this->assertEqual($v_mgr->getTemplateDataItem('errormsg'), 'Password must not be empty');
        $this->assertTrue(strpos( $results, "Log In") > 0 );
    }

    public function testUserNotFound() {
        $_POST['Submit'] = 'Log In';
        $_POST['email'] = 'me1@example.com';
        $_POST['pwd'] = 'ddd';
        $controller = new LoginController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Log in');
        $this->assertEqual($v_mgr->getTemplateDataItem('errormsg'), 'Incorrect email');
        $this->assertTrue(strpos( $results, "Log In") > 0 );
    }

    public function testIncorrectPassword() {
        $_POST['Submit'] = 'Log In';
        $_POST['email'] = 'me@example.com';
        $_POST['pwd'] = 'notherightpassword';
        $controller = new LoginController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Log in');
        $this->assertEqual($v_mgr->getTemplateDataItem('errormsg'), 'Incorrect password');
        $this->assertTrue(strpos( $results, "Log In") > 0 );
    }

    public function testCorrectUserPassword() {
        $_POST['Submit'] = 'Log In';
        $_POST['email'] = 'me@example.com';
        $_POST['pwd'] = 'secretpassword';

        $controller = new LoginController(true);
        $results = $controller->go();

        $this->assertTrue(strpos( $results, "Logged in as: me@example.com") > 0 );
    }

    public function testAlreadyLoggedIn() {
        $_SESSION['user'] = 'me@example.com';

        $controller = new LoginController(true);
        $results = $controller->go();

        $this->assertTrue(strpos( $results, 'Logged in as: me@example.com') > 0 );
    }
}

