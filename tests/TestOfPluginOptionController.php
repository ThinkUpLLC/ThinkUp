<?php
/**
 *
 * ThinkUp/tests/TestOfPluginOptionController.php
 *
 * Copyright (c) 2009-2013 Gina Trapani, Mark Wilkie, Guillaume Boudreau
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
 * Test TestOfPluginOptionController class
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, Mark Wilkie, Guillaume Boudreau
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 */

require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfPluginOptionController extends ThinkUpUnitTestCase {

    const TEST_TABLE = 'options';
    const TEST_TABLE_PLUGIN = 'plugins';

    public function setUp(){
        parent::setUp();
        $config = Config::getInstance();
        $config->setValue('debug', true);
        $dao = DAOFactory::getDAO('PluginOptionDAO');
        $this->pdo = PluginOptionMySQLDAO::$PDO;
    }

    public function tearDown(){
        parent::tearDown();
    }

    public function testConstructor() {
        $controller = new PluginOptionController(true);
        $this->assertTrue(isset($controller), 'constructor test');
    }

    /**
     * Test bad action
     */
    public function testBadAction() {
        $controller = $this->getController();
        $_POST['csrf_token'] = parent::CSRF_TOKEN;
        // no actions defined
        $results = $controller->go();
        // var_dump($results);
        $json_response = json_decode($results);
        $this->assertIsA($json_response, 'stdClass');
        $this->assertEqual($json_response->status, 'failed');
        $this->assertEqual($json_response->message, 'No action defined for this request');

        // a bad action defined
        $_GET['action'] = 'a bad action';
        $results = $controller->go();
        $json_response = json_decode($results);
        $this->assertIsA($json_response, 'stdClass');
        $this->assertEqual($json_response->status, 'failed');
        $this->assertEqual($json_response->message, 'No action defined for this request');
    }

    public function testNoProfilerOutput() {
        // Enable profiler
        $_POST['csrf_token'] = parent::CSRF_TOKEN;
        $config = Config::getInstance();
        $config->setValue('enable_profiler', true);
        $_SERVER['HTTP_HOST'] = 'something';

        $controller = $this->getController();
        $_GET['action'] = 'set_options';
        $results = $controller->go();
        $json_response = json_decode($results);
        // If the profiler outputs HTML (it shouldn't), the following will fail
        $this->assertIsA($json_response, 'stdClass');

        unset($_SERVER['HTTP_HOST']);
    }

    /**
     * Test bad plugin id
     */
    public function testBadPluginId() {
        $controller = $this->getController();
        $_POST['csrf_token'] = parent::CSRF_TOKEN;
        // no plugin id defined
        $_GET['action'] = 'set_options';
        $results = $controller->go();
        $json_response = json_decode($results);
        $this->assertIsA($json_response, 'stdClass');
        $this->assertEqual($json_response->status, 'failed');
        $this->assertEqual($json_response->message, 'Bad plugin id defined for this request');

        // bad plugin id defined
        $_GET['action'] = 'set_options';
        $_GET['plugin_id'] = 'not an integer';
        $results = $controller->go();
        $json_response = json_decode($results);
        $this->assertIsA($json_response, 'stdClass');
        $this->assertEqual($json_response->status, 'failed');
        $this->assertEqual($json_response->message, 'Bad plugin id defined for this request');

        // plugin id not found
        $_GET['action'] = 'set_options';
        $_GET['plugin_id'] = -99;
        $results = $controller->go();
        $json_response = json_decode($results);
        $this->assertIsA($json_response, 'stdClass');
        $this->assertEqual($json_response->status, 'failed');
        $this->assertEqual($json_response->message, 'Bad plugin id defined for this request');
    }

    /**
     * test validate plugin id
     */
    public function testValidatePluginId() {
        $controller = $this->getController();
        $builder = $this->buildPlugin();
        // bad id
        $this->assertFalse($controller->isValidPluginId(-99));
        $this->assertTrue($controller->isValidPluginId( $builder->columns[ 'last_insert_id' ] ));
    }

    /**
     * test add plugin options
     */
    public function testSavePluginOptionNoCSRFToken() {
        // add one option
        $controller = $this->getController();
        $builder = $this->buildPlugin();
        $_GET['plugin_id'] = $builder->columns[ 'last_insert_id' ];
        $_GET['action'] = 'set_options';
        $_GET['option_test0'] = 'value0';
        try {
            $results = $controller->control();
            $this->fail("should throw InvalidCSRFTokenException");
        } catch(InvalidCSRFTokenException $e) {
            $this->assertIsA($e, 'InvalidCSRFTokenException');
        }
    }

    /**
     * test add plugin options
     */
    public function testSavePluginOption() {
        // add one option
        $controller = $this->getController();
        $builder = $this->buildPlugin();
        $_GET['plugin_id'] = $builder->columns[ 'last_insert_id' ];
        $_GET['action'] = 'set_options';
        $_GET['option_test0'] = 'value0';
        $_GET['csrf_token'] = parent::CSRF_TOKEN;
        $results = $controller->go();
        $json_response = json_decode($results);
        $this->assertIsA($json_response, 'stdClass');
        // {"status":"success","results":{"updated":1}}
        $this->assertEqual($json_response->status, 'success');
        $this->assertEqual($json_response->results->updated, 1);
        unset($_GET['option_test0']);

        // add more
        $controller = $this->getController();
        $_GET['option_test1'] = 'value1';
        $_GET['option_test2'] = 'value2';
        $results = $controller->go();

        $json_response = json_decode($results);
        $this->assertIsA($json_response, 'stdClass');
        $this->assertEqual($json_response->status, 'success');
        $this->assertEqual($json_response->results->updated, 2);
        // has insert info with id
        $this->assertEqual($json_response->results->inserted->test1, 3);
        $this->assertEqual($json_response->results->inserted->test2, 4);

        $sql = "select * from " . $this->table_prefix . 'options where namespace = \'plugin_options-7\'';
        $stmt = $this->pdo->query($sql);
        $this->assertEqual($stmt->rowCount(), 3);
        for($i = 0; $i < 3; $i++) {
            $data[$i] = $stmt->fetch();
            $this->assertEqual($data[$i]['option_name'], 'test' . $i);
            $this->assertEqual($data[$i]['option_value'], 'value' . $i);
        }
    }

    public function testSavePluginOptionWithWhitespace() {
        // add one option
        $controller = $this->getController();
        $builder = $this->buildPlugin();
        $_GET['plugin_id'] = $builder->columns[ 'last_insert_id' ];
        $_GET['action'] = 'set_options';
        $_GET['option_test0'] = 'value0 ';
        $_GET['csrf_token'] = parent::CSRF_TOKEN;
        $results = $controller->go();
        $json_response = json_decode($results);
        $this->assertIsA($json_response, 'stdClass');
        // {"status":"success","results":{"updated":1}}
        $this->assertEqual($json_response->status, 'success');
        $this->assertEqual($json_response->results->updated, 1);
        unset($_GET['option_test0']);

        // add more
        $controller = $this->getController();
        $_GET['option_test1'] = '   value1';
        $_GET['option_test2'] = 'value2 ';
        $results = $controller->go();

        $json_response = json_decode($results);
        $this->assertIsA($json_response, 'stdClass');
        $this->assertEqual($json_response->status, 'success');
        $this->assertEqual($json_response->results->updated, 2);
        // has insert info with id
        $this->assertEqual($json_response->results->inserted->test1, 3);
        $this->assertEqual($json_response->results->inserted->test2, 4);

        $sql = "select * from " . $this->table_prefix . 'options where namespace = \'plugin_options-7\'';
        $stmt = $this->pdo->query($sql);
        $this->assertEqual($stmt->rowCount(), 3);
        for($i = 0; $i < 3; $i++) {
            $data[$i] = $stmt->fetch();
            $this->assertEqual($data[$i]['option_name'], 'test' . $i);
            $this->assertEqual($data[$i]['option_value'], 'value' . $i);
        }
    }

    /**
     * test update plugin option
     */
    public function testUpdatePluginOptionNoCSRFToken() {
        // update two options out of three, third has same data
        $controller = $this->getController();
        $builder = $this->buildPlugin();
        $builder_pos = $this->buildPluginOptions($builder->columns[ 'last_insert_id' ]);
        $_GET['plugin_id'] = $builder->columns[ 'last_insert_id' ];
        $_GET['action'] = 'set_options';
        $_GET['option_' . $builder_pos[0]->columns['option_name']] = 'value0';
        $_GET['option_' . $builder_pos[1]->columns['option_name']] = 'value1';
        $_GET['option_' . $builder_pos[2]->columns['option_name']] = $builder_pos[2]->columns['option_value'];
        $_GET['id_option_' . $builder_pos[0]->columns['option_name']] = $builder_pos[0]->columns['last_insert_id'];
        $_GET['id_option_' . $builder_pos[1]->columns['option_name']] = $builder_pos[1]->columns['last_insert_id'];
        $_GET['id_option_' . $builder_pos[2]->columns['option_name']] = $builder_pos[2]->columns['last_insert_id'];
        try {
            $results = $controller->control();
            $this->fail("should throw InvalidCSRFTokenException");
        } catch(InvalidCSRFTokenException $e) {
            $this->assertIsA($e, 'InvalidCSRFTokenException');
        }
    }
    /**
     * test update plugin option
     */
    public function testUpdatePluginOption() {
        // update two options out of three, third has same data
        $controller = $this->getController();
        $builder = $this->buildPlugin();
        $builder_pos = $this->buildPluginOptions($builder->columns[ 'last_insert_id' ]);
        $_GET['plugin_id'] = $builder->columns[ 'last_insert_id' ];
        $_GET['csrf_token'] = parent::CSRF_TOKEN;
        $_GET['action'] = 'set_options';
        $_GET['option_' . $builder_pos[0]->columns['option_name']] = 'value0';
        $_GET['option_' . $builder_pos[1]->columns['option_name']] = 'value1';
        $_GET['option_' . $builder_pos[2]->columns['option_name']] = $builder_pos[2]->columns['option_value'];
        $_GET['id_option_' . $builder_pos[0]->columns['option_name']] = $builder_pos[0]->columns['last_insert_id'];
        $_GET['id_option_' . $builder_pos[1]->columns['option_name']] = $builder_pos[1]->columns['last_insert_id'];
        $_GET['id_option_' . $builder_pos[2]->columns['option_name']] = $builder_pos[2]->columns['last_insert_id'];

        $results = $controller->go();

        $json_response = json_decode($results);
        $this->assertIsA($json_response, 'stdClass');
        // // {"status":"success","results":{"updated":1}}
        $this->assertEqual($json_response->status, 'success');
        $this->assertEqual($json_response->results->updated, 2);

        $sql = "select * from " . $this->table_prefix . "options where namespace = 'plugin_options-7'";
        $stmt = $this->pdo->query($sql);
        $this->assertEqual($stmt->rowCount(), 3);
        for($i = 0; $i < 3; $i++) {
            $data[$i] = $stmt->fetch();
            if ($i < 2) {
                $this->assertEqual(trim($data[$i]['option_name']), trim($builder_pos[$i]->columns['option_name']) );
                $this->assertEqual(trim($data[$i]['option_value']), trim('value' . $i));
            } else {
                $this->assertEqual(trim($data[$i]['option_name']), trim($builder_pos[$i]->columns['option_name']) );
                $this->assertEqual(trim($data[$i]['option_value']), trim($builder_pos[$i]->columns['option_value']));
            }
        }
    }
    /**
     * test update/delete plugin options no csrf
     */
    public function testUpdateDeletePluginOptionNoCSRFToken() {
        // update two options out of three, third has same data
        $controller = $this->getController();
        $builder = $this->buildPlugin();
        $builder_pos = $this->buildPluginOptions($builder->columns[ 'last_insert_id' ]);
        $_GET['plugin_id'] = $builder->columns[ 'last_insert_id' ];
        $_GET['action'] = 'set_options';
        $_GET['option_' . $builder_pos[0]->columns['option_name']] = '';
        $_GET['option_' . $builder_pos[1]->columns['option_name']] = 'value1';
        $_GET['option_' . $builder_pos[2]->columns['option_name']] = $builder_pos[2]->columns['option_value'];
        $_GET['id_option_' . $builder_pos[0]->columns['option_name']] = $builder_pos[0]->columns['last_insert_id'];
        $_GET['id_option_' . $builder_pos[1]->columns['option_name']] = $builder_pos[1]->columns['last_insert_id'];
        $_GET['id_option_' . $builder_pos[2]->columns['option_name']] = $builder_pos[2]->columns['last_insert_id'];
        try {
            $results = $controller->control();
            $this->fail("should throw InvalidCSRFTokenException");
        } catch(InvalidCSRFTokenException $e) {
            $this->assertIsA($e, 'InvalidCSRFTokenException');
        }
    }

    /**
     * test update/delete plugin options
     */
    public function testUpdateDeletePluginOption() {
        // update one option, delete another, leave third alone because it has same data
        $controller = $this->getController();
        // create a plugin
        $builder = $this->buildPlugin();
        // add three options for newly-made plugin
        $builder_pos = $this->buildPluginOptions($builder->columns[ 'last_insert_id' ]);
        $_GET['plugin_id'] = $builder->columns[ 'last_insert_id' ];
        $_GET['csrf_token'] = parent::CSRF_TOKEN;
        $_GET['action'] = 'set_options';
        $_GET['option_' . $builder_pos[0]->columns['option_name']] = '';
        $_GET['option_' . $builder_pos[1]->columns['option_name']] = 'value1';
        $_GET['option_' . $builder_pos[2]->columns['option_name']] = $builder_pos[2]->columns['option_value'];
        $_GET['id_option_' . $builder_pos[0]->columns['option_name']] = $builder_pos[0]->columns['last_insert_id'];
        $_GET['id_option_' . $builder_pos[1]->columns['option_name']] = $builder_pos[1]->columns['last_insert_id'];
        $_GET['id_option_' . $builder_pos[2]->columns['option_name']] = $builder_pos[2]->columns['last_insert_id'];

        $results = $controller->go();
        $json_response = json_decode($results);
        $this->assertIsA($json_response, 'stdClass');
        // // {"status":"success","results":{"updated":1}}
        $this->assertEqual($json_response->status, 'success');
        $this->assertEqual($json_response->results->updated, 1);
        $this->assertEqual($json_response->results->deleted, 1);

        $sql = "select * from " . $this->table_prefix . "options where namespace = 'plugin_options-7'";
        $stmt = $this->pdo->query($sql);
        $this->assertEqual($stmt->rowCount(), 2);
        for($i = 1; $i < 3; $i++) {
            $data[$i] = $stmt->fetch();
            if ($i<2) {
                $this->assertEqual($data[$i]['option_name'],  $builder_pos[$i]->columns['option_name'] );
                $this->assertEqual($data[$i]['option_value'], 'value' . $i);
            } else {
                $this->assertEqual($data[$i]['option_name'],  $builder_pos[$i]->columns['option_name'] );
                $this->assertEqual(trim($data[$i]['option_value']), trim($builder_pos[$i]->columns['option_value']));
            }
        }
    }

    /**
     * test add plugin options
     */
    /* TODO figure out how to test exception without dropping table as the table drop triggers update mode */
    //    public function xtestPluginOptionException() {
    //        // add one option
    //        $controller = $this->getController();
    //        $builder = $this->buildPlugin();
    //        $this->pdo->query("alter table tu_options");
    //        $_GET['plugin_id'] = $builder->columns[ 'last_insert_id' ];
    //        $_GET['action'] = 'set_options';
    //        $_GET['option_test0'] = 'value0';
    //        $results = $controller->go();
    //        $json_response = json_decode($results);
    //        $this->assertIsA($json_response, 'stdClass');
    //        $this->assertEqual($json_response->error->type, 'PDOException');
    //        $this->assertPattern("/tu_options' doesn't exist/", $json_response->error->message);
    //    }

    /**
     * get a plugin option controller
     */
    public function getController() {
        $this->simulateLogin('me@example.com', true, true);
        $config = Config::getInstance();
        $config->setValue('site_root_path', '/my/path/to/thinktank/');
        return new PluginOptionController(true);
    }

    /**
     * build plugin data
     */
    public function buildPlugin() {
        return FixtureBuilder::build(self::TEST_TABLE_PLUGIN);
    }

    /**
     * build plugin data
     */
    public function buildPluginOptions($plugin_id) {
        $builder1 = FixtureBuilder::build(self::TEST_TABLE, array('namespace' => OptionDAO::PLUGIN_OPTIONS . '-' .
        $plugin_id));
        $builder2 = FixtureBuilder::build(self::TEST_TABLE, array('namespace' => OptionDAO::PLUGIN_OPTIONS . '-' .
        $plugin_id, 'option_value' => 'PaGrms'));
        $builder3 = FixtureBuilder::build(self::TEST_TABLE, array('namespace' => OptionDAO::PLUGIN_OPTIONS . '-' .
        $plugin_id, 'option_value' => 'p97nFy'));
        return array( $builder1, $builder2, $builder3);
    }
}