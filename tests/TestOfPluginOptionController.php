<?php
/**
 *
 * ThinkUp/tests/TestOfPluginOptionController.php
 *
 * Copyright (c) 2009-2010 Dwi Widiastuti, Gina Trapani, Mark Wilkie, Guillaume Boudreau
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
 * Test TestOfPluginOptionController class
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Dwi Widiastuti, Gina Trapani, Mark Wilkie, Guillaume Boudreau
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 */
class TestOfPluginOptionController extends ThinkUpUnitTestCase {

    const TEST_TABLE = 'plugin_options';
    const TEST_TABLE_PLUGIN = 'plugins';

    /**
     * Constructor
     */
    public function __construct() {
        $this->UnitTestCase('PluginOptionController class test');
    }

    public function setUp(){
        parent::setUp();
        $config = Config::getInstance();
        $config->setValue('debug', true);
        $this->prefix = $config->getValue('table_prefix');
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

        // no actions defined
        $results = $controller->go();
        // var_dump($results);
        $json_resonse = json_decode($results);
        $this->assertIsA($json_resonse, 'stdClass');
        $this->assertEqual($json_resonse->status, 'failed');
        $this->assertEqual($json_resonse->message, 'No action defined for this request');

        // a bad action defined
        $_GET['action'] = 'a bad action';
        $results = $controller->go();
        $json_resonse = json_decode($results);
        $this->assertIsA($json_resonse, 'stdClass');
        $this->assertEqual($json_resonse->status, 'failed');
        $this->assertEqual($json_resonse->message, 'No action defined for this request');

    }

    public function testNoProfilerOutput() {
        // Enable profiler
        $config = Config::getInstance();
        $config->setValue('enable_profiler', true);
        $_SERVER['HTTP_HOST'] = 'something';

        $controller = $this->getController();
        $_GET['action'] = 'set_options';
        $results = $controller->go();
        $json_resonse = json_decode($results);
        // If the profiler outputs HTML (it shouldn't), the following will fail
        $this->assertIsA($json_resonse, 'stdClass');

        unset($_SERVER['HTTP_HOST']);
    }

