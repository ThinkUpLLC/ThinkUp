<?php
/**
 * Configuration singleton
 *
 * Singleton acess object for ThinkTank configuration values set in config.inc.php.
 * Never reference $THINKTANK_CFG directly; always do it through this object.
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
 * @package     ThinkTank
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
    var $config;
    /**
     * Constructor
     * @return Config
     */
    public function __construct() {
        global $THINKTANK_CFG;
        $this->config = $THINKTANK_CFG;
    }

    /**
     * Get the singleton instance of Config
     * @return Config
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new Config();
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
}