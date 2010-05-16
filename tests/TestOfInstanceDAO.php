<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Instance.php';

class TestOfInstanceDAO extends ThinkTankUnitTestCase {
    function TestOInstanceDAO() {
        $this->UnitTestCase('InstanceDAO class test');
    }

    function setUp() {
        parent::setUp();

        $q = "INSERT INTO tt_instances (`network_user_id`, `network_username`, `network`, `network_viewer_id`) VALUES (10 , 'jack', 'twitter', 10);";
        $this->db->exec($q);
    }

    function tearDown() {
        parent::tearDown();
    }

    function testGetByUserIdExists() {
        $id = new InstanceDAO($this->db, $this->logger);

        $result = $id->getByUserId(10);

        $this->assertEqual($result->network_username, 'jack');
        $this->assertEqual($result->network_user_id, 10);
        $this->assertEqual($result->network_viewer_id, 10);
    }

    function testGetByUserIdDoesNotExist() {
        $id = new InstanceDAO($this->db, $this->logger);

        $result = $id->getByUserId(11);

        $this->assertEqual($result, null);
    }

    function testGetByUsernameExists() {
        $id = new InstanceDAO($this->db, $this->logger);

        $result = $id->getByUsername('jack');

        $this->assertEqual($result->network_user_id, 10);
        $this->assertEqual($result->network_viewer_id, 10);
    }

    function testGetByUsernameDoesNotExist() {
        $id = new InstanceDAO($this->db, $this->logger);

        $result = $id->getByUsername('no one');

        $this->assertEqual($result, null);
    }

    function testInsertInstance() {
        $id = new InstanceDAO($this->db, $this->logger);

        $result = $id->insert(11, 'ev');
        $this->assertEqual($result, 2);
        $i = $id->getByUserId(11);
        $this->assertEqual($i->network_user_id, 11);
        $this->assertEqual($i->network_viewer_id, 11);
        $this->assertEqual($i->network_username, 'ev');
        $this->assertEqual($i->network, 'twitter');


        $result = $id->insert(12, 'The White House Facebook Page', 'facebook', 10);
        $this->assertEqual($result, 3);
        $i = $id->getByUserId(12);
        $this->assertEqual($i->network_user_id, 12);
        $this->assertEqual($i->network_viewer_id, 10);
        $this->assertEqual($i->network_username, 'The White House Facebook Page');
        $this->assertEqual($i->network, 'facebook');
    }
}
?>
