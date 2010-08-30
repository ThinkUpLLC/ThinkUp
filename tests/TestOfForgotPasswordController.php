<?php
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

/**
 * Test ForgotPasswordController class
 */
class TestOfForgotPasswordController extends ThinkUpUnitTestCase {

    public function __construct() {
        $this->UnitTestCase('ForgotPasswordController class test');
    }

    public function setUp() {
        parent::setUp();

        $session = new Session();
        $cryptpass = $session->pwdcrypt("oldpassword");
        $q = <<<SQL
INSERT INTO #prefix#owners SET
    id = 1,
    full_name = 'ThinkUp J. User',
    email = 'me@example.com',
    pwd = '$cryptpass',
    activation_code='8888',
    is_activated =1
SQL;
        $this->db->exec($q);
    }

    public function testOfControllerNoParams() {
        $controller = new ForgotPasswordController(true);
        $result = $controller->go();

        $this->assertTrue(strpos($result, 'Forgot Password') > 0);
    }

    public function testOfControllerWithBadEmailAddress() {
        $_POST['email'] = 'im a broken email address';
        $_POST['Submit'] = "Send";

        $controller = new ForgotPasswordController(true);
        $result = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('errormsg'), 'Error: account does not exist.');
    }

    public function testOfControllerWithValidEmailAddress() {
        $_POST['email'] = 'me@example.com';
        $_POST['Submit'] = "Send";

        $controller = new ForgotPasswordController(true);
        $result = $controller->go();

        $this->assertTrue(strpos($result, 'Password recovery information has been sent to your email address.') > 0);
    }
}
