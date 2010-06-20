<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankBasicUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Profiler.php';

/**
 * Test of Profiler object
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfProfiler extends ThinkTankBasicUnitTestCase {
    /**
     * Constructor
     */
    public function __construct() {
        $this->UnitTestCase('Profiler class test');
    }
    /**
     * Test Profiler singleton instantiation
     */
    public function testProfilerSingleton() {
        $profiler = Profiler::getInstance();
        $this->assertTrue(isset($profiler), 'constructor');
        $this->assertIsA($profiler, 'Profiler', 'object type');
    }

    public function testAdd() {
        $profiler = Profiler::getInstance();
        $profiler->add(0.02503434, 'My 1st action');
        $profiler->add(0.02303434, 'My 2nd action');
        $profiler->add(0.12003434, 'My 3rd action');
        $profiler->add(0.62003434, 'My 4th action', true, 10);
        $profiler->add(0.40003434, 'My 5th action', true);
        $actions = $profiler->getProfile();
        $this->assertEqual($actions[0]['time'], '0.620');
        $this->assertEqual($actions[0]['action'], 'My 4th action');
        $this->assertEqual($actions[0]['num_rows'], 10);
        $this->assertEqual($profiler->total_queries, 2);
    }
}
