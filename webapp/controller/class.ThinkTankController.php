<?php
/**
 * ThinkTank Controller
 *
 * The parent class for all ThinkTank webapp controllers.
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
     * @var string Smarty template cache key
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
     * Sets default values all views have access to:
     *  $site_root_path - path of the ThinkTank installation site root
     *  $logged_in_user - email address of currently logged in ThinkTank user
     */
    public function __construct($session_started=false) {
        if (!$session_started) {
            session_start();
        }
        $config = Config::getInstance();
        $this->is_view_cached = ($config->getValue('cache_pages')==0 ? false:true);
        $this->view_mgr = new SmartyThinkTank();

        //set default values all views have access to
        $this->view_mgr->assign('site_root_path', $config->getValue('site_root_path'));
        $this->view_mgr->assign('logged_in_user', $this->getLoggedInUser());

        //add currently logged in user to cache key if logged in
        if ($this->isLoggedIn()) {
            $this->addToViewCacheKey($this->getLoggedInUser());
        }
    }

    /**
     * Clean up
     *
     * Close open database connection
     *
     * @TODO: Remove this once global db and conn variables are no longer created in init.php (after PDO port is complete)
     */
    public function __destruct() {
        //        global $db;
        //        global $conn;
        //        if (isset($db) && isset($conn)) {
        //            $db->closeConnection($conn);
        //        }
    }

    /**
     * Adds $addition to cache key
     * @param string $addition
     */
    protected function addToViewCacheKey($addition) {
        array_push($this->view_cache_key, $addition);
    }

    /**
     * Returns whether or not ThinkTank user is logged in
     * @return boolean whether or not user is logged in
     */
    protected function isLoggedIn() {
        return isset($_SESSION['user']);
    }

    /**
     * Return email address of logged-in user
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
     * @return string cache key
     */
    private function getCacheKeyString() {
        return implode($this->view_cache_key, self::KEY_SEPARATOR);
    }
    /**
     * Displays web page
     * @return string results
     */
    public function renderView() {
        if (isset($this->view_template)) {
            if ($this->is_view_cached) {
                $cacheKey = $this->view_template . self::KEY_SEPARATOR .$this->getCacheKeyString();
                return $this->view_mgr->fetch($this->view_template, $cacheKey);
            } else {
                return $this->view_mgr->fetch($this->view_template);
            }
        } else {
            throw new Exception('No view template specified');
        }
    }

    /**
     * Sets the view template filename
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
}