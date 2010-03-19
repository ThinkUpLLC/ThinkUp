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
        
        $this->logger = new Logger($THINKTANK_CFG['log_location']);
        $this->db = new Database($THINKTANK_CFG);
        $this->conn = $this->db->getConnection();
        
        //Create all the tables based on the build script
        $create_db_script = file_get_contents($THINKTANK_CFG['source_root_path']."sql/build-db_mysql.sql");
        $create_db_script = str_replace("ALTER DATABASE thinktank", "ALTER DATABASE thinktank_tests", $create_db_script);
        $create_statements = split(";", $create_db_script);
        foreach ($create_statements as $q) {
            if (trim($q) != '') {
                $this->db->exec($q.";");
            }
        }
        
        //Insert test data into test table
        //The default Twitter plugin is inserted by default
        
        $q = "INSERT INTO  `tt_plugins` (`name` , `folder_name` , `description` , `author` , `homepage` , `version` , `is_active` )
VALUES (  'My Test Plugin',  'testplugin',  'Proof of concept plugin',  'Gina Trapani',  'http://thinktankapp.com',  '0.01',  '0' );";
        $this->db->exec($q);
        
    }
    
    function tearDown() {
        $this->logger->close();
        
        //Delete test data
        $q = "DROP TABLE `tt_follows`, `tt_instances`, `tt_links`, `tt_owners`, `tt_owner_instances`, `tt_users`, `tt_user_errors`, `tt_plugins`, `tt_plugin_options`, `tt_posts`, `tt_post_errors`, `tt_replies`;";
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
