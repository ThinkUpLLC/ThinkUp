<?php
require_once THINKUP_ROOT_PATH.'webapp/model/class.Loader.php';
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
}
