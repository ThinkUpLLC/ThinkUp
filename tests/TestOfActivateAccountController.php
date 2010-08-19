<?php
require dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

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