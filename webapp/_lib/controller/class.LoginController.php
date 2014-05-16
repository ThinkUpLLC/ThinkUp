<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.LoginController.php
 *
 * Copyright (c) 2009-2013 Gina Trapani
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
 * Login Controller
 *
 * @TODO Build mechanism for redirecting user to originally-requested logged-in only page.
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class LoginController extends ThinkUpController {

    public function control() {
        if (isset($_GET['redirect'])) {
            $this->redirectToThinkUpLLCEndpoint($page=null, $redirect=$_GET['redirect']);
        } else {
            $this->redirectToThinkUpLLCEndpoint();
        }
        $this->setPageTitle('Log in');
        $this->setViewTemplate('session.login.tpl');
        $this->view_mgr->addHelp('login', 'userguide/accounts/index');
        $this->disableCaching();

        // set var for open registration
        $config = Config::getInstance();
        $is_registration_open = $config->getValue('is_registration_open');
        $this->addToView('is_registration_open', $is_registration_open);

        // Set successful login redirect destination
        if (isset($_GET['redirect'])) {
            $this->addToView('redirect', $_GET['redirect']);
        }
        // If form has been submitted
        if (isset($_POST['redirect'])) {
            $this->addToView('redirect', $_POST['redirect']);
        }

        //don't show login form if already logged in
        if ($this->isLoggedIn()) {
            $controller = new InsightStreamController(true);
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
                    if (get_magic_quotes_gpc()) {
                        $user_email = stripslashes($user_email);
                    }
                    $this->addToView('email', $user_email);
                    $owner = $owner_dao->getByEmail($user_email);
                    if (!$owner) {
                        $this->addErrorMessage("Hmm, that email seems wrong?");
                        return $this->generateView();
                    } elseif (!$owner->is_activated) {
                        $error_msg = 'Inactive account. ';
                        if ($owner->failed_logins == 0) {
                            $error_msg .=
                            '<a href="http://thinkup.com/docs/install/install.html#activate-your-account">' .
                            'You must activate your account.</a>';
                        } elseif ($owner->failed_logins == 10) {
                            $error_msg .= $owner->account_status .
                            '. <a href="forgot.php">Reset your password.</a>';
                        }
                        $disable_xss = true;
                        $this->addErrorMessage($error_msg, null, $disable_xss);
                        return $this->generateView();
                        // If the credentials supplied by the user are incorrect
                    } elseif (!$owner_dao->isOwnerAuthorized($user_email, $_POST['pwd']) ) {
                        $error_msg = "Hmm, that password seems wrong?";
                        if ($owner->failed_logins == 9) { // where 9 represents the 10th attempt!
                            $owner_dao->deactivateOwner($user_email);
                            $status = 'Account deactivated due to too many failed logins';
                            $owner_dao->setAccountStatus($user_email, $status);
                            $error_msg = 'Inactive account. ' . $status .
                            '. <a href="forgot.php">Reset your password.</a>';
                        }
                        $owner_dao->incrementFailedLogins($user_email);
                        $disable_xss = true;
                        $this->addErrorMessage($error_msg, null, $disable_xss);
                        return $this->generateView();
                    } else {
                        // user has logged in sucessfully this sets variables in the session
                        $session->completeLogin($owner);
                        $owner_dao->updateLastLogin($user_email);
                        $owner_dao->resetFailedLogins($user_email);
                        $owner_dao->clearAccountStatus($user_email);

                        if (isset($_POST['redirect']) && $_POST['redirect'] != '') {
                            $success_redir = $_POST['redirect'];
                        } else {
                            $success_redir = $config->getValue('site_root_path');
                        }

                        if (!$this->redirect($success_redir)) {
                            $controller = new InsightStreamController(true);
                            return $controller->go();
                        }
                    }
                }
            } else  {
                return $this->generateView();
            }
        }
    }
}
