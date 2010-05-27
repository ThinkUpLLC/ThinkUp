<?php 
/**
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

    private static $instance;
    
    var $config;
    
    /**
     * get the singleton instance of Config
     * @return object Config
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new Config();
        }
        return self::$instance;
    }
    
    /**
     * get the configuration value
     * @param    string   $key   key of the configuration key/value pair
     * @return   mixed    value of the configuration key/value pair
     */
    public function getValue($key) {
        $value = isset($this->config[$key]) ? $this->config[$key] : null;
        return $value;
    }
    
    /** 
     * provided only for use when overriding config.inc.php values in tests
     */ 
    public function setValue($key, $value) {
        $value = $this->config[$key] = $value;
        return $value;
    }
    
    function Config() {
        global $THINKTANK_CFG;
        $this->config = $THINKTANK_CFG;
    }
}
?>
