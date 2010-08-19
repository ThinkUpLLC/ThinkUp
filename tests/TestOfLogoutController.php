<?php
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

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