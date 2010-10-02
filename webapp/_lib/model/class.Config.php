<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.Config.php
 *
 * Copyright (c) 2009-2010 Mark Wilkie, Gina Trapani
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
 * @copyright 2009-2010 Gina Trapani, Guillaume Boudreau
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
     * Constructor
     * @param array $vals Optional values to override file config
     * @return Config
     */
    public function __construct($vals = null) {
        if ($vals != null ) {
            $this->config = $vals;
        } else {
            Utils::defineConstants();
            if (file_exists(THINKUP_WEBAPP_PATH . 'config.inc.php')) {
                require THINKUP_WEBAPP_PATH . 'config.inc.php';
                $this->config = $THINKUP_CFG;
            } else {
                throw new Exception('ThinkUp\'s configuration file does not exist! Try <a href="'.THINKUP_BASE_URL.
                'install/">installing ThinkUp.</a>');
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
        $value = isset($this->config[$key]) ? $this->config[$key] : null;
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
}