<?php
require_once THINKUP_ROOT_PATH.'webapp/_lib/model/class.Loader.php';
/**
 * ThinkUp Basic Unit Test Case
 *
 * Base test case for tests without the need for database availability.
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class ThinkUpBasicUnitTestCase extends UnitTestCase {
    /**
     * Set up
     * Initializes Config and Webapp objects, clears $_SESSION, $_POST, $_REQUEST
     */
    public function setUp() {
        parent::setUp();
        Loader::register(array(
        THINKUP_ROOT_PATH . 'tests' . DS,
        THINKUP_ROOT_PATH . 'tests' . DS . 'classes'. DS,
        THINKUP_ROOT_PATH . 'tests' . DS . 'fixtures'. DS
        ));

        $config = Config::getInstance();
        //disable caching for tests
        $config->setValue('cache_pages', false);

        //tests assume profiling is off
        $config->setValue('enable_profiler', false);
        if ($config->getValue('timezone')) {
            date_default_timezone_set($config->getValue('timezone'));
        }
        $webapp = Webapp::getInstance();
        $crawler = Crawler::getInstance();
    }

    /**
     * Tear down
     * Destroys Config, Webapp, $_SESSION, $_POST, $_GET, $_REQUEST
     */
    public function tearDown() {
        Config::destroyInstance();
        Webapp::destroyInstance();
        Crawler::destroyInstance();
        if (isset($_SESSION)) {
            $this->unsetArray($_SESSION);
        }
        $this->unsetArray($_POST);
        $this->unsetArray($_GET);
        $this->unsetArray($_REQUEST);
        $this->unsetArray($_SERVER);
        Loader::unregister();
        parent::tearDown();
    }

    /**
     * Unset all the values for every key in an array
     * @param array $array
     */
    private function unsetArray(&$array) {
        $keys = array_keys($array);
        foreach ($keys as $key) {
            unset($array[$key]);
        }
    }

    /**
     * Move webapp/config.inc.php to webapp/config.inc.bak.php for tests with no config file
     */
    protected function removeConfigFile() {
        if (file_exists(THINKUP_WEBAPP_PATH . 'config.inc.php')) {
            $cmd = 'mv '.THINKUP_WEBAPP_PATH . 'config.inc.php ' .THINKUP_WEBAPP_PATH . 'config.inc.bak.php';
            exec($cmd, $output, $return_val);
            if ($return_val != 0) {
                echo "Could not ".$cmd;
            }
        }
    }

    /**
     * Move webapp/config.inc.bak.php to webapp/config.inc.php
     */
    protected function restoreConfigFile() {
        if (file_exists(THINKUP_WEBAPP_PATH . 'config.inc.bak.php')) {
            $cmd = 'mv '.THINKUP_WEBAPP_PATH . 'config.inc.bak.php ' .THINKUP_WEBAPP_PATH . 'config.inc.php';
            exec($cmd, $output, $return_val);
            if ($return_val != 0) {
                echo "Could not ".$cmd;
            }
        }
    }

    public function __destruct() {
        $this->restoreConfigFile();
    }
}
