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
        $q = "INSERT INTO tt_channels (name, network_id, network) VALUES ('#mysavedhashtagsearch', 100, 'twitter');";
        $this->db->exec($q);

        $q = "INSERT INTO tt_channels (name, network_id, network) VALUES ('whitehouse', 200, 'facebook');";
        $this->db->exec($q);

    }

    function tearDown() {
        parent::tearDown();
    }

    function testGetExists() {
        $cdao = new ChannelDAO($this->db, $this->logger);

        $c = $cdao->get(1);

        $this->assertEqual($c->id, 1);
        $this->assertEqual($c->name, '#mysavedhashtagsearch');
        $this->assertEqual($c->network, 'twitter');
    }

    function testGetDoesntExist() {
        $cdao = new ChannelDAO($this->db, $this->logger);

        $c = $cdao->get(4);

        $this->assertEqual($c, null);
    }


    function testGetByNetworkIdExists() {
        $cdao = new ChannelDAO($this->db, $this->logger);

        $c = $cdao->getByNetworkId(100, 'twitter');

        $this->assertEqual($c->name, '#mysavedhashtagsearch');
        $this->assertEqual($c->id, 1);
        $this->assertEqual($c->network, 'twitter');
        $this->assertEqual($c->network_id, 100);
    }

    function testGetByNetworkIdDoesntExist() {
        $cdao = new ChannelDAO($this->db, $this->logger);

        $c = $cdao->getByNetworkId(300, 'twitter');

        $this->assertEqual($c, null);
    }

    function testInsert() {
        $cdao = new ChannelDAO($this->db, $this->logger);
        $newchannelid = $cdao->insert('#thatswhatshesaid', 'twitter', 0, 'htttp://search.twitter.com/?q=#thatswhatshesaid');
        $this->assertEqual(3, $newchannelid);
    }

    function testDeleteExistingChannel(){
        $cdao = new ChannelDAO($this->db, $this->logger);
        $result = $cdao->delete(100, 'twitter');
        $this->assertTrue($result);
    }

    function testDeleteNonExistentChannel(){
        $cdao = new ChannelDAO($this->db, $this->logger);
        $result = $cdao->delete(300, 'twitter');
        $this->assertTrue(!$result);
    }

}
?>
