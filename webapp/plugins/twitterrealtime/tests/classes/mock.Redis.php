<?php
/**
 * ThinkUp/webapp/plugins/twitterrealtime/tests/TestOfConsumerUserStream.php
 *
 * Copyright (c) 2011-2013 Amy Unruh, Mark Wilkie
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
 * Mock Redis class
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 Amy Unruh, Mark Wilkie
 * @author Amy Unruh
 *
 */
class MockRedis {
    /**
     * a list of queue items
     * @var array Queue
     */
    static $queue = array();

    public function __construct() {
        self::$queue = array();
    }

    public function rpush($tlist, $string) {
        array_push(self::$queue, $string);
    }

    public function lpop($tlist) {
        return array_shift(self::$queue);
    }
}