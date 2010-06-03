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
     * @var boolean whether or not view is cached
     */
    protected $is_view_cached = false;
    /**
     *
     * @var array contains the cache key key/value pairs
     */
    private $view_cache_key = array();
    /**
     *
     * @var string cache key separator
     */
    const KEY_SEPARATOR='-';

    /**
     * Constructs ThinkTankController
     *
     *  Adds email address of currently logged in ThinkTank user, '' if not logged in, to view
     *  {$logged_in_user}
     */
    public function __construct($session_started=false) {
        if (!$session_started) {
            session_start();
        }
        $config = Config::getInstance();
        $this->is_view_cached = ($config->getValue('cache_pages')==0 ? false:true);
        $this->view_mgr = new SmartyThinkTank();

        //@TODO Add this logic to the ThinkTankAuthController
        $this->addToView('logged_in_user', $this->getLoggedInUser());
        //add currently logged in user to cache key if logged in
        if ($this->isLoggedIn()) {
            $this->addToViewCacheKey($this->getLoggedInUser());
        }
    }

    /**
     * Adds $addition to cache key array
     *
     * @param string $addition
     */
    protected function addToViewCacheKey($addition) {
        array_push($this->view_cache_key, $addition);
    }

    /**
     * Returns whether or not ThinkTank user is logged in
     *
     * @return boolean whether or not user is logged in
     */
    protected function isLoggedIn() {
        return (isset($_SESSION['user']) && $_SESSION['user']!= '') ? true : false;
    }

    /**
     * Return email address of logged-in user
     *
     * @return string email
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
     * @return string cache key
     */
    public function getCacheKeyString() {
        return implode($this->view_cache_key, self::KEY_SEPARATOR);
    }

    /**
     * Generates web page markup
     *
     * @return string view markup
     */
    protected function generateView() {
        if (isset($this->view_template)) {
            if ($this->is_view_cached) {
                $cache_key = $this->view_template . self::KEY_SEPARATOR .$this->getCacheKeyString();
                return $this->view_mgr->fetch($this->view_template, $cache_key);
            } else {
                return $this->view_mgr->fetch($this->view_template);
            }
        } else {
            throw new Exception('No view template specified');
        }
    }

    /**
     * Sets the view template filename
     *
     * @param string $tpl_filename
     */
    protected function setViewTemplate($tpl_filename) {
        $this->view_template = $tpl_filename;
    }

    /**
     * Add data to view template engine for rendering
     *
     * @param string $key
     * @param mixed $value
     */
    protected function addToView($key, $value) {
        $this->view_mgr->assign($key, $value);
    }

    /**
     * Invoke the controller
     *
     * Always use this method, not control(), to invoke the controller.
     * @TODO show get 500 error template on Exception (if debugging is true, pass the exception details to the 500 template)
     */
    public function go() {
        try {
            return $this->control();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Provided for tests only, to assert that proper view values have been set. (Debug must be equal to true.)
     */
    public function getViewManager() {
        return $this->view_mgr;
    }
}