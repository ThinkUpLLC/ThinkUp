<?php
/**
 * ThinkUp's Smarty object
 *
 * Configures and initalizes Smarty per ThinkUp's configuration.
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class SmartyThinkUp extends Smarty {

    /**
     * @var boolean
     */
    private $debug = false;

    /**
     * @var array
     */
    private $template_data = array();
    /**
     * Constructor
     *
     * Sets default values all view templates have access to:
     *
     *  <code>
     *  //path of the ThinkUp installation site root as defined in config.inc.php
     *  {$site_root_path}
     *  //file the ThinkUp logo links to, 'index.php' by default
     *  {$logo_link}
     *  //application name
     *  {$app_title}
     *  </code>
     *
     */
    public function __construct() {
        $config = Config::getInstance();
        $src_root_path = $config->getValue('source_root_path');
        $this->Smarty();
        $this->template_dir = array( $src_root_path.'webapp/view', $src_root_path.'tests/view');
        $this->compile_dir = $src_root_path.'webapp/view/compiled_view/';
        $this->plugins_dir = array('plugins', $src_root_path.'webapp/view/plugins/');
        $this->cache_dir = $src_root_path.'webapp/view/compiled_view/cache';
        $this->caching = ($config->getValue('cache_pages'))?1:0;
        $this->cache_lifetime = 300;
        $this->debug = $config->getValue('debug');

        $this->assign('app_title', $config->getValue('app_title'));
        $this->assign('site_root_path', $config->getValue('site_root_path'));
        $this->assign('logo_link', 'index.php');
    }

    /**
     * Assigns data to a template variable.
     * If debug is true, stores it for access by tests or developer.
     * @param string $key
     * @param mixed $value
     */
    public function assign($key, $value) {
        parent::assign($key, $value);
        if ($this->debug) {
            $this->template_data[$key] = $value;
        }
    }

    /**
     * For use only by tests: return a template data value by key.
     * @param string $key
     */
    public function getTemplateDataItem($key) {
        return isset($this->template_data[$key]) ? $this->template_data[$key]:null;
    }

    /**
     * Check if caching is enabled
     * @return bool
     */
    public function isViewCached() {
        return ($this->caching==1)?true:false;
    }

    /**
     * Turn off caching
     */
    public function disableCaching() {
        $this->caching=0;
    }
}
