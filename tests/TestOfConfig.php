<?php
/**
 *
 * ThinkUp/tests/TestOfConfig.php
 *
 * Copyright (c) 2009-2013 Gina Trapani, Mark Wilkie
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkup.com).
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
 * Test of Config object
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, Mark Wilkie
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */

require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfConfig extends ThinkUpUnitTestCase {

    public function setUp() {
        parent::setUp();
        $this->logger = Logger::getInstance();
        $this->config = Config::getInstance();
        $optiondao = new OptionMySQLDAO();
        $this->pdo = $optiondao->connect();
    }

    public function tearDown() {
        parent::tearDown();
        $this->logger->close();
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
        require THINKUP_WEBAPP_PATH.'config.inc.php';
        require THINKUP_WEBAPP_PATH.'install/version.php';
        $config = Config::getInstance();
        //tests assume profiler and caching is off
        $THINKUP_CFG['cache_pages']=false;
        $THINKUP_CFG['THINKUP_VERSION'] = $THINKUP_VERSION;
        $THINKUP_CFG['THINKUP_VERSION_REQUIRED'] =
        array('php' => $THINKUP_VERSION_REQUIRED['php'], 'mysql' => $THINKUP_VERSION_REQUIRED['mysql']);
        $THINKUP_CFG['enable_profiler']=false;
        //tests assume Mandrill is not enabled
        $THINKUP_CFG['mandrill_api_key']='';
        $values_array = $config->getValuesArray();
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

    public function testDBConfigValues() {
        Config::destroyInstance();
        $config = Config::getInstance();
        $this->assertEqual($config->getValue('is_registration_open'), '', "uses default app config value");
        $this->assertFalse($config->getValue('recaptcha_enable'), "uses default app config value");
        $this->assertEqual($config->getValue('recaptcha_private_key'), '', "uses default app config value");
        $this->assertEqual($config->getValue('recaptcha_public_key'), '', "uses default app config value");

        if (isset($_SESSION)) {
            $this->unsetArray($_SESSION);
        }

        $bvalue = array('namespace' => OptionDAO::APP_OPTIONS, 'option_name' => 'recaptcha_enable',
        'option_value' => 'false');
        $bdata = FixtureBuilder::build('options', $bvalue);
        $this->assertFalse($config->getValue('is_registration_open'), "uses default app config value");
        $this->assertFalse($config->getValue('recaptcha_enable'), "uses db config value");
        $this->assertEqual($config->getValue('recaptcha_private_key'), '', "uses default app config value");
        $this->assertEqual($config->getValue('recaptcha_public_key'), '', "uses default app config value");

        if (isset($_SESSION)) {
            $this->unsetArray($_SESSION);
        }
        FixtureBuilder::truncateTable('options');
        $bvalue['option_value'] = 'true';
        $bvalue2 = array('namespace' => OptionDAO::APP_OPTIONS, 'option_name' => 'recaptcha_private_key',
        'option_value' => 'abc123');
        $bvalue3 = array('namespace' => OptionDAO::APP_OPTIONS, 'option_name' => 'recaptcha_public_key',
        'option_value' => 'abc123public');
        $bvalue4 = array('namespace' => OptionDAO::APP_OPTIONS, 'option_name' => 'is_registration_open',
        'option_value' => 'true');
        $bdata2 = FixtureBuilder::build('options', $bvalue);
        $bdata3 = FixtureBuilder::build('options', $bvalue2);
        $bdata4 = FixtureBuilder::build('options', $bvalue3);
        $bdata5 = FixtureBuilder::build('options', $bvalue4);
        $this->assertTrue($config->getValue('recaptcha_enable'), "uses db config value");
        $this->assertEqual($config->getValue('recaptcha_private_key'), 'abc123', "uses db config value");
        $this->assertEqual($config->getValue('is_registration_open'), true, "uses db config value");
    }

    public function testGetGMTOffset() {
        Config::destroyInstance();
        $this->removeConfigFile();
        $config = Config::getInstance(array('timezone' => 'America/Los_Angeles'));
        $this->assertEqual($config->getGMTOffset('January 1, 2010'), -8);
        $this->assertEqual($config->getGMTOffset('August 1, 2010'), -7);

        Config::destroyInstance();
        $this->removeConfigFile();
        $config = Config::getInstance(array('timezone' => 'America/New_York'));
        $this->assertEqual($config->getGMTOffset('January 1, 2010'), -5);
        $this->assertEqual($config->getGMTOffset('August 1, 2010'), -4);

        $this->restoreConfigFile();
    }

    public function testGetUnsetDefaultValue() {
        Config::destroyInstance();
        $this->removeConfigFile();
        $config = Config::getInstance(array('timezone' => 'America/Los_Angeles'));
        $this->assertEqual($config->getValue('app_title_prefix'), '');
        $this->assertNotNull($config->getValue('app_title_prefix'));
        $this->restoreConfigFile();
    }
}
