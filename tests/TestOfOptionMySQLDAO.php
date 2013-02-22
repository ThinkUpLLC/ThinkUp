<?php
/**
 *
 * ThinkUp/tests/TestOfOptionMySQLDAO.php
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
 *
 * @author Mark Wilkie <mark[at]bitterpill[dot]org>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, Mark Wilkie
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfOptionMySQLDAO extends ThinkUpUnitTestCase {

    const TEST_TABLE = 'options';

    public function setUp() {
        parent::setUp();
        $this->logger = Logger::getInstance();
        $this->config = Config::getInstance();
        $optiondao = new OptionMySQLDAO();
        $this->pdo = $optiondao->connect();
    }

    public function tearDown() {
        parent::tearDown();
        $this->logger->close();
    }

    public function testCreateOption() {
        // add one option
        $optiondao = new OptionMySQLDAO();
        $optiondao->insertOption('test_namespace', 'test_name', 'test_value');
        $sql = "select * from " . $this->table_prefix . 'options where namespace = ' .
            '\'test_namespace\' order by option_id';
        $stmt = PluginOptionMySQLDAO::$PDO->query($sql);
        $data = array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($data, $row);
        }
        $stmt->closeCursor();
        $this->assertEqual(count($data), 1);
        $data = $data[0];
        $this->assertEqual($data['option_id'], 2);
        $this->assertEqual($data['namespace'], 'test_namespace');
        $this->assertEqual($data['option_name'], 'test_name');
        $this->assertEqual($data['option_value'], 'test_value');
        $this->assertTrue( (strtotime($data['created']) + 10) > time());
        $this->assertTrue( (strtotime($data['last_updated']) + 10) > time());

        // add another with different namespace
        $optiondao->insertOption('test_namespace2', 'test_name', 'test_value');

        $sql = "select * from " . $this->table_prefix . 'options where namespace = ' .
            '\'test_namespace2\' order by option_id';
        $stmt = PluginOptionMySQLDAO::$PDO->query($sql);
        $data = array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($data, $row);
        }
        $stmt->closeCursor();
        $this->assertEqual(count($data), 1);
        $data = $data[0];
        $this->assertEqual($data['option_id'], 3);
        $this->assertEqual($data['namespace'], 'test_namespace2');
        $this->assertEqual($data['option_name'], 'test_name');
        $this->assertEqual($data['option_value'], 'test_value');
        # we'll add a few seconds to the date to account for any lag...
        # our dates should be nowish
        $this->assertTrue( (strtotime($data['created']) + 10) > time());
        $this->assertTrue( (strtotime($data['last_updated']) + 10) > time());
    }

    public function testCreateDuplicateOption() {
        // add one option
        $optiondao = new OptionMySQLDAO();
        $optiondao->insertOption('test_namespace', 'test_name', 'test_value');
        try {
            $optiondao->insertOption('test_namespace', 'test_name', 'test_value');
            $this->fail('Should throw DuplicateOptionException');
        } catch(DuplicateOptionException $e) {
            $this->assertIsA($e, 'DuplicateOptionException');
            $this->assertPattern('/namespace test_namespace and name test_name exists/', $e->getMessage());
        }
    }

    public function testDeleteOptionById() {
        // add one option
        $optiondao = new OptionMySQLDAO();

        // no options delete
        $this->assertEqual(0, $optiondao->deleteOption(2));

        // delete only one row
        $builder1 = FixtureBuilder::build(self::TEST_TABLE);
        $builder2 = FixtureBuilder::build(self::TEST_TABLE);
        $this->assertEqual(1, $optiondao->deleteOption($builder1->columns['last_insert_id']));
    }

    public function testDeleteOptionByName() {
        // add one option
        $optiondao = new OptionMySQLDAO();

        // no options delete
        $this->assertEqual(0, $optiondao->deleteOptionByName('nonamespace', 'noname'));

        // delete only one
        $builder1 = FixtureBuilder::build(self::TEST_TABLE, array('namespace'=>'test', 'option_name'=>'testname') );
        $builder2 = FixtureBuilder::build(self::TEST_TABLE, array('namespace'=>'test2', 'option_name'=>'testname2') );
        $this->assertEqual(1, $optiondao->deleteOption($builder1->columns['last_insert_id']));
    }

    public function testUpdateOptionById() {
        // add one option
        $optiondao = new OptionMySQLDAO();

        // no option to update
        $this->assertEqual(0, $optiondao->updateOption(2, 'value'));

        $builder1 = FixtureBuilder::build(self::TEST_TABLE,
        array('namespace' => 'test', 'option_name' => 'testname', 'created' => '-2d', 'last_updated' => '-2d') );
        $builder2 = FixtureBuilder::build(self::TEST_TABLE,
        array('namespace' => 'test2', 'option_name' => 'testname2', 'created' => '-2d', 'last_updated' => '-2d') );
        $this->assertEqual(1, $optiondao->updateOption($builder1->columns['last_insert_id'], 'test_value123'));

        $sql = "select * from " . $this->table_prefix . 'options where option_id = ' . $builder1->columns['last_insert_id'];
        $stmt = PluginOptionMySQLDAO::$PDO->query($sql);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEqual($data['option_name'], 'testname');
        $this->assertEqual($data['namespace'], 'test');
        $this->assertEqual($data['option_value'], 'test_value123');
        # we'll add or subtract a few seconds from/to the date to account for any lag...
        # date created is still 2 days old
        $this->assertTrue(strtotime($data['created']) < (time() - (24 * 60 * 60 * 2) + 20000 ), '
        '.strtotime($data['created']) . ' 
        ' . (time() - (24 * 60 * 60 * 2) + 20000 ));
        # last updated is now
        $this->assertTrue(strtotime($data['last_updated']) > (time() - 10) );
    }

    public function testUpdateOptionByIdWithnameUpdate() {
        // add one option
        $optiondao = new OptionMySQLDAO();

        $builder1 = FixtureBuilder::build(self::TEST_TABLE,
        array('namespace' => 'test', 'option_name' => 'testname', 'created' => '-2d', 'last_updated' => '-2d') );
        $builder2 = FixtureBuilder::build(self::TEST_TABLE,
        array('namespace' => 'test2', 'option_name' => 'testname2', 'created' => '-2d', 'last_updated' => '-2d') );

        $this->assertEqual(1, $optiondao->updateOption($builder1->columns['last_insert_id'], 'test_value123', 'newname'));

        $sql = "select * from " . $this->table_prefix . 'options where option_id = ' . $builder1->columns['last_insert_id'];
        $stmt = PluginOptionMySQLDAO::$PDO->query($sql);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEqual($data['option_name'], 'newname');
        $this->assertEqual($data['namespace'], 'test');
        $this->assertEqual($data['option_value'], 'test_value123');
        # we'll add or subtract a few seconds from/to the date to account for any lag...
        # date created is still 2 days old
        $this->assertTrue(strtotime($data['created']) < (time() - (24 * 60 * 60 * 2) + 20000 ), '
        '.strtotime($data['created']) . ' 
        ' . (time() - (24 * 60 * 60 * 2) + 20000 ));
        # last updated is now
        $this->assertTrue(strtotime($data['last_updated']) > (time() - 10) );
    }

    public function testUpdateOptionByName() {
        // add one option
        $optiondao = new OptionMySQLDAO();

        // no options to update
        $this->assertEqual(0, $optiondao->updateOptionbyName('nonamespace', 'noname', 'value'));

        $builder1 = FixtureBuilder::build(self::TEST_TABLE, array('namespace'=>'test', 'option_name'=>'testname') );
        $builder2 = FixtureBuilder::build(self::TEST_TABLE, array('namespace'=>'test2', 'option_name'=>'testname2') );
        $this->assertEqual(1, $optiondao->updateOptionByName('test', 'testname', 'test_value123'));

        $sql = "select * from " . $this->table_prefix . 'options where option_id = ' . $builder1->columns['last_insert_id'];
        $stmt = PluginOptionMySQLDAO::$PDO->query($sql);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEqual($data['option_name'], 'testname');
        $this->assertEqual($data['namespace'], 'test');
        $this->assertEqual($data['option_value'], 'test_value123');
        # we'll add or subtract a few seconds from/to the date to account for any lag...
        # date created is still 2 days old
        $this->assertTrue(strtotime($data['created']) < (time() - (24 * 60 * 60 * 2) + 10 ));
        # last updated is now
        $this->assertTrue(strtotime($data['last_updated']) > (time() - 10) );
    }

    public function testGetOptionByName() {
        // add one option
        $optiondao = new OptionMySQLDAO();

        // no options
        $this->assertNull($optiondao->getOptionByName('nonamespace', 'noname'));

        // get option
        $data = array('namespace' => 'test', 'option_name' => 'testname', 'option_value' => 'test_value');
        $builder1 = FixtureBuilder::build(self::TEST_TABLE, $data);
        $option = $optiondao->getOptionByName('test', 'testname');
        $this->assertEqual($option->option_id, $builder1->columns['last_insert_id']);
        $this->assertEqual($option->namespace, 'test');
        $this->assertEqual($option->option_name, 'testname');
        $this->assertEqual($option->option_value, 'test_value');
    }

    public function testGetOptionById() {
        // add one option
        $optiondao = new OptionMySQLDAO();

        // no options
        $this->assertNull( $optiondao->getOption(2) );

        // get option
        $data = array('namespace' => 'test', 'option_name' => 'testname', 'option_value' => 'test_value');
        $builder1 = FixtureBuilder::build(self::TEST_TABLE, $data);
        $option = $optiondao->getOption($builder1->columns['last_insert_id']);
        $this->assertEqual($option->option_id, $builder1->columns['last_insert_id']);
        $this->assertEqual($option->namespace, 'test');
        $this->assertEqual($option->option_name, 'testname');
        $this->assertEqual($option->option_value, 'test_value');
    }

    public function testGetOptionsHash() {
        // add one option
        $optiondao = new OptionMySQLDAO();

        // no options
        $this->assertNull($optiondao->getOptions('nonamespace'));

        // get option
        $data1 = array('namespace' => 'test', 'option_name' => 'testname', 'option_value' => 'test_value');
        $builder1 = FixtureBuilder::build(self::TEST_TABLE, $data1);
        $data2 = array('namespace' => 'test', 'option_name' => 'testname2', 'option_value' => 'test_value2');
        $builder2 = FixtureBuilder::build(self::TEST_TABLE, $data2);
        $data3 = array('namespace' => 'testb', 'option_name' => 'testnameb', 'option_value' => 'test_valueb');
        $builder3 = FixtureBuilder::build(self::TEST_TABLE, $data3);

        $options = $optiondao->getOptions('test');
        $this->assertEqual(count($options), 2);
        $this->assertIsa($options['testname'], 'Option');
        $this->assertIsa($options['testname2'], 'Option');

        $data1 = array('option_id' => $builder1->columns['last_insert_id'], 'namespace' => 'test',
        'option_name' => 'testname', 'option_value' => 'test_value');
        $option1 = new Option($data1);
        $this->assertIdentical($options['testname'], $option1);

        $data2 = array('option_id' => $builder2->columns['last_insert_id'], 'namespace' => 'test',
        'option_name' => 'testname2', 'option_value' => 'test_value2');
        $option2 = new Option($data2);
        $this->assertIdentical($options['testname2'], $option2);
        ;
    }

    public function testGetOptionValue() {
        $optiondao = new OptionMySQLDAO();

        // no option value
        $this->assertNull($optiondao->getOptionValue('nonamespace', 'noname'));

        // get option value
        $data1 = array('namespace' => 'test', 'option_name' => 'testname', 'option_value' => 'test_value');
        $builder1 = FixtureBuilder::build(self::TEST_TABLE, $data1);
        $this->assertEqual($optiondao->getOptionValue('test', 'testname'), 'test_value');
    }

    public function testSession() {
        $optiondao = new OptionMySQLDAO();

        $config = Config::getInstance();
        $app_path = $config->getValue('source_root_path');

        // set session data
        $optiondao->setSessionData('bla', array('name' => 'value'));
        $key = 'options_data:bla';
        $this->assertIdentical(array('name' => 'value'), SessionCache::get($key) );

        // clear session data
        $optiondao->clearSessionData('bla');
        $this->assertFalse(SessionCache::isKeySet($key));

        // get session data
        $this->assertFalse($optiondao->getSessionData('bla')); // no data

        // with data
        SessionCache::put($key, array('name' => 'value') );
        $this->assertIdentical(array('name' => 'value'), $optiondao->getSessionData('bla'));

        // test updates
        $data1 = array('namespace' => 'test', 'option_name' => 'testname', 'option_value' => 'test_value');
        $builder1 = FixtureBuilder::build(self::TEST_TABLE, $data1);
        $options = $optiondao->getOptions('test');
        $this->assertNotNull($options);

        # update by name
        $optiondao->updateOptionByName('test', 'testname', 'test_value123');
        $options = $optiondao->getOptions('test');
        $this->assertEqual($options['testname']->option_value, 'test_value123');

        # update by id
        $optiondao->updateOption($options['testname']->option_id, 'test_value1234');
        $options = $optiondao->getOptions('test');
        $this->assertEqual($options['testname']->option_value, 'test_value1234');

        # delete by name
        $optiondao->deleteOptionByName('test', 'testname');
        $options = $optiondao->getOptions('test');
        $this->assertNull($options);

        # delete by id
        $builder1 = null;
        $builder1 = FixtureBuilder::build(self::TEST_TABLE, $data1);
        $optiondao->deleteOption($builder1->columns['last_insert_id']);
        $options = $optiondao->getOptions('test');
        $this->assertNull($options);
    }

    public function testIsOptionsTable() {
        $optiondao = new OptionMySQLDAO();
        $this->assertTrue($optiondao->isOptionsTable(), 'we have an option table');
        PluginOptionMySQLDAO::$PDO->query("drop table " . $this->table_prefix . 'options');
        $this->assertFalse($optiondao->isOptionsTable(), 'we do not have an option table');
    }
}