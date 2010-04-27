<?php 
require_once (dirname(__FILE__).'/simpletest/autorun.php');
require_once (dirname(__FILE__).'/simpletest/web_tester.php');

require_once (dirname(__FILE__).'/config.tests.inc.php');

ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once ("classes/class.ThinkTankTestCase.php");
require_once ("common/class.Channel.php");

class TestOfChannelDAO extends ThinkTankUnitTestCase {
    function TestofChannelDAO() {
        $this->UnitTestCase('ChannelDAO class test');
    }
    
    function setUp() {
        parent::setUp();
        $q = "INSERT INTO tt_channels (keyword, network) VALUES ('#mysavedhashtagsearch', 'twitter');";
        $this->db->exec($q);
        
        $q = "INSERT INTO tt_channels (keyword, network) VALUES ('whitehouse', 'facebook');";
        $this->db->exec($q);
        
    }
    
    function tearDown() {
        parent::tearDown();
    }
    
    function testGetExists() {
        $cdao = new ChannelDAO($this->db, $this->logger);
        
        $c = $cdao->get(1);

        $this->assertEqual($c->id, 1);
        $this->assertEqual($c->keyword, '#mysavedhashtagsearch');
        $this->assertEqual($c->network, 'twitter');
    }
    
    function testGetDoesntExist() {
        $cdao = new ChannelDAO($this->db, $this->logger);
        
        $c = $cdao->get(4);
        
        $this->assertEqual($c, null);
    }

    
    function testGetByKeywordExists() {
        $cdao = new ChannelDAO($this->db, $this->logger);
        
        $c = $cdao->getByKeyword('#mysavedhashtagsearch', 'twitter');
        
        $this->assertEqual($c->keyword, '#mysavedhashtagsearch');
        $this->assertEqual($c->id, 1);
        $this->assertEqual($c->network, 'twitter');
    }
    
    function testGetByKeywordDoesntExist() {
        $cdao = new ChannelDAO($this->db, $this->logger);
        
        $c = $cdao->getByKeyword('#idontexist', 'twitter');
        
        $this->assertEqual($c, null);
    }
    
    function testInsert() {
        $cdao = new ChannelDAO($this->db, $this->logger);
        $newchannelid = $cdao->insert('#thatswhatshesaid', 'twitter');
        $this->assertEqual(3, $newchannelid);
    }
    
}
?>
