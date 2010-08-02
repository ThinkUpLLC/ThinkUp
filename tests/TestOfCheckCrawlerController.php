<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkUpUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.ThinkUpController.php';
require_once $SOURCE_ROOT_PATH.'tests/fixtures/class.FixtureBuilder.php';
require_once $SOURCE_ROOT_PATH.'tests/fixtures/class.FixtureBuilderException.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.CheckCrawlerController.php';
require_once $SOURCE_ROOT_PATH.'extlib/Smarty-2.6.26/libs/Smarty.class.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.SmartyThinkUp.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Instance.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.DAOFactory.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Profiler.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Session.php';

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
