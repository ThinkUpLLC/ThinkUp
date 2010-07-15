<?php
/**
 * Login Controller
 *
 * @TODO Build mechanism for redirecting user to originally-requested logged-in only page.
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class LoginController extends ThinkTankController {

    public function __construct($session_started=false) {
        parent::__construct($session_started);
        $this->addToView('controller_title', 'Log in');
        $this->setViewTemplate('session.login.tpl');
        $this->disableCaching();
    }

    public function control() {
        //don't show login form if already logged in
        if ($this->isLoggedIn()) {
            $controller = new PrivateDashboardController(true);
            return $controller->go();
        } else  {
            $od = DAOFactory::getDAO('OwnerDAO');

            if (isset($_POST['Submit']) && $_POST['Submit']=='Log In'
            && isset($_POST['email']) && isset($_POST['pwd']) ) {
                if ( $_POST['email']=='' || $_POST['pwd']=='') {
                    if ( $_POST['email']=='') {
                        $this->addToView('errormsg', "Email must not be empty");
                        return $this->generateView();
                    } else {
                        $this->addToView('errormsg', "Password must not be empty");
                        return $this->generateView();
                    }
                } else {
                    $session = new Session();
                    $user_email = $_POST['email'];
                    $this->addToView('email', $user_email);
                    $owner = $od->getByEmail($user_email);
                    if (!$owner) {
                        $this->addToView('errormsg', "Incorrect email");
                        return $this->generateView();
                    } elseif (!$session->pwdCheck($_POST['pwd'], $od->getPass($user_email))) {
                        $this->addToView('errormsg', "Incorrect password");
                        return $this->generateView();
                    } else {
                        // this sets variables in the session
                        $session->completeLogin($owner);
                        $od->updateLastLogin($user_email);
                        $controller = new PrivateDashboardController(true);
                        return $controller->go();
                    }
                }
            } else  {
                return $this->generateView();
            }
        }
    }
}
