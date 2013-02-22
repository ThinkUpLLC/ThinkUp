<?php
/**
 *
 * ThinkUp/tests/classes/class.ThinkUpBasicWebTestCase.php
 *
 * Copyright (c) 2009-2013 Gina Trapani
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
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
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

        putenv("MODE=TESTS");
        $this->get($this->url.'/install/setmode.php?m=tests');

        ThinkUpBasicUnitTestCase::isTestEnvironmentReady();

        require THINKUP_ROOT_PATH.'tests/config.tests.inc.php';
        $this->test_database_name = $TEST_DATABASE;
        if (ThinkUpUnitTestCase::ramDiskTestMode()) {
            putenv("RD_MODE=1");
            require THINKUP_WEBAPP_PATH.'config.inc.php';
            $this->test_database_name = $THINKUP_CFG['db_name'];
            $this->get($this->url.'/install/setmode.php?rd=1');
        }
    }

    public function tearDown() {
        putenv("MODE=PROD");
        $this->get($this->url.'/install/setmode.php?m=prod');
    }

    public function debug($message) {
        if ($this->DEBUG) {
            $bt = debug_backtrace();
            print get_class($this) . ": line " . $bt[0]['line'] . " - " . $message . "\n";
        }
    }
}