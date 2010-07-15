<?php
/**
 * Logout Controller
 *
 * Log out of ThinkTank.
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class LogoutController extends ThinkTankAuthController {
    public function authControl() {
        $this->app_session->logout();
        $controller = new PublicTimelineController(true);
        $controller->addSuccessMessage("You have successfully logged out.");
        return $controller->go();
    }
}