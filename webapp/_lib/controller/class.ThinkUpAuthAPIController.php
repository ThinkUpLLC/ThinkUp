<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.ThinkUpAuthAPIController.php
 *
 * Copyright (c) 2009-2010 Gina Trapani, Guillaume Boudreau
 *
 * LICENSE:
 *
 * This file is part of ThinkUp.
 * 
 * ThinkUp is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * ThinkUp is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with ThinkUp.  If not, see <http://www.gnu.org/licenses/>.
 *
*/
/**
 * ThinkUp Authorized Controller for API calls
 * API calls can be made while a valid session is open, or by specifying a username and an API secret in parameters.
 *
 * Parent controller for all API calls
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani, Guillaume Boudreau
 * @author Guillaume Boudreau <gboudreau@pommepause.com>
 *
 */
abstract class ThinkUpAuthAPIController extends ThinkUpAuthController {
    public function __construct($session_started=false) {
        parent::__construct($session_started);
    }

    /**
     * Checks if the authorization tokens (username & API secret) are valid or not, and allow the request if they are.
     * If there are no authorization tokens, the request could be allowed if a valid session is found.
     */
    public function control() {
        if ($this->isAPICallValid()) {
            $owner_dao = DAOFactory::getDAO('OwnerDAO');
            $owner = $owner_dao->getByEmail($this->getLoggedInUser());
            Session::completeLogin($owner);
            return $this->authControl();
        }
        $as = $this->getAPISecretFromRequest();
        if (empty($as) && $this->isLoggedIn()) {
            return $this->authControl();
        }
        $this->setContentType("text/plain; charset=UTF-8");
        throw new UnauthorizedUserException("Unauthorized API call");
    }

    /**
     * Return the username specified in the request, or from the session.
     * @return string Username
     */
    protected function getLoggedInUser() {
        if (isset($_POST['un'])) {
            return $_POST['un'];
        }
        if (isset($_GET['un'])) {
            return $_GET['un'];
        }
        return parent::getLoggedInUser();
    }

    /**
     * Return the API secret specified in the request.
     * @return string $api_secret
     */
    protected static function getAPISecretFromRequest() {
        return isset($_POST['as']) ? $_POST['as'] : @$_GET['as'];
    }

    /**
     * Checks the username and API secret from the request, and returns true if they match, and are both valid.
     * @return boolean Are the provided username and API secret parameters valid?
     */
    private function isAPICallValid() {
        $logged_in_username = $this->getLoggedInUser();
        $api_secret = self::getAPISecretFromRequest();
        return Session::isAPICallAuthorized($logged_in_username, $api_secret);
    }

    /**
     * Returns URL-encoded parameters needed to make an API call.
     * @param str $username
     * @return str Parameters to use in a URL to make an API call
     */
    public static function getAuthParameters($username) {
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $pwd_from_db = $owner_dao->getPass($username);
        $api_secret = Session::getAPISecretFromPassword($pwd_from_db);
        return 'un='.urlencode($username).'&as='.urlencode($api_secret);
    }

    /**
     * Checks if the request is an API call, where the username and API secret were specified in the request.
     * @return boolean
     */
    protected function isAPICall() {
        $as = $this->getAPISecretFromRequest();
        return !empty($as);
    }
}
