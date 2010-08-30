<?php
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

class TestOfOwnerInstanceMySQLDAO extends ThinkUpUnitTestCase {

    const TEST_TABLE_OI = 'owner_instances';
    const TEST_TABLE_I = 'instances';

    function __construct() {
        $this->UnitTestCase('OwnerInstanceMySQLDAO class test');
    }

    function setUp() {
        parent::setUp();
        $this->logger = Logger::getInstance();
        $this->config = Config::getInstance();
        $this->prefix = $this->config->getValue('table_prefix');
    }

    function tearDown() {
        parent::tearDown();
        $this->logger->close();
    }

    function testInsertOwnerInstance() {
        $dao = new OwnerInstanceMysqlDAO();
        $result = $dao->insert(10, 20, 'aaa', 'bbb');
        $this->assertTrue($result);
        $stmt = OwnerInstanceMysqlDAO::$PDO->query( "select * from " . $this->prefix . 'owner_instances' );
        $data = $stmt->fetch();
        $this->assertEqual(10, $data['owner_id'], 'we have an owner_id of: 10');
        $this->assertEqual(20, $data['instance_id'], 'we have an instance_id of: 20');
        $this->assertEqual('aaa', $data['oauth_access_token'], 'we have an oauth_access_token of: aaa');
        $this->assertEqual('bbb', $data['oauth_access_token_secret'], 'we have an oauth_access_token_secret of: bbb');
        $this->assertFalse( $stmt->fetch(), 'we have only one record' );
    }

    function testGetOAuthTokens() {

        $builder = FixtureBuilder::build(self::TEST_TABLE_OI, array('instance_id' => 20) );
        $dao = new OwnerInstanceMysqlDAO();

        // no record
        $tokens = $dao->getOAuthTokens(21);
        $this->assertNull($tokens);

        // valid record
        $tokens = $dao->getOAuthTokens(20);
        $this->assertEqual($tokens['oauth_access_token'], $builder->columns['oauth_access_token'],
        'we queried a valid oauth_access_token');
        $this->assertEqual($tokens['oauth_access_token_secret'], $builder->columns['oauth_access_token_secret'],
        'we queried a valid oauth_access_token_secret');
    }

    function testGetOwnerInstance() {

        $builder = FixtureBuilder::build(self::TEST_TABLE_OI, array('instance_id' => 20) );
        $dao = new OwnerInstanceMysqlDAO();

        // no record
        $owner_instance = $dao->get(1, 20);
        $this->assertNull($owner_instance);
        $owner_instance = $dao->get($builder->columns['owner_id'], 21);
        $this->assertNull($owner_instance);

        // valid record
        $owner_instance = $dao->get( $builder->columns['owner_id'], 20);
        $this->assertIsA($owner_instance, 'OwnerInstance');
        $columns = $builder->columns;
        $this->assertEqual($owner_instance->owner_id, $columns['owner_id'], 'valid owner id');
        $this->assertEqual($owner_instance->instance_id, $columns['instance_id'], 'valid instance id');
        $this->assertEqual($owner_instance->oauth_access_token, $columns['oauth_access_token'],
        'valid oauth_access_token');
        $this->assertEqual($owner_instance->oauth_access_token_secret, $columns['oauth_access_token_secret'],
        'valid oauth_access_token_secret');
    }

     
    function testUpdateTokens() {
        $builder_data = array('owner_id' => 2, 'instance_id' => 20);
        $builder = FixtureBuilder::build(self::TEST_TABLE_OI,  $builder_data);
        $dao = new OwnerInstanceMysqlDAO();

        // invalid instance id
        $result = $dao->updateTokens(2, 21, 'ccc', 'ddd');
        $this->assertFalse($result);

        // invalid owner id
        $result = $dao->updateTokens(3, 20, 'ccc2', 'ddd2');
        $this->assertFalse($result);

        // valid update
        $result = $dao->updateTokens(2, 20, 'ccc3', 'ddd3');
        $sql = "select * from " . $this->prefix . 'owner_instances where instance_id = 20';
        $stmt = OwnerInstanceMysqlDAO::$PDO->query($sql);
        $data = $stmt->fetch();
        $this->assertEqual($data['oauth_access_token'], 'ccc3');
        $this->assertEqual($data['oauth_access_token_secret'], 'ddd3');
    }

    function testDoesOwnerHaveAccess() {
        $oi_data = array('owner_id' => 2, 'instance_id' => 20);
        $oinstances_builder = FixtureBuilder::build(self::TEST_TABLE_OI,  $oi_data);
        $i_data = array('network_username' => 'mojojojo', 'id' => 20, 'network_user_id' =>'filler_data');
        $instances_buuilder = FixtureBuilder::build(self::TEST_TABLE_I,  $i_data);

        $dao = new OwnerInstanceMysqlDAO();

        // bad owner
        try {
            $dao->doesOwnerHaveAccess('wa', 'mojo');
            $this->fail("should throw BadArgumentException");
        } catch(BadArgumentException $e) {
            $this->assertPattern('/requires a valid/', $e->getMessage());
        }

        // no owner id
        try {
            $dao->doesOwnerHaveAccess(new Owner(), 'mojo');
            $this->fail("should throw BadArgumentException");
        } catch(BadArgumentException $e) {
            $this->assertPattern('/requires a valid/', $e->getMessage());
        }

        // no match
        $owner = new Owner(); $owner->id = 1;
        $this->assertFalse($dao->doesOwnerHaveAccess($owner, 'mojo'), 'no access');
        $owner->id = 2;
        $this->assertFalse($dao->doesOwnerHaveAccess($owner, 'mojo'), 'no access');

        // valid match
        $this->assertTrue($dao->doesOwnerHaveAccess($owner, 'mojojojo'), 'has access');

    }
}
