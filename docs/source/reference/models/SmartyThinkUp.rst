SmartyThinkUp
=============
Inherits from `Smarty <./Smarty.html>`_.

ThinkUp/webapp/_lib/model/class.SmartyThinkUp.php

Copyright (c) 2009-2011 Gina Trapani

ThinkUp's Smarty object

Configures and initalizes Smarty per ThinkUp's configuration.


Properties
----------

debug
~~~~~



template_data
~~~~~~~~~~~~~



contextual_help
~~~~~~~~~~~~~~~





Methods
-------

__construct
~~~~~~~~~~~
* **@param** array $config_array Defaults to null; Override source_root_path, site_root_path, app_title, cache_pages,  debug


Constructor

Sets default values all view templates have access to:

 <code>
 //path of the ThinkUp installation site root as defined in config.inc.php
 {$site_root_path}
 //file the ThinkUp logo links to, 'index.php' by default
 {$logo_link}
 //application name
 {$app_title}
 </code>

.. code-block:: php5

    <?php
        public function __construct($config_array=null) {
            if ($config_array==null) {
                $config = Config::getInstance();
                $config_array = $config->getValuesArray();
            }
    
            $src_root_path = $config_array['source_root_path'];
            $site_root_path = $config_array['site_root_path'];
            $app_title = $config_array['app_title'];
            $cache_pages = $config_array['cache_pages'];
            $debug =  $config_array['debug'];
            Utils::defineConstants();
    
            $this->Smarty();
            $this->template_dir = array( THINKUP_WEBAPP_PATH.'_lib/view', $src_root_path.'tests/view');
            $this->compile_dir = THINKUP_WEBAPP_PATH.'_lib/view/compiled_view/';
            $this->plugins_dir = array('plugins', THINKUP_WEBAPP_PATH.'_lib/view/plugins/');
            $this->cache_dir = THINKUP_WEBAPP_PATH.'_lib/view/compiled_view/cache';
            $this->caching = ($cache_pages)?1:0;
            $this->cache_lifetime = 300;
            $this->debug = $debug;
    
            $this->assign('app_title', $app_title);
            $this->assign('site_root_path', $site_root_path);
            $this->assign('logo_link', '');
        }


assign
~~~~~~
* **@param** string $key
* **@param** mixed $value


Assigns data to a template variable.
If debug is true, stores it for access by tests or developer.

.. code-block:: php5

    <?php
        public function assign($key, $value = null) {
            parent::assign($key, $value);
            if ($this->debug) {
                $this->template_data[$key] = $value;
            }
        }


addHelp
~~~~~~~
* **@param** $key Unique help item key.
* **@param** $link_slug Documentation page slug, ie, 'userguide/api/posts/index'


Assign contextual help to the template.

.. code-block:: php5

    <?php
        public function addHelp($id, $link_slug) {
            $this->contextual_help[$id] = $link_slug;
            $this->assign('help', $this->contextual_help);
        }


getTemplateDataItem
~~~~~~~~~~~~~~~~~~~
* **@param** string $key


For use only by tests: return a template data value by key.

.. code-block:: php5

    <?php
        public function getTemplateDataItem($key) {
            return isset($this->template_data[$key]) ? $this->template_data[$key]:null;
        }


isViewCached
~~~~~~~~~~~~
* **@return** bool


Check if caching is enabled

.. code-block:: php5

    <?php
        public function isViewCached() {
            return ($this->caching==1)?true:false;
        }


disableCaching
~~~~~~~~~~~~~~

Turn off caching

.. code-block:: php5

    <?php
        public function disableCaching() {
            $this->caching=0;
        }


fetch
~~~~~
* **@param** str $template Template name
* **@param** str $cache_key Cache key
* **@param** str Results


Override the parent's fetch method to handle an unwritable compilation directory.

.. code-block:: php5

    <?php
        public function fetch($template, $cache_key=null, $compile_id=null, $display=false) {
            if (! is_writable($this->compile_dir) || ! is_writable($this->compile_dir.'/cache') ) {
                Utils::defineConstants();
                $whoami = @exec('whoami');
                if (empty($whoami)) {
                    $whoami = 'nobody';
                }
                return str_replace(array('#THINKUP_BASE_URL#', '#WHOAMI#', '#COMPILE_DIR#'),
                array(THINKUP_BASE_URL, $whoami, $this->compile_dir),
                file_get_contents(THINKUP_WEBAPP_PATH.'_lib/view/500-perm.html'));
            } else {
                return parent::fetch($template, $cache_key, $compile_id, $display);
            }
        }


clear_all_cache
~~~~~~~~~~~~~~~
* **@param** int $expire_time


Override the parent's clear_all_cache method to check if caching is on to begin with. We do this to prevent the
cache/MAKETHISDIRWRITABLE.txt from being deleted during test runs; this file needs to exist in order for the
cache directory to remain in the git repository.

.. code-block:: php5

    <?php
        public function clear_all_cache($exp_time = null) {
            if ($this->caching == 1) {
                parent::clear_all_cache($exp_time);
            }
        }




