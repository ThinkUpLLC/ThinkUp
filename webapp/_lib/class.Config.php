<?php
/**
 *
 * ThinkUp/webapp/_lib/class.Config.php
 *
 * Copyright (c) 2009-2013 Mark Wilkie, Gina Trapani
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
 * Configuration singleton
 *
 * Singleton acess object for ThinkUp configuration values set in config.inc.php.
 * Never reference $THINKUP_CFG directly; always do it through this object.
 *
 * Example of use:
 *
 * <code>
 * // get the Config singleton
 * $config = Config::getInstance();
 * // get a value from it
 * $config->getValue('log_location');
 * </code>
 *
 * @package     ThinkUp
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, Guillaume Boudreau
 * @author      Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author      Mark Wilkie
 */
class Config {
    /**
     *
     * @var Config
     */
    private static $instance;
    /**
     *
     * @var array
     */
    var $config = array();

    /**
     *
     * @var array some reasonable defaults if null in config
     */
    protected static $defaults = array('app_title_prefix' => '');

    /**
     * Private Constructor
     * @param array $vals Optional values to override file config
     * @return Config
     */
    private function __construct($vals = null) {
        if ($vals != null ) {
            $this->config = $vals;
        } else {
            Loader::definePathConstants();
            if (file_exists(THINKUP_WEBAPP_PATH . 'config.inc.php')) {
                require THINKUP_WEBAPP_PATH . 'config.inc.php';
                $this->config = $THINKUP_CFG;
                //set version info...
                require THINKUP_WEBAPP_PATH . 'install/version.php';
                $this->config['THINKUP_VERSION']  = $THINKUP_VERSION;
                $this->config['THINKUP_VERSION_REQUIRED'] =
                array('php' => $THINKUP_VERSION_REQUIRED['php'], 'mysql' => $THINKUP_VERSION_REQUIRED['mysql']);
            } else {
                throw new ConfigurationException("ThinkUp's configuration file does not exist! ".
                "Try installing ThinkUp.");
            }
        }
        foreach (array_keys(self::$defaults) as $default) {
            if (!isset($this->config[$default])) {
                $this->config[$default] = self::$defaults[$default];
            }
        }
    }
    /**
     * Get the singleton instance of Config
     * @param array $vals Optional values to override file config
     * @return Config
     */
    public static function getInstance($vals = null) {
        if (!isset(self::$instance)) {
            self::$instance = new Config($vals);
        }
        return self::$instance;
    }
    /**
     * Get the configuration value
     * @param    string   $key   key of the configuration key/value pair
     * @return   mixed    value of the configuration key/value pair
     */
    public function getValue($key) {
        // is this config value stored in the db?
        $db_value_config = AppConfig::getConfigValue($key);
        $value = null;
        if ($db_value_config) {
            $option_dao = DAOFactory::getDAO("OptionDAO");
            $db_value = $option_dao->getOptionValue(OptionDAO::APP_OPTIONS, $key, false );
            $value =  $db_value ? $db_value : $db_value_config['default'];
            // convert db text booleans if needed
            if ($value == 'false') {
                $value = false;
            } else if ($value == 'true') {
                $value = true;
            }
        } else {
            // if not a db config value, get from config file
            $value = isset($this->config[$key]) ? $this->config[$key] : null;
        }
        return $value;
    }
    /**
     * Provided only for use when overriding config.inc.php values in tests
     * @param string $key
     * @param string $value
     * @return string $value
     */
    public function setValue($key, $value) {
        $value = $this->config[$key] = $value;
        return $value;
    }
    /**
     * Provided only for tests that want to kill Config object in tearDown()
     */
    public static function destroyInstance() {
        if (isset(self::$instance)) {
            self::$instance = null;
        }
    }
    /**
     * Provided for tests which expect an array
     */
    public function getValuesArray() {
        return $this->config;
    }
    /**
     * Returns the GMT offset in hours based on the application's defined timezone.
     *
     * If $time is given, gives the offset for that time; otherwise uses the current time.
     *
     * @param int $time The time to base it on, as anything strtotime() takes; leave blank for current time.
     * @return int The GMT offset in hours.
     */
    public function getGMTOffset($time = 0) {
        $time = $time ? $time : 'now';
        $tz = ($this->getValue('timezone')==null)?date('e'):$this->getValue('timezone');
        // this may be currently required for some setups to avoid fatal php timezone complaints when
        // exec'ing off the streaming child processes.
        // date_default_timezone_set($tz);
        return timezone_offset_get( new DateTimeZone($tz), new DateTime($time) ) / 3600;
    }
}
