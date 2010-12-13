<?php
/**
 *
 * ThinkUp/tests/TestOfAppConfigController.php
 *
 * Copyright (c) 2009-2010 Mark Wilkie
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
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

/**
 * Test TestAppConfigController class
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Mark Wilkie
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 */
class TestOfAppConfigController extends ThinkUpUnitTestCase {

    const TEST_TABLE = 'options';

    /**
     * Constructor
     */
    public function __construct() {
        $this->UnitTestCase('AppConfigController class test');
    }

    public function setUp(){
        parent::setUp();
        $this->config = Config::getInstance();
        $this->config->setValue('debug', true);
        $this->prefix = $this->config->getValue('table_prefix');
        $dao = DAOFactory::getDAO('OptionDAO');
        $this->pdo = PluginOptionMySQLDAO::$PDO;
    }

    public function tearDown(){
        parent::tearDown();
    }

    public function testConstructor() {
        $controller = new AppConfigController(true);
        $this->assertTrue(isset($controller), 'constructor test');
    }

    public function testNotLoggedIn() {
        $controller = new AppConfigController(true);
        $results = $controller->go();
        $v_mgr = $controller->getViewManager();
        $config = Config::getInstance();
        $this->assertEqual('You must <a href="'.$config->getValue('site_root_path').
        'session/login.php">log in</a> to do this.', $v_mgr->getTemplateDataItem('errormsg'));
    }

    public function testNonAdminAccess() {
        $this->simulateLogin('me@example.com');
        $controller = new AppConfigController(true);
        $this->expectException('Exception', 'You must be a ThinkUp admin to do this');
        $results = $controller->control();
    }

    public function testLoadConfigViewData() {
        $this->simulateLogin('me@example.com', true);
        $controller = new AppConfigController(true);
        $results = $controller->control();
        $json_obj = json_decode($results);
        //var_dump($json_obj);
        $this->assertFalse($json_obj->app_config_settings->recaptcha_enable->required);
        $this->assertEqual(count($json_obj->values), 0, 'no app settings stored');


        $bvalue = array('namespace' => OptionDAO::APP_OPTIONS, 'option_name' => 'recaptcha_enable',
        'option_value' => 'true');
        $bvalue2 = array('namespace' => OptionDAO::APP_OPTIONS, 'option_name' => 'recaptcha_private_key',
        'option_value' => 'abc123');
        $bvalue3 = array('namespace' => OptionDAO::APP_OPTIONS, 'option_name' => 'recaptcha_public_key',
        'option_value' => 'abc123public');
        $bdata2 = FixtureBuilder::build('options', $bvalue);
        $bdata3 = FixtureBuilder::build('options', $bvalue2);
        $bdata4 = FixtureBuilder::build('options', $bvalue3);

        $results = $controller->control();
        $json_obj = json_decode($results);
        $this->assertFalse($json_obj->app_config_settings->recaptcha_enable->required);
        $this->assertTrue($json_obj->values->recaptcha_enable->option_value, "uses db config value");
        $this->assertEqual($json_obj->values->recaptcha_private_key->option_value, 'abc123');
        $this->assertEqual($json_obj->values->recaptcha_public_key->option_value, 'abc123public');
    }


