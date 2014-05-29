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

    public function testSecondsToGeneralTime() {
        $minute = 60;
        $hour = 60 * $minute;
        $day = $hour * 24;
        $week = $day * 7;

        $this->assertEqual('1 second', TimeHelper::secondsToGeneralTime(1));
        $this->assertEqual('0 seconds', TimeHelper::secondsToGeneralTime(0));
        $this->assertEqual('11 seconds', TimeHelper::secondsToGeneralTime(11));
        $this->assertEqual('1 minute', TimeHelper::secondsToGeneralTime($minute));
        $this->assertEqual('6 minutes', TimeHelper::secondsToGeneralTime($minute*6));
        $this->assertEqual('1 hour', TimeHelper::secondsToGeneralTime($hour));
        $this->assertEqual('23 hours', TimeHelper::secondsToGeneralTime($hour*23));
        $this->assertEqual('1 day', TimeHelper::secondsToGeneralTime($hour*26));
        $this->assertEqual('3 days', TimeHelper::secondsToGeneralTime($day*3));
        $this->assertEqual('1 week', TimeHelper::secondsToGeneralTime($week));
        $this->assertEqual('1 week', TimeHelper::secondsToGeneralTime($day * 8));
        $this->assertEqual('3 weeks', TimeHelper::secondsToGeneralTime($day * 23));
        $this->assertEqual('2 weeks', TimeHelper::secondsToGeneralTime($week*2));
    }

    public function testSecondsToExactTime() {
        $minute = 60;
        $hour = 60 * $minute;
        $day = $hour * 24;
        $week = $day * 7;

        $result = array('d'=>0, 'h'=>0, 'm'=>0, 's'=>1);
        $this->assertEqual($result, TimeHelper::secondsToExactTime(1));

        $result = array('d'=>0, 'h'=>0, 'm'=>0, 's'=>0);
        $this->assertEqual($result, TimeHelper::secondsToExactTime(0));

        $result = array('d'=>0, 'h'=>0, 'm'=>0, 's'=>11);
        $this->assertEqual($result, TimeHelper::secondsToExactTime(11));

        $result = array('d'=>0, 'h'=>0, 'm'=>1, 's'=>11);
        $this->assertEqual($result, TimeHelper::secondsToExactTime(60+11));

        $result = array('d'=>0, 'h'=>0, 'm'=>6, 's'=>11);
        $this->assertEqual($result, TimeHelper::secondsToExactTime((60*6)+11));

        $result = array('d'=>0, 'h'=>1, 'm'=>6, 's'=>11);
        $this->assertEqual($result, TimeHelper::secondsToExactTime((60*60)+(60*6)+11));

        $result = array('d'=>0, 'h'=>23, 'm'=>6, 's'=>11);
        $this->assertEqual($result, TimeHelper::secondsToExactTime((23*(60*60))+(60*6)+11));

        $result = array('d'=>2, 'h'=>0, 'm'=>6, 's'=>11);
        $this->assertEqual($result, TimeHelper::secondsToExactTime((48*(60*60))+(60*6)+11));
]    }
}
