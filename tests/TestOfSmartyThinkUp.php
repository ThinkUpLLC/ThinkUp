<?php
/**
 *
 * ThinkUp/tests/TestOfSmartyThinkUp.php
 *
 * Copyright (c) 2009-2011 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
 *
 * ThinkUp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any
 * later version.
 *
 * ThinkUp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with ThinkUp.  If not, see
 * <http://www.gnu.org/licenses/>.
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

/**
 * Test of SmartyThinkUp class
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
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

        $this->assertTrue(sizeof($smtt->template_dir), 2);
        $this->assertEqual($smtt->template_dir[1], '/path/to/thinkup/tests/view');
        $this->assertTrue(sizeof($smtt->plugins_dir), 2);
        $this->assertEqual($smtt->plugins_dir[0], 'plugins');
        $this->assertEqual($smtt->cache_dir, THINKUP_WEBAPP_PATH.'_lib/view/compiled_view/cache');
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

    /**
     * Test override config with passed-in array
     */
    public function testSmartyThinkUpPassedInArray() {
        $cfg_array = array('debug'=>true,
        'site_root_path'=>'/my/thinkup/folder/test',
        'source_root_path'=>'/Users/gina/Sites/thinkup', 
        'app_title'=>"My ThinkUp", 
        'cache_pages'=>true);
        $smtt = new SmartyThinkUp($cfg_array);

        $this->assertEqual($smtt->getTemplateDataItem('app_title'), 'My ThinkUp');
        $this->assertEqual($smtt->getTemplateDataItem('logo_link'), 'index.php');
        $this->assertEqual($smtt->getTemplateDataItem('site_root_path'), '/my/thinkup/folder/test');
    }
}