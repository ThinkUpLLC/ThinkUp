<?php
/**
 *
 * ThinkUp/tests/TestOfAppConfigController.php
 *
 * Copyright (c) 2009-2013 Mark Wilkie
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
 * Test TestAppConfigController class
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Mark Wilkie
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfAppConfigController extends ThinkUpUnitTestCase {

    const TEST_TABLE = 'options';

    public function setUp(){
        parent::setUp();
        $this->config = Config::getInstance();
        $this->config->setValue('debug', true);
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
        $this->assertPattern( '/session\/login.php\?redirect\=/', $controller->redirect_destination);
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
        $bvalue4 = array('namespace' => OptionDAO::APP_OPTIONS, 'option_name' => 'default_instance',
        'option_value' => '123');
        $bvalue5 = array('namespace' => OptionDAO::APP_OPTIONS, 'option_name' => 'is_opted_out_usage_stats',
        'option_value' => 'true');
        $bdata = FixtureBuilder::build('options', $bvalue);
        $bdata2 = FixtureBuilder::build('options', $bvalue2);
        $bdata3 = FixtureBuilder::build('options', $bvalue3);
        $bdata4 = FixtureBuilder::build('options', $bvalue4);
        $bdata5 = FixtureBuilder::build('options', $bvalue5);

        $results = $controller->control();
        $json_obj = json_decode($results);
        $this->assertFalse($json_obj->app_config_settings->recaptcha_enable->required);
        $this->assertTrue($json_obj->values->recaptcha_enable->option_value, "uses db config value");
        $this->assertEqual($json_obj->values->recaptcha_private_key->option_value, 'abc123');
        $this->assertEqual($json_obj->values->recaptcha_public_key->option_value, 'abc123public');
        $this->assertEqual($json_obj->values->default_instance->option_value, '123');
        $this->assertTrue($json_obj->values->is_opted_out_usage_stats->option_value);
    }

    public function testSaveConfigViewDataNoCSRFTokenPassed() {
        // create a session with a session token
        $this->simulateLogin('me@example.com', true, true);
        $_POST['save'] = true;

        // bad session token set
        //SessionCache::setKey('csrf_token', '1234567');

        $controller = new AppConfigController(true);
        try {
            $results = $controller->control();
            $this->fail("Should throw InvalidCSRFTokenException");
        } catch(InvalidCSRFTokenException $e) {
            $this->assertIsA($e, 'InvalidCSRFTokenException', "threw a InvalidCSRFTokenException");
        }
    }

    public function testSaveConfigViewData() {
        $this->simulateLogin('me@example.com', true, true);
        $_POST['save'] = true;
        $_POST['csrf_token'] = parent::CSRF_TOKEN;

        // no values
        $controller = new AppConfigController(true);
        $results = $controller->control();
        $json_obj = json_decode($results);
        $this->assertEqual($json_obj->status, 'success');
        $this->assertEqual($json_obj->saved, 0);
        $this->assertEqual($json_obj->deleted, 0);

        // bad arg for is_registration_open
        $_POST['is_opted_out_usage_stats'] = 'falsify';
        $controller = new AppConfigController(true);
        $results = $controller->control();
        $json_obj = json_decode($results);
        $this->assertEqual($json_obj->status, 'failed');
        $this->assertNotNull($json_obj->required->is_opted_out_usage_stats);
        $_POST['is_opted_out_usage_stats'] = 'false';

        // bad arg for is_registration_open
        $_POST['is_registration_open'] = 'falsey';
        //$_POST['recaptcha_enable'] = 'false';
        $controller = new AppConfigController(true);
        $results = $controller->control();
        $json_obj = json_decode($results);
        $this->assertEqual($json_obj->status, 'failed');
        $this->assertNotNull($json_obj->required->is_registration_open);

        // bad arg for recaptcha
        $_POST['is_registration_open'] = 'false';
        $_POST['recaptcha_enable'] = 'false';
        $controller = new AppConfigController(true);
        $results = $controller->control();
        $json_obj = json_decode($results);
        $this->assertEqual($json_obj->status, 'failed');
        $this->assertNotNull($json_obj->required->recaptcha_enable);

        // bad deps for recaptcha
        $_POST['recaptcha_enable'] = 'true';
        $controller = new AppConfigController(true);
        $results = $controller->control();
        $json_obj = json_decode($results);
        $this->assertEqual($json_obj->status, 'failed');
        $this->assertNotNull($json_obj->required);
        $this->assertNotNull($json_obj->required->recaptcha_public_key);
        $this->assertNotNull($json_obj->required->recaptcha_private_key);

        // valid save for recaptcha
        $_POST['recaptcha_enable'] = 'true';
        $_POST['recaptcha_public_key'] = '1234';
        // test magic quotes if enabled...
        if (get_magic_quotes_gpc()) {
            $_POST['recaptcha_public_key'] = "1\\'23\\\"4";
        }
        $_POST['recaptcha_private_key'] = '1234abc';
        $controller = new AppConfigController(true);
        $results = $controller->control();
        $json_obj = json_decode($results);
        $this->assertEqual($json_obj->status, 'success');
        $this->assertEqual($json_obj->saved, 5);

        // bad arg, not numeric
        $_POST['default_instance'] = 'notanumber';
        $controller = new AppConfigController(true);
        $results = $controller->control();
        $json_obj = json_decode($results);
        $this->assertEqual($json_obj->status, 'failed');
        $this->assertNotNull($json_obj->required);
        $this->assertNotNull($json_obj->required->default_instance);

        // bad arg, not completely numeric
        $_POST['default_instance'] = '10notanumber';
        $controller = new AppConfigController(true);
        $results = $controller->control();
        $json_obj = json_decode($results);
        $this->assertEqual($json_obj->status, 'failed');
        $this->assertNotNull($json_obj->required);
        $this->assertNotNull($json_obj->required->default_instance);

        // good single digit arg for default_instance
        $_POST['default_instance'] = '1';
        $controller = new AppConfigController(true);
        $results = $controller->control();
        $json_obj = json_decode($results);
        $this->assertEqual($json_obj->status, 'success');
        $this->assertEqual($json_obj->saved, 6);

        // good double digit arg for default_instance
        $_POST['default_instance'] = '57';
        $controller = new AppConfigController(true);
        $results = $controller->control();
        $json_obj = json_decode($results);
        $this->assertEqual($json_obj->status, 'success');
        $this->assertEqual($json_obj->saved, 6);

        // good triple digit arg for default_instance
        $_POST['default_instance'] = '105';
        $controller = new AppConfigController(true);
        $results = $controller->control();
        $json_obj = json_decode($results);
        $this->assertEqual($json_obj->status, 'success');
        $this->assertEqual($json_obj->saved, 6);

        //assert Session info re: selected instance has been cleared
        $session_instance_network = SessionCache::get('selected_instance_network');
        $session_instance_username = SessionCache::get('selected_instance_username');
        $this->assertNull($session_instance_network);
        $this->assertNull($session_instance_username);

        $sql = "select * from " . $this->table_prefix . 'options where namespace = \'' . OptionDAO::APP_OPTIONS
        . '\' order by option_id';
        $stmt = PluginOptionMySQLDAO::$PDO->query($sql);
        $data = array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($data, $row);
        }
        $stmt->closeCursor();
        array_shift($data); //shift off database version record
        $this->assertEqual(count($data), 6);
        $this->assertEqual($data[0]['namespace'], OptionDAO::APP_OPTIONS);
        $this->assertEqual($data[0]['option_name'], 'is_registration_open');
        $this->assertEqual($data[0]['option_value'], 'false');
        $this->assertEqual($data[1]['namespace'], OptionDAO::APP_OPTIONS);
        $this->assertEqual($data[1]['option_name'], 'recaptcha_enable');
        $this->assertEqual($data[1]['option_value'], 'true');
        $this->assertEqual($data[2]['namespace'], OptionDAO::APP_OPTIONS);
        $this->assertEqual($data[2]['option_name'], 'recaptcha_public_key');
        $value = '1234';
        if (get_magic_quotes_gpc()) {
            $value = '1\'23"4';
        }
        $this->assertEqual($data[2]['option_value'], $value);
        $this->assertEqual($data[3]['namespace'], OptionDAO::APP_OPTIONS);
        $this->assertEqual($data[3]['option_name'], 'recaptcha_private_key');
        $this->assertEqual($data[3]['option_value'], '1234abc');
        $this->assertEqual($data[4]['namespace'], OptionDAO::APP_OPTIONS);
        $this->assertEqual($data[4]['option_name'], 'is_opted_out_usage_stats');
        $this->assertEqual($data[4]['option_value'], 'false');
        $this->assertEqual($data[5]['option_name'], 'default_instance');
        $this->assertEqual($data[5]['option_value'], '105');

        // update records...
        $_POST['is_registration_open'] = 'true';
        $_POST['recaptcha_enable'] = 'true';
        $_POST['recaptcha_public_key'] = '12345';
        // test magic quotes if enabled...
        if (get_magic_quotes_gpc()) {
            $_POST['recaptcha_public_key'] = "1\\'23\\\"45";
        }
        $_POST['recaptcha_private_key'] = '12345abc';
        $_POST['default_instance'] = '12345';
        $controller = new AppConfigController(true);
        $results = $controller->control();
        $json_obj = json_decode($results);
        $this->assertEqual($json_obj->status, 'success');
        $this->assertEqual($json_obj->saved, 6);
        $this->assertEqual($json_obj->deleted, 0);
        $sql = "select * from " . $this->table_prefix . 'options where namespace = \'' . OptionDAO::APP_OPTIONS
        . '\' order by option_id';
        $stmt = PluginOptionMySQLDAO::$PDO->query($sql);
        $data = array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($data, $row);
        }
        $stmt->closeCursor();
        array_shift($data); //shift off database version record
        $this->assertEqual(count($data), 6);
        $this->assertEqual($data[0]['namespace'], OptionDAO::APP_OPTIONS);
        $this->assertEqual($data[0]['option_name'], 'is_registration_open');
        $this->assertEqual($data[0]['option_value'], 'true');
        $this->assertEqual($data[1]['namespace'], OptionDAO::APP_OPTIONS);
        $this->assertEqual($data[1]['option_name'], 'recaptcha_enable');
        $this->assertEqual($data[1]['option_value'], 'true');
        $this->assertEqual($data[2]['namespace'], OptionDAO::APP_OPTIONS);
        $this->assertEqual($data[2]['option_name'], 'recaptcha_public_key');
        $value = '12345';
        if (get_magic_quotes_gpc()) {
            $value = '1\'23"45';
        }
        $this->assertEqual($data[2]['option_value'], $value);
        $this->assertEqual($data[3]['namespace'], OptionDAO::APP_OPTIONS);
        $this->assertEqual($data[3]['option_name'], 'recaptcha_private_key');
        $this->assertEqual($data[3]['option_value'], '12345abc');
        $this->assertEqual($data[4]['option_value'], 'false');
        $this->assertEqual($data[5]['option_value'], '12345');

        // delete records...
        $_POST['is_registration_open'] = 'true';
        $_POST['recaptcha_enable'] = '';
        $_POST['recaptcha_public_key'] = '';
        $_POST['recaptcha_private_key'] = '';
        $_POST['default_instance'] = '';
        $_POST['is_opted_out_usage_stats'] = '';
        $controller = new AppConfigController(true);
        $results = $controller->control();
        $json_obj = json_decode($results);
        $this->assertEqual($json_obj->status, 'success');
        $this->assertEqual($json_obj->saved, 1);
        $this->assertEqual($json_obj->deleted, 5);
        $sql = "select * from " . $this->table_prefix . 'options where namespace = \'' . OptionDAO::APP_OPTIONS
        . '\' order by option_id';
        $stmt = PluginOptionMySQLDAO::$PDO->query($sql);
        $data = array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($data, $row);
        }
        $stmt->closeCursor();
        array_shift($data); //shift off database version record
        $this->assertEqual(count($data), 1);
    }
}