<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.Profiler.php
 *
 * Copyright (c) 2009-2010 Gina Trapani
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
 * Profiler
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class Profiler {
    /**
     *
     * @var Profiler
     */
    private static $instance;
    /**
     *
     * @var array
     */
    private $logged_actions = array();
    /**
     * @var int
     */
    public $total_queries = 0;
    /**
     * Get singleton instance
     * @return Profiler
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new Profiler();
        }
        return self::$instance;
    }
    /**
     * Add action
     * @param float $time
     * @param str $action
     */
    public function add($time, $action, $is_query=false, $num_rows=0 ) {
        if ($is_query) {
            $this->total_queries = $this->total_queries + 1;
        }
        $rounded_time = round($time, 3);
        $this->logged_actions[] =  array('time'=>number_format($rounded_time,3), 'action'=> trim($action),
        'num_rows'=>$num_rows, 'is_query'=>$is_query);
    }

    /**
     * Get sorted profiled actions
     * @return array
     */
    public function getProfile() {
        sort($this->logged_actions);
        return array_reverse($this->logged_actions);
    }

    /**
     * Check if Profiler is enabled; that is, if enabled in config file and running a web page.
     * @return bool Whether the profiler is enabled
     */
    public static function isEnabled() {
        if (isset($_SERVER['HTTP_HOST'])) {
            $config = Config::getInstance();
            return $config->getValue('enable_profiler');
        } else {
            return false;
        }
    }

    /**
     * Clear out all logged items, reset query count to 0
     */
    public function clearLog() {
        $keys = array_keys($this->logged_actions);
        foreach ($keys as $key) {
            unset($this->logged_actions[$key]);
        }
        $this->total_queries = 0;
    }
}