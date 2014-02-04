<?php
/**
 *
 * ThinkUp/tests/TestOfLogoutController.php
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
 * Test of LoginController
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfLogoutController extends ThinkUpUnitTestCase {

    public function testLogoutNotLoggedIn() {
        $controller = new LogoutController(true);
        $results = $controller->go();
        $this->assertPattern( '/session\/login.php\?redirect\=/', $controller->redirect_destination);
    }

    public function testLogoutWhileLoggedIn() {
        $this->simulateLogin('me@example.com');
        $controller = new LogoutController(true);
        $results = $controller->go();
        $this->debug($results);
        //$this->assertPattern("/You have successfully logged out/", $results);
        $this->assertPattern("/Log In/", $results);
    }

    public function testOfThinkUpLLCRedirect() {
        $this->simulateLogin('me@example.com');
        $config = Config::getInstance();
        $config->setValue('thinkupllc_endpoint', 'http://example.com/user/');

        $controller = new LogoutController(true);
        $result = $controller->go();

        $this->assertEqual($controller->redirect_destination, 'http://example.com/user/logout.php');
    }
}