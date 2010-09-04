<?php
/**
 * ThinkUp Authorized Controller
 *
 * Parent controller for all logged-in user-only actions
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
abstract class ThinkUpAuthController extends ThinkUpController {
    public function __construct($session_started=false) {
        parent::__construct($session_started);
    }

    public function control() {
        if ($this->isLoggedIn()) {
            return $this->authControl();
        } else {
            return $this->bounce();
        }
    }

    /**
     * Bounce user to public page or to error page.
     * @TODO bounce back to original action once signed in
     */
    protected function bounce() {
        if (get_class($this)=='DashboardController' || get_class($this)=='PostController') {
            $controller = new DashboardController(true);
            return $controller->go();
        } else {
            $config = Config::getInstance();
            throw new Exception('You must <a href="'.$config->getValue('site_root_path').
            'session/login.php">log in</a> to do this.');
        }
    }
}