<?php
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

/**
 * Test of CheckCrawlerController
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfCheckCrawlerController extends ThinkUpUnitTestCase {

    public function __construct() {
        $this->UnitTestCase('CheckCrawlerController class test');
    }

    public function setUp(){
        parent::setUp();
    }

    public function tearDown(){
        parent::tearDown();
    }

    public function testConstructor() {
        $controller = new CheckCrawlerController(true);
        $this->assertTrue(isset($controller));
    }

    public function testNoInstances() {
        $controller = new CheckCrawlerController(true);
        $results = $controller->go();
        $this->assertEqual('', $results);
    }

    public function testInstanceLessThan3Hours() {
        $instance_builder = FixtureBuilder::build('instances', array('crawler_last_run'=>'-1h'));
        $controller = new CheckCrawlerController(true);
        $results = $controller->go();
        $this->assertEqual('', $results);
    }

    public function testInstanceMoreThan3Hours() {
        $instance_builder = FixtureBuilder::build('instances', array('crawler_last_run'=>'-4h'));
        $controller = new CheckCrawlerController(true);
        $results = $controller->go();
        $this->assertEqual("Crawler hasn't run in 4 hours", $results);
    }

}
