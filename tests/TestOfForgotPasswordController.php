<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkUpUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.ThinkUpController.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.ForgotPasswordController.php';
require_once $SOURCE_ROOT_PATH.'extlib/Smarty-2.6.26/libs/Smarty.class.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.SmartyThinkUp.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Config.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Owner.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.OwnerInstance.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Instance.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.DAOFactory.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Profiler.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Session.php';
require_once $SOURCE_ROOT_PATH.'webapp/config.inc.php';

/**
 * Test ForgotPasswordController class
 */
class TestOfForgotPasswordController extends ThinkUpUnitTestCase {

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
