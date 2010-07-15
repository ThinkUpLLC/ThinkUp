<?php
/**
 * ThinkTank Admin Controller
 *
 * Parent controller for all logged-in admin user-only actions.
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
abstract class ThinkTankAdminController extends ThinkTankAuthController implements Controller {
    public function __construct($session_started=false) {
        parent::__construct($session_started);
    }

    public function control() {
        if ($this->isAdmin()) {
            return $this->authControl();
        } else {
            return "You must be a ThinkTank admin in to do this";
        }
    }
}