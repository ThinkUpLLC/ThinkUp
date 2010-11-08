<?php
/**
 *
 * ThinkUp/tests/TestOfGridExportController.php
 *
 * Copyright (c) 2009-2010 Gina Trapani, Mark Wilkie
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
 * @author Mark Wilkie <mark[at]bitterpill[dot]org>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani, Mark Wilkie
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

class TestOfGridExportController extends ThinkUpUnitTestCase {

    public function __construct() {
        $this->UnitTestCase('GridExportController class test');
    }

    public function testConstructor() {
        $controller = new GridExportController(true);
        $this->assertTrue(isset($controller));
    }

    public function testNotLoggedIn() {
        $controller = new GridExportController(true);
        $this->assertTrue(isset($controller));

        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $config = Config::getInstance();
        $this->assertEqual('You must <a href="'.$config->getValue('site_root_path').
        'session/login.php">log in</a> to do this.', $v_mgr->getTemplateDataItem('errormsg'));
    }

    public function testMissingParams() {
        $this->simulateLogin('me@example.com');
        $controller = new GridExportController(true);
        $this->assertTrue(isset($controller));
        ob_start();
        $controller->control();
        $results = ob_get_contents();
        ob_end_clean();
        $this->assertPattern("/No search data to export./", $results);
    }

    public function testNonExistentUser() {
        $this->simulateLogin('me@example.com');
        $_GET['u'] = 'idontexist';
        $_GET['n'] = 'idontexist';
        $controller = new GridExportController(true);
        ob_start();
        $controller->control();
        $results = ob_get_contents();
        ob_end_clean();
        $this->assertPattern("/No search data to export./", $results);
    }


    public function testGridExport() {
        $builders = $this->buildData();
        $this->simulateLogin('me@example.com');
        $data = array( array('name' => 'value1'), array('name' => 'value2'));
        $json = json_encode($data);
        //echo $json;
        $_POST['grid_export_data'] = $json;
        $controller = new GridExportController(true);
        ob_start();
        $controller->control();
        $results = ob_get_contents();
        ob_end_clean();
        $data = split("\n", $results);
        $this->assertEqual(3, count($data), 'we should have three lines, one blank');
        $value = str_getcsv($data[0]);
        $this->assertEqual($value[0], 'value1');
        $value = str_getcsv($data[1]);
        $this->assertEqual($value[0], 'value2');
        $this->assertEqual($data[2], '');
    }


    private function buildData() {
        $owner_builder = FixtureBuilder::build('owners', array('id'=>1, 'email'=>'me@example.com'));
        $instance_builder = FixtureBuilder::build('instances', array('id'=>1, 'network_username'=>'someuser1',
        'network'=>'twitter'));
        $instance1_builder = FixtureBuilder::build('instances', array('id'=>2, 'network_username'=>'someuser2',
        'network'=>'twitter'));
        $owner_instance_builder = FixtureBuilder::build('owner_instances', array('instance_id'=>1, 'owner_id'=>1));
        return array($owner_builder, $instance_builder, $instance1_builder, $owner_instance_builder);
    }
}