PasswordResetController
=======================
Inherits from `ThinkUpController <./ThinkUpController.html>`_.

ThinkUp/webapp/_lib/controller/class.PasswordResetController.php

Copyright (c) 2009-2011 Gina Trapani, Michael Louis Thaler

Password Reset Controller
Given the correct hash, changes a ThinkUp user's password and activates a deactivated account.



Methods
-------

control
~~~~~~~



.. code-block:: php5

    <?php
        public function control() {
            $session = new Session();
            $owner_dao = DAOFactory::getDAO('OwnerDAO');
    
            $this->setViewTemplate('session.resetpassword.tpl');
            $this->disableCaching();
    
            if (!isset($_GET['token']) || !preg_match('/^[\da-f]{32}$/', $_GET['token']) ||
            (!$user = $owner_dao->getByPasswordToken($_GET['token']))) {
                // token is nonexistant or bad
                $this->addErrorMessage('You have reached this page in error.');
                return $this->generateView();
            }
    
            if (!$user->validateRecoveryToken($_GET['token'])) {
                $this->addErrorMessage('Your token is expired.');
                return $this->generateView();
            }
    
            if (isset($_POST['password'])) {
                if ($_POST['password'] == $_POST['password_confirm']) {
                    $login_controller = new LoginController(true);
                    if ($owner_dao->updatePassword($user->email, $session->pwdcrypt($_POST['password'])) < 1 ) {
                        $login_controller->addErrorMessage('Problem changing your password!');
                    } else {
                        $owner_dao->activateOwner($user->email);
                        $owner_dao->clearAccountStatus($user->email);
                        $login_controller->addSuccessMessage('You have changed your password.');
                    }
                    return $login_controller->go();
                } else {
                    $this->addErrorMessage("Passwords didn't match.");
                }
            } else if (isset($_POST['Submit'])) {
                $this->addErrorMessage('Please enter a new password.');
            }
            return $this->generateView();
        }




