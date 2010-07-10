<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Config.php';

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Profiler.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.DAOFactory.php';

/**
 * Test of DAOFactory
 *
 * @author Mark Wilkie
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfDAOFactory extends ThinkTankUnitTestCase {

    function __construct() {
        $this->UnitTestCase('DAOFactory test');
    }

    function setUp() {
        parent::setUp();
        // test table for our test dao
        $test_table_sql = 'CREATE TABLE tt_test_table(' .
            'id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,' . 
            'test_name varchar(20),' .
            'test_id int(11),' .
            'unique key test_id_idx (test_id)' .
            ')';
        $this->db->exec($test_table_sql);
        //some test data as well
        $q = sprintf("INSERT INTO tt_test_table (test_name, test_id) VALUES ('name%s', %d)", 1, 1);
        for($i = 2; $i <= 20; $i++) {
            $q .= sprintf(",('name%s', %d)", $i, $i);
        }
        $this->db->exec($q);
    }

    function tearDown() {
        parent::tearDown();
        //make sure our db_type is set to the default...
        Config::getInstance()->setValue('db_type', 'mysql');
    }

    /*
     * test fetching the proper db_type
     */
    function testDAODBType() {
        Config::getInstance()->setValue('db_type', null);
        $type = DAOFactory::getDBType();
        $this->assertEqual($type, 'mysql', 'should default to mysql');

        Config::getInstance()->setValue('db_type', 'some_sql_server');
        $type = DAOFactory::getDBType();
        $this->assertEqual($type, 'some_sql_server', 'is set to some_sql_server');
    }

    /*
     * test init DAOs, bad params and all...
     */
    function testGetTestDAO() {
        // no map for this DAO
        try {
            DAOFactory::getDAO('NoSuchDAO');
            $this->fail('should throw an exception');
        } catch(Exception $e) {
            $this->assertPattern('/No DAO mapping defined for: NoSuchDAO/', $e->getMessage(), 'no dao mapping');
        }

        // invalid db type for this dao
        Config::getInstance()->setValue('db_type', 'nodb');
        try {
            DAOFactory::getDAO('TestDAO');
            $this->fail('should throw an exception');
        } catch(Exception $e) {
            $this->assertPattern("/No db mapping defined for 'TestDAO'/", $e->getMessage(), 'no dao db_type mapping');
        }

        // valid mysql test dao
        Config::getInstance()->setValue('db_type', 'mysql');
        $test_dao = DAOFactory::getDAO('TestDAO');
        $this->assertIsA($test_dao, 'TestMysqlDAO', 'we are a mysql dao');
        $data_obj = $test_dao->selectRecord(1);
        $this->assertNotNull($data_obj);
        $this->assertEqual($data_obj->test_name, 'name1');
        $this->assertEqual($data_obj->test_id, 1);

        // valid fuax test dao
        Config::getInstance()->setValue('db_type', 'faux');
        $test_dao = DAOFactory::getDAO('TestDAO');
        $this->assertIsA($test_dao, 'TestFauxDAO', 'we are a mysql dao');
        $data_obj = $test_dao->selectRecord(1);
        $this->assertNotNull($data_obj);
        $this->assertEqual($data_obj->test_name, 'Mojo Jojo');
        $this->assertEqual($data_obj->test_id, 2001);

    }
    /**
     * Test get InstanceDAO
     */
    function testGetInstanceDAO(){
        $dao = DAOFactory::getDAO('InstanceDAO');
        $this->assertTrue(isset($dao));
        $this->assertIsA($dao, 'InstanceMySQLDAO');
    }

    /**
     * Test get FollowDAO
     */
    function testGetFollowDAO(){
        $dao = DAOFactory::getDAO('FollowDAO');
        $this->assertTrue(isset($dao));
        $this->assertIsA($dao, 'FollowMySQLDAO');
    }

    /**
     * Test get PostErrorDAO
     */
    function testGetPostErrorDAO(){
        $dao = DAOFactory::getDAO('PostErrorDAO');
        $this->assertTrue(isset($dao));
        $this->assertIsA($dao, 'PostErrorMySQLDAO');
    }
    /**
     * Test get PostDAO
     */
    function testGetPostDAO(){
        $dao = DAOFactory::getDAO('PostDAO');
        $this->assertTrue(isset($dao));
        $this->assertIsA($dao, 'PostMySQLDAO');
    }

    /**
     * Test get UserDAO
     */
    function testGetUserDAO(){
        $dao = DAOFactory::getDAO('UserDAO');
        $this->assertTrue(isset($dao));
        $this->assertIsA($dao, 'UserMySQLDAO');
    }

    /**
     * Test get UserErrorDAO
     */
    function testGetUserErrorDAO(){
        $dao = DAOFactory::getDAO('UserErrorDAO');
        $this->assertTrue(isset($dao));
        $this->assertIsA($dao, 'UserErrorMySQLDAO');
    }

    /**
     * Test get OwnerDAO
     */
    function testGetOwnerDAO(){
        $dao = DAOFactory::getDAO('OwnerDAO');
        $this->assertTrue(isset($dao));
        $this->assertIsA($dao, 'OwnerMySQLDAO');
    }

    /**
     * Test get LinkDAO
     */
    function testGetLinkDAO(){
        $dao = DAOFactory::getDAO('LinkDAO');
        $this->assertTrue(isset($dao));
        $this->assertIsA($dao, 'LinkMySQLDAO');
    }

    /**
     * Test get OwnerInstanceDAO
     */
    function testGetOwnerInstanceDAO() {
        $owner_instance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
        $this->assertNotNull($owner_instance_dao);
        $this->assertIsA($owner_instance_dao, 'OwnerInstanceMySQLDAO');
    }

    /**
     * Test get PluginDAO
     */
    function testGetPluginDAO() {
        $plugin_dao = DAOFactory::getDAO('PluginDAO');
        $this->assertNotNull($plugin_dao);
        $this->assertIsA($plugin_dao, 'PluginMySQLDAO');
    }

    /**
     * Test get PluginOptionDAO
     */
    function testGetPluginOptionDAO() {
        $plugin_dao = DAOFactory::getDAO('PluginOptionDAO');
        $this->assertNotNull($plugin_dao);
        $this->assertIsA($plugin_dao, 'PluginOptionMySQLDAO');
    }
    
    /**
     * Test get FollowerCountDAO
     */
    function testGetFollowerCountDAO() {
        $plugin_dao = DAOFactory::getDAO('FollowerCountDAO');
        $this->assertNotNull($plugin_dao);
        $this->assertIsA($plugin_dao, 'FollowerCountMySQLDAO');
    }
}