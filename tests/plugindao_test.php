<?php 
require_once (dirname(__FILE__).'/simpletest/autorun.php');
require_once (dirname(__FILE__).'/simpletest/web_tester.php');

require_once (dirname(__FILE__).'/config.tests.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once ("classes/class.ThinkTankTestCase.php");
require_once ("class.Plugin.php");


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
        
    }
    
    function tearDown() {
        parent::tearDown();
    }

    
    function testCreateNewPluginDAO() {
        $dao = new PluginDAO($this->db, $this->logger);
        $this->assertTrue(isset($dao->logger), "Logger set");
        $this->assertTrue(isset($dao->db), "DB set");
        
    }
    
    function testGetAllPlugins() {
        $dao = new PluginDAO($this->db, $this->logger);
        
        $plugins = $dao->getAllPlugins();
        $this->assertTrue(count($plugins) == 2);
        
        $this->assertTrue($plugins[1]->name == "My Test Plugin");
        $this->assertTrue($plugins[1]->folder_name == "testplugin");
    }
    
    function testGetActivePlugins() {
        $dao = new PluginDAO($this->db, $this->logger);
        
        $plugins = $dao->getActivePlugins();
        $this->assertTrue(count($plugins) == 1);
        $this->assertTrue($plugins[0]->name == "Twitter");
        $this->assertTrue($plugins[0]->folder_name == "twitter");
        
    }
}
