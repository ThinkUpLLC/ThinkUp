<?php
/**
 *
 * ThinkUp/tests/classes/class.ThinkUpBasicWebTestCase.php
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
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 */
class ThinkUpBasicWebTestCase extends WebTestCase {
    /**
     *
     * @var str The test web server URL, ie, http://dev.thinkup.com
     */
    var $url;
    /**
     * @var str
     */
    var $test_database_name;

    public function setUp() {
        global $TEST_SERVER_DOMAIN;

        $this->url = $TEST_SERVER_DOMAIN;
        $this->DEBUG = (getenv('TEST_DEBUG')!==false) ? true : false;

        self::isWebTestEnvironmentReady();

        require THINKUP_ROOT_PATH.'tests/config.tests.inc.php';
        $this->test_database_name = $TEST_DATABASE;
    }

    public function tearDown() {
    }

    public function debug($message) {
        if($this->DEBUG) {
            $bt = debug_backtrace();
            print get_class($this) . ": line " . $bt[0]['line'] . " - " . $message . "\n";
        }
    }

    /**
     * Preemptively halt test run if integration testing environment requirement isn't met.
     * Prevents unnecessary/inexplicable failures and data loss.
     */
    public static function isWebTestEnvironmentReady() {
        ThinkUpBasicUnitTestCase::isTestEnvironmentReady();

        require THINKUP_WEBAPP_PATH.'config.inc.php';
        global $TEST_DATABASE;

        if ($THINKUP_CFG['db_name'] != $TEST_DATABASE) {
            $message = "The database name in webapp/config.inc.php does not match \$TEST_DATABASE in ".
            "tests/config.tests.inc.php. 
In order to test your ThinkUp installation without losing data, these database names must both point to the same ".
"empty test database.";
        }

        if ($THINKUP_CFG['cache_pages']) {
            $message = "In order to test your ThinkUp installation, \$THINKUP_CFG['cache_pages'] must be set to false.";
        }

        if (isset($message)) {
            die("Stopping tests...Integration test environment isn't ready.
".$message."
Please try again.
");
        }
    }

}
