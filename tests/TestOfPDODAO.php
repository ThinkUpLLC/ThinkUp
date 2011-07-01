<?php
/**
 *
 * ThinkUp/tests/TestOfPDODAO.php
 *
 * Copyright (c) 2009-2011 Gina Trapani, Mark Wilkie, Christoffer Viken, Guillaume Boudreau
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
 * @author Christoffer Viken <christoffer[at]viken[dot]me>
 * @author Guillaume Boudreau <gboudreau[at]pommepause[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2011 Gina Trapani, Mark Wilkie, Christoffer Viken, Guillaume Boudreau
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

class TestOfPDODAO extends ThinkUpUnitTestCase {

    public function setUp() {
        parent::setUp();
    }

    protected function buildData() {
        $builders = array();

        $test_table_sql = 'CREATE TABLE tu_test_table(' .
            'id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,' . 
            'test_name varchar(20),' .
            'test_id int(11),' .
            'unique key test_id_idx (test_id)' .
            ')';
        $this->testdb_helper->runSQL($test_table_sql);

        for($i = 1; $i <= 20; $i++) {
            $builders[] = FixtureBuilder::build('test_table', array('test_name'=>'name'.$i, 'test_id'=>$i));
        }

        // Insert test data into test user table
        $builders[] = FixtureBuilder::build('users', array('user_id'=>12, 'user_name'=>'mary',
        'full_name'=>'Mary Jane', 'avatar'=>'avatar.jpg'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>13, 'user_name'=>'sweetmary',
        'full_name'=>'Sweet Mary Jane', 'avatar'=>'avatar.jpg'));
        return $builders;
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
    }

    public function testInitDAO() {
        $testdao = DAOFactory::getDAO('TestDAO');
        $this->assertNotNull(TestMySQLDAO::$PDO);
        $this->assertNotNull($testdao->config);
        $this->assertNotNull($testdao->logger);
    }

    public function testConvertBetweenBoolAndDB() {
        $testdao = DAOFactory::getDAO('TestDAO');
        $this->assertEqual(0, $testdao->testBoolToDB(null), 'should be 0');
        $this->assertEqual(0, $testdao->testBoolToDB(''), 'should be 0');
        $this->assertEqual(0, $testdao->testBoolToDB(false), 'should be 0');
        $this->assertEqual(0, $testdao->testBoolToDB(0), 'should be 0');
        $this->assertEqual(1, $testdao->testBoolToDB('a'), 'should be 1');
        $this->assertEqual(1, $testdao->testBoolToDB(true), 'should be 1');
        $this->assertEqual(1, $testdao->testBoolToDB(new TestData()), 'should be 1');
        $this->assertEqual(1, $testdao->testBoolToDB(1), 'should be 1');

        $this->assertEqual(false, $testdao->convertDBToBool(0), 'should be true');
        $this->assertEqual(true, $testdao->convertDBToBool(1), 'should be false');
    }

    public function testTwoObjectsOneConnection() {
        DAOFactory::getDAO('TestDAO');
        $this->assertNotNull(TestMysqlDAO::$PDO);
        TestMysqlDAO::$PDO->tu_testing = "testing";
        $testdao2 = DAOFactory::getDAO('TestDAO');
        $this->assertEqual(TestMySQLDAO::$PDO->tu_testing, "testing");
    }

    public function testBasicSelectUsingStatementHandleDirectly() {
        $this->builders = self::buildData();
        $testdao = DAOFactory::getDAO('TestDAO');
        $users = $testdao->getUserCount(0, 'mary');
        $this->assertIsA($users, "array");
        
        $this->assertEqual(count($users), 2);
        $this->assertEqual($users[0]['user_name'], 'mary');
        $this->assertEqual($users[1]['user_name'], 'sweetmary');
    }

    public function testBadSql() {
        $testdao = DAOFactory::getDAO('TestDAO');
        try {
            $testdao->badSql();
        } catch(PDOException $e) {
            $this->assertPattern('/Syntax error/', $e->getMessage(), 'should get a Syntax error message');
        }
    }

    public function testBadBinds() {
        $testdao = DAOFactory::getDAO('TestDAO');
        try {
            $testdao->badBinds();
        } catch(PDOException $e) {
            $this->assertPattern('/Invalid parameter number/', $e->getMessage(),
            'should get an Invalid parameter number error message');
        }
    }

    public function testInsertData() {
        $this->builders = self::buildData();
        $testdao = DAOFactory::getDAO('TestDAO');

        // ad a test_user with a test_id get insert count
        $cnt = $testdao->insertDataGetCount('test_user', '1000');
        $this->assertEqual(1, $cnt, "should have inserted 1 record");

        // add test_naem and id get last insert id
        $cnt = $testdao->insertDataGetId('test_user', '1001');
        $this->assertEqual(22, $cnt, "should get an insert id of 22");

        // add multiple records and get count
        $cnt = $testdao->insertMultiDataGetCount(array( array( 'test_user23', '23'), array( 'test_user24', '24') ));
        $this->assertEqual(2, $cnt, "should get an insert count of 2");

        // test duplicate key err, check for message?
        try {
            $cnt = $testdao->insertDataGetCount('test_user', '1000');
            $this->fail('should throw a PDOException');
        } catch(PDOException $e) {
            $this->assertPattern('/Duplicate entry/', $e->getMessage(), 'should get a dup key message');
        }
    }

    public function testUpdateData() {
        $this->builders = self::buildData();
        $testdao = DAOFactory::getDAO('TestDAO');

        // update a with a bad test_id get 0 insert count
        $cnt = $testdao->update( 'sally', 9999);
        $this->assertEqual(0, $cnt, "updated 0 records");

        // update a test_user with a test_id get insert count
        $cnt = $testdao->update('harry', 1);
        $this->assertEqual(1, $cnt, "updated 1 record");

        // update multiple test_users
        $cnt = $testdao->updateMulti('nick', 1);
        $this->assertEqual(19, $cnt, "updated 19 records");
    }

    public function testSelectSingleRecord() {
        $this->builders = self::buildData();
        $testdao = DAOFactory::getDAO('TestDAO');

        $data_obj = $testdao->selectRecord(999);
        $this->assertNull($data_obj);

        $data_obj = $testdao->selectRecord(2);
        $this->assertEqual($data_obj->id, '2');
        $this->assertEqual($data_obj->test_name, 'name2');
        $this->assertEqual($data_obj->test_id, '2');
    }

    public function testSelectSingleRecordAsArray() {
        $this->builders = self::buildData();
        $testdao = DAOFactory::getDAO('TestDAO');
        $data_obj = $testdao->selectRecordAsArray(999);
        $this->assertNull($data_obj);

        $data_obj = $testdao->selectRecordAsArray(3);
        $this->assertEqual($data_obj['id'], '3');
        $this->assertEqual($data_obj['test_name'], 'name3');
        $this->assertEqual($data_obj['test_id'], '3');
    }

    public function testSelectRecordsAsArrayWithLimit() {
        $this->builders = self::buildData();
        $testdao = DAOFactory::getDAO('TestDAO');
        $data_obj = $testdao->selectRecordsWithLimit(2);
        $this->assertEqual(count($data_obj), 2, 'should have limited to two records');
    }

    public function testSelectRecords() {
        $this->builders = self::buildData();
        $testdao = DAOFactory::getDAO('TestDAO');

        //this should return no records
        $data_array = $testdao->selectRecords(999);
        $this->assertIsA($data_array, "array", "array should be returned");
        $this->assertEqual(count($data_array), 0, "Empty array should be returned");

        // get all data with a test_id greater than 5
        $data_array = $testdao->selectRecords(5);
        $this->assertIsA($data_array, "array", "array should be returned");
        $this->assertEqual(count($data_array), 16, 'we have 16 records');
        for( $i = 0; $i < 16; $i++ ) {
            $data_obj = $data_array[$i];
            $this->assertIsA($data_obj, "TestData");
            $id = $i + 5;
            $this->assertEqual($data_obj->test_id, $id, "we have test_id $i");
        }
    }

    public function testSelectRecodsAsArray() {
        $this->builders = self::buildData();
        $testdao = DAOFactory::getDAO('TestDAO');

        //this should return no records
        $data_array = $testdao->selectRecordsAsArrays(999);
        $this->assertIsA($data_array, "array", "array should be returned");
        $this->assertEqual(count($data_array), 0, "Empty array should be returned");

        // get all data with a test_id greater than 8
        $data_array = $testdao->selectRecordsAsArrays(8);
        $this->assertIsA($data_array, "array", "array should be returned");
        $this->assertEqual(count($data_array), 13, 'we have 13 records');
        for( $i = 0; $i < 13; $i++ ) {
            $data_obj = $data_array[$i];
            $this->assertIsA($data_obj, "array");
            $id = $i + 8;
            $this->assertEqual($data_obj['test_id'], $id, "we have test_id $i");
        }
    }

    public function testDeleteData() {
        $this->builders = self::buildData();
        $testdao = DAOFactory::getDAO('TestDAO');

        // nothing deleted
        $cnt = $testdao->delete(9999);
        $this->assertEqual(0, $cnt, "deleted 0 records");

        // one record deleted
        $cnt = $testdao->delete(19);
        $this->assertEqual(1, $cnt, "deleted 1 record");

        // delete multiple test_users
        $cnt = $testdao->delete(5);
        $this->assertEqual(14, $cnt, "deleted 14 records");
    }

    public function testIsPattern() {
        $this->builders = self::buildData();
        $testdao = DAOFactory::getDAO('TestDAO');

        // nothing deleted
        $cnt = $testdao->isExisting(9999);
        $this->assertFalse($cnt);

        // one record deleted
        $cnt = $testdao->isExisting(19);
        $this->assertTrue($cnt);
    }

    public function testInstantiateDaoWithoutConfigFile() {
        $this->builders = self::buildData();
        $this->removeConfigFile();
        Config::destroyInstance();
        $cfg_values = array("table_prefix"=>"tu_", "db_host"=>"localhost");
        $config = Config::getInstance($cfg_values);
        $test_dao = new TestMySQLDAO($cfg_values);
        $users = $test_dao->getUserCount(0, 'mary');
        $this->assertIsA($users, "array");
        $this->assertEqual(count($users), 2);
        $this->assertEqual($users[0]['user_name'], 'mary');
        $this->assertEqual($users[1]['user_name'], 'sweetmary');
        $this->restoreConfigFile();
    }

    public function testGetConnectString() {
        $this->removeConfigFile();
        Config::destroyInstance();
        $cfg_values = array("db_host"=>"localhost");
        $config = Config::getInstance($cfg_values);
        $this->assertEqual(PDODAO::getConnectString($config), "mysql:dbname=;host=localhost");

        $this->removeConfigFile();
        Config::destroyInstance();
        $cfg_values = array("db_type" => "mysql", "db_host"=>"localhost", "db_name" => "thinkup");
        $config = Config::getInstance($cfg_values);
        $this->assertEqual(PDODAO::getConnectString($config), "mysql:dbname=thinkup;host=localhost");

        $this->removeConfigFile();
        Config::destroyInstance();
        $cfg_values = array("db_host"=>"localhost", "db_name" => "thinkup", "db_port" => "3306");
        $config = Config::getInstance($cfg_values);
        $this->assertEqual(PDODAO::getConnectString($config), "mysql:dbname=thinkup;host=localhost;port=3306");

        $this->removeConfigFile();
        Config::destroyInstance();
        $cfg_values = array("db_host"=>"localhost", "db_name" => "thinkup", "db_socket" => "/var/mysql");
        $config = Config::getInstance($cfg_values);
        $this->assertEqual(PDODAO::getConnectString($config),
        "mysql:dbname=thinkup;host=localhost;unix_socket=/var/mysql");
        $this->restoreConfigFile();
    }
}