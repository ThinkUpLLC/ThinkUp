<?php
/**
 *
 * ThinkUp/tests/TestOfSmartyThinkUp.php
 *
 * Copyright (c) 2009-2012 Gina Trapani
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
 *
 * Test of SmartyThinkUp class
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2012 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

class TestOfSmartyThinkUp extends ThinkUpBasicUnitTestCase {

    /**
     * Test constructor
     */
    public function testNewSmartyThinkUp() {
        $v_mgr = new SmartyThinkUp();
        $this->assertTrue(isset($v_mgr));
    }

    /**
     * Test default values
     */
    public function testSmartyThinkUpDefaultValues() {
        $cfg = Config::getInstance();
        $cfg->setValue('source_root_path', '/path/to/thinkup/');
        $cfg->setValue('cache_pages', true);
        $cfg->setValue('cache_lifetime', 600);
        $v_mgr = new SmartyThinkUp();

        $this->assertTrue(sizeof($v_mgr->template_dir), 2);
        $this->assertEqual($v_mgr->template_dir[1], '/path/to/thinkup/tests/view');
        $this->assertTrue(sizeof($v_mgr->plugins_dir), 2);
        $this->assertEqual($v_mgr->plugins_dir[0], 'plugins');
        $this->assertEqual($v_mgr->cache_dir, FileDataManager::getDataPath('compiled_view/cache'));
        $this->assertEqual($v_mgr->cache_lifetime, $cfg->getValue('cache_lifetime'));
        $this->assertTrue($v_mgr->caching);
    }

    /**
     * Test assigned variables get saved when debug is true
     */
    public function testSmartyThinkUpAssignedValuesDebugOn() {
        $cfg = Config::getInstance();
        $cfg->setValue('debug', true);
        $cfg->setValue('cache_lifetime', 1200);
        $cfg->setValue('app_title_prefix', 'Testy ');
        $cfg->setValue('site_root_path', '/my/thinkup/folder/');
        $v_mgr = new SmartyThinkUp();

        $v_mgr->assign('test_var_1', "Testing, testing, 123");
        $this->assertEqual($v_mgr->getTemplateDataItem('test_var_1'), "Testing, testing, 123");

        $this->assertEqual($v_mgr->getTemplateDataItem('app_title'), ($cfg->getValue('app_title_prefix')  . 'ThinkUp'));
        $this->assertEqual($v_mgr->getTemplateDataItem('logo_link'), '');
        $this->assertEqual($v_mgr->getTemplateDataItem('site_root_path'), '/my/thinkup/folder/');
        $this->assertEqual($v_mgr->cache_lifetime, 1200);
    }

    /**
     * Test assigned variables don't get saved when debug is false
     */
    public function testSmartyThinkUpAssignedValuesDebugOff() {
        $cfg = Config::getInstance();
        $cfg->setValue('debug', false);
        $v_mgr = new SmartyThinkUp();

        $v_mgr->assign('test_var_1', "Testing, testing, 123");
        $this->assertEqual($v_mgr->getTemplateDataItem('test_var_1'), null);
        $test_var_1 = $v_mgr->getTemplateDataItem('test_var_1');
        $this->assertTrue(!isset($test_var_1));
    }

    /**
     * Test override config with passed-in array
     */
    public function testSmartyThinkUpPassedInArray() {
        $cfg_array = array('debug'=>true,
        'site_root_path'=>'/my/thinkup/folder/test',
        'source_root_path'=>'/Users/gina/Sites/thinkup',
        'app_title_prefix'=>'My ',
        'cache_pages'=>true, 'cache_lifetime'=>1000);
        $v_mgr = new SmartyThinkUp($cfg_array);

        $this->assertEqual($v_mgr->getTemplateDataItem('app_title'), 'My ThinkUp');
        $this->assertEqual($v_mgr->getTemplateDataItem('logo_link'), '');
        $this->assertEqual($v_mgr->getTemplateDataItem('site_root_path'), '/my/thinkup/folder/test');
        $this->assertEqual($v_mgr->cache_lifetime, 1000);
    }

    public function testAddHelp() {
        $cfg = Config::getInstance();
        $cfg->setValue('debug', true);
        $v_mgr = new SmartyThinkUp();

        $v_mgr->addHelp('api', 'userguide/api/posts/index');
        $v_mgr->addHelp('user_guide', 'userguide/index');

        $help_array = array('api'=>'userguide/api/posts/index', 'user_guide'=>'userguide/index');
        $this->assertEqual($v_mgr->getTemplateDataItem('help'), $help_array);
        $debug_arr = $v_mgr->getTemplateDataItem('help');
        $this->debug(Utils::varDumpToString($debug_arr));
        $this->debug($debug_arr['api']);
    }

    public function testAddErrorMessage() {
        $cfg = Config::getInstance();
        $cfg->setValue('debug', true);
        $v_mgr = new SmartyThinkUp();

        $v_mgr->addErrorMessage('Page level error');
        $v_mgr->addErrorMessage('Field level error', 'fieldname');

        $this->assertEqual($v_mgr->getTemplateDataItem('error_msg'), 'Page level error');
        $debug_arr = $v_mgr->getTemplateDataItem('error_msgs');
        $this->assertEqual($debug_arr['fieldname'], 'Field level error');
        $this->debug(Utils::varDumpToString($debug_arr));
    }

    public function testAddInfoMessage() {
        $cfg = Config::getInstance();
        $cfg->setValue('debug', true);
        $v_mgr = new SmartyThinkUp();

        $v_mgr->addInfoMessage('Field level info', 'fieldname');
        $v_mgr->addInfoMessage('Page level info');

        $this->assertEqual($v_mgr->getTemplateDataItem('info_msg'), 'Page level info');
        $debug_arr = $v_mgr->getTemplateDataItem('info_msgs');
        $this->assertEqual($debug_arr['fieldname'], 'Field level info');
        $this->debug(Utils::varDumpToString($debug_arr));
    }

    public function testAddSuccessMessage() {
        $cfg = Config::getInstance();
        $cfg->setValue('debug', true);
        $v_mgr = new SmartyThinkUp();

        $v_mgr->addSuccessMessage('Field level info 1', 'fieldname1');
        $v_mgr->addSuccessMessage('Page level info');
        $v_mgr->addSuccessMessage('Field level info 2', 'fieldname2');

        $this->assertEqual($v_mgr->getTemplateDataItem('success_msg'), 'Page level info');
        $debug_arr = $v_mgr->getTemplateDataItem('success_msgs');
        $this->assertEqual($debug_arr['fieldname1'], 'Field level info 1');
        $this->assertEqual($debug_arr['fieldname2'], 'Field level info 2');
        $this->debug(Utils::varDumpToString($debug_arr));
    }
}