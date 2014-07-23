<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.SessionAPILoginController.php
 *
 * Copyright (c) 2013 Gina Trapani
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
 * Session API Login Controller
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class SessionAPILoginController extends ThinkUpController {
    /**
     * URL to redirect to if login fails.
     * @var str
     */
    var $failure_redir;
    /**
     * URL to redirect to if login succeeds.
     * @var str
     */
    var $success_redir;
    /**
     * Attempt to log in user via private API key and redirect to specified success or failure URLs based on result
     * with msg parameter set.
     * Expected $_GET parameters:
     * u: email address
     * k: private API key
     * failure_redir: failure redirect URL
     * success_redir: success redirect URL
     */
    public function control() {
        $this->disableCaching();
        if (!isset($_GET['success_redir']) || !isset($_GET['failure_redir'])
        || $_GET['success_redir'] == "" || $_GET['failure_redir'] == "") {
            if (!isset($_GET['success_redir']) || $_GET['success_redir'] == "") {
                $controller = new LoginController(true);
                $controller->addErrorMessage('No success redirect specified');
                return $controller->go();
            }
            if (!isset($_GET['failure_redir']) || $_GET['failure_redir'] == "") {
                $controller = new LoginController(true);
                $controller->addErrorMessage('No failure redirect specified');
                return $controller->go();
            }
        } else {
            $this->success_redir = $_GET['success_redir'];
            $this->failure_redir = $_GET['failure_redir'];

            if (!isset($_GET['u'])) {
                $this->fail('User is not set.');
            }
            if (!isset($_GET['k'])) {
                $this->fail('API key is not set.');
            }

            if ($this->isLoggedIn()) {
                Session::logout();
            }
            $owner_dao = DAOFactory::getDAO('OwnerDAO');

            if ( $_GET['u']=='' || $_GET['k']=='') {
                if ( $_GET['u']=='') {
                    $this->fail("Email must not be empty.");
                } else {
                    $this->fail("API key must not be empty.");
                }
            } else {
                $user_email = $_GET['u'];
                if (get_magic_quotes_gpc()) {
                    $user_email = stripslashes($user_email);
                }
                $owner = $owner_dao->getByEmail($user_email);
                if (!$owner) {
                    $this->fail("Invalid email.");
                } elseif (!$owner->is_activated) {
                    $error_msg = 'Inactive account.';
                    $this->fail($error_msg);
                    // If the credentials supplied by the user are incorrect
                } elseif (!$owner_dao->isOwnerAuthorizedViaPrivateAPIKey($user_email, $_GET['k']) ) {
                    $error_msg = 'Invalid API key.';
                    $this->fail($error_msg);
                } else {
                    // user has logged in sucessfully this sets variables in the session
                    Session::completeLogin($owner);
                    $owner_dao->updateLastLogin($user_email);
                    $owner_dao->resetFailedLogins($user_email);
                    $owner_dao->clearAccountStatus($user_email);
                    $this->succeed();
                }
            }
        }
    }
    /**
     * Redirect to the success URL.
     */
    private function succeed() {
        $this->redirect($this->success_redir);
    }
    /**
     * Redirect to the failure URL with a given message on the query string (msg=).
     * @param str $message
     */
    private function fail($message) {
        $param_chainlink = (strpos($this->failure_redir, '?') !== false)?'&':'?';
        $this->redirect($this->failure_redir.$param_chainlink."msg=".urlencode($message));
    }
}
