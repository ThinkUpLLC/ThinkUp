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
        $q = "INSERT INTO tt_channels (keyword, network) VALUES ('#mysavedhashtagsearch', 'twitter');";
        $this->db->exec($q);
        
        $q = "INSERT INTO tt_channels (keyword, network) VALUES ('whitehouse', 'facebook');";
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

    
    function testGetByInstanceExists() {
        $icdao = new InstanceChannelDAO($this->db, $this->logger);
        
        $ic = $icdao->getByInstance(1);
        
        $this->assertEqual($ic->instance_id, 1);
        $this->assertEqual($ic->channel_id, 1);
    }
    
    function testGetByInstanceDoesntExist() {
        $icdao = new InstanceChannelDAO($this->db, $this->logger);
        
        $ic = $icdao->getByInstance(4);
        
        $this->assertEqual($ic, null);
    }
    
}
?>
