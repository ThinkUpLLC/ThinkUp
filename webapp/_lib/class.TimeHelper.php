<?php
/**
 *
 * ThinkUp/webapp/_lib/class.TimeHelper.php
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
 *
 * TimeHelper
 *
 * A simple helper function to facilitate time-based testing
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Chris Moyer
 * @author Chris Moyer <chris[at]inarow[dot]net>
 *
 */
class TimeHelper {
    /**
     * @var int Do we have an override time set?
     */
    private static $override_time = 0;

    /**
     * @var int What was the last time we returned?
     */
    private static $last_time = 0;

    /**
     * Get the time.  Just like time(), but test friendly
     * @return int Unix timestamp
     */
    public static function getTime() {
        $ret = self::$override_time ? self::$override_time : time();
        self::$last_time = $ret;
        return $ret;
    }

    /**
     * Set the time for testing
     * Should ONLY be used inside tests.
     * @param int $time Unix time stamp
     */
    public static function setTime($time) {
        self::$override_time = $time;
    }

    /**
     * Clear the overriden time for testing
     * Should ONLY be used inside tests.
     */
    public static function clearTime() {
        self::setTime(0);
    }

    /**
     * Get the number of days in a given month and year.
     * @param  int $year
     * @param  int $month
     * @return int
     */
    public static function getDaysInMonth($year, $month) {
        return round((mktime(0, 0, 0, $month+1, 1, $year) - mktime(0, 0, 0, $month, 1, $year)) / 86400);
    }

    /**
     * Convert seconds to a general, non-exact, conversational unit of time, i.e., a day, 5 minutes, 2 weeks.
     * @param  int $seconds
     * @return str
     */
    public static function secondsToGeneralTime($seconds) {
        if ($seconds >= (60*60*24*7)) {
            $weeks = floor($seconds / (60*60*24*7));
            return $weeks." week".($weeks==1?'':'s');
        }
        if ($seconds >= (60*60*24)) {
            $days = floor($seconds / (60*60*24));
            return $days." day".($days==1?'':'s');
        }
        if ($seconds >= (60*60)) {
            $hours = floor($seconds / (60*60));
            return $hours." hour".($hours==1?'':'s');
        }
        if ($seconds >= 60) {
            $minutes = floor($seconds / 60);
            return $minutes." minute".($minutes==1?'':'s');
        }

        return $seconds." second".($seconds==1?'':'s');
    }

    /**
     * Get exact number of days, hours, minutes, and seconds a total number of seconds represents.
     *
     * @param int $seconds How many seconds
     * @return arr Units of time array ('d'=>$days, 'h'=> $hours, 'm'=>$minutes, 's'=>$seconds)
     */
    public static function secondsToExactTime($seconds) {
        $seconds_in_a_minute = 60;
        $seconds_in_an_hour  = 60 * $seconds_in_a_minute;
        $seconds_in_a_day    = 24 * $seconds_in_an_hour;

        // extract days
        $days = floor($seconds / $seconds_in_a_day);

        // extract hours
        $hour_seconds = $seconds % $seconds_in_a_day;
        $hours = floor($hour_seconds / $seconds_in_an_hour);

        // extract minutes
        $minute_seconds = $hour_seconds % $seconds_in_an_hour;
        $minutes = floor($minute_seconds / $seconds_in_a_minute);

        // extract the remaining seconds
        $remaining_seconds = $minute_seconds % $seconds_in_a_minute;
        $seconds = ceil($remaining_seconds);

        // return the final array
        return array(
            'd' => (int) $days,
            'h' => (int) $hours,
            'm' => (int) $minutes,
            's' => (int) $seconds,
        );
    }
}
