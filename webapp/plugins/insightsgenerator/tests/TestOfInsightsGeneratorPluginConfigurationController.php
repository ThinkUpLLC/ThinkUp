<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfInsightsGeneratorPluginConfigurationController.php
 *
 * Copyright (c) 2012-2013 (Your Name)
 *
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
 * Test of Insights Generator Plugin Configuration Controller
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2013 Gina Trapani
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_WEBAPP_PATH.
'plugins/insightsgenerator/controller/class.InsightsGeneratorPluginConfigurationController.php';
require_once THINKUP_WEBAPP_PATH.'plugins/insightsgenerator/model/class.InsightsGeneratorPlugin.php';

class TestOfInsightsGeneratorPluginConfigurationController extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
        $_SERVER['SERVER_NAME'] = 'dev.thinkup.com';
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testLoginRequired  () {
        $controller = $this->getController(false);
        $controller->go();
        $this->assertPattern( '/session\/login.php\?redirect\=/', $controller->redirect_destination);
    }

    public function testPluginsListed () {
        $controller = $this->getController(true);
        $controller->go();
        $v_mgr = $controller->getViewManager();
        $plugins = $v_mgr->getTemplateDataItem('installed_plugins');
        $this->assertIsA($plugins, 'array');
        $this->assertTrue(count($plugins) > 0);
        $this->assertNotNull($plugins[0]['name']);
        $this->assertNotNull($plugins[0]['description']);
    }

    public function testOptionsShow () {
        // Non-admin doesn't get mandrill option
        $controller = $this->getController(true, false);
        $output = $controller->go();
        $this->assertNoPattern('/Mandrill Template Name/', $output);

        // Admin gets option
        $controller = $this->getController(true, true);
        $output = $controller->go();
        $this->assertPattern('/Mandrill Template Name/', $output);
    }

    private function getController($logged_in, $is_admin=false) {
        if ($logged_in) {
            $this->simulateLogin('me@example.com', $is_admin);
            $owner_dao = DAOFactory::getDAO('OwnerDAO');
            $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
            $controller = new InsightsGeneratorPluginConfigurationController($owner);
        } else {
            $controller = new InsightsGeneratorPluginConfigurationController(null);
        }
        return $controller;
    }
}
