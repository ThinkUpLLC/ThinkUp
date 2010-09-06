<?php
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

/**
 * Test of TestAuthAPIController
 *
 * @author Guillaume Boudreau <gboudreau@pommepause.com>
 *
 */
class TestOfTestAuthAPIController extends ThinkUpUnitTestCase {
    public function __construct() {
        $this->UnitTestCase('TestAuthAPIController class test');
    }

    public function setUp() {
        parent::setUp();
        $_SERVER['HTTP_HOST'] = 'http://localhost';
    }
    
    public function testConstructor() {
        $controller = new TestAuthAPIController(true);
        $this->assertTrue(isset($controller));
    }

    public function testControl() {
        $builders = $this->buildData();

        $controller = new TestAuthAPIController(true);

        // No username, no API secret provided
        $results = $controller->go();
        $this->assertPattern("/UnauthorizedUserException: Unauthorized API call/", $results);
        
        // No API secret provided
        $_GET['un'] = 'me@example.com';
        $results = $controller->go();
        $this->assertPattern("/UnauthorizedUserException: Unauthorized API call/", $results);
        
        // Wrong API secret provided
        $_GET['as'] = 'fail_me';
        $results = $controller->go();
        $this->assertPattern("/UnauthorizedUserException: Unauthorized API call/", $results);
        
        // Wrong username provided
        $_GET['as'] = Session::getAPISecretFromPassword('XXX');
        $_GET['un'] = 'fail_me';
        $results = $controller->go();
        $this->assertPattern("/UnauthorizedUserException: Unauthorized API call/", $results);

        // Working request
        $_GET['un'] = 'me@example.com';
        $_GET['as'] = Session::getAPISecretFromPassword('XXX');
        $results = $controller->go();
        $this->assertPattern('/{"result":"success"}/', $results);

        $this->assertEqual($_SESSION['user'], 'me@example.com');

        // Now that _SESSION['user'] is set, we shouldn't need to provide un/as to use this controller
        // Also, the result will be returned as HTML, not JSON
        unset($_GET['as']);
        $results = $controller->go();
        $this->assertPattern('/<html/', $results);

        // And just to make sure, if we 'logout', we should be denied access now
        unset($_SESSION['user']);
        $results = $controller->go();
        $this->assertPattern("/UnauthorizedUserException: Unauthorized API call/", $results);
    }

    public function testGetLoggedInUser() {
        // Using _POST
        $builders = $this->buildData();
        $controller = new TestAuthAPIController(true);
        $_POST['un'] = 'me@example.com';
        $_POST['as'] = Session::getAPISecretFromPassword('XXX');
        $results = $controller->go();
        $this->assertPattern('/{"result":"success"}/', $results);
    }

    public function testGetAuthParameters() {
        $builders = $this->buildData();
        $this->assertEqual(ThinkUpAuthAPIController::getAuthParameters('me@example.com'), 
        'un=me%40example.com&as=1829cc1b13f920a05fb201e8d2a9e4dc58b669b1');
    }
    
    public function testIsAPICall() {
        $builders = $this->buildData();
        $controller = new TestAuthAPIController(true);
        
        // API call (JSON)
        $_GET['un'] = 'me@example.com';
        $_GET['as'] = Session::getAPISecretFromPassword('XXX');
        $results = $controller->go();
        $this->assertPattern('/{"result":"success"}/', $results);
        $this->assertFalse(strpos($results, '<html'));
        unset($_GET['as']);
        unset($_GET['un']);
        
        // HTML
        $_SESSION['user'] = 'me@example.com';
        $results = $controller->go();
        $this->assertFalse(strpos($results, '{"result":"success"}'));
        $this->assertPattern('/<html/', $results);
    }

    private function buildData() {
        $owner_builder = FixtureBuilder::build('owners', array(
            'id' => 1, 
            'email' => 'me@example.com', 
            'pwd' => 'XXX', 
            'is_activated' => 1
        ));
        
        $instance_builder = FixtureBuilder::build('instances', array(
            'id' => 1,
            'network_username' => 'jack',
            'network' => 'twitter'
        ));

        $owner_instance_builder = FixtureBuilder::build('owner_instances', array(
            'owner_id' => 1, 
            'instance_id' => 1
        ));
        
        return array($owner_builder, $instance_builder, $owner_instance_builder);
    }
}
