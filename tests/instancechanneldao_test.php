<?php
require_once (dirname(__FILE__).'/simpletest/autorun.php');
require_once (dirname(__FILE__).'/simpletest/web_tester.php');

require_once (dirname(__FILE__).'/config.tests.inc.php');

ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once ("classes/class.ThinkTankTestCase.php");
require_once ("common/class.InstanceChannel.php");

class TestOfInstanceChannelDAO extends ThinkTankUnitTestCase {
    function TestofInstanceChannelDAO() {
        $this->UnitTestCase('InstanceChannelDAO class test');
    }

    function setUp() {
        parent::setUp();
        $q = "INSERT INTO tt_channels (name, network) VALUES ('#mysavedhashtagsearch', 'twitter');";
        $this->db->exec($q);

        $q = "INSERT INTO tt_channels (name, network) VALUES ('whitehouse', 'facebook');";
        $this->db->exec($q);

        $q = "INSERT INTO tt_instance_channels (instance_id, channel_id) VALUES (1, 1);";
        $this->db->exec($q);
    }

    function tearDown() {
        parent::tearDown();
    }


    function testInsert() {
        $icdao = new InstanceChannelDAO($this->db, $this->logger);
        $newicid = $icdao->insert(1, 2);
        $this->assertEqual(2, $newicid);
    }

    function testGetExists() {
        $icdao = new InstanceChannelDAO($this->db, $this->logger);

        $ic = $icdao->get(1, 1);

        $this->assertEqual($ic->instance_id, 1);
        $this->assertEqual($ic->channel_id, 1);
    }

    function testGetDoesNotExist() {
        $icdao = new InstanceChannelDAO($this->db, $this->logger);

        $ic = $icdao->get(5, 2);

        $this->assertEqual($ic, null);
    }


    function testGetByInstanceExists() {
        $icdao = new InstanceChannelDAO($this->db, $this->logger);

        $ic = $icdao->getByInstanceAndNetwork(1, 'twitter');

        $this->assertEqual($ic[0]->instance_id, 1);
        $this->assertEqual($ic[0]->channel_id, 1);
        $this->assertEqual($ic[0]->name, "#mysavedhashtagsearch");
        $this->assertEqual($ic[0]->network, "twitter");
    }

    function testGetByInstanceDoesntExist() {
        $icdao = new InstanceChannelDAO($this->db, $this->logger);

        $ic = $icdao->getByInstanceAndNetwork(4, 'nonnetwork');

        $this->assertEqual($ic, null);
    }

    function testDeleteExistingInstanceChannel(){
        $icdao = new InstanceChannelDAO($this->db, $this->logger);

        $result = $icdao->delete(1, 1);
        $this->assertTrue($result);
    }

    function testDeleteNonExistentInstanceChannel(){
        $icdao = new InstanceChannelDAO($this->db, $this->logger);

        $result = $icdao->delete(4, 10);
        $this->assertTrue(!$result);
    }


}
?>
