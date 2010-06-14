<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankBasicUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Config.php';

/**
 * Test of Config object
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfConfig extends ThinkTankBasicUnitTestCase {
    /**
     * Constructor
     */
    function __construct() {
        $this->UnitTestCase('Config class test');
    }
    /**
     * Test config singleton instantiation
     */
    function testConfigSingleton() {
        // this here just to test values, not needed in normal use.
        global $THINKTANK_CFG;
        // prefered method is:
        // $config = Config::getInstance(); $config->getValue('log_location');
        $config = Config::getInstance();
        $this->assertTrue($config->getValue('log_location') == $THINKTANK_CFG['log_location'], 'Log location set');
    }
}
