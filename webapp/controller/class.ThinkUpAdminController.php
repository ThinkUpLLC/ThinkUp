<?php
/**
 * ThinkUp Admin Controller
 *
 * Parent controller for all logged-in admin user-only actions.
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
abstract class ThinkUpAdminController extends ThinkUpAuthController {
    public function __construct($session_started=false) {
        parent::__construct($session_started);
    }

    public function authControl() {
        if ($this->isAdmin()) {
            return $this->adminControl();
        } else {
            return "You must be a ThinkUp admin to do this";
        }
    }
}