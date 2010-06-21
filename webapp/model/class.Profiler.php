<?php
/**
 * Profiler
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
    private static $logged_actions = array();
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
        'num_rows'=>$num_rows);
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
}