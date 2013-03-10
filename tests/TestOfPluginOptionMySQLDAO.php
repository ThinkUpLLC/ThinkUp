<?php
/**
 *
 * ThinkUp/tests/TestOfPluginOptionMySQLDAO.php
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
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Mark Wilkie <mark[at]bitterpill[dot]org>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, Mark Wilkie
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfPluginOptionMySQLDAO extends ThinkUpUnitTestCase {

    const TEST_TABLE = 'options';
    const TEST_TABLE_PLUGIN = 'plugins';

    public function setUp() {
        parent::setUp();
        $this->logger = Logger::getInstance();
        $this->config = Config::getInstance();
    }

    public function tearDown() {
        parent::tearDown();
        $this->logger->close();
        // clear options cache
        PluginOptionMySQLDAO::$cached_options = array();
    }

    public function testDeleteOption() {
        # init our dao
        $dao = new PluginOptionMySQLDAO();

        # build some options
        $builder1 = $this->buildOptions(1, 'test name', 'test option');
        $builder2 = $this->buildOptions(2, 'test name2', 'test option2');
        $builder3 = $this->buildOptions(3, 'test name3', 'test option3');

        $insert_id = $builder1->columns[ 'last_insert_id' ];
        $this->assertTrue( $dao->deleteOption( $insert_id ), "delete an option" );
        $sql = "select * from " . $this->table_prefix . 'options where option_id = ' . $insert_id
        . " and namespace != 'application_options'";
        $stmt = PluginOptionMySQLDAO::$PDO->query($sql);
        $data = $stmt->fetch();
        $this->assertFalse($data, 'should be no plugin option data');

        $sql = "select count(*) as option_count from " . $this->table_prefix . 'options'
        . " where namespace != 'application_options'";
        $stmt = PluginOptionMySQLDAO::$PDO->query($sql);
        $data = $stmt->fetch();
        $this->assertEqual($data['option_count'], 2, 'we should have two options left');

        # try and delete a non existent option
        $this->assertFalse( $dao->deleteOption( -99 ), "delete an non existent option" );;
    }

    public function testOfInsertOption() {
        # init our dao
        $dao = new PluginOptionMySQLDAO();
        $this->assertEqual(
        $dao->insertOption( 101, 'an option name', 'an option value' ), 2, "added/inserted an option, id is 2" );

        $sql = "select * from " . $this->table_prefix . 'options where namespace = \''
        . OptionDAO::PLUGIN_OPTIONS . '-101\'';
        $stmt = PluginOptionMySQLDAO::$PDO->query($sql);
        $data = $stmt->fetch();
        $this->assertEqual($data['option_id'], 2);
        $this->assertEqual($data['namespace'], OptionDAO::PLUGIN_OPTIONS . '-101');
        $this->assertEqual($data['option_name'], 'an option name', 'an option name');
        $this->assertEqual($data['option_value'], 'an option value', 'an option value');

        $sql = "select count(*) as option_count from " . $this->table_prefix . 'options';
        $stmt = PluginOptionMySQLDAO::$PDO->query($sql);
        $data = $stmt->fetch();
        $this->assertEqual($data['option_count'], 2, 'we should have two options');
    }

    public function testOfUpdateOption() {
        # init our dao
        $dao = new PluginOptionMySQLDAO();
        $builder1 = $this->buildOptions(1, 'test name', 'test option');
        $insert_id1 = $builder1->columns[ 'last_insert_id' ];
        $builder2 = $this->buildOptions(2, 'test name2', 'test option2');
        $insert_id2 = $builder2->columns[ 'last_insert_id' ];

        // update with a bad id
        $this->assertFalse( $dao->updateOption( -99, 'a name', 'a value', 'nothing updated' ) );

        // update with valid id
        $this->assertTrue(
        $dao->updateOption( $insert_id1, 'an option name updated', 'an option value updated' ),
            "updated an option" );
        // validate updated data
        $sql = "select * from " . $this->table_prefix . 'options where option_id = '. $insert_id1;
        $stmt = PluginOptionMySQLDAO::$PDO->query($sql);
        $data = $stmt->fetch();
        $this->assertEqual($data['option_id'], $insert_id1);
        $this->assertEqual($data['namespace'], $builder1->columns[ 'namespace' ]);
        $this->assertEqual($data['option_name'], 'an option name updated', 'name updated');
        $this->assertEqual($data['option_value'], 'an option value updated', 'value updated');

        // make sure we only update data for our id
        $sql = "select * from " . $this->table_prefix . 'options where option_id = '. $insert_id2;
        $stmt = PluginOptionMySQLDAO::$PDO->query($sql);
        $data = $stmt->fetch();
        $this->assertEqual($data['option_id'], $insert_id2);
        $this->assertEqual($data['namespace'], $builder2->columns[ 'namespace' ]);
        $this->assertEqual($data['option_name'], 'test name2', 'name not updated');
        $this->assertEqual($data['option_value'], 'test option2', 'value not updated');
    }

    public function testOfGetOptions() {
        $plugin_builder1 = FixtureBuilder::build('plugins', array('id'=>'7', 'folder_name'=>'test_plugin',
        'is_active'=>1));
        $plugin_builder2 = FixtureBuilder::build('plugins', array('id'=>'8', 'folder_name'=>'test_plugin1',
        'is_active'=>1));

        # init our dao
        $dao = new PluginOptionMySQLDAO();
        $builder1 = $this->buildOptions(1, 'test name', 'test option');
        $insert_id1 = $builder1->columns[ 'last_insert_id' ];
        $builder2 = $this->buildOptions(7, 'test name2', 'test option2');
        $insert_id2 = $builder2->columns[ 'last_insert_id' ];
        $builder3 = $this->buildOptions(7, 'test name3', 'test option3');
        $insert_id3 = $builder3->columns[ 'last_insert_id' ];

        // bad plugin id
        $this->assertNull( $dao->getOptions(-99) );

        // gets all options if plugin_id passed
        $options = $dao->getOptions('twitter');
        $this->assertNotNull( $options );
        $this->assertEqual(1, count($options));
        $this->assertIsA($options[0], 'PluginOption');
        $this->assertEqual($options[0]->plugin_id, 1);
        $this->assertEqual($options[0]->id, 2);

        // gets all options if plugin_id passed one more time
        $options = $dao->getOptions('test_plugin');
        $this->assertNotNull( $options );
        $this->assertEqual(count($options), 2);
        $this->assertIsA($options[0], 'PluginOption');
        $this->assertEqual($options[0]->plugin_id, 7);
        $this->assertEqual($options[0]->id, 3);
        $this->assertIsA($options[1], 'PluginOption');
        $this->assertEqual($options[1]->plugin_id, 7);
        $this->assertEqual($options[1]->id, 4);
    }

    public function buildOptions($id, $name, $value) {
        $plugin_data = array(
            'namespace' => OptionDAO::PLUGIN_OPTIONS . '-' . $id,
            'option_name' => $name,
            'option_value' => $value
        );
        $builder = FixtureBuilder::build(self::TEST_TABLE,  $plugin_data);
        return $builder;
    }
}