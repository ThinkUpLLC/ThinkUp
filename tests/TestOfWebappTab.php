<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankBasicUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.WebappTab.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.WebappTabDataset.php';

/**
 * Test of WebappTab
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfWebappTab extends ThinkTankBasicUnitTestCase {

    /**
     * Constructor
     */
    public function __construct() {
        $this->UnitTestCase('WebappTab class test');
    }

    /**
     * Set up test
     */
    public function setUp() {
        parent::setUp();
    }

    /**
     * Tear down test
     */
    public function tearDown() {
        parent::tearDown();
    }

    /**
     * Test constructor
     */
    public function testConstructor() {
        $tab = new WebappTab('my_short_name', "Name of My Tab");
        $this->assertEqual($tab->short_name, 'my_short_name');
        $this->assertEqual($tab->name, 'Name of My Tab');
        $this->assertEqual($tab->description, '');
        $this->assertEqual($tab->view_template, 'inline.view.tpl');

        $datasets = $tab->getDatasets();
        $this->assertIsA($datasets, 'array');
        $this->assertEqual(sizeof($datasets), 0);

    }
}