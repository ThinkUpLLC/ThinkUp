<?php
/**
 * ThinkTank Controller
 *
 * The parent class of all ThinkTank webapp controllers.
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */

abstract class ThinkTankController {
    /**
     * @var SmartyThinkTank
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
    private $profiler_enabled = false;
    /**
     *
     * @var float
     */
    private $start_time = 0;
    /**
     *
     * @var Session
     */
    protected $app_session;

    /**
     * Constructs ThinkTankController
     *
     *  Adds email address of currently logged in ThinkTank user, '' if not logged in, to view
     *  {$logged_in_user}
     *  @return ThinkTankController
     */
    public function __construct($session_started=false) {
        if (!$session_started) {
            session_start();
        }
        $config = Config::getInstance();
        $this->profiler_enabled = Profiler::isEnabled();
        if ( $this->profiler_enabled) {
            $this->start_time = microtime(true);
        }
        $this->view_mgr = new SmartyThinkTank();
        $this->app_session = new Session();
        if ($this->isLoggedIn()) {
            $this->addToView('logged_in_user', $this->getLoggedInUser());
        }
    }

    /**
     * Handle request parameters for a particular resource and return view markup.
     *
     * @return str Markup which renders controller results.
     */
    abstract public function control();

    /**
     * Returns whether or not ThinkTank user is logged in
     *
     * @return bool whether or not user is logged in
     */
    protected function isLoggedIn() {
        //return (isset($_SESSION['user']) && $_SESSION['user']!= '') ? true : false;
        return $this->app_session->isLoggedIn();
    }

    /**
     * Returns whether or not a logged-in ThinkTank user is an admin
     *
     * @return bool whether or not logged-in user is an admin
     */
    protected function isAdmin() {
        return $this->app_session->isAdmin();
    }

    /**
     * Return email address of logged-in user
     *
     * @return str email
     */
    protected function getLoggedInUser() {
        if ($this->isLoggedIn()) {
            return $_SESSION['user'];
        } else {
            return null;
        }
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
        if (isset($this->view_template)) {
            if ($this->view_mgr->isViewCached()) {
                $cache_key = $this->getCacheKeyString();
                if ($this->profiler_enabled) {
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
                if ($this->profiler_enabled) {
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
            if ($this->profiler_enabled) {
                $results = $this->control();
                $end_time = microtime(true);
                $total_time = $end_time - $this->start_time;
                $profiler = Profiler::getInstance();
                $this->disableCaching();
                $profiler->add($total_time, "total page execution time, running ".$profiler->total_queries." queries.");
                $this->setViewTemplate('_profiler.tpl');
                $this->addToView('profile_items',$profiler->getProfile());
                return  $results . $this->generateView();
            } else  {
                return $this->control();
            }

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Provided for tests only, to assert that proper view values have been set. (Debug must be equal to true.)
     * @return SmartyThinkTank
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