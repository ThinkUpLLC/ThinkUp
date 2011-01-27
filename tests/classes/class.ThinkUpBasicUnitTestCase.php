<?php
/**
 *
 * ThinkUp/tests/classes/class.ThinkUpBasicUnitTestCase.php
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
 *
 * ThinkUp Basic Unit Test Case
 *
 * Base test case for tests without the need for database availability.
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
require_once THINKUP_ROOT_PATH.'webapp/_lib/model/class.Loader.php';

class ThinkUpBasicUnitTestCase extends UnitTestCase {
    /**
     * Set up
     * Initializes Config and Webapp objects, clears $_SESSION, $_POST, $_REQUEST
     */
    public function setUp() {
        parent::setUp();
        Loader::register(array(
        THINKUP_ROOT_PATH . 'tests/',
        THINKUP_ROOT_PATH . 'tests/classes/',
        THINKUP_ROOT_PATH . 'tests/fixtures/'
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
        $this->DEBUG = (getenv('TEST_DEBUG')!==false) ? true : false;
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
        $this->unsetArray($_FILES);
        Loader::unregister();
        parent::tearDown();
    }

    /**
     * Unset all the values for every key in an array
     * @param array $array
     */
    protected function unsetArray(&$array) {
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

    /**
     * Wrapper for logging in a ThinkUp user in a test
     * @param str $email
     * @param bool $is_admin Default to false
     */
    protected function simulateLogin($email, $is_admin = false) {
        $config = Config::getInstance();
        $_SESSION[$config->getValue('source_root_path')]['user'] = $email;
        if ($is_admin) {
            $_SESSION[$config->getValue('source_root_path')]['user_is_admin'] = true;
        }
    }

    public function debug($message) {
        if($this->DEBUG) {
            $bt = debug_backtrace();
            print get_class($this) . ": line " . $bt[0]['line'] . " - " . $message . "\n";
        }
    }

}
