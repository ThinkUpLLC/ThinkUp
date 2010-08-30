<?php
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

/**
 * Test of Config object
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
