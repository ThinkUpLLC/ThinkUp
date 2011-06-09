ThinkUpController
=================

ThinkUp/webapp/_lib/controller/class.ThinkUpController.php

Copyright (c) 2009-2011 Gina Trapani

ThinkUp Controller

The parent class of all ThinkUp webapp controllers.


Properties
----------

view_mgr
~~~~~~~~



view_template
~~~~~~~~~~~~~



profiler_enabled
~~~~~~~~~~~~~~~~



start_time
~~~~~~~~~~



header_scripts
~~~~~~~~~~~~~~



json_data
~~~~~~~~~



content_type
~~~~~~~~~~~~





Methods
-------

__construct
~~~~~~~~~~~
* **@return** ThinkUpController


Constructs ThinkUpController

 Adds email address of currently logged in ThinkUp user, '' if not logged in, to view
 {$logged_in_user}

.. code-block:: php5

    <?php
        public function __construct($session_started=false) {
            if (!$session_started) {
                session_start();
            }
            try {
                $config = Config::getInstance();
                $this->profiler_enabled = Profiler::isEnabled();
                if ( $this->profiler_enabled) {
                    $this->start_time = microtime(true);
                }
                $this->view_mgr = new SmartyThinkUp();
                $this->app_session = new Session();
                if ($this->isLoggedIn()) {
                    $this->addToView('logged_in_user', $this->getLoggedInUser());
                }
                if ($this->isAdmin()) {
                    $this->addToView('user_is_admin', true);
                }
                $THINKUP_VERSION = $config->getValue('THINKUP_VERSION');
                $this->addToView('thinkup_version', $THINKUP_VERSION);
    
                if (SessionCache::isKeySet('selected_instance_network') &&
                SessionCache::isKeySet('selected_instance_username')) {
                    $this->addToView('selected_instance_network', SessionCache::get('selected_instance_network'));
                    $this->addToView('selected_instance_username', SessionCache::get('selected_instance_username'));
                    $this->addToView('logo_link', 'index.php?u='. urlencode(SessionCache::get('selected_instance_username'))
                    .'&n='. urlencode(SessionCache::get('selected_instance_network')));
                }
            } catch (Exception $e) {
                Utils::defineConstants();
                $cfg_array =  array(
                'site_root_path'=>THINKUP_BASE_URL,
                'source_root_path'=>THINKUP_ROOT_PATH, 
                'debug'=>false, 
                'app_title'=>"ThinkUp", 
                'cache_pages'=>false);
                $this->view_mgr = new SmartyThinkUp($cfg_array);
            }
        }


control
~~~~~~~
* **@return** str Markup which renders controller results.


Handle request parameters for a particular resource and return view markup.

.. code-block:: php5

    <?php
        abstract public function control();


isLoggedIn
~~~~~~~~~~
* **@return** bool whether or not user is logged in


Returns whether or not ThinkUp user is logged in

.. code-block:: php5

    <?php
        protected function isLoggedIn() {
            return Session::isLoggedIn();
        }


isAdmin
~~~~~~~
* **@return** bool whether or not logged-in user is an admin


Returns whether or not a logged-in ThinkUp user is an admin

.. code-block:: php5

    <?php
        protected function isAdmin() {
            return Session::isAdmin();
        }


getLoggedInUser
~~~~~~~~~~~~~~~
* **@return** str email


Return email address of logged-in user

.. code-block:: php5

    <?php
        protected function getLoggedInUser() {
            return Session::getLoggedInUser();
        }


getCacheKeyString
~~~~~~~~~~~~~~~~~
* **@return** str cache key


Returns cache key as a string

Set to public for the sake of tests.

.. code-block:: php5

    <?php
        public function getCacheKeyString() {
            $view_cache_key = array();
            if ($this->getLoggedInUser()) {
                array_push($view_cache_key, $this->getLoggedInuser());
            }
            $keys = array_keys($_GET);
            foreach ($keys as $key) {
                array_push($view_cache_key, $_GET[$key]);
            }
            return $this->view_template.self::KEY_SEPARATOR.(implode($view_cache_key, self::KEY_SEPARATOR));
        }


generateView
~~~~~~~~~~~~
* **@return** str view markup


Generates web page markup

