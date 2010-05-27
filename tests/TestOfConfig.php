<?php 
require_once dirname(__FILE__).'/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'webapp/model/class.Config.php';

class TestOfConfig extends UnitTestCase {
    function TestOfConfig() {
        $this->UnitTestCase('Config class test');
    }
    
    function setUp() {
    }
    function tearDown() {
    }
    
    function testConfigSingleton() {
        // this here just to test values, not needed in normal use.
        global $THINKTANK_CFG;
        // prefered method is:
        // $config = Config::getInstance(); $config->getValue('log_location');
        $config = Config::getInstance();
        $this->assertTrue($config->getValue('log_location') == $THINKTANK_CFG['log_location'], 'Log location set');
    }
}
?>
