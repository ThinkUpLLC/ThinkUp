<?php
require_once $SOURCE_ROOT_PATH.'webapp/model/class.PluginHook.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Config.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Webapp.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Crawler.php';
/**
 * ThinkTank Basic Unit Test Case
 *
 * Base test case for tests without the need for database availability.
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class ThinkTankBasicUnitTestCase extends UnitTestCase {
    /**
     * Set up
     * Initializes Config and Webapp objects
     */
    function setUp() {
        $config = Config::getInstance();
        //tests assume profiling is off
        $config->setValue('enable_profiler', false);
        $webapp = Webapp::getInstance();
        $crawler = Crawler::getInstance();
        parent::setUp();
    }

    /**
     * Tear down
     * Destroys Config, Webapp, and Session objects
     * @TODO Destroy all SESSION variables
     * @TODO Destroy all REQUEST/GET/POST variables
     */
    function tearDown() {
        Config::destroyInstance();
        Webapp::destroyInstance();
        Crawler::destroyInstance();
        if (isset($_SESSION['user'])) {
            $_SESSION['user']=null;
        }
        parent::tearDown();
    }
}
