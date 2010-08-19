<?php
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

/**
 * Test of SmartyThinkUp class
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class TestOfSmartyThinkUp extends ThinkUpBasicUnitTestCase {
    public function __construct() {
        $this->UnitTestCase('SmartyThinkUp class test');
    }
    /**
     * Test constructor
     */
    public function testNewSmartyThinkUp() {
        $smtt = new SmartyThinkUp();
        $this->assertTrue(isset($smtt));
    }

    /**
     * Test default values
     */
    public function testSmartyThinkUpDefaultValues() {
        $cfg = Config::getInstance();
        $cfg->setValue('source_root_path', '/path/to/thinkup/');
        $cfg->setValue('cache_pages', true);
        $smtt = new SmartyThinkUp();

        $this->assertEqual($smtt->compile_dir, '/path/to/thinkup/webapp/view/compiled_view/');
        $this->assertTrue(sizeof($smtt->template_dir), 2);
        $this->assertEqual($smtt->template_dir[0], '/path/to/thinkup/webapp/view');
        $this->assertEqual($smtt->template_dir[1], '/path/to/thinkup/tests/view');
        $this->assertEqual($smtt->compile_dir, '/path/to/thinkup/webapp/view/compiled_view/');
        $this->assertTrue(sizeof($smtt->plugins_dir), 2);
        $this->assertEqual($smtt->plugins_dir[0], 'plugins');
        $this->assertEqual($smtt->cache_dir, '/path/to/thinkup/webapp/view/compiled_view/cache');
        $this->assertEqual($smtt->cache_lifetime, 300);
        $this->assertTrue($smtt->caching);
    }

    /**
     * Test assigned variables get saved when debug is true
     */
    public function testSmartyThinkUpAssignedValuesDebugOn() {
        $cfg = Config::getInstance();
        $cfg->setValue('debug', true);
        $cfg->setValue('app_title', 'Testy ThinkUp Custom Application Name');
        $cfg->setValue('site_root_path', '/my/thinkup/folder/');
        $smtt = new SmartyThinkUp();

        $smtt->assign('test_var_1', "Testing, testing, 123");
        $this->assertEqual($smtt->getTemplateDataItem('test_var_1'), "Testing, testing, 123");

        $this->assertEqual($smtt->getTemplateDataItem('app_title'), 'Testy ThinkUp Custom Application Name');
        $this->assertEqual($smtt->getTemplateDataItem('logo_link'), 'index.php');
        $this->assertEqual($smtt->getTemplateDataItem('site_root_path'), '/my/thinkup/folder/');
    }

    /**
     * Test assigned variables don't get saved when debug is false
     */
    public function testSmartyThinkUpAssignedValuesDebugOff() {
        $cfg = Config::getInstance();
        $cfg->setValue('debug', false);
        $smtt = new SmartyThinkUp();

        $smtt->assign('test_var_1', "Testing, testing, 123");
        $this->assertEqual($smtt->getTemplateDataItem('test_var_1'), null);
        $test_var_1 = $smtt->getTemplateDataItem('test_var_1');
        $this->assertTrue(!isset($test_var_1));
    }
}