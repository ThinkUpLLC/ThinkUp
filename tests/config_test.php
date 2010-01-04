<?php
require_once(dirname(__FILE__) . '/simpletest/autorun.php');


require_once(dirname(__FILE__) . '/config.tests.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);
require_once("class.Config.php");

class TestOfConfig extends UnitTestCase {
    function TestOfConfig() {
        $this->UnitTestCase('Config class test');
    }

    function setUp() {
       // @unlink('J:\data\code\twitalytic\test\temp\test.log');
    }
    function tearDown() {
       // @unlink('J:\data\code\twitalytic\test\temp\test.log');
    }

	function testCreatingNewConfig() {
		global $THINKTANK_CFG;
		$cfg = new Config();
		$this->assertTrue($cfg->log_location == $THINKTANK_CFG['log_location'], 'Log location set');
	}
}

?>
