<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.ThinkUpController.php
 *
 * Copyright (c) 2009-2011 Gina Trapani
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
 * ThinkUp Controller
 *
 * The parent class of all ThinkUp webapp controllers.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2011 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */

abstract class ThinkUpController {
    /**
     * @var SmartyThinkUp
     */
    protected $view_mgr;
    /**
     * @var string Smarty template filename
     */
    protected $view_template = null;
    /**
     *
     * @var string cache key separator
     */
    const KEY_SEPARATOR='-';
    /**
     *
     * @var bool
     */
    protected $profiler_enabled = false;
    /**
     *
     * @var float
     */
    private $start_time = 0;
    /**
     *
     * @var araray
     */
    protected $header_scripts = array ();

    /**
     *
     * @var array
     */
    protected $json_data = null;

    /**
     *
     * @var str
     */
    protected $content_type = 'text/html';

    /**
     * Constructs ThinkUpController
     *
     *  Adds email address of currently logged in ThinkUp user, '' if not logged in, to view
     *  {$logged_in_user}
     *  @return ThinkUpController
     */
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
                $this->addToView('logo_link', 'index.php?u='. SessionCache::get('selected_instance_username')
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

    /**
     * Handle request parameters for a particular resource and return view markup.
     *
     * @return str Markup which renders controller results.
     */
    abstract public function control();

    /**
     * Returns whether or not ThinkUp user is logged in
     *
     * @return bool whether or not user is logged in
     */
    protected function isLoggedIn() {
        return Session::isLoggedIn();
    }

    /**
     * Returns whether or not a logged-in ThinkUp user is an admin
     *
     * @return bool whether or not logged-in user is an admin
     */
    protected function isAdmin() {
        return Session::isAdmin();
    }

    /**
     * Return email address of logged-in user
     *
     * @return str email
     */
    protected function getLoggedInUser() {
        return Session::getLoggedInUser();
    }

    /**
     * Returns cache key as a string
     *
     * Set to public for the sake of tests.
     * @return str cache key
     */
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

    /**
     * Generates web page markup
     *
     * @return str view markup
     */
    protected function generateView() {
        // add header javascript if defined
        if( count($this->header_scripts) > 0) {
            $this->addToView('header_scripts', $this->header_scripts);
        }
        if (isset($this->view_template)) {
            if ($this->view_mgr->isViewCached()) {
                $cache_key = $this->getCacheKeyString();
                if ($this->profiler_enabled && !isset($this->json_data) && strpos($this->content_type,
                'text/javascript') === false) {
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
                if ($this->profiler_enabled && !isset($this->json_data) && strpos($this->content_type,
                'text/javascript') === false) {
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
                    $json = json_encode($this->json_data);
                    // strip escaped forwardslashes
                    $json = preg_replace("/\\\\\//", '/', $json);
                    $json = Utils::convertNumericStrings($json);
                    $json = Utils::indentJSON($json);
                    $this->addToView('json', $json);
                    return $this->view_mgr->fetch('json.tpl', $this->getCacheKeyString());
                }
            } else {
                $json = json_encode($this->json_data);
                // strip escaped forwardslashes
                $json = preg_replace("/\\\\\//", '/', $json);
                $json = Utils::convertNumericStrings($json);
                $json = Utils::indentJSON($json);
                $this->addToView('json', $json);
                return $this->view_mgr->fetch('json.tpl');
            }
        } else {
            throw new Exception(get_class($this).': No view template specified');
        }
    }

    /**
     * Sets the view template filename
     *
     * @param str $tpl_filename
     */
    protected function setViewTemplate($tpl_filename) {
        $this->view_template = $tpl_filename;
    }

    /**
     * Sets json data structure to output a json string, and sets Content-Type to appplication/json
     *
     * @param array json data
     */
    protected function setJsonData($data) {
        $this->json_data = $data;
    }

    /**
     * Sets Content Type header
     *
     * @param string Content Type
     */
    protected function setContentType($content_type) {
        $this->content_type = $content_type;
        // if is to suppress 'headers already sent' error while testing, etc.
        if( ! headers_sent() ) {
            header('Content-Type: ' . $this->content_type, true);
        }
    }

    /**
     * Gets Content Type header
     *
     * @return string Content Type
     */
    public function getContentType() {
        return $this->content_type;
    }

    /**
     * Add javascript to header
     *
     * @param str javascript path
     */
    public function addHeaderJavaScript($script) {
        array_push($this->header_scripts, $script);
    }

    /**
     * Add data to view template engine for rendering
     *
     * @param str $key
     * @param mixed $value
     */
    protected function addToView($key, $value) {
        $this->view_mgr->assign($key, $value);
    }

    /**
     * Invoke the controller
     *
     * Always use this method, not control(), to invoke the controller.
     * @TODO show get 500 error template on Exception
     * (if debugging is true, pass the exception details to the 500 template)
     */
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
            if (strpos($content_type, ';') !== FALSE) {
                $content_type = array_shift(explode(';', $content_type));
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

    /**
     * Initalize app
     * Load config file and required plugins
     * @throws Exception
     */
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

    /**
     * Provided for tests only, to assert that proper view values have been set. (Debug must be equal to true.)
     * @return SmartyThinkUp
     */
    public function getViewManager() {
        return $this->view_mgr;
    }

    /**
     * Turn off caching
     * Provided in case an individual controller wants to override the application-wide setting.
     */
    protected function disableCaching() {
        $this->view_mgr->disableCaching();
    }

    /**
     * Check if cache needs refreshing
     * @return bool
     */
    protected function shouldRefreshCache() {
        if ($this->view_mgr->isViewCached()) {
            return !$this->view_mgr->is_cached($this->view_template, $this->getCacheKeyString());
        } else {
            return true;
        }
    }

    /**
     * Set web page title
     * This method only works for views that reference _header.tpl.
     * @param str $title
     */
    public function setPageTitle($title) {
        $this->addToView('controller_title', $title);
    }

    /**
     * Add error message to view
     * @param str $msg
     */
    public function addErrorMessage($msg) {
        $this->disableCaching();
        $this->addToView('errormsg', $msg );
    }

    /**
     * Add success message to view
     * @param str $msg
     */
    public function addSuccessMessage($msg) {
        $this->disableCaching();
        $this->addToView('successmsg', $msg );
    }

    /**
     * Add informational message to view
     * @param str $msg
     */
    public function addInfoMessage($msg) {
        $this->disableCaching();
        $this->addToView('infomsg', $msg );
    }
}
