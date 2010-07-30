<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkUpUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.ThinkUpController.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.ThinkUpAuthController.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.ActivateAccountController.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.LoginController.php';
require_once $SOURCE_ROOT_PATH.'extlib/Smarty-2.6.26/libs/Smarty.class.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.SmartyThinkUp.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Profiler.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Session.php';
require_once $SOURCE_ROOT_PATH.'tests/fixtures/class.FixtureBuilder.php';

/**
 * Test of ActivateAccountController
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfActivateAccountController extends ThinkUpUnitTestCase {

    public function __construct() {
        $this->UnitTestCase('ActivateAccountController class test');
    }

    public function testNoParams() {
        $controller = new ActivateAccountController(true);
        $results = $controller->go();
        $this->assertTrue(strpos( $results, "Invalid account activation credentials.") > 0 );
    }

    public function testInvalidActivation() {
        $owner = array('id'=>1, 'email'=>'me@example.com', 'activation_code'=>'1001', 'is_activated'=>0);
        $builder1 = FixtureBuilder::build('owners', $owner);
        $_GET['usr'] = 'me@example.com';
        $_GET['code'] = 'invalidcode';

        $controller = new ActivateAccountController(true);
        $results = $controller->go();
        $this->assertTrue(strpos( $results, "Houston, we have a problem: Account activation failed.") > 0 );

        $_GET['usr'] = 'idontexist@example.com';
        $_GET['code'] = 'invalidcode';

        $controller = new ActivateAccountController(true);
        $results = $controller->go();
        $this->assertTrue(strpos( $results, "Houston, we have a problem: Account activation failed.") > 0 );

        $_GET['usr'] = 'me@example.com';
        $_GET['code'] = '10011';

        $controller = new ActivateAccountController(true);
        $results = $controller->go();
        $this->assertTrue(strpos( $results, "Houston, we have a problem: Account activation failed.") > 0 );
    }

    public function testValidActivation() {
        $owner = array('id'=>1, 'email'=>'me@example.com', 'activation_code'=>'1001', 'is_activated'=>0);
        $builder1 = FixtureBuilder::build('owners', $owner);
        $_GET['usr'] = 'me@example.com';
        $_GET['code'] = '1001';

        $controller = new ActivateAccountController(true);
        $results = $controller->go();
        $this->assertTrue(strpos( $results, "Success! Your account has been activated. Please log in.") > 0, $results );
    }
}