    /**
     * Test bad plugin id
     */
    public function testBadPluginId() {
        $controller = $this->getController();

        // no plugin id defined
        $_GET['action'] = 'set_options';
        $results = $controller->go();
        $json_resonse = json_decode($results);
        $this->assertIsA($json_resonse, 'stdClass');
        $this->assertEqual($json_resonse->status, 'failed');
        $this->assertEqual($json_resonse->message, 'Bad plugin id defined for this request');

        // bad plugin id defined
        $_GET['action'] = 'set_options';
        $_GET['plugin_id'] = 'not an integer';
        $results = $controller->go();
        $json_resonse = json_decode($results);
        $this->assertIsA($json_resonse, 'stdClass');
        $this->assertEqual($json_resonse->status, 'failed');
        $this->assertEqual($json_resonse->message, 'Bad plugin id defined for this request');

        // plugin id not found
        $_GET['action'] = 'set_options';
        $_GET['plugin_id'] = -99;
        $results = $controller->go();
        $json_resonse = json_decode($results);
        $this->assertIsA($json_resonse, 'stdClass');
        $this->assertEqual($json_resonse->status, 'failed');
        $this->assertEqual($json_resonse->message, 'Bad plugin id defined for this request');


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
    public function testSavePluginOption() {

        // add one option
        $controller = $this->getController();
        $builder = $this->buildPlugin();
        $_GET['plugin_id'] = $builder->columns[ 'last_insert_id' ];
        $_GET['action'] = 'set_options';
        $_GET['option_test0'] = 'value0';
        $results = $controller->go();
        $json_resonse = json_decode($results);
        $this->assertIsA($json_resonse, 'stdClass');
        // {"status":"success","results":{"updated":1}}
        $this->assertEqual($json_resonse->status, 'success');
        $this->assertEqual($json_resonse->results->updated, 1);
        unset($_GET['option_test0']);

        // add more
        $controller = $this->getController();
        $_GET['option_test1'] = 'value1';
        $_GET['option_test2'] = 'value2';
        $results = $controller->go();
        $json_resonse = json_decode($results);
        $this->assertIsA($json_resonse, 'stdClass');
        $this->assertEqual($json_resonse->status, 'success');
        $this->assertEqual($json_resonse->results->updated, 2);
        // has insert info with id
        $this->assertEqual($json_resonse->results->inserted->test1, 2);
        $this->assertEqual($json_resonse->results->inserted->test2, 3);

        $sql = "select * from " . $this->prefix . 'plugin_options where plugin_id = '
        . $builder->columns[ 'last_insert_id' ];
        $stmt = $this->pdo->query($sql);
        $this->assertEqual($stmt->rowCount(), 3);
        for($i = 0; $i < 3; $i++) {
            $data[$i] = $stmt->fetch();
            $this->assertEqual($data[$i]['plugin_id'], $builder->columns[ 'last_insert_id' ]);
            $this->assertEqual($data[$i]['option_name'], 'test' . $i);
            $this->assertEqual($data[$i]['option_value'], 'value' . $i);
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
        $_GET['action'] = 'set_options';
        $_GET['option_' . $builder_pos[0]->columns['option_name']] = 'value0';
        $_GET['option_' . $builder_pos[1]->columns['option_name']] = 'value1';
        $_GET['option_' . $builder_pos[2]->columns['option_name']] = $builder_pos[2]->columns['option_value'];
        $_GET['id_option_' . $builder_pos[0]->columns['option_name']] = $builder_pos[0]->columns['last_insert_id'];
        $_GET['id_option_' . $builder_pos[1]->columns['option_name']] = $builder_pos[1]->columns['last_insert_id'];
        $_GET['id_option_' . $builder_pos[2]->columns['option_name']] = $builder_pos[2]->columns['last_insert_id'];

        $results = $controller->go();
        $json_resonse = json_decode($results);
        $this->assertIsA($json_resonse, 'stdClass');
        // // {"status":"success","results":{"updated":1}}
        $this->assertEqual($json_resonse->status, 'success');
        $this->assertEqual($json_resonse->results->updated, 2);

        $sql = "select * from " . $this->prefix . 'plugin_options where plugin_id = '
        . $builder->columns[ 'last_insert_id' ];
        $stmt = $this->pdo->query($sql);
        $this->assertEqual($stmt->rowCount(), 3);
        for($i = 0; $i < 3; $i++) {
            $data[$i] = $stmt->fetch();
            $this->assertEqual($data[$i]['plugin_id'], $builder->columns[ 'last_insert_id' ]);
            if($i<2) {
                $this->assertEqual($data[$i]['option_name'],  $builder_pos[$i]->columns['option_name'] );
                $this->assertEqual($data[$i]['option_value'], 'value' . $i);
            } else {
                $this->assertEqual($data[$i]['option_name'],  $builder_pos[$i]->columns['option_name'] );
                $this->assertEqual($data[$i]['option_value'], $builder_pos[$i]->columns['option_value']);
            }
        }
    }

    /**
     * test update/delete plugin options
     */
    public function testUpdateDeletePluginOption() {

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

        $results = $controller->go();
        $json_resonse = json_decode($results);
        $this->assertIsA($json_resonse, 'stdClass');
        // // {"status":"success","results":{"updated":1}}
        $this->assertEqual($json_resonse->status, 'success');
        $this->assertEqual($json_resonse->results->updated, 2);

        $sql = "select * from " . $this->prefix . 'plugin_options where plugin_id = '
        . $builder->columns[ 'last_insert_id' ];
        $stmt = $this->pdo->query($sql);
        $this->assertEqual($stmt->rowCount(), 2);
        for($i = 1; $i < 3; $i++) {
            $data[$i] = $stmt->fetch();
            $this->assertEqual($data[$i]['plugin_id'], $builder->columns[ 'last_insert_id' ]);
            if($i<2) {
                $this->assertEqual($data[$i]['option_name'],  $builder_pos[$i]->columns['option_name'] );
                $this->assertEqual($data[$i]['option_value'], 'value' . $i);
            } else {
                $this->assertEqual($data[$i]['option_name'],  $builder_pos[$i]->columns['option_name'] );
                $this->assertEqual($data[$i]['option_value'], $builder_pos[$i]->columns['option_value']);
            }
        }
    }

    /**
     * get a plugin option controller
     */
    public function getController() {
        $this->simulateLogin('me@example.com', true);
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
        $builder1 = FixtureBuilder::build(self::TEST_TABLE, array('plugin_id' => $plugin_id));
        $builder2 = FixtureBuilder::build(self::TEST_TABLE, array('plugin_id' => $plugin_id));
        $builder3 = FixtureBuilder::build(self::TEST_TABLE, array('plugin_id' => $plugin_id));
        return array( $builder1, $builder2, $builder3);
    }
}