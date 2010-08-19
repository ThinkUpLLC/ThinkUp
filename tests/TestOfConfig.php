<?php
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'extlib/simpletest/autorun.php';
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
}
