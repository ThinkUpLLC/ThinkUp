<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.PasswordResetController.php
 *
 * Copyright (c) 2009-2013 Gina Trapani, Michael Louis Thaler
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkup.com).
 *
 * ThinkUp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any
 * later version.
 *
 * ThinkUp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with ThinkUp.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 *
 * Password Reset Controller
 * Given the correct hash, changes a ThinkUp user's password and activates a deactivated account.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Michael Louis Thaler <michael.louis.thaler[at]gmail[dot]com>
 */
class PasswordResetController extends ThinkUpController {

    public function control() {
        $session = new Session();
        $owner_dao = DAOFactory::getDAO('OwnerDAO');

        $this->view_mgr->addHelp('reset', 'userguide/accounts/index');
        $this->setViewTemplate('session.resetpassword.tpl');
        $this->addHeaderJavaScript('assets/js/jqBootstrapValidation.js');
        $this->addHeaderJavaScript('assets/js/validate-fields.js');
        $this->disableCaching();

        $config = Config::getInstance();
        $this->addToView('is_registration_open', $config->getValue('is_registration_open'));

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
                // Try to update the password
                if ($owner_dao->updatePassword($user->email, $_POST['password'] ) < 1 ) {
                    $login_controller->addErrorMessage('Problem changing your password!');
                } else {
                    $owner_dao->activateOwner($user->email);
                    $owner_dao->clearAccountStatus($user->email);
                    $owner_dao->resetFailedLogins($user->email);
                    $owner_dao->updatePasswordToken($user->email, '');
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
}
