<?php
/**
 *
 * ThinkUp/tests/TestOfTogglePublicInstanceController.php
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
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfTogglePublicInstanceController extends ThinkUpUnitTestCase {

    public function setUp() {
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $controller = new TogglePublicInstanceController(true);
        $this->assertTrue(isset($controller));
    }

    public function testNotLoggedIn() {
        $controller = new TogglePublicInstanceController(true);
        $results = $controller->go();
        $this->assertPattern( '/session\/login.php\?redirect\=/', $controller->redirect_destination);
    }

    public function testMissingInstanceParam() {
        $this->simulateLogin('me@example.com');
        $_GET['p'] = 1;
        $controller = new TogglePublicInstanceController(true);
        $results = $controller->go();
        $this->assertEqual($results, 'Missing required parameters.', $results);
    }

    public function testMissingPublicParam() {
        $this->simulateLogin('me@example.com');
        $_GET['u'] = 'ginatrapani';
        $controller = new TogglePublicInstanceController(true);
        $results = $controller->go();
        $this->assertEqual($results, 'Missing required parameters.', $results);
    }

    public function testBothParamsNonExistentInstance() {
        $this->simulateLogin('me@example.com');
        $_GET['u'] = 1;
        $_GET['p'] = 1;
        $controller = new TogglePublicInstanceController(true);
        $results = $controller->go();
        $this->assertEqual($results, 0, $results);
    }

    public function testBothParamsExistentInstanceNoCSRFToken() {
        $builder = FixtureBuilder::build('instances', array('id'=>12, 'is_public'=>1));
        $this->simulateLogin('me@example.com', true, true);
        $_GET['u'] = '12';
        $_GET['p'] = '0';
        $controller = new TogglePublicInstanceController(true);
        try {
            $results = $controller->control();
            $this->fail("should throw InvalidCSRFTokenException");
        } catch(InvalidCSRFTokenException $e) {
            $this->assertIsA($e, 'InvalidCSRFTokenException');
        }
    }

    public function testBothParamsExistentInstance() {
        $builder = FixtureBuilder::build('instances', array('id'=>12, 'is_public'=>1));
        $this->simulateLogin('me@example.com', true, true);
        $_GET['u'] = '12';
        $_GET['p'] = '0';
        $_GET['csrf_token'] = parent::CSRF_TOKEN;
        $controller = new TogglePublicInstanceController(true);
        $results = $controller->go();
        $this->assertEqual($results, 1);
    }
}