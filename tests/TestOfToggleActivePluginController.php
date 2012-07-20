<?php
/**
 *
 * ThinkUp/tests/TestOfToggleActivePluginController.php
 *
 * Copyright (c) 2009-2012 Gina Trapani
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
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2012 Gina Trapani
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfToggleActivePluginController extends ThinkUpUnitTestCase {

    public function setUp() {
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $controller = new ToggleActivePluginController(true);
        $this->assertTrue(isset($controller));
    }

    public function testNotLoggedIn() {
        $controller = new ToggleActivePluginController(true);
        $results = $controller->go();
        $v_mgr = $controller->getViewManager();
        $config = Config::getInstance();
        $this->assertEqual('You must <a href="'.$config->getValue('site_root_path').
        'session/login.php">log in</a> to do this.', $v_mgr->getTemplateDataItem('error_msg'));
    }

    public function testNotAnAdmin() {
        $this->simulateLogin('me@example.com');
        $controller = new ToggleActivePluginController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $config = Config::getInstance();
        $this->assertEqual('You must be a ThinkUp admin to do this', $v_mgr->getTemplateDataItem('error_msg'));
    }

    public function testMissingPluginIdParam() {
        $this->simulateLogin('me@example.com', true);
        $_GET['a'] = 1;
        $controller = new ToggleActivePluginController(true);
        $results = $controller->go();
        $this->assertEqual($results, 'Missing required parameters.', $results);
    }

    public function testMissingActiveParam() {
        $this->simulateLogin('me@example.com', true);
        $_GET['pid'] = 1;
        $controller = new ToggleActivePluginController(true);
        $results = $controller->go();
        $this->assertEqual($results, 'Missing required parameters.', $results);
    }

    public function testBothParamsNonExistentInstance() {
        $this->simulateLogin('me@example.com', true);
        $_GET['pid'] = 1;
        $_GET['a'] = 1;
        $controller = new ToggleActivePluginController(true);
        $results = $controller->go();
        $this->assertEqual($results, 0, $results);
    }

    public function testBothParamsExistentInstance() {
        $builder = FixtureBuilder::build('plugins', array('id'=>51, 'is_active'=>0));
        $this->simulateLogin('me@example.com', true, true);
        $_GET['csrf_token'] = parent::CSRF_TOKEN;
        $_GET['pid'] = '51';
        $_GET['a'] = '1';
        $controller = new ToggleActivePluginController(true);
        $results = $controller->go();
        $this->assertEqual($results, 1);
    }

    public function testBothParamsExistentInstanceNoCSRFToken() {
        $builder = FixtureBuilder::build('plugins', array('id'=>51, 'is_active'=>0));
        $this->simulateLogin('me@example.com', true, true);
        //$_GET['csrf_token'] = parent::CSRF_TOKEN;
        $_GET['pid'] = '51';
        $_GET['a'] = '1';
        $controller = new ToggleActivePluginController(true);
        try {
            $results = $controller->control();
            $this->fail("Should throw InvalidCSRFTokenException");
        } catch(InvalidCSRFTokenException $e) {
            $this->assertIsA($e, 'InvalidCSRFTokenException', "threw a InvalidCSRFTokenException");
        }
    }

    public function testBothParamsExistentInstanceDeactivateCallback() {
        $webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
        $webapp_plugin_registrar->registerPlugin('twitter', "TwitterPlugin");

        //set up 2 active Twitter instances
        $instance_builder_1 = FixtureBuilder::build('instances', array('network_username'=>'julie',
        'network'=>'twitter', 'crawler_last_run'=>'-1d', 'is_activated'=>'1', 'is_public'=>'1'));
        $instance_builder_2 = FixtureBuilder::build('instances', array('network_username'=>'john',
        'network'=>'twitter', 'crawler_last_run'=>'-1d', 'is_activated'=>'1', 'is_public'=>'1'));

        $instance_dao = DAOFactory::getDAO('InstanceDAO');
        $active_instances = $instance_dao->getAllInstances("DESC", true, "twitter");
        $this->assertEqual(sizeof($active_instances), 2);

        $this->simulateLogin('me@example.com', true, true);
        $_GET['csrf_token'] = parent::CSRF_TOKEN;
        $_GET['pid'] = '1';
        $_GET['a'] = '0';

        $controller = new ToggleActivePluginController(true);
        $results = $controller->go();
        $this->assertEqual($results, 1);
        $this->debug($results);

        //make sure the 2 active Twitter instances got deactivated
        $active_instances = $instance_dao->getAllInstances("DESC", true, "twitter");
        $this->assertEqual(sizeof($active_instances), 0);
    }
}