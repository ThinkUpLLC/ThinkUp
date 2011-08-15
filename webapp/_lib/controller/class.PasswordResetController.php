<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.PasswordResetController.php
 *
 * Copyright (c) 2009-2011 Gina Trapani, Michael Louis Thaler
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
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
 * @copyright 2009-2011 Gina Trapani
 * @author Michael Louis Thaler <michael.louis.thaler[at]gmail[dot]com>
 */
class PasswordResetController extends ThinkUpController {
    
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
                // Generate a new salt and store it in the database
                $salt = $owner_dao->generateSalt($user->email);
                $owner_dao->updateSalt($user->email, $salt);
                // Combine the password and salt
                $newpass = $owner_dao->generateUniqueSaltedPassword($_POST['password'], $salt);
                // Try to update the password
                if ($owner_dao->updatePassword($user->email, $newpass ) < 1 ) {
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
}
