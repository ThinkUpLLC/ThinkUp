<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.ThinkTankController.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.ThinkTankAuthController.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.PublicTimelineController.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Instance.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.LogoutController.php';
require_once $SOURCE_ROOT_PATH.'extlib/Smarty-2.6.26/libs/Smarty.class.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.SmartyThinkTank.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Profiler.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Session.php';

/**
 * Test of LoginController
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfLogoutController extends ThinkTankUnitTestCase {
    public function __construct() {
        $this->UnitTestCase('LogoutController class test');
    }

    public function testLogoutNotLoggedIn() {
        $controller = new LogoutController(true);
        $results = $controller->go();
        $this->assertEqual($results, "You must be logged in to do this");
    }

    public function testLogoutWhileLoggedIn() {
        $_SESSION['user'] = 'me@example.com';
        $controller = new LogoutController(true);
        $results = $controller->go();
        $this->assertTrue(strpos( $results, "You have successfully logged out") > 0 );
    }
}