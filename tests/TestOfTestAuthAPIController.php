<?php
/**
 *
 * ThinkUp/tests/TestOfTestAuthAPIController.php
 *
 * Copyright (c) 2009-2013 Gina Trapani, Guillaume Boudreau
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
 * Test of TestAuthAPIController
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, Guillaume Boudreau
 * @author Guillaume Boudreau <gboudreau@pommepause.com>
 *
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfTestAuthAPIController extends ThinkUpUnitTestCase {

    public function setUp() {
        parent::setUp();
        $_SERVER['HTTP_HOST'] = 'http://localhost';
        ThinkUpAuthAPIController::$owner = false;
    }

    public function testConstructor() {
        $controller = new TestAuthAPIController(true);
        $this->assertTrue(isset($controller));
    }

    public function testControl() {
        $builders = $this->buildData();
        $config = Config::getInstance();
        $escaped_site_root_path = str_replace('/', '\/', $config->getValue('site_root_path'));

        $controller = new TestAuthAPIController(true);

        // No username, no API secret provided
        // This isn't an API call, so present HTML error output
        $results = $controller->go();
        $this->assertPattern( '/session\/login.php\?redirect\=/', $controller->redirect_destination);
        // No API secret provided
        // This isn't an API call, so present HTML error output
        $_GET['un'] = 'me@example.com';
        $results = $controller->go();
        $this->assertPattern( '/session\/login.php\?redirect\=/', $controller->redirect_destination);

        // Wrong API secret provided
        $_GET['as'] = 'fail_me';
        $results = $controller->go();
        $this->assertPattern("/UnauthorizedUserException/", $results);
        $this->assertPattern("/Unauthorized API call/", $results);

        $controller = new TestAuthAPIController(true);

        // Wrong username provided
        $_GET['as'] = 'c9089f3c9adaf0186f6ffb1ee8d6501c';
        $_GET['un'] = 'fail_me';
        $results = $controller->go();
        $this->assertPattern("/UnauthorizedUserException/", $results);
        $this->assertPattern("/Unauthorized API call/", $results);

        // Working request
        $_GET['un'] = 'me@example.com';
        $_GET['as'] = 'c9089f3c9adaf0186f6ffb1ee8d6501c';
        $results = $controller->go();
        $this->assertPattern('/{"result":"success"}/', $results);

        $config = Config::getInstance();
        $this->assertEqual(SessionCache::get('user'), 'me@example.com');

        // Now that _SESSION['user'] is set, we shouldn't need to provide un/as to use this controller
        // Also, the result will be returned as HTML, not JSON
        unset($_GET['as']);
        $results = $controller->go();
        $this->assertPattern('/<html><body>Success<\/body><\/html>/', $results);

        // And just to make sure, if we 'logout', we should be denied access now
        Session::logout();
        $results = $controller->go();
        $this->assertPattern( '/ControllerAuthException/', $results);
        $this->assertPattern( '/You must/', $results);
        $this->assertPattern( '/log in/', $results);
    }

    public function testGetLoggedInUser() {
        // Using _POST
        $builders = $this->buildData();
        $controller = new TestAuthAPIController(true);
        $_POST['un'] = 'me@example.com';
        $_POST['as'] = 'c9089f3c9adaf0186f6ffb1ee8d6501c';
        $results = $controller->go();
        $this->assertPattern('/{"result":"success"}/', $results);
    }

    public function testGetAuthParameters() {
        $builders = $this->buildData();
        $this->assertEqual(ThinkUpAuthAPIController::getAuthParameters('me@example.com'),
        'un=me%40example.com&as=c9089f3c9adaf0186f6ffb1ee8d6501c');
    }

    public function testIsAPICall() {
        $builders = $this->buildData();
        $controller = new TestAuthAPIController(true);

        // API call (JSON)
        $_GET['un'] = 'me@example.com';
        $_GET['as'] = 'c9089f3c9adaf0186f6ffb1ee8d6501c';
        $results = $controller->go();
        $this->assertPattern('/{"result":"success"}/', $results);
        $this->assertFalse(strpos($results, '<html'));
        unset($_GET['as']);
        unset($_GET['un']);

        // HTML
        $this->simulateLogin('me@example.com');
        $results = $controller->go();
        $this->assertFalse(strpos($results, '{"result":"success"}'));
        $this->assertPattern('/<html/', $results);
    }

    private function buildData() {
        $owner_builder = FixtureBuilder::build('owners', array(
            'id' => 1,
            'email' => 'me@example.com',
            'api_key' => 'c9089f3c9adaf0186f6ffb1ee8d6501c',
            'is_activated' => 1
        ));

        $instance_builder = FixtureBuilder::build('instances', array(
            'id' => 1,
            'network_username' => 'jack',
            'network' => 'twitter'
            ));

            $owner_instance_builder = FixtureBuilder::build('owner_instances', array(
            'owner_id' => 1,
            'instance_id' => 1
            ));

            return array($owner_builder, $instance_builder, $owner_instance_builder);
    }
}