.. code-block:: php5

    <?php
        protected function generateView() {
            // add header javascript if defined
            if( count($this->header_scripts) > 0) {
                $this->addToView('header_scripts', $this->header_scripts);
            }
            $this->sendHeader();
            if (isset($this->view_template)) {
                if ($this->view_mgr->isViewCached()) {
                    $cache_key = $this->getCacheKeyString();
                    if ($this->profiler_enabled && !isset($this->json_data) &&
                    strpos($this->content_type, 'text/javascript') === false) {
                        $view_start_time = microtime(true);
                        $cache_source = $this->shouldRefreshCache()?"DATABASE":"FILE";
                        $results = $this->view_mgr->fetch($this->view_template, $cache_key);
                        $view_end_time = microtime(true);
                        $total_time = $view_end_time - $view_start_time;
                        $profiler = Profiler::getInstance();
                        $profiler->add($total_time, "Rendered view from ". $cache_source . ", cache key: <i>".
                        $this->getCacheKeyString(), false).'</i>';
                        return $results;
                    } else {
                        return $this->view_mgr->fetch($this->view_template, $cache_key);
                    }
                } else {
                    if ($this->profiler_enabled && !isset($this->json_data) &&
                    strpos($this->content_type, 'text/javascript') === false) {
                        $view_start_time = microtime(true);
                        $results = $this->view_mgr->fetch($this->view_template);
                        $view_end_time = microtime(true);
                        $total_time = $view_end_time - $view_start_time;
                        $profiler = Profiler::getInstance();
                        $profiler->add($total_time, "Rendered view (not cached)", false);
                        return $results;
                    } else  {
                        return $this->view_mgr->fetch($this->view_template);
                    }
                }
            } else if(isset($this->json_data) ) {
                $this->setContentType('application/json');
                if ($this->view_mgr->isViewCached()) {
                    if ($this->view_mgr->is_cached('json.tpl', $this->getCacheKeyString())) {
                        return $this->view_mgr->fetch('json.tpl', $this->getCacheKeyString());
                    } else {
                        $this->prepareJSON();
                        return $this->view_mgr->fetch('json.tpl', $this->getCacheKeyString());
                    }
                } else {
                    $this->prepareJSON();
                    return $this->view_mgr->fetch('json.tpl');
                }
            } else {
                throw new Exception(get_class($this).': No view template specified');
            }
        }


prepareJSON
~~~~~~~~~~~
* **@param** bool $indent Whether or not to indent the JSON string. Defaults to true.
* **@param** bool $stripslashes Whether or not to strip escaped slashes. Default to true.
* **@param** bool $convert_numeric_strings Whether or not to convert numeric strings to numbers. Defaults to true.


Prepares the JSON data in $this->json_data and adds it to the current view under the key "json".

.. code-block:: php5

    <?php
        private function prepareJSON($indent = true, $stripslashes = true, $convert_numeric_strings = true) {
            if (isset($this->json_data)) {
                $json = json_encode($this->json_data);
                if ($stripslashes) {
                    // strip escaped forwardslashes
                    $json = preg_replace("/\\\\\//", '/', $json);
                }
                if ($convert_numeric_strings) {
                    // converts numeric strings to numbers
                    $json = Utils::convertNumericStrings($json);
                }
                if ($indent) {
                    // indents JSON strings so they are human readable
                    $json = Utils::indentJSON($json);
                }
                $this->addToView('json', $json);
            }
        }


sendHeader
~~~~~~~~~~

Send content type header

.. code-block:: php5

    <?php
        protected function sendHeader() {
            if( ! headers_sent() ) { // suppress 'headers already sent' error while testing
                header('Content-Type: ' . $this->content_type, true);
            }
        }


setViewTemplate
~~~~~~~~~~~~~~~
* **@param** str $tpl_filename


Sets the view template filename

.. code-block:: php5

    <?php
        protected function setViewTemplate($tpl_filename) {
            $this->view_template = $tpl_filename;
        }


setJsonData
~~~~~~~~~~~
* **@param** array json data


Sets json data structure to output a json string, and sets Content-Type to appplication/json

.. code-block:: php5

    <?php
        protected function setJsonData($data) {
            if ($data != null) {
                $this->setContentType('application/json');
            }
    
            $this->json_data = $data;
        }


setContentType
~~~~~~~~~~~~~~
* **@param** string Content Type


Sets Content Type header

.. code-block:: php5

    <?php
        protected function setContentType($content_type) {
            if ($content_type != 'image/png') {
                $this->content_type = $content_type.'; charset=UTF-8';
            } else {
                $this->content_type = $content_type;
            }
        }


getContentType
~~~~~~~~~~~~~~
* **@return** string Content Type


Gets Content Type header

.. code-block:: php5

    <?php
        public function getContentType() {
            return $this->content_type;
        }


addHeaderJavaScript
~~~~~~~~~~~~~~~~~~~
* **@param** str javascript path


Add javascript to header

.. code-block:: php5

    <?php
        public function addHeaderJavaScript($script) {
            array_push($this->header_scripts, $script);
        }


addToView
~~~~~~~~~
* **@param** str $key
* **@param** mixed $value


Add data to view template engine for rendering

.. code-block:: php5

    <?php
        protected function addToView($key, $value) {
            $this->view_mgr->assign($key, $value);
        }


go
~~

Invoke the controller

Always use this method, not control(), to invoke the controller.
@TODO show get 500 error template on Exception
(if debugging is true, pass the exception details to the 500 template)

