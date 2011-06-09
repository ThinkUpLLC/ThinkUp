LoginController
===============
Inherits from `ThinkUpController <./ThinkUpController.html>`_.

ThinkUp/webapp/_lib/controller/class.LoginController.php

Copyright (c) 2009-2011 Gina Trapani

Login Controller

@TODO Build mechanism for redirecting user to originally-requested logged-in only page.



Methods
-------

control
~~~~~~~



.. code-block:: php5

    <?php
        public function control() {
            $this->setPageTitle('Log in');
            $this->setViewTemplate('session.login.tpl');
            $this->view_mgr->addHelp('login', 'userguide/accounts/index');
            $this->disableCaching();
            //don't show login form if already logged in
            if ($this->isLoggedIn()) {
                $controller = new DashboardController(true);
                return $controller->go();
            } else  {
                $owner_dao = DAOFactory::getDAO('OwnerDAO');
    
                if (isset($_POST['Submit']) && $_POST['Submit']=='Log In' && isset($_POST['email']) &&
                isset($_POST['pwd']) ) {
                    if ( $_POST['email']=='' || $_POST['pwd']=='') {
                        if ( $_POST['email']=='') {
                            $this->addErrorMessage("Email must not be empty");
                            return $this->generateView();
                        } else {
                            $this->addErrorMessage("Password must not be empty");
                            return $this->generateView();
                        }
                    } else {
                        $session = new Session();
                        $user_email = $_POST['email'];
                        $this->addToView('email', $user_email);
                        $owner = $owner_dao->getByEmail($user_email);
                        if (!$owner) {
                            $this->addErrorMessage("Incorrect email");
                            return $this->generateView();
                        } elseif (!$owner->is_activated) {
                            $this->addErrorMessage("Inactive account. " . $owner->account_status. ". ".
                            '<a href="forgot.php">Reset your password.</a>');
                            return $this->generateView();
                        } elseif (!$session->pwdCheck($_POST['pwd'], $owner_dao->getPass($user_email))) { //failed login
                            if ($owner->failed_logins >= 10) {
                                $owner_dao->deactivateOwner($user_email);
                                $owner_dao->setAccountStatus($user_email,
                                "Account deactivated due to too many failed logins");
                            }
                            $owner_dao->incrementFailedLogins($user_email);
                            $this->addErrorMessage("Incorrect password");
                            return $this->generateView();
                        } else {
                            // this sets variables in the session
                            $session->completeLogin($owner);
                            $owner_dao->updateLastLogin($user_email);
                            $owner_dao->resetFailedLogins($user_email);
                            $owner_dao->clearAccountStatus('');
                            $controller = new DashboardController(true);
                            return $controller->control();
                        }
                    }
                } else  {
                    return $this->generateView();
                }
            }
        }




