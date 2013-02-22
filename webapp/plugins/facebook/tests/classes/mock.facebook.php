<?php
/**
 *
 * ThinkUp/webapp/plugins/facebook/tests/classes/mock.facebook.php
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
 * Mock Facebook object
 *
 * Use this Facebook object for testing so tests don't hit the live Facebook API.
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2010 Gina Trapani
 *
 */
class Facebook {

    public function __construct($config) {
    }

    public function setAppId($appId) {
    }

    public function getSession() {
        return 'session';
    }

    public function getUser() {
        $session = $this->getSession();
        return $session ? $session['uid'] : null;
    }

    public function getAccessToken() {
        return 'accesstoken';
    }

    public function getLoginUrl($params=array()) {
        return 'mockloginurl';
    }

    public function getLogoutUrl($params=array()) {
        return 'mocklogouturl';
    }

    public function api($str) {
        if ($str = '/me') {
            return array('name'=>'Gina Trapani', 'id'=>'606837591');
        }
    }

    public function setAccessToken($token) {
    }
}

class BaseFacebook {
    //placeholder for mock class load detection in facebook.php plugin file
}
