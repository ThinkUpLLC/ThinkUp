<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.PluginHook.php';

class TestOfPluginHook extends ThinkTankUnitTestCase {

	function TestOfPluginHook() {
		$this->UnitTestCase('PluginHook class test');
	}

	function setUp() {
	}

	function tearDown() {
	}

	function testRegisterPlugin() {
		$ph = new PluginHook();
		$ph->registerPlugin('facebook', "FacebookPlugin");
		$plugin_obj = $ph->getPluginObject("facebook");
		$this->assertEqual($plugin_obj, "FacebookPlugin");

		$this->expectException( new Exception("No plugin object defined for: notregistered") );
		$plugin_obj = $ph->getPluginObject("notregistered");
	}
}