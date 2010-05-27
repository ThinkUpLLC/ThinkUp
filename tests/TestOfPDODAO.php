<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.DAOFactory.php';
require_once $SOURCE_ROOT_PATH.'webapp/config.inc.php';

class TestOfPDODAO extends ThinkTankUnitTestCase {

    function TestOfPDODAO() {
        $this->UnitTestCase('TestDAO class test');
    }

    function setUp() {
        parent::setUp();

        $test_table_sql = 'CREATE TABLE tt_test_table(' .
            'id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,' . 
            'test_name varchar(20),' .
            'test_id int(11),' .
            'unique key test_id_idx (test_id)' .
            ')';
        $this->db->exec($test_table_sql);

        $q = sprintf("INSERT INTO tt_test_table (test_name, test_id) VALUES ('name%s', %d)", 1, 1);
        for($i = 2; $i <= 20; $i++) {
            $q .= sprintf(",('name%s', %d)", $i, $i);
        }
        $this->db->exec($q);

        // Insert test data into test user table
        $q = "INSERT INTO tt_users (user_id, user_name, full_name, avatar) VALUES (12, 'mary', 'Mary Jane', 'avatar.jpg');";
        $this->db->exec($q);
        $q = "INSERT INTO tt_users (user_id, user_name, full_name, avatar) VALUES (13, 'sweetmary', 'Sweet Mary Jane', 'avatar.jpg');";
        $this->db->exec($q);
    }

    function tearDown() {
        parent::tearDown();
    }

    function testInitDAO() {
        $testdao = DAOFactory::getDAO('TestDAO');
        $this->assertNotNull(TestMysqlDAO::$PDO);
        $this->assertNotNull($testdao->config);
        $this->assertNotNull($testdao->logger);
    }

    function testConvertBetweenBoolAndDB() {
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

    function testTwoObjectsOneConnection() {
        DAOFactory::getDAO('TestDAO');
        $this->assertNotNull(TestMysqlDAO::$PDO);
        TestMysqlDAO::$PDO->tt_testing = "testing";
        $testdao2 = DAOFactory::getDAO('TestDAO');
        $this->assertEqual(TestMysqlDAO::$PDO->tt_testing, "testing");
    }

    function testBasicSelectUsingStatementHandleDirectly() {
        $testdao = DAOFactory::getDAO('TestDAO');
        $users = $testdao->getUserCount(0, 'mary');
        $this->assertIsA($users, "array");
        $this->assertEqual(count($users), 2);
        $this->assertEqual($users[0]['user_name'], 'mary');
        $this->assertEqual($users[1]['user_name'], 'sweetmary');
    }

    function testBadSql() {
        $testdao = DAOFactory::getDAO('TestDAO');
        try {
            $testdao->badSql();
        } catch(PDOException $e) {
            $this->assertPattern('/Syntax error/', $e->getMessage(), 'should get a Syntax error message');
        }
    }

    function testBadBinds() {
        $testdao = DAOFactory::getDAO('TestDAO');
        try {
            $testdao->badBinds();
        } catch(PDOException $e) {
            $this->assertPattern('/Invalid parameter number/', $e->getMessage(), 'should get an Invalid parameter number error message');
        }
    }

    function testInsertData() {
        $testdao = DAOFactory::getDAO('TestDAO');

        // ad a test_user with a test_id get insert count
        $cnt = $testdao->insertDataGetCount('test_user', '1000');
        $this->assertEqual(1, $cnt, "should have inserted 1 record");

        // add test_naem and id get last insert id
        $cnt = $testdao->insertDataGetId('test_user', '1001');
        $this->assertEqual(22, $cnt, "should get an insert id of 22");

        // add multiple records and get count
        $cnt = $testdao->insertMultiDataGetCount(array( array( 'test_user23', '23'), array( 'test_user24', '24') )) ;
        $this->assertEqual(2, $cnt, "should get an insert count of 2");

        // test duplicate key err, check for message?
        try {
            $cnt = $testdao->insertDataGetCount('test_user', '1000');
            $this->fail('should throw a PDOException');
        } catch(PDOException $e) {
            $this->assertPattern('/Duplicate entry/', $e->getMessage(), 'should get a dup key message');
        }
    }

    function testUpdateData() {
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

    function testSelectSingleRecord() {
        $testdao = DAOFactory::getDAO('TestDAO');

        $data_obj = $testdao->selectRecord(999);
        $this->assertNull($data_obj);

        $data_obj = $testdao->selectRecord(2);
        $this->assertEqual($data_obj->id, '2');
        $this->assertEqual($data_obj->test_name, 'name2');
        $this->assertEqual($data_obj->test_id, '2');
    }

    function testSelectSingleRecordAsArray() {
        $testdao = DAOFactory::getDAO('TestDAO');

        $data_obj = $testdao->selectRecordAsArray(999);
        $this->assertNull($data_obj);

        $data_obj = $testdao->selectRecordAsArray(3);
        $this->assertEqual($data_obj['id'], '3');
        $this->assertEqual($data_obj['test_name'], 'name3');
        $this->assertEqual($data_obj['test_id'], '3');
    }

    function testSelectRecords() {
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

    function testSelectRecodsAsArray() {
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

    function testDeleteData() {
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

    function testIsPattern() {
        $testdao = DAOFactory::getDAO('TestDAO');

        // nothing deleted
        $cnt = $testdao->isExisting(9999);
        $this->assertFalse($cnt);

        // one record deleted
        $cnt = $testdao->isExisting(19);
        $this->assertTrue($cnt);
    }

}