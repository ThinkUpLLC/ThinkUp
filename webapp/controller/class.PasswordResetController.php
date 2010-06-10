<?php
require_once 'class.LoginController.php';
require_once 'class.PrivateDashboardController.php';

/**
 * A controller for allowing a user to change their password if they have
 * the correct hash.
 *
 * @author Michael Louis Thaler <michael.louis.thaler[at]gmail[dot]com>
 */
class PasswordResetController extends ThinkUpController {

    public function control() {
        $session = new Session();
        $dao = DAOFactory::getDAO('OwnerDAO');

        $this->setViewTemplate('session.resetpassword.tpl');
        $this->disableCaching();

        if (!isset($_GET['token']) ||
        !preg_match('/^[\da-f]{32}$/', $_GET['token']) ||
        (!$user = $dao->getByPasswordToken($_GET['token']))) {
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
                if ($dao->updatePassword($user->email, $session->pwdcrypt($_POST['password'])) < 1 ) {
                    echo "not updated";
                }
                $login_controller = new LoginController(true);
                $login_controller->addSuccessMessage('You have changed your password.');
                return $login_controller->go();
            } else {
                $this->addErrorMessage("Passwords didn't match.");
            }
        } else if (isset($_POST['Submit'])) {
            $this->addErrorMessage('Please enter a new password.');
        }

        return $this->generateView();
    }

}
