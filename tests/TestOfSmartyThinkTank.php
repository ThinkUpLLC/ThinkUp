<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankBasicUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/config.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/Smarty-2.6.26/libs/Smarty.class.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Config.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.SmartyThinkTank.php';

/**
 * Test of SmartyThinkTank class
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class TestOfSmartyThinkTank extends ThinkTankBasicUnitTestCase {
    function __construct() {
        $this->UnitTestCase('SmartyThinkTank class test');
    }
    /**
     * Test constructor
     */
    function testNewSmartyThinkTank() {
        $smtt = new SmartyThinkTank();
        $this->assertTrue(isset($smtt));
    }

    /**
     * Test default values
     */
    function testSmartyThinkTankDefaultValues() {
        $cfg = Config::getInstance();
        $cfg->setValue('source_root_path', '/path/to/thinktank/');
        $cfg->setValue('cache_pages', true);
        $smtt = new SmartyThinkTank();

        $this->assertEqual($smtt->compile_dir, '/path/to/thinktank/webapp/view/compiled_view/');
        $this->assertTrue(sizeof($smtt->template_dir), 2);
        $this->assertEqual($smtt->template_dir[0], '/path/to/thinktank/webapp/view');
        $this->assertEqual($smtt->template_dir[1], '/path/to/thinktank/tests/view');
        $this->assertEqual($smtt->compile_dir, '/path/to/thinktank/webapp/view/compiled_view/');
        $this->assertTrue(sizeof($smtt->plugins_dir), 2);
        $this->assertEqual($smtt->plugins_dir[0], 'plugins');
        $this->assertEqual($smtt->cache_dir, '/path/to/thinktank/webapp/view/compiled_view/cache');
        $this->assertEqual($smtt->cache_lifetime, 300);
        $this->assertTrue($smtt->caching);
    }

    /**
     * Test assigned variables get saved when debug is true
     */
    function testSmartyThinkTankAssignedValuesDebugOn() {
        $cfg = Config::getInstance();
        $cfg->setValue('debug', true);
        $cfg->setValue('app_title', 'Testy ThinkTank Custom Application Name');
        $cfg->setValue('site_root_path', '/my/thinktank/folder/');
        $smtt = new SmartyThinkTank();

        $smtt->assign('test_var_1', "Testing, testing, 123");
        $this->assertEqual($smtt->getTemplateDataItem('test_var_1'), "Testing, testing, 123");

        $this->assertEqual($smtt->getTemplateDataItem('app_title'), 'Testy ThinkTank Custom Application Name');
        $this->assertEqual($smtt->getTemplateDataItem('logo_link'), 'index.php');
        $this->assertEqual($smtt->getTemplateDataItem('site_root_path'), '/my/thinktank/folder/');
    }

    /**
     * Test assigned variables don't get saved when debug is false
     */
    function testSmartyThinkTankAssignedValuesDebugOff() {
        $cfg = Config::getInstance();
        $cfg->setValue('debug', false);
        $smtt = new SmartyThinkTank();

        $smtt->assign('test_var_1', "Testing, testing, 123");
        $this->assertEqual($smtt->getTemplateDataItem('test_var_1'), null);
        $test_var_1 = $smtt->getTemplateDataItem('test_var_1');
        $this->assertTrue(!isset($test_var_1));
    }
}