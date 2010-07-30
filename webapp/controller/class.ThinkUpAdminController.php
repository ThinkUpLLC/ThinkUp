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

    public function control() {
        if ($this->isAdmin()) {
            return $this->authControl();
        } else {
            return "You must be a ThinkUp admin in to do this";
        }
    }
}