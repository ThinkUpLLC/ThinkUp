<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkUpUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.ThinkUpController.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.ThinkUpAuthController.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.PublicTimelineController.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Instance.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.LogoutController.php';
require_once $SOURCE_ROOT_PATH.'extlib/Smarty-2.6.26/libs/Smarty.class.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.SmartyThinkUp.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Profiler.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Session.php';

/**
 * Test of LoginController
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfLogoutController extends ThinkUpUnitTestCase {
    public function __construct() {
        $this->UnitTestCase('LogoutController class test');
    }

    public function testLogoutNotLoggedIn() {
        $controller = new LogoutController(true);
        $results = $controller->go();
        $v_mgr = $controller->getViewManager();
        $config = Config::getInstance();
        $this->assertEqual('You must <a href="'.$config->getValue('site_root_path').
        'session/login.php">log in</a> to do this.', $v_mgr->getTemplateDataItem('errormsg'));
    }

    public function testLogoutWhileLoggedIn() {
        $_SESSION['user'] = 'me@example.com';
        $controller = new LogoutController(true);
        $results = $controller->go();
        $this->assertTrue(strpos( $results, "You have successfully logged out") > 0 );
    }
}