<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.ThinkUpAuthAPIController.php
 *
 * Copyright (c) 2009-2013 Guillaume Boudreau
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
 * ThinkUp Authorized Controller for API calls
 * API calls can be made while a valid session is open, or by specifying a username and an API secret in parameters.
 *
 * Parent controller for all API calls
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Guillaume Boudreau
 * @author Guillaume Boudreau <gboudreau@pommepause.com>
 *
 */
abstract class ThinkUpAuthAPIController extends ThinkUpAuthController {

    /**
     *
     * @var Owner - owner for all sub classes, mainly so we only make the db call once
     */
    static $owner = false;

    public function __construct($session_started=false) {
        parent::__construct($session_started);
        self::$owner = false;
    }

    /**
     * Checks if the authorization tokens (username & API secret) are valid or not, and allow the request if they are.
     * If there are no authorization tokens, the request could be allowed if a valid session is found.
     */
    public function control() {
        $owner = $this->isAPICallValid();
        if ($owner) {
            Session::completeLogin($owner);
            return $this->authControl();
        }
        $as = $this->getAPISecretFromRequest();
        if (empty($as) && $this->isLoggedIn()) {
            return $this->authControl();
        }
        // Assume if no API key is set, that it's a regular HTML page request
        if (empty($as)) {
            parent::control();
        } else {
            //$this->setContentType("text/plain; charset=UTF-8");
            $this->setContentType('application/json');
            throw new UnauthorizedUserException("Unauthorized API call");
        }
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
     * Checks the owner api_key and API secret from the request, and returns true if they match, and are both valid.
     * @return boolean Are the provided owner fetched by email and API secret parameters valid?
     */
    private function isAPICallValid() {
        $email = $this->getLoggedInUser();
        $owner = self::getOwner($email);
        $api_secret = self::getAPISecretFromRequest();
        if (isset($owner) && $owner->api_key == $api_secret) {
            return $owner;
        } else {
            return (false);
        }
    }

    /**
     * Returns URL-encoded parameters needed to make an API call.
     * @param str $email
     * @return str Parameters to use in a URL to make an API call
     */
    public static function getAuthParameters($email) {
        $owner = self::getOwner($email);
        if (isset($owner)) {
            return 'un='.urlencode($email).'&as='.urlencode($owner->api_key);
        } else {
            throw new Exception("Invalid email passed to ThinkUpAuthAPIController->getAuthParameters()");
        }
    }

    /**
     * Checks if the request is an API call, where the username and API secret were specified in the request.
     * @return boolean
     */
    protected function isAPICall() {
        $as = $this->getAPISecretFromRequest();
        return !empty($as);
    }

    /**
     * Gets an owner by email address
     * @param str Email address
     * @return Owner
     */
    protected static function getOwner($email) {
        if (self::$owner) {
            return self::$owner;
        } else {
            $owner_dao = DAOFactory::getDAO('OwnerDAO');
            self::$owner = $owner_dao->getByEmail($email);
            return self::$owner;
        }
    }
}
