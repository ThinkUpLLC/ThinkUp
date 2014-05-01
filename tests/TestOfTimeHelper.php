<?php
/**
 *
 * ThinkUp/tests/TestOfTimeHelper.php
 *
 * Copyright (c) 2014 Chris Moyer
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
 * Test of TimeHelper
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Chris Moyer
 * @author Chris Moyer
 *
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';


class TestOfTimeHelper extends ThinkUpUnitTestCase {
    public function testGetTime() {
        $time = time();
        $time2 = TimeHelper::getTime();
        $this->assertEqual($time, $time2);
        sleep(1);
        $time2 = TimeHelper::getTime();
        $this->assertNotEqual($time, $time2);
    }

    public function testSetClearTime() {
        $start = TimeHelper::getTime();
        $this->assertNotEqual($start, 4179);

        TimeHelper::setTime(4179);
        $now = TimeHelper::getTime();
        $this->assertEqual(4179, $now);

        TimeHelper::clearTime();
        $now = TimeHelper::getTime();
        $this->assertNotEqual(4179, $now);
    }

    public function testGetDaysInMonth() {
        $this->assertEqual(TimeHelper::getDaysInMonth(2014, 2), 28);
        $this->assertEqual(TimeHelper::getDaysInMonth(2013, 2), 28);
        $this->assertEqual(TimeHelper::getDaysInMonth(2012, 2), 29);
        $this->assertEqual(TimeHelper::getDaysInMonth(2014, 1), 31);
        $this->assertEqual(TimeHelper::getDaysInMonth(2014, 3), 31);
        $this->assertEqual(TimeHelper::getDaysInMonth(2014, 4), 30);
        $this->assertEqual(TimeHelper::getDaysInMonth(2014, 5), 31);
    }
}
