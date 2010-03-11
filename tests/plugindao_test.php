<?php 
require_once (dirname(__FILE__).'/simpletest/autorun.php');


require_once (dirname(__FILE__).'/config.tests.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once ("class.MySQLDAO.php");
require_once ("class.Plugin.php");
require_once ("class.Database.php");
require_once ("class.Logger.php");
require_once ("class.LoggerSlowSQL.php");
require_once ("config.inc.php");


class TestOfPluginDAO extends UnitTestCase {
    var $logger;
    var $db;
    var $conn;
    
    function TestOfPluginDAO() {
        $this->UnitTestCase('PluginDAO class test');
    }
    
    function setUp() {
        global $THINKTANK_CFG;
        
        //Override default CFG values
        $THINKTANK_CFG['db_name'] = "thinktank_tests";
        
        //Build test table
        $q = "CREATE TABLE  IF NOT EXISTS `tt_plugins` (
`id` INT NOT NULL AUTO_INCREMENT,
`name` VARCHAR( 255 ) NOT NULL ,
`folder_name` VARCHAR( 255 ) NOT NULL ,
`description` VARCHAR( 255 ),
`author` VARCHAR( 255 ),
`homepage` VARCHAR( 255 ),
`version` VARCHAR( 255 ),
`is_active` TINYINT NOT NULL ,
PRIMARY KEY (  `id` )
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
		";
		
        $this->logger = new Logger($THINKTANK_CFG['log_location']);
        $this->db = new Database($THINKTANK_CFG);
        $this->conn = $this->db->getConnection();
        $this->db->exec($q);
        
        //Insert test data into test table
        $q = "INSERT INTO  `tt_plugins` ( `name` , `folder_name` , `description` , `author` , `homepage` , `version` , `is_active` ) 
VALUES ( 'Twitter',  'twitter',  'Twitter support',  'Gina Trapani',  'http://thinktankapp.com',  '0.01',  '1' );";
        $this->db->exec($q);
		
        $q = "INSERT INTO  `tt_plugins` (`name` , `folder_name` , `description` , `author` , `homepage` , `version` , `is_active` )
VALUES (  'My Test Plugin',  'testplugin',  'Proof of concept plugin',  'Gina Trapani',  'http://thinktankapp.com',  '0.01',  '0' );";
        $this->db->exec($q);
        
    }
    
    function tearDown() {
        $this->logger->close();
        
        //Delete test data
        $q = "DROP TABLE tt_plugins;";
        $this->db->exec($q);
        
        //Clean up
        $this->db->closeConnection($this->conn);
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
