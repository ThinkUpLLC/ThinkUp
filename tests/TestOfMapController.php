<?php
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

/**
 * Test of Map Controller
 *
 * @author Ekansh Preet Singh <ekanshpreet[at]gmail[dot]com>
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 */
class TestOfMapController extends ThinkUpUnitTestCase {

    /**
     * Constructor
     */
    public function __construct() {
        $this->UnitTestCase('MapController class test');
    }

    /**
     * Test constructor
     */
    public function testConstructor() {
        $controller = new MapController(true);
        $this->assertTrue(isset($controller), 'constructor test');
    }

    /**
     * Test controller when all data is correctly provided
     */
    public function testControlCreateMap() {
        $builder = $this->testInstantiateFixtureBuilder();
        $_GET["pid"] = '1001';
        $_GET["t"] = 'post';
        $_GET["n"] = 'twitter';
        $controller = new MapController(true);
        $results = $controller->go();

        $this->assertTrue(strpos($results, 'This is a test post') > 0);
    }

    /**
     * Test controller when post ID is invalid/non-existant
     */
    public function testControlNonNumericPostID(){
        $builder = $this->testInstantiateFixtureBuilder();
        $this->simulateLogin('me@example.com');
        $_GET["pid"] = 'notapostID45';

        $controller = new MapController(true);
        $results = $controller->go();

        $this->assertTrue(strpos( $results, "No visualization data found for this post") > 0, "no post");
    }

    /**
     * Test controller when post ID is invalid/non-existant
     */
    public function testMissingPostID(){
        $builder = $this->testInstantiateFixtureBuilder();
        $this->simulateLogin('me@example.com');

        $controller = new MapController(true);
        $results = $controller->go();

        $this->assertTrue(strpos( $results, "No visualization data found for this post") > 0, "no post");
    }

    /**
     * Test controller when network is invalid
     */
    public function testControlInvalidNetwork(){
        $builder = $this->testInstantiateFixtureBuilder();
        $this->simulateLogin('me@example.com');
        $_GET['n'] = 'notavalidnetwork';
        $_GET["pid"] = '1001';

        $controller = new MapController(true);
        $results = $controller->go();

        $this->assertTrue(strpos( $results, "No visualization data found for this post") > 0, "no post");
    }

    /**
     * Test controller when type is invalid
     */
    public function testControlInvalidType(){
        $builder = $this->testInstantiateFixtureBuilder();
        $this->simulateLogin('me@example.com');
        $_GET["pid"] = '1001';
        $_GET["t"] = 'notavalidtype';

        $controller = new MapController(true);
        $results = $controller->go();

        $this->assertTrue(strpos( $results, "No visualization data found for this post") > 0, "no post");
    }

    /**
     * Method to instantiate FixtureBuilder
     */
    private function testInstantiateFixtureBuilder() {
        $post_data = array(
            'post_id' => 1001,
            'post_text' => 'This is a test post',
            'location' => 'New Delhi, Delhi, India',
            'geo' => '28.11,78.08',
            'is_geo_encoded' => 1
        );
        $builder = FixtureBuilder::build('posts', $post_data);
        return $builder;
    }
}