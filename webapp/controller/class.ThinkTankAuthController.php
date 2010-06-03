<?php
/**
 * ThinkTank Authorized Controller
 *
 * Parent controller for all logged-in user-only actions
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
abstract class ThinkTankAuthController extends ThinkTankController implements Controller {
    public function control() {
        if ($this->isLoggedIn()) {
            $this->addToView('logged_in_user', $this->getLoggedInUser());
            if ($this->isLoggedIn()) {
                $this->addToViewCacheKey($this->getLoggedInUser());
            }
            return $this->auth_control();
        } else {
            //@TODO bounce to sign in page and bounce back to original action once signed in
            if (get_class($this)=='PrivateDashboardController') {
                $controller = new PublicTimelineController(true);
                return $controller->go();
            } else {
                return "You must be logged in to do this";
            }
        }
    }
}