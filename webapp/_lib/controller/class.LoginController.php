<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.LoginController.php
 *
 * Copyright (c) 2009-2010 Gina Trapani
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
 * Login Controller
 *
 * @TODO Build mechanism for redirecting user to originally-requested logged-in only page.
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class LoginController extends ThinkUpController {

    public function control() {
        $this->setPageTitle('Log in');
        $this->setViewTemplate('session.login.tpl');
        $this->disableCaching();
        //don't show login form if already logged in
        if ($this->isLoggedIn()) {
            $controller = new DashboardController(true);
            return $controller->go();
        } else  {
            $od = DAOFactory::getDAO('OwnerDAO');

            if (isset($_POST['Submit']) && $_POST['Submit']=='Log In'
            && isset($_POST['email']) && isset($_POST['pwd']) ) {
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
                    $owner = $od->getByEmail($user_email);
                    if (!$owner) {
                        $this->addErrorMessage("Incorrect email");
                        return $this->generateView();
                    } elseif (!$session->pwdCheck($_POST['pwd'], $od->getPass($user_email))) {
                        $this->addErrorMessage("Incorrect password");
                        return $this->generateView();
                    } else {
                        // this sets variables in the session
                        $session->completeLogin($owner);
                        $od->updateLastLogin($user_email);
                        $controller = new DashboardController(true);
                        return $controller->control();
                    }
                }
            } else  {
                return $this->generateView();
            }
        }
    }
}
