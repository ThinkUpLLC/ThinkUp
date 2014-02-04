<?php
/**
 *
 * ThinkUp/tests/TestOfTestAdminController.php
 *
 * Copyright (c) 2009-2013 Gina Trapani, Mark Wilkie
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
 * Test TestAdminController class
 *
 * TestController isn't a real ThinkUp controller, this is just a template for all Controller tests.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, Mark Wilkie
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfTestAdminController extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
        $config = Config::getInstance();
        $config->setValue('debug', true);
    }

    public function tearDown(){
        parent::tearDown();
    }

    /**
     * Test constructor
     */
    public function testConstructor() {
        $controller = new TestAdminController(true);
        $this->assertTrue(isset($controller), 'constructor test');
    }

    /**
     * Test controller for non-logged in user
     * @TODO Possibly load the resulting markup as a DOM object and test various children in it;
     * this would enforce valid markup
     */
    public function testControlNotLoggedIn() {
        $config = Config::getInstance();
        $controller = new TestAdminController(true);
        $results = $controller->go();
        $this->assertPattern( '/session\/login.php\?redirect\=/', $controller->redirect_destination);
    }

    public function testLoggedInAsAdmin() {
        $this->simulateLogin('me@example.com', true);
        $config = Config::getInstance();
        $config->setValue("app_title_prefix", "");
        $config->setValue('site_root_path', '/my/path/to/thinkup/');

        $controller = new TestAdminController(true);
        $results = $controller->go();

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('test'), 'Testing, testing, 123');
        $this->assertEqual($v_mgr->getTemplateDataItem('app_title'), 'ThinkUp');

        $this->assertEqual($results,
        '<a href="/my/path/to/thinkup/">ThinkUp</a>: Testing, testing, 123 | Logged in as me@example.com',
        "auth controller output when logged in");
    }

    public function testLoggedInNotAsAdmin() {
        $this->simulateLogin('me@example.com');
        $config = Config::getInstance();
        $config->setValue("app_title_prefix", "");
        $config->setValue('site_root_path', '/my/path/to/thinkup/');

        $controller = new TestAdminController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $config = Config::getInstance();
        $this->assertEqual('You must be a ThinkUp admin to do this', $v_mgr->getTemplateDataItem('error_msg'));
    }
}