    public function testSaveConfigViewData() {
        $this->simulateLogin('me@example.com', true);
        $_POST['save'] = true;

        # no values
        $controller = new AppConfigController(true);
        $results = $controller->control();
        $json_obj = json_decode($results);
        $this->assertEqual($json_obj->status, 'success');
        $this->assertEqual($json_obj->saved, 0);
        $this->assertEqual($json_obj->deleted, 0);

        # bad arg for is_registration_open
        $_POST['is_registration_open'] = 'falsey';
        //$_POST['recaptcha_enable'] = 'false';
        $controller = new AppConfigController(true);
        $results = $controller->control();
        $json_obj = json_decode($results);
        $this->assertEqual($json_obj->status, 'failed');
        $this->assertNotNull($json_obj->required->is_registration_open);


        # bad arg for recaptcha
        $_POST['is_registration_open'] = 'false';
        $_POST['recaptcha_enable'] = 'false';
        $controller = new AppConfigController(true);
        $results = $controller->control();
        $json_obj = json_decode($results);
        $this->assertEqual($json_obj->status, 'failed');
        $this->assertNotNull($json_obj->required->recaptcha_enable);

        # bad deps for recaptcha
        $_POST['recaptcha_enable'] = 'true';
        $controller = new AppConfigController(true);
        $results = $controller->control();
        $json_obj = json_decode($results);
        $this->assertEqual($json_obj->status, 'failed');
        $this->assertNotNull($json_obj->required);
        $this->assertNotNull($json_obj->required->recaptcha_public_key);
        $this->assertNotNull($json_obj->required->recaptcha_private_key);


        # valid save for recaptcha
        $_POST['recaptcha_enable'] = 'true';
        $_POST['recaptcha_public_key'] = '1234';
        $_POST['recaptcha_private_key'] = '1234abc';
        $controller = new AppConfigController(true);
        $results = $controller->control();
        $json_obj = json_decode($results);
        $this->assertEqual($json_obj->status, 'success');
        $this->assertEqual($json_obj->saved, 4);

        $sql = "select * from " . $this->prefix . 'options where namespace = \'' . OptionDAO::APP_OPTIONS
        . '\' order by option_id';
        $stmt = PluginOptionMysqlDAO::$PDO->query($sql);
        $data = array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($data, $row);
        }
        $stmt->closeCursor();
        array_shift($data); //shift off database version record
        $this->assertEqual(count($data), 4);
        $this->assertEqual($data[0]['namespace'], OptionDAO::APP_OPTIONS);
        $this->assertEqual($data[0]['option_name'], 'is_registration_open');
        $this->assertEqual($data[0]['option_value'], 'false');
        $this->assertEqual($data[1]['namespace'], OptionDAO::APP_OPTIONS);
        $this->assertEqual($data[1]['option_name'], 'recaptcha_enable');
        $this->assertEqual($data[1]['option_value'], 'true');
        $this->assertEqual($data[2]['namespace'], OptionDAO::APP_OPTIONS);
        $this->assertEqual($data[2]['option_name'], 'recaptcha_public_key');
        $this->assertEqual($data[2]['option_value'], '1234');
        $this->assertEqual($data[3]['namespace'], OptionDAO::APP_OPTIONS);
        $this->assertEqual($data[3]['option_name'], 'recaptcha_private_key');
        $this->assertEqual($data[3]['option_value'], '1234abc');

        # update records...
        $_POST['is_registration_open'] = 'true';
        $_POST['recaptcha_enable'] = 'true';
        $_POST['recaptcha_public_key'] = '12345';
        $_POST['recaptcha_private_key'] = '12345abc';
        $controller = new AppConfigController(true);
        $results = $controller->control();
        $json_obj = json_decode($results);
        $this->assertEqual($json_obj->status, 'success');
        $this->assertEqual($json_obj->saved, 4);
        $this->assertEqual($json_obj->deleted, 0);
        $sql = "select * from " . $this->prefix . 'options where namespace = \'' . OptionDAO::APP_OPTIONS
        . '\' order by option_id';
        $stmt = PluginOptionMysqlDAO::$PDO->query($sql);
        $data = array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($data, $row);
        }
        $stmt->closeCursor();
        array_shift($data); //shift off database version record
        $this->assertEqual(count($data), 4);
        $this->assertEqual($data[0]['namespace'], OptionDAO::APP_OPTIONS);
        $this->assertEqual($data[0]['option_name'], 'is_registration_open');
        $this->assertEqual($data[0]['option_value'], 'true');
        $this->assertEqual($data[1]['namespace'], OptionDAO::APP_OPTIONS);
        $this->assertEqual($data[1]['option_name'], 'recaptcha_enable');
        $this->assertEqual($data[1]['option_value'], 'true');
        $this->assertEqual($data[2]['namespace'], OptionDAO::APP_OPTIONS);
        $this->assertEqual($data[2]['option_name'], 'recaptcha_public_key');
        $this->assertEqual($data[2]['option_value'], '12345');
        $this->assertEqual($data[3]['namespace'], OptionDAO::APP_OPTIONS);
        $this->assertEqual($data[3]['option_name'], 'recaptcha_private_key');
        $this->assertEqual($data[3]['option_value'], '12345abc');

        # delete records...
        $_POST['is_registration_open'] = 'true';
        $_POST['recaptcha_enable'] = '';
        $_POST['recaptcha_public_key'] = '';
        $_POST['recaptcha_private_key'] = '';
        $controller = new AppConfigController(true);
        $results = $controller->control();
        $json_obj = json_decode($results);
        $this->assertEqual($json_obj->status, 'success');
        $this->assertEqual($json_obj->saved, 1);
        $this->assertEqual($json_obj->deleted, 3);
        $sql = "select * from " . $this->prefix . 'options where namespace = \'' . OptionDAO::APP_OPTIONS
        . '\' order by option_id';
        $stmt = PluginOptionMysqlDAO::$PDO->query($sql);
        $data = array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($data, $row);
        }
        $stmt->closeCursor();
        array_shift($data); //shift off database version record
        $this->assertEqual(count($data), 1);
    }
}