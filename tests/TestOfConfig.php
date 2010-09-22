<?php
/**
 *
 * ThinkUp/tests/TestOfConfig.php
 *
 * Copyright (c) 2009-2010 Gina Trapani, Dwi Widiastuti, Mark Wilkie
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
 * Test of Config object
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani, Dwi Widiastuti, Mark Wilkie
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfConfig extends ThinkUpBasicUnitTestCase {
    /**
     * Constructor
     */
    public function  __construct() {
        $this->UnitTestCase('Config class test');
    }
    /**
     * Test config singleton instantiation
     */
    public function testConfigSingleton() {
        $config = Config::getInstance();
        $log_location = $config->getValue('log_location');
        $this->assertTrue(isset($log_location));
    }

    public function testGetValuesArray() {
        require THINKUP_ROOT_PATH.'webapp/config.inc.php';
        $config = Config::getInstance();
        $values_array = $config->getValuesArray();
        //tests assume profiler and caching is off
        $THINKUP_CFG['enable_profiler']=false;
        $THINKUP_CFG['cache_pages']=false;
        $this->assertIdentical($THINKUP_CFG, $values_array);
    }

    public function testPassInArray() {
        Config::destroyInstance();
        $cfg_values = array("table_prefix"=>"thinkupyo", "db_host"=>"myserver.com");
        $config = Config::getInstance($cfg_values);
        $this->assertEqual($config->getValue("table_prefix"), "thinkupyo");
        $this->assertEqual($config->getValue("db_host"), "myserver.com");
    }

    public function testNoConfigFileArray() {
        Config::destroyInstance();
        $this->removeConfigFile();
        $cfg_values = array("table_prefix"=>"thinkupyo", "db_host"=>"myserver.com");
        $config = Config::getInstance($cfg_values);
        $this->assertEqual($config->getValue("table_prefix"), "thinkupyo");
        $this->assertEqual($config->getValue("db_host"), "myserver.com");
        $this->restoreConfigFile();
    }

    public function testNoConfigFileNoArray() {
        Config::destroyInstance();
        $this->removeConfigFile();
        try {
            $config = Config::getInstance();
            $this->assertNull($config->getValue('table_prefix'));
        } catch(Exception $e) {
            $this->assertPattern("/ThinkUp's configuration file does not exist!/", $e->getMessage());
        }
        $this->restoreConfigFile();
    }
}
