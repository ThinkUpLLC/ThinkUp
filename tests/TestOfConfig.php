<?php 
require_once dirname(__FILE__).'/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once "model/class.Config.php";

class TestOfConfig extends UnitTestCase {
    function TestOfConfig() {
        $this->UnitTestCase('Config class test');
    }
    
    function setUp() {
    }
    function tearDown() {
    }
    
    function testCreatingNewConfig() {
        global $THINKTANK_CFG;
        $cfg = new Config();
        $this->assertTrue($cfg->log_location == $THINKTANK_CFG['log_location'], 'Log location set');
    }
    
    function testConfigSingleton() {
        // this here just to test values, not needed in normal use.
        // prefered method would be as
        // $cfg = Config::getInstance(); $cfg->getValue('log_location');
        global $THINKTANK_CFG;
        $cfg = Config::getInstance();
        $this->assertTrue($cfg->log_location == $THINKTANK_CFG['log_location'], 'Log location set');
        $this->assertTrue($cfg->config['log_location'] == $THINKTANK_CFG['log_location'], 'Log location set');
        $this->assertTrue($cfg->getValue('log_location') == $THINKTANK_CFG['log_location'], 'Log location set');
    }
}
?>