.. code-block:: php5

    <?php
        public function go() {
            try {
                $this->initalizeApp();
    
                // are we in need of a database migration?
                $classname = get_class($this);
                if ($classname != 'InstallerController' && $classname != 'BackupController' &&
                UpgradeController::isUpgrading( $this->isAdmin(), $classname) ) {
                    $this->setViewTemplate('install.upgradeneeded.tpl');
                    $this->disableCaching();
                    $option_dao = DAOFactory::getDAO('OptionDAO');
                    $option_dao->clearSessionData(OptionDAO::APP_OPTIONS);
                    return $this->generateView();
                } else {
                    $results = $this->control();
                    if ($this->profiler_enabled && !isset($this->json_data)
                    && strpos($this->content_type, 'text/javascript') === false
                    && strpos($this->content_type, 'text/csv') === false) {
                        $end_time = microtime(true);
                        $total_time = $end_time - $this->start_time;
                        $profiler = Profiler::getInstance();
                        $this->disableCaching();
                        $profiler->add($total_time,
                        "total page execution time, running ".$profiler->total_queries." queries.");
                        $this->setViewTemplate('_profiler.tpl');
                        $this->addToView('profile_items',$profiler->getProfile());
                        return  $results . $this->generateView();
                    } else  {
                        return $results;
                    }
                }
            } catch (Exception $e) {
                //Explicitly set TZ (before we have user's choice) to avoid date() warning about using system settings
                date_default_timezone_set('America/Los_Angeles');
                $content_type = $this->content_type;
                if (strpos($content_type, ';') !== false) {
                    $exploded = explode(';', $content_type);
                    $content_type = array_shift($exploded);
                }
                switch ($content_type) {
                    case 'application/json':
                        $this->setViewTemplate('500.json.tpl');
                        break;
                    case 'text/plain':
                        $this->setViewTemplate('500.txt.tpl');
                        break;
                    default:
                        $this->setViewTemplate('500.tpl');
                }
                $this->addToView('error_type', get_class($e));
                $this->addErrorMessage($e->getMessage());
                return $this->generateView();
            }
        }


initalizeApp
~~~~~~~~~~~~
* **@throws** Exception


Initalize app
Load config file and required plugins

.. code-block:: php5

    <?php
        private function initalizeApp() {
            $classname = get_class($this);
            if ($classname != "InstallerController") {
                //Initialize config
                $config = Config::getInstance();
                if ($config->getValue('timezone')) {
                    date_default_timezone_set($config->getValue('timezone'));
                }
                if ($config->getValue('debug')) {
                    ini_set("display_errors", 1);
                    ini_set("error_reporting", E_ALL);
                }
                if($classname != "BackupController") {
                    //Init plugins
                    $pdao = DAOFactory::getDAO('PluginDAO');
                    $active_plugins = $pdao->getActivePlugins();
                    Utils::defineConstants();
                    foreach ($active_plugins as $ap) {
                        //add plugin's model and controller folders as Loader paths here
                        Loader::addPath(THINKUP_WEBAPP_PATH.'plugins/'.$ap->folder_name."/model/");
                        Loader::addPath(THINKUP_WEBAPP_PATH.'plugins/'.$ap->folder_name.
                        "/controller/");
                        //require the main plugin registration file here
                        if ( file_exists(
                        THINKUP_WEBAPP_PATH.'plugins/'.$ap->folder_name."/controller/".$ap->folder_name.".php")) {
                            require_once THINKUP_WEBAPP_PATH.'plugins/'.$ap->folder_name."/controller/".$ap->folder_name.
                            ".php";
                        }
                    }
                }
            }
        }


getViewManager
~~~~~~~~~~~~~~
* **@return** SmartyThinkUp


Provided for tests only, to assert that proper view values have been set. (Debug must be equal to true.)

.. code-block:: php5

    <?php
        public function getViewManager() {
            return $this->view_mgr;
        }


disableCaching
~~~~~~~~~~~~~~

Turn off caching
Provided in case an individual controller wants to override the application-wide setting.

.. code-block:: php5

    <?php
        protected function disableCaching() {
            $this->view_mgr->disableCaching();
        }


shouldRefreshCache
~~~~~~~~~~~~~~~~~~
* **@return** bool


Check if cache needs refreshing

.. code-block:: php5

    <?php
        protected function shouldRefreshCache() {
            if ($this->view_mgr->isViewCached()) {
                return !$this->view_mgr->is_cached($this->view_template, $this->getCacheKeyString());
            } else {
                return true;
            }
        }


setPageTitle
~~~~~~~~~~~~
* **@param** str $title


Set web page title
This method only works for views that reference _header.tpl.

.. code-block:: php5

    <?php
        public function setPageTitle($title) {
            $this->addToView('controller_title', $title);
        }


addErrorMessage
~~~~~~~~~~~~~~~
* **@param** str $msg


Add error message to view

.. code-block:: php5

    <?php
        public function addErrorMessage($msg) {
            $this->disableCaching();
            $this->addToView('errormsg', $msg );
        }


addSuccessMessage
~~~~~~~~~~~~~~~~~
* **@param** str $msg


Add success message to view

.. code-block:: php5

    <?php
        public function addSuccessMessage($msg) {
            $this->disableCaching();
            $this->addToView('successmsg', $msg );
        }


addInfoMessage
~~~~~~~~~~~~~~
* **@param** str $msg


Add informational message to view

.. code-block:: php5

    <?php
        public function addInfoMessage($msg) {
            $this->disableCaching();
            $this->addToView('infomsg', $msg );
        }




