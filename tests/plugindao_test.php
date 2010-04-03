<?php 
require_once (dirname(__FILE__).'/simpletest/autorun.php');
require_once (dirname(__FILE__).'/simpletest/web_tester.php');

require_once (dirname(__FILE__).'/config.tests.inc.php');


require_once ("classes/class.ThinkTankTestCase.php");
require_once ("common/class.Utils.php");
require_once ("common/class.Plugin.php");


class TestOfPluginDAO extends ThinkTankUnitTestCase {

    function TestOfPluginDAO() {
        $this->UnitTestCase('PluginDAO class test');
    }
    
    function setUp() {
        parent::setUp();
        
        //Insert test data into test table
        //The default Twitter plugin is inserted by default
        
        $q = "INSERT INTO  `tt_plugins` (`name` , `folder_name` , `description` , `author` , `homepage` , `version` , `is_active` )
VALUES (  'My Test Plugin',  'testplugin',  'Proof of concept plugin',  'Gina Trapani',  'http://thinktankapp.com',  '0.01',  '0' );";
        $this->db->exec($q);

        $q = "INSERT INTO  `tt_plugins` (`name` , `folder_name` , `description` , `author` , `homepage` , `version` , `is_active` )
VALUES (  'My Test Plugin Activated',  'testpluginact',  'Proof of concept plugin',  'Gina Trapani',  'http://thinktankapp.com',  '0.01',  '1' );";
        $this->db->exec($q);
    }
    
    function tearDown() {
        parent::tearDown();
    }

    function testGetInstalledPlugins() {
    	global $THINKTANK_CFG;
        $dao = new PluginDAO($this->db, $this->logger);
        
		$plugins = $dao->getInstalledPlugins($THINKTANK_CFG["source_root_path"]);
		
        $this->assertTrue(count($plugins) == 5);
		
        $this->assertTrue($plugins[0]->name == "Facebook");
        $this->assertTrue($plugins[0]->folder_name == "facebook");

        $this->assertTrue($plugins[1]->name == "Flickr");
        $this->assertTrue($plugins[1]->folder_name == "flickr");
        
        $this->assertTrue($plugins[2]->name == "LongURL");
        $this->assertTrue($plugins[2]->folder_name == "longurl");

    }
    
    function testCreateNewPluginDAO() {
        $dao = new PluginDAO($this->db, $this->logger);
        $this->assertTrue(isset($dao->logger), "Logger set");
        $this->assertTrue(isset($dao->db), "DB set");
        
    }

    function testIsPluginActive() {
        $dao = new PluginDAO($this->db, $this->logger);
        $this->assertTrue($dao->isPluginActive(1));
        $this->assertTrue(!$dao->isPluginActive(2));
        $this->assertTrue(!$dao->isPluginActive(15));
    }

    function testGetPluginId() {
        $dao = new PluginDAO($this->db, $this->logger);
        $this->assertTrue($dao->getPluginId('twitter')==1);
        $this->assertTrue($dao->getPluginId('idontexist')==null);
        $this->assertTrue($dao->getPluginId('testpluginact')==3);
    }
    
    function testGetAllPlugins() {
        $dao = new PluginDAO($this->db, $this->logger);
        
        $plugins = $dao->getAllPlugins();
        $this->assertTrue(count($plugins) == 3);
        
        $this->assertTrue($plugins[1]->name == "My Test Plugin");
        $this->assertTrue($plugins[1]->folder_name == "testplugin");
    }
    
    function testGetActivePlugins() {
        $dao = new PluginDAO($this->db, $this->logger);
        
        $plugins = $dao->getActivePlugins();
        $this->assertTrue(count($plugins) == 2);
        $this->assertTrue($plugins[0]->name == "Twitter");
        $this->assertTrue($plugins[0]->folder_name == "twitter");
        
    }
	
	
}
